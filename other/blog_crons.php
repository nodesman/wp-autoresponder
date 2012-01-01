<?php


//handle deletion of blog post
add_action("trash_post","_wpr_blog_subscription_post_deleted",10,1);
//TODO: The blog subscriptions that were processed and updated with this blog post should be set to the blog post before this blog post for integrity.
//select the post ids uniquely, select the one that is before this blog post and update all to that blog post's info. this should be done in the bg.
//bleep me. this sucks so bad.
function _wpr_blog_subscription_post_deleted($post_id)
{
    global $wpdb;
    $post_id = intval($post_id);
    //find all those subscribers

    $affected_rows = _wpr_delete_post_emails($post_id);
    
    //there are chances that the background process is running as this code is being executed. 
    
    //so we schedule a deletion after one minute.
    
    if ($affected_rows != 0)
    {
        $nextRunTime = time()+60;
        wp_schedule_single_event($nextRunTime, "_wpr_ensure_deletion", array($post_id));
        do_action("_wpr_restore_blog_subscription",$post_id);        
    }
    
}   

add_action("_wpr_ensure_deletion","wpr_blog_subscription_post_deleted",10,1);

add_action("_wpr_restore_blog_subscription","_wpr_restore_blog_subscription_dates",10,1);

function _wpr_restore_blog_subscription_dates($post_id)
{
    global $wpdb;
    $post = get_post($post_id);
    $timeStampOfLastPost = $post->post_date_gmt;
    $getPostAfterThisOneQuery = sprintf("SELECT * FROM %sposts WHERE `post_type`='post' AND  `post_status`='publish' AND `post_date_gmt` > '%s' AND `post_password`=''ORDER BY `post_date_gmt` DESC LIMIT 1;",$wpdb->prefix,$timeStampOfLastPost);
    $afterPost = $wpdb->get_results($getPostAfterThisOneQuery);
    
    if (0 != count($afterPost)) //this is not the latest post. in which case we do not have to fall back to the previous post's vars.
        return;
    

    $getPostBeforeThisOneQuery = sprintf("SELECT * FROM %sposts WHERE `post_type`='post' AND  `post_status`='publish' AND `post_date_gmt` < '%s' AND `post_password`=''ORDER BY `post_date_gmt`  DESC LIMIT 1;",$wpdb->prefix,$timeStampOfLastPost);
    $prevPost = $wpdb->get_results($getPostBeforeThisOneQuery);
    
    if (count($prevPost) == 0)
    {
        $updateQuery = sprintf("UPDATE %swpr_blog_subscription SET last_published_postid=0, last_processed_date=0, last_published_postdate=0 WHERE last_published_postid=%d",$wpdb->prefix,$post_id);
        $wpdb->query($updateQuery);
        return;
    }
    else
    {
        $post = $prevPost[0];
        $prevPostDate = strtotime($post->post_date_gmt);
        $updateQuery = sprintf("UPDATE %swpr_blog_subscription SET last_published_postid='%s', last_published_post_date='%s' WHERE last_published_postid=%d",$wpdb->prefix,$post->ID,$prevPostDate,$post_id);
        $wpdb->query($updateQuery);
        return;
    }
}

function _wpr_delete_post_emails($post_id)
{
    global $wpdb;
    $meta_key = sprintf("BP-%%%%-%d",$post_id);
    //delete relevant delivery records    
    //delete relevant pending emails from queue
    $deleteEmailsPendingDeliveryQuery = sprintf("DELETE FROM %swpr_queue WHERE meta_key LIKE '%s' AND sent=0",
            $wpdb->prefix,$meta_key);
    $rowsAffected = $wpdb->query($deleteEmailsPendingDeliveryQuery);
    return $rowsAffected;
}

//cache of variables that will be used in _wpr_blog_subscription_get_post_to_deliver()
$GLOBALS['_wpr_blog_subscription_process_vars'] = array();

