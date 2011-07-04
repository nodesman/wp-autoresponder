<?php

function getNumberOfEmailsToDeliver()
{
	$currentTime = time();	
	$hourly_limit = get_option("wpr_hourlylimit");

	if ($hourly_limit == 0)
		return WPR_MAX_QUEUE_EMAILS_SENT_PER_MINUTE;
		
	$last_reset_time = get_option("_wpr_dq_last_reset_time");
	if (empty($last_reset_time)) // first run
	{
                $first_reset_time = mktime(date("H"),0,0);
		update_option("_wpr_dq_last_reset_time",$first_reset_time);
		$emails_sent_this_hour = 0;
		update_option("_wpr_dq_emails_sent_this_hour",$emails_sent_this_hour);
	}
	
	//reseting emails sent this hour value
	if ($currentTime > ($last_reset_time+3600))
	{
		update_option("_wpr_dq_last_reset_time",$currentTime);
		update_option("_wpr_dq_emails_sent_this_hour",0);
	}
	
	$number_of_emails_delivered_this_hour = get_option("_wpr_dq_emails_sent_this_hour");

        if ($number_of_emails_delivered_this_hour >= $hourly_limit)
            return 0;

	$emails_to_deliver_per_minute = $hourly_limit/60;
	$minute_since_last_start_of_hour = intval(date("i"));
	$number_of_emails_should_have_delivered = floor($minute_since_last_start_of_hour*$emails_to_deliver_per_minute);

        if ($number_of_emails_delivered_this_hour > $number_of_emails_should_have_delivered)
                return 0;

        //the number of emails to deliver ideally....
	$numberOfEmailsToDeliverThisMinute = $number_of_emails_should_have_delivered - $number_of_emails_delivered_this_hour;

        //in case we are closer than usual to the houly limit..
        if ($numberOfEmailsToDeliverThisMinute+$number_of_emails_delivered_this_hour > $hourly_limit)
        {
            $numberOfEmailsToDeliverThisMinute = $hourly_limit-$number_of_emails_delivered_this_hour;
        }
	
	if ($numberOfEmailsToDeliverThisMinute > WPR_MAX_QUEUE_EMAILS_SENT_PER_MINUTE)
		return WPR_MAX_QUEUE_EMAILS_SENT_PER_MINUTE;
	
	return $numberOfEmailsToDeliverThisMinute;
}

function _wpr_process_queue()
{
	global $wpdb;

	/*ENSURING THERE IS ONLY ONE INSTANCE THAT RUNS FOR A MAXIMUM OF ONE HOUR START HERE*/
	
	$last_cron_status = get_option("_wpr_queue_delivery_status");

        /*
	When the cron is running the _wpr_queue_delivery_status
	is set to the timestamp at which the cron processing was started.
	
	Before shutting down the _wpr_queue_delivery_status is
	set to 'stopped'.
	
	This cron will run only if the _wpr_queue_delivery_status option
	is set to "stopped" or is empty.
	*/
	$timeOfStart = time();
	$timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_QUEUE_DELIVERY_EXECUTION_TIME;
	if (!empty($last_cron_status) && $last_cron_status != "stopped")
	{
		$last_cron_status = intval($last_cron_status);
		if ($last_cron_status !=0 && ($last_cron_status > $timeMaximumExecutionTimeAgo))
		{
			return;
		}
	}	
	update_option("_wpr_queue_delivery_status",$timeOfStart);
	
	$numberOfEmailsToDeliver = getNumberOfEmailsToDeliver();
	$limitClause = " LIMIT $numberOfEmailsToDeliver";
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_queue where sent=0 $limitClause ";
	$results = $wpdb->get_results($query);
	foreach ($results as $mail)  
	{
		$mail = (array) $mail;	
		
		
		try {
			dispatchEmail($mail);
		}
		catch (Swift_RfcComplianceException $exception) //invalidly formatted email.
		{
			//disable all subscribers with that email.
			$email = $mail['to'];			
			$query = "UPDATE ".$wpdb->prefix."wpr_subscribers set active=3, confirmed=0 where email='$email'";
			$wpdb->query($query);
		}
		$query = "UPDATE ".$wpdb->prefix."wpr_queue set sent=1 where id=".$mail['id'];
		$wpdb->query($query);

                $timeThisInstant = time();

                $timeSinceStart = $timeThisInstant-$timeOfStart;
                if ($timeSinceStart > WPR_MAX_QUEUE_DELIVERY_EXECUTION_TIME)
                    return;

	}
	
	//WE JUST FINISHED
	update_option("_wpr_queue_delivery_status","stopped");
}

