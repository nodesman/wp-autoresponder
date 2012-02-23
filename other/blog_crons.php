<?php

add_action("post_updated","_wpr_blog_subscription_post_updated",10,3);


function _wpr_blog_subscription_post_updated($post_id,$post_after,$post_before)
{
    global $wpdb;
    //delete all blog posts that haven't been sent that are in the queue
    $updateAllBlogPostsForReprocess = sprintf("UPDATE {$wpdb->prefix}wpr_blog_subscription 
                                                LEFT JOIN {$wpdb->prefix}wpr_queue
                                                ON {$wpdb->prefix}wpr_queue.sid={$wpdb->prefix}wpr_blog_subscription.sid
                                                SET {$wpdb->prefix}wpr_blog_subscription.pending_reprocess=1 
                                                WHERE {$wpdb->prefix}wpr_queue.meta_key LIKE 'BP-%%%%-%d' AND {$wpdb->prefix}wpr_queue.sent=0;",$post_id);
    $wpdb->query($updateAllBlogPostsForReprocess);

    $affected_rows = _wpr_delete_post_emails($post_id);
    if ($affected_rows == 0)
        return; //nothing can be done now. They're all out or none were even delivered.
    
    /*
     * NOW REVERT ALL THE CATEGORY SUBSCRIPTIONS OF CATEGORIES FROM WHICH THE POST
     * HAS BEEN REMOVED TO PROCEED FROM THE PREVIOUS BLOG POST.
     */
    
    
    //set the pending_reprocess to all subscriptions that last received that blog post
    
    
    //get all the categories to which this post was delivered
    $getCategoriesDeliveredToQuery = sprintf("SELECT DISTINCT catid FROM %swpr_blog_subscription WHERE last_published_postid=%d AND type='cat';",$wpdb->prefix, $post_id);
    $categoryIdsRes = $wpdb->get_results($getCategoriesDeliveredToQuery);
    if (0 == count($categoryIdsRes))
        return; //there are no blog category subscriptions at all.
    return;
    $deliveredCategories = array();
    foreach ($categoryIdsRes as $c) 
    {
        //too complex to test and running out of time.
        /*
         $deliveredCategories[] = $c->catid;
    }
    
    $categoriesOfPost = wp_get_post_categories($post_id);
    $categoriesCurrentlySet = array();
    foreach ($categoriesOfPost as $category)
    {
        $categoriesCurrentlySet[] = $category->term_id;
    }
    
    $deletedCategories = array_diff($deliveredCategories,$categoriesCurrentlySet);
    foreach ($deletedCategories as $catid)
    {
        //treat this post as having been deleted for the subscribers of these categories.
        */
        $category = get_category($c->catid);
        _wpr_restore_blog_category_dates($post_id, $category, strtotime($post_before->post_date_gmt));
    }
    
    
}
//handle deletion of blog post
add_action("trash_post","_wpr_blog_subscription_post_deleted",10,1);
add_action("trash_post","_wpr_blog_category_subscription_post_deleted",10,1);

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
function _wpr_blog_category_subscription_post_deleted($post_id)
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
        do_action("_wpr_restore_blog_category_subscription",$post_id);        
    }
    
}   

add_action("_wpr_ensure_deletion","wpr_blog_subscription_post_deleted",10,1);

add_action("_wpr_restore_blog_subscription","_wpr_restore_blog_subscription_dates",10,1);
add_action("_wpr_restore_blog_category_subscription","_wpr_restore_blog_category_subscription_dates",10,1);

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
        $updateQuery = sprintf("UPDATE %swpr_blog_subscription SET last_published_postid=0, last_processed_date=0, last_published_postdate=0 WHERE last_published_postid=%d AND type='all'",$wpdb->prefix,$post_id);
        $wpdb->query($updateQuery);
        return;
    }
    else
    {
        $post = $prevPost[0];
        $prevPostDate = strtotime($post->post_date_gmt);
        $updateQuery = sprintf("UPDATE %swpr_blog_subscription SET last_published_postid='%s', last_published_post_date='%s' WHERE last_published_postid=%d AND type='all';",$wpdb->prefix,$post->ID,$prevPostDate,$post_id);
        $wpdb->query($updateQuery);
        return;
    }
}