function _wpr_blog_subscription_get_post_to_deliver($subscription)
{
    global $wpdb;
    $cache = &$GLOBALS['_wpr_blog_subscription_get_post_to_deliver'];
    if ($subscription->last_published_postid == 0)
    {
        //get the latest post
        $latestPost = false;
        if (!isset($cache['latest_post']))
        {
            $latestPost = $cache['latest_post'];
            $args = array(
                'numberposts'     => 1,
                'offset'          => 0,
                'orderby'         => 'post_date',
                'order'           => 'DESC',
                'post_type'       => 'post',
                'post_status'     => 'publish' );
            $posts = get_posts($args);
            if (0 == count($posts))
                $latestPost = false;
            //latest post
            $latestPost = $cache['latest_post'] = $posts[0];
            
        }
        else
        {
            $latestPost = $cache['latest_post'];
        }
        
        if ($latestPost === false)
            return false;
        $post_id = $latestPost->ID;
        
        $sentPosts = get_option("wpr_sent_posts");
        $sentPostsList = explode(",",$sentPosts);
        
        foreach ($sentPostsList as $index=>$postid)
        {
            $thePostId = intval($postid);
            if ($thePostId == $post_id) //this post has already been delivered.
                return false;
        }
        return $latestPost;
    }
    else //this subscription has been ported to the new format. now find a newer post to deliver.
    {
        $timeOfLastPost = $subscription->last_published_post_date;
        $timeStampOfLastPost = date("Y-m-d H:i:s",$timeOfLastPost);
        $timeStampForNow  = date("Y-m-d H:i:s");
        $getPostsSinceThisDateQuery = sprintf("SELECT * FROM %sposts WHERE `post_type`='post' AND  `post_status`='publish' AND `post_date_gmt` > '%s' AND `post_date_gmt` < '%s' AND `post_password`=''ORDER BY `post_date_gmt`  ASC;",$wpdb->prefix,$timeStampOfLastPost, $timeStampForNow);
	$posts = $wpdb->get_results($getPostsSinceThisDateQuery);
        $posttoreturn = false;
        //sometimes this post may have been delivered by the blog category subscription
        foreach ($posts as $p)
        {
            $checkWhetherInDelvieryRecordQuery = sprintf("SELECT COUNT(*) num FROM `%swpr_delivery_record` WHERE eid=%d AND type='blog_post' AND sid=%d",$wpdb->prefix,$p->ID,$subscription->sid);
            $whetherDeliveredResults = $wpdb->query($checkWhetherInDelvieryRecordQuery);
            $num = $whetherDeliveredResults[0]->num;
            if ($num == 0)
            {
                $posttoreturn = $p;
                break;
                
            }
        }
        
        if ($posttoreturn != false)
            return $posttoreturn;
        else
            return false;
    }
}