/*
 *
 * This is the function that performs the autoresponder subscription processing
 */

function _wpr_autoresponder_process($id=0)
{
	global $wpdb;


        $id = intval($id);
        if ($id ==0)
            $send_immediately=false;
        else
        {
            $send_immediately = true;
            $subscriberClause = "AND b.id=$id";
        }




	$last_cron_status = get_option("_wpr_autoresponder_process_status");
	/*
	When the cron is running the _wpr_autoresponder_process_status
	is set to the timestamp at which the cron processing was started.
	
	Before shutting down the _wpr_autoresponder_process_status is
	set to 'stopped'.
	
	This cron will run only if the _wpr_autoresponder_process_status option
	is set to "stopped" or is empty.
	*/
	
	$timeOfStart = time();
	$timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_AUTORESPONDER_PROCESS_EXECUTION_TIME;
	if (!empty($last_cron_status) && $last_cron_status != "stopped")
	{
		$last_cron_status = intval($last_cron_status);
		if ($last_cron_status !=0 && ($last_cron_status > $timeMaximumExecutionTimeAgo))
		{
			return;
		}
	}
	
	delete_option("_wpr_autoresponder_process_status");
	add_option("_wpr_autoresponder_process_status",$timeOfStart);
	
	$currentTime = time();
        $prefix = $wpdb->prefix;





	$getActiveFollowupSubscriptionsQuery = "SELECT a.*, b.id sid, FLOOR(($currentTime - a.doc)/86400) daysSinceSubscribing FROM `".$prefix."wpr_followup_subscriptions` a, `".$prefix."wpr_subscribers` b  WHERE a.type='autoresponder' AND  a.sequence < FLOOR(($currentTime - a.doc)/86400) AND a.sequence <> -2 AND a.sid=b.id $subscriberClause AND b.active=1 AND b.confirmed=1 LIMIT 1000;";
	$autoresponderSubscriptions = $wpdb->get_results($getActiveFollowupSubscriptionsQuery);
	
	foreach ($autoresponderSubscriptions as $asubscription)
	{
		$aid = $asubscription->eid;
		$daysSinceSubscribing = $asubscription->daysSinceSubscribing;
                /*Below is a strange bunch of code that:
                 *
                 * 1.  fetches the email
                 * 2.  expires subscriptions if there are no more messages to deliver
                 * 
                 */
		$query = "SELECT * FROM ".$prefix."wpr_autoresponder_messages where aid=$aid and sequence>=$daysSinceSubscribing LIMIT 1;";
		$listOfMessages = get_rows($query);
		
		if (0 == count($listOfMessages))
		{
			_wpr_expire_followup($asubscription->id);	
			continue;
		}
		
		$message = $listOfMessages[0];
		
                //in case this is a message of a later day.
                if ($message->sequence != $daysSinceSubscribing)
                        continue;
                /*
                 * End strange bunch of code.
                 */
		$message_id = $message->id;
		$subscriber_id = $asubscription->sid;
		$autoresponder_id = $asubscription->eid;
		
		$meta_key = sprintf("AR-%s-%s-%s-%s",$autoresponder_id, $subscriber_id, $message_id, $daysSinceSubscribing);
		
		$emailParameters = array("subject" => $message->subject, "textbody" => $message->textbody , "htmlbody" => $message->htmlbody, "htmlenabled"=> $message->htmlenabled,"attachimages"=> $message->attachimages, "email_type" => "user_followup_autoresponder_email", 'meta_key'=>$meta_key);
		wpr_place_tags($asubscription->sid,$emailParameters);
		
		try {
		
			if ($send_immediately == false)
				sendmail($asubscription->sid,$emailParameters);
			else
			{
				$emailParameters['delivery_type'] = 1;
				_wpr_send_and_save($asubscription->sid,$emailParameters);
			}
		}
		catch (Exception $exp)
		{
			//just in case.
		}
		
		$updateSubscriptionStatusQuery = "UPDATE ".$prefix."wpr_followup_subscriptions set last_date='".time()."', sequence='$message->sequence' WHERE sid=$asubscription->sid";
		$wpdb->query($updateSubscriptionStatusQuery);
		
		//if another cron has started, then this cron should be terminated.
		$timeThisInstant = time();
                $timeSinceStart = $timeThisInstant-$timeOfStart;
                if ($timeSinceStart > WPR_MAX_AUTORESPONDER_PROCESS_EXECUTION_TIME)
                    return;
	}
	update_option("_wpr_autoresponder_process_status","stopped");
}
function _wpr_postseries_process()
{
	global $wpdb;
        //return;
	$last_cron_status = get_option("_wpr_postseries_process_status");
        $currentTime = time();
        //return;
	/*
	When the cron is running the _wpr_postseries_process_status
	is set to the timestamp at which the cron processing was started.
	
	Before shutting down the _wpr_postseries_process_status is
	set to 'stopped'.
	
	This cron will run only if the _wpr_postseries_process_status option
	is set to "stopped" or is empty.
	*/
	$timeOfStart = time();
	$timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_POSTSERIES_PROCESS_EXECUTION_TIME;
	if (!empty($last_cron_status) && $last_cron_status != "stopped")
	{
		$last_cron_status = intval($last_cron_status);
		if ($last_cron_status !=0 && ($last_cron_status > $timeMaximumExecutionTimeAgo))
		{
			return;
		}
	}
	
	update_option("_wpr_postseries_process_status",$timeOfStart);
	
	
	$prefix = $wpdb->prefix;	
	$getActiveFollowupSubscriptionsQuery = "SELECT a.*, b.id sid, FLOOR(($currentTime - a.doc)/86400) daysSinceSubscribing FROM `".$prefix."wpr_followup_subscriptions` a, `".$prefix."wpr_subscribers` b  WHERE a.type='postseries' AND  a.sequence < FLOOR(($currentTime - a.doc)/86400) AND a.sequence <> -2 AND  a.sid=b.id AND b.active=1 AND b.confirmed=1 LIMIT 1000;";
	$postseriesSubscriptionList = $wpdb->get_results($getActiveFollowupSubscriptionsQuery);
	foreach ($postseriesSubscriptionList as $psubscription)
	{
		$sid = $psubscription->sid;
		$query = "SELECT nid from ".$wpdb->prefix."wpr_subscribers where id=".$sid;
		$results = $wpdb->get_results($query);
		if (count($results) != 1) //where's the newsletter?!!
			continue;
		$nid = $results[0]->nid;
		$subscriber = _wpr_subscriber_get($psubscription->sid);
		
		//how many days since subscribing?
		$daysSinceSubscribing = floor((time()-$psubscription->doc)/86400);
                //get the post series as an object
		$postseries = _wpr_postseries_get($psubscription->eid);
                //get the posts in the post series
        $posts = get_postseries_posts($postseries->catid,$nid);
		$numberOfPosts = count($posts);
		if ($numberOfPosts == 0)
		{
			_wpr_expire_followup($psubscription->id);
			continue;
		}
		$timeBetweenPosts = $postseries->frequency;
		$last_post = $psubscription->sequence;
		$currentIndex = floor($daysSinceSubscribing/$timeBetweenPosts);

                if ($currentIndex == $last_post)
                    continue;
		
		//all posts have been sent. expire the post series subscription
		if ($last_post >= count($posts)-1)
		{			
			_wpr_expire_followup($psubscription->id);
			continue;
		}
		$indexToDeliver = $last_post+1;
		$category = $psubscription->eid;
		$postToSend = $posts[$indexToDeliver];
		$sitename = get_bloginfo("name");
		
		
		$meta_key = sprintf("PS-%s-%s-%s",$psubscription->eid,$psubscription->sid,$postToSend->ID);
		$additionalParams = array('meta_key'=>$meta_key);
		
        deliverBlogPost($sid,$postToSend->ID,"You are receiving this blog post as a part of a post series at $name.",true,true,$additionalParams);
		
		$query = "UPDATE ".$prefix."wpr_followup_subscriptions set sequence=$indexToDeliver , last_date='".time()."' where id='".$psubscription->id."';";
		$wpdb->query($query);
		
		$timeThisInstant = time();
		$timeSinceStart = $timeThisInstant-$timeOfStart;
		if ($timeSinceStart > WPR_MAX_POSTSERIES_PROCESS_EXECUTION_TIME)
			return;
	}
	update_option("_wpr_postseries_process_status","stopped");
	
}
function _wpr_expire_followup($id)
{
	global $wpdb;
        $prefix = $wpdb->prefix;
	$timeNow = time();
	$query = "UPDATE ".$prefix."wpr_followup_subscriptions set sequence='-2', last_date='$timeNow' where id='$id';";
	$wpdb->query($query);	
}