function _wpr_restore_blog_category_dates($post_id,$category, $timeStampOfLastPost)
{
    global $wpdb;
    //find the post in this category which comes after this blog post.
    $getPostAfterThisOneQuery = sprintf("SELECT * FROM %sposts p, %sterm_relationships r WHERE p.`post_type`='post' AND  p.`post_status`='publish' AND p.`post_date_gmt` > '%s' AND p.`post_password`='' AND p.ID=r.object_id AND r.term_taxonomy_id=%d ORDER BY p.`post_date_gmt` DESC LIMIT 1;",$wpdb->prefix,$wpdb->prefix,$timeStampOfLastPost,$category->term_id);
    $afterPost = $wpdb->get_results($getPostAfterThisOneQuery);

    if (0 != count($afterPost)) //this is not the latest post. in which case we do not have to fall back to the previous post's vars.
        return;

    $getPostBeforeThisOneQuery = sprintf("SELECT * FROM %sposts p, %sterm_relationships r WHERE `post_type`='post' AND  `post_status`='publish' AND `post_date_gmt` < '%s' AND `post_password`='' AND p.ID=r.object_id AND r.term_taxonomy_id=%d ORDER BY `post_date_gmt`  DESC LIMIT 1;",$wpdb->prefix,$wpdb->prefix,$timeStampOfLastPost,$category->term_id);;
    $prevPost = $wpdb->get_results($getPostBeforeThisOneQuery);

    if (count($prevPost) == 0)
    {
        $updateQuery = sprintf("UPDATE %swpr_blog_subscription SET last_published_postid=0, last_processed_date=0, last_published_postdate=0 WHERE last_published_postid=%d AND type='cat' AND catid=%d",$wpdb->prefix,$post_id,$category->term_id);
        $wpdb->query($updateQuery);
        continue;
    }
    else
    {
        $post = $prevPost[0];
        $prevPostDate = strtotime($post->post_date_gmt);
        $updateQuery = sprintf("UPDATE %swpr_blog_subscription SET last_published_postid='%s', last_published_post_date='%s' WHERE last_published_postid=%d AND type='cat' AND catid=%d",$wpdb->prefix,$post->ID,$prevPostDate,$post_id,$categories->term_id);
        $wpdb->query($updateQuery);
        continue;
    }
}

function _wpr_restore_blog_category_subscription_dates($post_id)
{
    global $wpdb;
    $post = get_post($post_id);
    $categories = wp_get_post_categories($post_id);
    $timeStampOfLastPost = $post->post_date_gmt;
    foreach ($categories as $category)
    {
        _wpr_restore_blog_category_dates($post_id, $category, $timeStampOfLastPost);
    }
}



function _wpr_delete_post_emails($post_id)
{
    global $wpdb;
    $meta_key = sprintf("BP-%%%%-%d",$post_id);
    //delete relevant delivery records    
    //delete relevant pending emails from queue
    
    
    $deleteDeliveryRecordsQuery = sprintf("DELETE FROM {$wpdb->prefix}wpr_delivery_record 
                                           USING {$wpdb->prefix}wpr_queue, {$wpdb->prefix}wpr_delivery_record
                                           WHERE {$wpdb->prefix}wpr_queue.sent=0 AND {$wpdb->prefix}wpr_queue.sid={$wpdb->prefix}wpr_delivery_record.sid
                                           AND {$wpdb->prefix}wpr_queue.meta_key LIKE 'BP-%%%%-%d'
                                         ",$post_id);
    $wpdb->query($deleteDeliveryRecordsQuery);
        
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
            else
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
        if ($subscription->pending_reprocess == 1)
        {
            $getLastPublishedPostQuery = sprintf("SELECT * FROM %sposts WHERE `post_type`='post' AND  `post_status`='publish' AND ID=%d AND `post_password`=''ORDER BY `post_date_gmt`  ASC;",$wpdb->prefix,$subscription->last_published_postid);
            $posts = $wpdb->get_results($getLastPublishedPostQuery);
            
            if (0 == count($posts))
                $posttoreturn=false;
            else
                $posttoreturn=$posts[0];
            

            $updateReprocessToZeroQuery = sprintf("UPDATE %swpr_blog_subscription SET pending_reprocess=0 WHERE id=%d",$wpdb->prefix,$subscription->id);
            $wpdb->query($updateReprocessToZeroQuery);
        }
        else
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
                                            (b.last_published_post_date< %d or
                                            b.pending_reprocess=1) AND
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
                                            (b.last_published_post_date< %d OR
                                            b.pending_reprocess=1) AND
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
            }
        }
        wp_schedule_single_event(time(), "_wpr_process_blog_subscriptions");
        update_option("_wpr_blog_post_delivery_processor_state","off");	
}