function _wpr_process_blog_subscriptions()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	set_time_limit(3600);
        
        //ENSURE THAT ANOTHER INSTANCE OF THIS PROCESS DOESN'T START
        
        $whetherRunning = get_option("_wpr_blog_post_delivery_processor_state");
        $timeNow = time();
        $timeStamp = intval($whetherRunning);
        $timeSinceStart = $timeNow-$timeStamp;
        if ($timeSinceStart < 3600 && $whetherRunning != "off")
        {
            echo "Another process is running.";
            exit;
        }
        
        update_option("_wpr_blog_post_delivery_processor_state",$timeNow);
        //END ENSURING THAT ANOTHER INSTANCE DOESN'T START
        
	//now process the people who subscribe to the blog
        $timeStampForNow  = date("Y-m-d H:i:s");
        $getLatestPostQuery = sprintf("SELECT * FROM %sposts WHERE `post_type`='post' AND  `post_status`='publish' AND `post_date_gmt` < '%s' AND post_password='' ORDER BY `post_date_gmt` DESC LIMIT 1;",$wpdb->prefix,$timeStampForNow);
        $latestBlogPostResult = $wpdb->get_results($getLatestPostQuery);
        
        if (0 == count($latestBlogPostResult)) //there aren't any blog posts at all.
            return;
        //find the latest published, non-password protected, past dated blog post
        
        $latestBlogPostTimestamp = strtotime($latestBlogPostResult[0]->post_date_gmt);
        
        
        $getNumberOfSubscriptions = sprintf("SELECT COUNT(*) number FROM %swpr_blog_subscription b,
                                            %swpr_subscribers s
                                            WHERE b.type='all' AND 
                                            b.last_published_post_date< %d AND
                                            s.id=b.sid AND
                                            s.active=1 AND
                                            s.confirmed=1;",
                                            $wpdb->prefix,
                                            $wpdb->prefix,
                                            $latestBlogPostTimestamp 
                                            );
        $getCountRes = $wpdb->get_results($getNumberOfSubscriptions);
        $number = $getCountRes[0]->number;
        
        $perIteration = intval(WPR_MAX_BLOG_SUBSCRIPTION_PROCESSED_PER_ITERATION);
        $perIteration = (0 == $perIteration)?100:$perIteration;
        
        $numberOfIterations = ceil($number/$perIteration);
        for ($iter=0;$iter<$numberOfIterations;$iter++)
        {
            $getSubscriptionsQuery = sprintf("SELECT b.* number FROM %swpr_blog_subscription b,
                                            %swpr_subscribers s
                                            WHERE b.type='all' AND 
                                            b.last_published_post_date< %d AND
                                            s.id=b.sid AND
                                            s.active=1 AND
                                            s.confirmed=1 ORDER BY last_published_post_date ASC, last_processed_date ASC LIMIT %d;",
                                            $wpdb->prefix,
                                            $wpdb->prefix,
                                            $latestBlogPostTimestamp,
                                            $perIteration
                                            );
            $blogSubscriptionsToProcess = $wpdb->get_results($getSubscriptionsQuery);
            foreach ($blogSubscriptionsToProcess as $subscription)
            {
                //the first thing to do: update last processed date.
                $updateLastProcessedDateQuery = sprintf("UPDATE `%swpr_blog_subscription` SET `last_processed_date`=%d WHERE  id=%d",$wpdb->prefix,time(), $subscription->id);
                $wpdb->query($updateLastProcessedDateQuery);
                
                $post = _wpr_blog_subscription_get_post_to_deliver($subscription);
                if ($post == false)
                    continue;
                $footerMessage = sprintf(__("You are receiving this email because you are subscribed to articles published at <a href=\"%s\">%s</a>"),$blogURL,$blogName);
                $postId = (int) $post->ID;
                if ($postId == 0)
                    continue;
                deliverBlogPost($subscription->sid,$postId,$footerMessage);
                $publishTime = strtotime($post->post_date_gmt);
                
                //update the subscription to this post
                $updateSubscriptionQuery = sprintf("UPDATE `%swpr_blog_subscription`        
                                                    SET `last_published_postid`=%d, 
                                                    `last_published_post_date`='%s' 
                                                    WHERE id=%d",
                                                    $wpdb->prefix,
                                                    $postId,
                                                    $publishTime,
                                                    $subscription->id
                        );
                
                $wpdb->query($updateSubscriptionQuery);
                
                //add a delivery record
                $insertDeliveryRecordQuery = sprintf("INSERT INTO `%swpr_delivery_record` (sid, type, eid, timestamp)
                                            VALUES
                                            (%d,'blog_post',%d,'%s')
                                            ",
                                             $wpdb->prefix,
                                             $subscription->sid,
                                             $postId,
                                             time()
                                            );
                $wpdb->query($insertDeliveryRecordQuery);
            }
        }
        wp_schedule_single_event(time(), "_wpr_process_blog_subscriptions");
        update_option("_wpr_blog_post_delivery_processor_state","off");	
}