function wpr_get_mailouts()
{
 	global $wpdb;
	$prefix = $wpdb->prefx;
	$timeStamp = time();
	$query = "SELECT * FROM `".$wpdb->prefix."wpr_newsletter_mailouts` WHERE `status` = 0 AND `time` <= $timeStamp;";
	$mailouts = $wpdb->get_results($query);
	return $mailouts;
}

function _wpr_process_blog_subscriptions()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	//now process the people who subscribe to the blog
	$lastPostDate = get_option("wpr_last_post_date");
	$timeNow = date("Y-m-d H:i:s",time());
	$query = "SELECT * FROM ".$prefix."posts where post_type='post' and  post_status='publish' and post_date_gmt > '$lastPostDate' and post_date_gmt < '$timeNow';";
	$posts = $wpdb->get_results($query);
	if (count($posts) > 0 ) // are there posts being delivered? 
	{
		foreach ($posts as $post)
		{
			$query = "SELECT a.* FROM ".$prefix."wpr_subscribers a, ".$prefix."wpr_blog_subscription b where b.type='all' and a.id=b.sid and a.active=1 and a.confirmed=1;";
			$subscribers = $wpdb->get_results($query);
			//deliver this post to all subscribers of the categories of
			// this post.
			$categories = wp_get_post_categories($post->ID);
			foreach ($categories as $category)
			{
				deliver_category_subscription($category,$post);
			}
	
			if (count($subscribers) > 0)
			{
				$blogName = get_bloginfo("name");
				$blogURL = get_bloginfo("home");
				$footerMessage = "You are receiving this email because you are subscribed to the latest articles on <a href=\"$blogURL\">$blogName</a>";
				foreach ($subscribers as $subscriber)
				{
							deliverBlogPost($subscriber->id,$post->ID,$footerMessage);
				}
			}

			delete_option("wpr_last_post_date");
			add_option("wpr_last_post_date",$post->post_date_gmt);
			
			$sentPosts = get_option("wpr_sent_posts");
			$sentPostsList = explode(",",$sentPosts);
			$sentPostsList[] = $post->ID;
			$sentPosts = implode(",",$sentPostsList);
			delete_option('wpr_sent_posts');
			add_option("wpr_sent_posts",$sentPosts);	
		}
	}
	
}