function _wpr_process_blog_category_subscriptions()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	set_time_limit(3600);
        
        //categories
        $args = array(
	'type'                     => 'post',
	'hide_empty'               => 1,
	'hierarchical'             => 0,
	'taxonomy'                 => 'category');
        $categories = get_categories($args);
        
        
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
        
        foreach ($categories as $category)
        {
            //now process the people who subscribe to the blog
            $timeStampForNow  = date("Y-m-d H:i:s");
            $getLatestPostQuery = sprintf("SELECT a.* FROM %sposts a, %sterm_relationships b WHERE a.ID=b.`object_id` AND b.`term_taxonomy_id`=%d AND a.`post_type`='post' AND  a.`post_status`='publish' AND a.`post_date_gmt` < '%s' AND a.post_password='' ORDER BY a.`post_date_gmt` DESC LIMIT 1;",$wpdb->prefix,$wpdb->prefix, $category->term_id,$timeStampForNow);
            $latestBlogPostResult = $wpdb->get_results($getLatestPostQuery);

            if (0 == count($latestBlogPostResult)) //there aren't any blog posts at all.
                continue;
            //find the latest published, non-password protected, past dated blog post

            $latestBlogPostTimestamp = strtotime($latestBlogPostResult[0]->post_date_gmt);


            $getNumberOfSubscriptions = sprintf("SELECT COUNT(*) number FROM %swpr_blog_subscription b,
                                                %swpr_subscribers s
                                                WHERE b.type='cat' AND 
                                                b.catid=%d AND
                                                (b.last_published_post_date< %d OR
                                                 b.pending_reprocess=1) AND
                                                s.id=b.sid AND
                                                s.active=1 AND
                                                s.confirmed=1;",
                                                $wpdb->prefix,
                                                $wpdb->prefix,
                                                $category->term_id,
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
                                                WHERE b.type='cat' AND 
                                                b.catid=%d AND
                                                (b.last_published_post_date< %d OR
                                                 b.pending_reprocess=1) AND
                                                s.id=b.sid AND
                                                s.active=1 AND
                                                s.confirmed=1 ORDER BY last_published_post_date ASC, last_processed_date ASC LIMIT %d;",
                                                $wpdb->prefix,
                                                $wpdb->prefix,
                                                $category->term_id,
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
                }
            }
        }
        update_option("_wpr_blog_category_delivery_processor_state","off");
}

function _wpr_blog_subscription_get_category_post_to_deliver($subscription)
{
   global $wpdb;
    $cache = &$GLOBALS['_wpr_blog_category_subscription_get_post_to_deliver'];
    $category = $subscription->catid;
    
    $afterDayTimestamp = get_option("_wpr_NEWAGE_activation"); 
    $timeStampForActivationDate  = date("Y-m-d H:i:s",$afterDayTimestamp);

    $timeOfLastPost = $subscription->last_published_post_date;
    $timeStampOfLastPost = date("Y-m-d H:i:s",$timeOfLastPost);
    $timeStampForNow  = date("Y-m-d H:i:s");
    
    
    if ($subscription->pending_reprocess == 1)
    {
        $getLastPublishedPostQuery = sprintf("SELECT * FROM %sposts WHERE `post_type`='post' AND  `post_status`='publish' AND ID=%d AND `post_password`=''ORDER BY `post_date_gmt`  ASC;",$wpdb->prefix,$subscription->last_published_postid);
        $posts = $wpdb->get_results($getLastPublishedPostQuery);

        if (0 == count($posts))
            $posttoreturn=false;
        else
            $posttoreturn=$posts[0];
    
        $updateReprocessToZeroQuery = sprintf("UPDATE %swpr_blog_subscription SET pending_reprocess=0 WHERE id=%d",$wpdb->prefix,$subscription->id);
        $wpdb->query($updateReprocessToZeroQuery);
    }
    else
    {
    
        if ($subscription->last_published_post_date == 0)
            $getPostsSinceThisDateQuery = sprintf("SELECT * FROM %sposts p, %sterm_relationships r WHERE p.ID=r.object_id AND r.term_taxonomy_id=%d AND p.`post_type`='post' AND  p.`post_status`='publish' AND p.`post_date_gmt` > '%s' AND p.`post_password`='' ORDER BY p.`post_date_gmt`  ASC;",$wpdb->prefix,$wpdb->prefix,$category,$timeStampForActivationDate);
        else
            $getPostsSinceThisDateQuery = sprintf("SELECT * FROM %sposts p, %sterm_relationships r WHERE p.ID=r.object_id AND r.term_taxonomy_id=%d AND p.`post_type`='post' AND  p.`post_status`='publish' AND p.`post_date_gmt` > '%s' AND p.`post_date_gmt` > '%s' AND p.`post_date_gmt` < '%s' AND p.`post_password`='' ORDER BY p.`post_date_gmt`  ASC;",$wpdb->prefix,$wpdb->prefix,$category,$timeStampForActivationDate,$timeStampOfLastPost, $timeStampForNow);
        $posts = $wpdb->get_results($getPostsSinceThisDateQuery);
        $posttoreturn = false;
        //sometimes this post may have been delivered by the blog category subscription
        foreach ($posts as $p)
        {
            $checkWhetherInDelvieryRecordQuery = sprintf("SELECT COUNT(*) num FROM `%swpr_delivery_record` WHERE eid=%d AND type='blog_post' AND sid=%d",$wpdb->prefix,$p->ID,$subscription->sid);
            $whetherDeliveredResults = $wpdb->get_results($checkWhetherInDelvieryRecordQuery);
            $num = $whetherDeliveredResults[0]->num;
            if ($num == 0)
            {
                $posttoreturn = $p;
                break;
            }
        }
    }
    return $posttoreturn;
}