/*
function _wpr_process_blog_category_subscriptions()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	set_time_limit(3600);
        
        //ENSURE THAT ANOTHER INSTANCE OF THIS PROCESS DOESN'T START
        
        $whetherRunning = get_option("_wpr_blog_category_delivery_processor_state");
        $timeNow = time();
        $timeStamp = intval($whetherRunning);
        $timeSinceStart = $timeNow-$timeStamp;
        if ($timeSinceStart < 3600 && $whetherRunning != "off")
        {
            echo "Another process is running.";
            exit;
        }
        
        update_option("_wpr_blog_category_delivery_processor_state",$timeNow);
        //END ENSURING THAT ANOTHER INSTANCE DOESN'T START
        
	//now process the people who subscribe to the blog
        $timeStampForNow  = date("Y-m-d H:i:s");
        $getLatestPostQuery = sprintf("SELECT * FROM %sposts WHERE `post_type`='post' AND  `post_status`='publish' AND `post_date_gmt` < '%s' AND post_password='' ORDER BY `post_date_gmt` DESC LIMIT 1;",$wpdb->prefix,$timeStampForNow);
        $latestBlogPostResult = $wpdb->get_results($getLatestPostQuery);
        
        if (0 == count($latestBlogPostResult)) //there aren't any blog posts at all.
            return;
        //find the latest published, non-password protected, past dated blog post
        
        $latestBlogPostTimestamp = strtotime($latestBlogPostResult[0]->post_date_gmt);
        
        
        $getNumberOfSubscriptions = sprintf("SELECT COUNT(*) number FROM %swpr_blog_subscription b,
                                            %swpr_subscribers s
                                            WHERE b.type='all' AND 
                                            b.last_published_post_date< %d AND
                                            s.id=b.sid AND
                                            s.active=1 AND
                                            s.confirmed=1;",
                                            $wpdb->prefix,
                                            $wpdb->prefix,
                                            $latestBlogPostTimestamp 
                                            );
        $getCountRes = $wpdb->get_results($getNumberOfSubscriptions);
        $number = $getCountRes[0]->number;
        
        $perIteration = intval(WPR_MAX_BLOG_SUBSCRIPTION_PROCESSED_PER_ITERATION);
        $perIteration = (0 == $perIteration)?100:$perIteration;
        
        $numberOfIterations = ceil($number/$perIteration);
        for ($iter=0;$iter<$numberOfIterations;$iter++)
        {
            $getSubscriptionsQuery = sprintf("SELECT b.* number FROM %swpr_blog_subscription b,
                                            %swpr_subscribers s
                                            WHERE b.type='all' AND 
                                            b.last_published_post_date< %d AND
                                            s.id=b.sid AND
                                            s.active=1 AND
                                            s.confirmed=1 ORDER BY last_published_post_date ASC, last_processed_date ASC LIMIT %d;",
                                            $wpdb->prefix,
                                            $wpdb->prefix,
                                            $latestBlogPostTimestamp,
                                            $perIteration
                                            );
            $blogSubscriptionsToProcess = $wpdb->get_results($getSubscriptionsQuery);
            foreach ($blogSubscriptionsToProcess as $subscription)
            {
                //the first thing to do: update last processed date.
                $updateLastProcessedDateQuery = sprintf("UPDATE `%swpr_blog_subscription` SET `last_processed_date`=%d WHERE  id=%d",$wpdb->prefix,time(), $subscription->id);
                $wpdb->query($updateLastProcessedDateQuery);
                
                $post = _wpr_blog_subscription_get_category_post_to_deliver($subscription);
                if ($post == false)
                    continue;
                $footerMessage = sprintf(__("You are receiving this email because you are subscribed to articles published at <a href=\"%s\">%s</a>"),$blogURL,$blogName);
                $postId = (int) $post->ID;
                if ($postId == 0)
                    continue;
                deliverBlogPost($subscription->sid,$postId,$footerMessage);
                $publishTime = strtotime($post->post_date_gmt);
                
                //update the subscription to this post
                $updateSubscriptionQuery = sprintf("UPDATE `%swpr_blog_subscription`        
                                                    SET `last_published_postid`=%d, 
                                                    `last_published_post_date`='%s' 
                                                    WHERE id=%d",
                                                    $wpdb->prefix,
                                                    $postId,
                                                    $publishTime,
                                                    $subscription->id
                        );
                
                $wpdb->query($updateSubscriptionQuery);
                
                //add a delivery record
                $insertDeliveryRecordQuery = sprintf("INSERT INTO `%swpr_delivery_record` (sid, type, eid, timestamp)
                                            VALUES
                                            (%d,'blog_post',%d,'%s')
                                            ",
                                             $wpdb->prefix,
                                             $subscription->sid,
                                             $postId,
                                             time()
                                            );
                $wpdb->query($insertDeliveryRecordQuery);
            }
        }
        wp_schedule_single_event(time(), "_wpr_process_blog_subscriptions");
        update_option("_wpr_blog_category_delivery_processor_state","off");	

 * }
 */