function _wpr_process_broadcasts()
{
	global $wpdb;
	$prefix = $wpdb->prefix;	
	$last_cron_status = get_option("_wpr_newsletter_process_status");
	
	
	
	
	/*
	

	When the cron is running the _wpr_newsletter_process_status
	is set to the timestamp at which the cron processing was started.
	
	Before shutting down the _wpr_newsletter_process_status is
	set to 'stopped'.
	
	This cron will run only if the _wpr_newsletter_process_status option
	is set to "stopped" or is empty.
	*/
	
	$timeOfStart = time();
	$timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_NEWSLETTER_PROCESS_EXECUTION_TIME;
	if (!empty($last_cron_status) && $last_cron_status != "stopped")
	{
		$last_cron_status = intval($last_cron_status);
		if ($last_cron_status !=0 && ($last_cron_status > $timeMaximumExecutionTimeAgo))
		{
			return;
		}
	}
	

	delete_option("_wpr_newsletter_process_status");
	add_option("_wpr_newsletter_process_status",$timeOfStart);
	
	
	$email_mailouts= wpr_get_mailouts();
	
	foreach ($email_mailouts as $broadcast)
	{
		$nid = $broadcast->nid;
		$subject = $broadcast->subject;
		$body = $broadcast->body;
		wpr_create_temporary_tables($nid);	  //this creates the tables based on which a bigger table will be created
		wpr_make_subscriber_temptable($nid);  //this table will be used for getting the user list.
		$customFieldsConditions = trim(wpr_filter_query($nid,$broadcast->recipients));
		$customFields = ($customFieldsConditions)?" AND ".$customFieldsConditions:"";
		$query = "SELECT * FROM ".$prefix."wpr_subscribers_".$nid." where active=1 and confirmed=1 $customFields;";
		$subscribersList = $wpdb->get_results($query);
		$subject = $broadcast->subject;
		$text_body = $broadcast->textbody;
		$html_body = $broadcast->htmlbody;
		$whetherToAttachImages = $broadcast->attachimages;
		$query = "SELECT fromname, fromemail from ".$wpdb->prefix."wpr_newsletters where id=".$nid;
		$results = $wpdb->get_results($query);
		$fromname = $results[0]->fromname;
		$fromemail = $results[0]->fromemail;
		
		
		if (count($subscribersList))
		{
			$broadcastId=$broadcast->id;
			$newsletterId= $broadcast->nid;
			
			
			

			foreach ($subscribersList as $subscriber)
			{
				$sid = $subscriber->id;
				$email = $subscriber->email;
				$meta_key = sprintf("BR-%s-%s-%s",$sid,$broadcastId,$newsletterId);
				$emailParameters = array( "subject" => $subject,
							  "from"=> $fromname,
							  "fromemail"=>$fromemail,
							  "textbody" => $text_body,
							  "htmlbody" => $html_body,
							  "htmlenabled"=> (empty($html_body))?0:1,
							  "attachimages"=> $whetherToAttachImages,
							  "meta_key"=> $meta_key
							  );
				wpr_place_tags($sid,$emailParameters);
				$emailParameters["to"] = $subscriber->email;	
				sendmail($sid,$emailParameters);
			}
		}
		
		$timeThisInstant = time();
                $timeSinceStart = $timeThisInstant-$timeOfStart;
                if ($timeSinceStart > WPR_MAX_NEWSLETTER_PROCESS_EXECUTION_TIME)
                    return;

		mailout_expire($broadcast->id);
	}
	
	delete_option("_wpr_newsletter_process_status");
	add_option("_wpr_newsletter_process_status","stopped");	
}
function wpr_filter_query($nid, $thestring)
{	
	$sections = explode(" ",$thestring);
	$size = count($sections);
	$count=0;
	$comparisonOpers = array("equal","notequal","lessthan","greaterthan");
	$stringOperators = array("startswith","endswith","contains");
	
	$final ="";
	for ($count=0;$count<$size;)
	{
		$condition = "";
		if ($count != 0)
		{
			$conjunction = " ".$sections[$count]." ";
		}
		else
		{
		   $conjunction = "";
		  $count = -1; //to adjust for the indices i have used below below..
		}
		  
		$field = $sections[$count+1];
		$equality = $sections[$count+2];
		$value = $sections[$count+3];
		
	
		if (in_array($equality,$comparisonOpers))
		{
			
			switch ($equality)
			{
				case 'equal':
				  $condition = "`$field` = '$value'";
				  break;
				case 'notequal':
				   $condition= "`$field` <> '$value'";
				   break;
				case 'lessthan':
				   $condition = "`$field` < '$value'";
				   break;
				case 'greaterthan':
				   $condition = "`$field` > '$value'";
			}
		}
		else if ($equality == "notnull")
		{
			$condition = "`$field` IS NOT NULL";
		}
		else if (in_array($equality,$stringOperators))
		{
			switch ($equality)
			{
				case 'startswith':
					$condition = "`$field` like '$value%'";
					break;
				case 'contains':
					$condition = "`$field` like '%$value%'";
					break;
				case 'endswith':
					$condition = "`$field` like '%$value'";
					break;
			}
		}
		else if (in_array($equality,array("before","after")) && $field == "dateofsubscription")
		{
				$thetime = strtotime($value);
				
				switch ($equality)
				{
					case 'before':
						$condition = "date < $thetime";
						break;
					case 'after':
						$condition = "date > $thetime";
						break;
				}
		}
		else if ($equality == "rlike")
		{
			$condition = "`$field` rlike '$value'"; 
		}
	
		
		$final .= $conjunction." ".$condition;
						 
		if ($count == 0) //the first element is not a conjunction
		{
			$count+=3;
		}
		else
		{
			$count +=4;
		}
	}
	return $final;
}
function get_postseries_posts($catid,$nid="")
{
	global $mailer;
    global $wpdb;

	$args = array(
					'post_type' => 'post',
					'numberposts' => -1,
					'category'=>$catid,
					'orderby' => 'date',
					'order' => 'ASC',
					'post_status' => 'publish'
				);
	$posts = get_posts($args);

        if (!empty($nid))
        {
                    foreach ($posts as $num=>$post)
                        {
                        $pid = $post->ID;
                        $query = "SELECT meta_value from ".$wpdb->prefix."postmeta where post_id=$pid and meta_key='wpr-options';";
                        $results = $wpdb->get_results($query);
                        $option = $results[0]->meta_value;
                        $decodedoptions = base64_decode($option);
                        $options = unserialize($decodedoptions);
                        $theRealPosts[] = $post;
                    }
        }
			
		
	return $theRealPosts;

}