function deliverBlogPost($sid,$post_id,$footerMessage="",$checkCondition=false,$whetherPostSeries=false,$additionalParams=array())
{
    global $wpdb;
    //get the post meta
    $sid = (int) $sid;
    $post_id = (int) $post_id;
    if ($sid == 0 || $post_id==0) // neither of these can be zero or empty.
        return;
    $post = get_post($post_id);
    //if plugin was activated after some posts were created

    //the options array will not exist. in that case, we just

    //deliver the blog post

    $optionsList = get_post_meta($post_id,"wpr-options",true);    
    if (!empty($optionsList))
    {
            $decoded = base64_decode($optionsList);
            $options = unserialize($decoded);
            $checkCondition = true; //if we have a valid options array, then we should
            //check the conditions of delivery.
    }
    else
    {
            $checkCondition=false;
    }
    
    $query = "SELECT nid from ".$wpdb->prefix."wpr_subscribers where id=".$sid;
    $results = $wpdb->get_results($query);
    $nid = $results[0]->nid;
    if (count($results) == 0) //if there is no subscriber by that sid
        return;

    
    $deliverFlag = true; // this flag is used to trigger the delivery
    if ($checkCondition == true)
    {
       //get the subscriber's newsletter id
        if (isset($options[$nid]))
        {
            if ($options[$nid]['disable']==1)
                {
                    $deliverFlag = false;
                }
        }
        else
            $deliverFlag=true;
   }
   else
       {
       $deliverFlag = true;
   }
   
	   if (isset($additionalParams['meta_key']))
	   {
			$meta_key =  $additionalParams['meta_key'];
	   }
	   else
	   {
			$meta_key = sprintf("BP-%s-%s",$sid,$post_id);
	   }

   //deliver the email.
   if ($deliverFlag)
       {
        //are customizations disabled? then get the html body for the blog post
        //from the default layout format.
       //check if the subscriber is currently receiving any follow up series emails
       if (isset($options) && $options[$nid]['skipactivesubscribers']==1 && isReceivingFollowupPosts($sid))
           return;

       /*
        * The conditions where the default layout is used are:
        * the customization has been disabled,
        * the customization has been disabled for post series
        * there is no customization information - the post was created when
        * wp responder was not installed/deactivated.
        */
		

       if (!isset($options[$nid]) || $options[$nid]['nocustomization']==1 || !isValidOptionsArray($options) || ($whetherPostSeries == true && $options[$nid]['nopostseries']==1))
           {
            $htmlbody = getBlogContentInDefaultLayout($post_id);
            $post = get_post($post_id);
            $subject = $post->post_title;
            $params = array("subject"=>$subject,
                            "htmlbody"=>$htmlbody,
                            "textbody"=>"",
                            "htmlenabled"=>1,
                            "attachimages"=>true,
							'meta_key'=> $meta_key,
							);
       }
       else
       {
		     $htmlBody = $options[$nid]['htmlbody'].nl2br($footerMessage);
			 
			 $htmlEnabled = ($options[$nid]['htmlenable']==1)?1:0;
			 if (!$htmlEnabled)
			 	$htmlBody="";
				
             $params = array("subject"=>$options[$nid]['subject'],
                            "htmlbody"=>$htmlBody,
                            "textbody"=>$options[$nid]['textbody'].strip_tags("$footerMessage"),
                            "attachimages"=>($options[$nid]['attachimages'])?1:0,
                            "htmlenabled"=> $htmlEnabled,
							'meta_key'=> $meta_key
                 );

       }

       $params['subject'] = substitutePostRelatedShortcodes($params['subject'],$post_id);
       $params['htmlbody'] = substitutePostRelatedShortcodes($params['htmlbody'],$post_id);
       $params['textbody'] = substitutePostRelatedShortcodes($params['textbody'],$post_id);
	   
	   //substitute newsletter related parameters.
	   
       wpr_place_tags($sid,$params);
       sendmail($sid,$params);

       $insertDeliveryRecordQuery = sprintf("INSERT INTO `%swpr_delivery_record` (sid, type, eid, timestamp)
                                            VALUES
                                            (%d,'blog_post',%d,'%s')
                                            ",
                                             $wpdb->prefix,
                                             $sid,
                                             $post_id,
                                             time()
                                            );
        $wpdb->query($insertDeliveryRecordQuery);

   }

}

//TODO: The caption shortcode is replaced with class="alignleft alignright" and such tags
//Right now most email clients just rip the class="" declarations. Layout breaks.
//These class attributes should be detected and replaced with the appropriate inline style="" tags.
//I'll do this on some day I need something better to do than gnaw my leg off.

function substitutePostRelatedShortcodes($text,$post_id)
{

    //the post's url
	
	
    $postUrl = get_permalink($post_id);
    $text = str_replace("[!post_url!]",$postUrl,$text);
	
    //teh post's delivery date
    //which is time right now.
    $time = date("g:iA d F Y ",time());
    $time .= date_default_timezone_get();
    $text = str_replace("[!delivery_date!]",$time,$text);
    $text = do_shortcode($text);
    //post publishing date
    $post = get_post($post_id);
    $postDate = $post->post_date;
    $postEpoch = strtotime($postDate);
    $postDate = date("dS, F Y",$postEpoch);
    $text = str_replace("[!post_date!] ",$postDate,$text);
   
    return $text;
    
}

/*
 * This function checks if the post $pid is to be skipped from being delivered to
 * subscribers of newsletter $nid.
 */

function whetherToSkipThisPost($nid,$pid)
{
    $theoptions = get_post_meta($pid,'wpr-options',true);
    $options = unserialize($theoptions);
    if (!isset($options))
        return 0;
    //by default, the skip is disabled.
    if ($options[$nid]['disable']==1)
        {
           return 1;
    }
    else
        return 0;
}

/*
 * This function is used to generate a body for the blog post sent via email
 * when the user doesn't customize it or chooses to use the default layout
 *
 * This function is also used when the post doesn't have any WP Responder options
 * associated with it.
 * Returns string with the HTML to be used for the email
 *
 */

function getBlogContentInDefaultLayout($post_id)
{
    $post = get_post($post_id);
    $content = '<div style="background-color:  #dfdfdf;padding: 5px;"><span style="font-size: 9px; font-family: Arial; text-align:center;\">You are receiving this email because you are subscribed to new posts at ';
    $content .= "<a href=\"".get_bloginfo("home")."\">".get_bloginfo("name")."</a></span></div>";

    $content .= "<h1><a href=\"".get_permalink($post_id)."\" style=\"font-size:22px; font-family: Arial, Verdana; text-decoration: none; color: #333399\">";
  $content .= $post->post_title;
  $content .= "</a></h1>";
    $content .= '<p style="font-family: Arial; font-size: 10px;">Dated: '.date("d F,Y",strtotime($post->post_date));
    $post->content = apply_filters("the_content",$post->post_content);
    $content .= "</p><p><span style=\"font-family: Arial, Verdana; font-size: 12px\">".wptexturize(wpautop(nl2br($post->post_content)))."</span>";

    $content .= "<br><br><span style=\"font-size: 12px; font-family: Arial\"><a href=\"".get_permalink($post_id)."\">Click here</a> to read this post at <a href=\"".get_bloginfo("home")."\">".get_bloginfo("name")."</a></div>.";
    return $content;
}