function mailout_expire($id)

{
	global $wpdb;
	$query = "UPDATE ".$wpdb->prefix."wpr_newsletter_mailouts set status=1 where id=$id";
	$wpdb->query($query);
}
function get_rows($query)
{
	global $wpdb;
	return $wpdb->get_results($query);
}

function isValidOptionsArray($options)
{
    if (is_array($options))
        {
        return true;
    }
    else
         return false;
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

/*
 * This function is used to see if the subscriber with sid $sid
 * is currently receiving any follow up emails from autoresponders
 * or post series subscriptions.
 
 * This function sends the blog post with post id $post_id via email to subscriber with subscriber id $sid
 * if the the subscriber doesnt belong to a newsletter with newsletter id
 * that is in the list of newsletters that are configured to not receive this blog post.
 *
 *
 *
 */
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
		

       if ($options[$nid]['nocustomization']==1 || !isValidOptionsArray($options) || ($whetherPostSeries == true && $options[$nid]['nopostseries']==1))
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
   }

}

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
	
    //post publishing date
    $post = get_post($post_id);
    $postDate = $post->post_date;
    $postEpoch = strtotime($postDate);
    $postDate = date("dS, F Y",$postEpoch);
    $text = str_replace("[!post_date!] ",$postDate,$text);
   
    return $text;
    
}

function deliver_category_subscription($catid,$post)
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$query = "SELECT a.* FROM  ".$prefix."wpr_subscribers a,".$prefix."wpr_blog_subscription b where b.type='cat' and b.catid='$catid' and a.id=b.sid and a.active=1 and a.confirmed=1";
	$subscribers = $wpdb->get_results($query);
	$theCategory = get_category($catid);
	$categoryName = $categoryname->name;
	$blogName = get_bloginfo("name");
	$blogURL = get_bloginfo("siteurl");
	$footerMessage = "You are receiving this e-mail because you have subscribed to the $categoryName category of $blogName

$blogUrl";
	foreach ($subscribers as $subscriber)
	{
               deliverBlogPost($subscriber->id,$post->ID);
	}

}