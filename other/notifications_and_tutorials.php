<?php


	function wpr_enable_tutorial()
	{


		$isItEnabled = get_option("wpr_tutorial_active");
		if (empty($isItEnabled))
		{
			//enabling the tutorial for the first time.
			add_option('wpr_tutorial_active','on');
			add_option('wpr_tutorial_activation_date',time());
			add_option('wpr_tutorial_current_index',"0");//set the index to zero.

			//schedule the cron to run once every day.
		}
		else
		{
			delete_option('wpr_tutorial_active');
			add_option('wpr_tutorial_active','on');
		}

		wp_schedule_event(time()+86400, 'daily' ,  "wpr_tutorial_cron");
	}

	function wpr_disable_tutorial()
	{
		$currentStatus = get_option("wpr_tutorial_active");
		//if the tutorial series is already off then do nothing.
		if ($currentStatus == 'off')
		{
			 return false;
		}
		//if the tutorial serise is on. then turn it off
		if ($currentStatus == "on")
		{
			delete_option('wpr_tutorial_active');
			add_option('wpr_tutorial_active','off');
		}
		//if for some reason the option is missing, then create it and then set it to off.
		if (empty($currentStatus))
		{
			add_option('wpr_tutorial_active','off');
		}
		wp_clear_scheduled_hook('wpr_tutorial_cron');
		return true;
	}

	/*
	Dispatch tutorial series to the user.
	*/

	function wpr_process_tutorial()
	{
		$isTutorialSeriesActive = get_option('wpr_tutorial_active');
		//double check before starting to check for a new post.
		if ($isTutorialSeriesActive == "on")
		{
			$theTutorialArticles = fetch_feed("http://www.wpresponder.com/tutorial/feed/");
			if (is_wp_error($theTutorialArticles)) //no feed? do nothing. leave it.
			{
				return false;
			}
			else
			{
				//get the index of the last email that was sent:
				$indexOfEmailLastSent = (int) get_option('wpr_tutorial_current_index');
				$numberOfTutorialArticles = $theTutorialArticles->get_item_quantity();

				if ($indexOfEmailLastSent < $numberOfTutorialArticles) //we have a new post to send.
				{
					$indexOfPostToSend = $indexOfEmailLastSent + 1;
					$items = $theTutorialArticles->get_items();
					$theArticle = $items[$indexOfPostToSend-1];
					$theTitle = $theArticle->get_title();
					$theContent = $theArticle->get_content();
					$theURL = $theArticle->get_link();
					$theEmailAddress = getNotificationEmailAddress();


					$mail = array(   'to'=> $theEmailAddress,
									 'from'=> get_bloginfo('admin_email'),
									 'fromname'=> 'WP Responder Tutorial',
									 'subject'=> $theTitle,
									 'htmlbody'=> $theContent,
									 'htmlenabled'=>1,
									 'attachimages'=>1
									 );
					
					try{
				
						dispatchEmail($mail);
					}
					catch (Swift_RfcComplianceException $exception) //invalidly formatted email.
					{
						return;
					}
	
					delete_option('wpr_tutorial_current_index');
					add_option('wpr_tutorial_current_index',$indexOfPostToSend);
					return true;
				}
			}
		}
		else
		{
			return false;
		}
	}

	function createNotificationEmail()
	{

		$not_email = get_option('wpr_notification_custom_email');
		if (empty($not_email))
			add_option('wpr_notification_custom_email','admin_email');
		else
			return false;
	}

	function wpr_enable_updates()
	{
		$updatesOption = get_option('wpr_updates_active');

		if (empty($updatesOption))
		{
			add_option('wpr_updates_active','on');
		}
                else
                {
                    delete_option("wpr_updates_active");
                    add_option("wpr_updates_active","on");
                }
		//set the date to current date.
		delete_option('wpr_updates_lastdate');
		add_option('wpr_updates_lastdate',time());

                
		//schedule the cron to run daily.
		wp_schedule_event(time()+86400,'daily','wpr_updates_cron');
	}

	function wpr_disable_updates()
	{
		delete_option('wpr_updates_active');
		add_option('wpr_updates_active','off');
		wp_clear_scheduled_hook('wpr_updates_cron');
	}

	function wpr_process_updates()
	{
		//double check
		$updatesEnabled = get_option('wpr_updates_active');
		if ($updatesEnabled == 'on')
		{
			//fetch the updates feed
			$updatesfeed = fetch_feed('http://www.wpresponder.com/updates/feed/');

			if (is_wp_error($updatesfeed))
			{
				return false;
			}
			else
			{

				//loop through the list of items and then deliver only the last update that is new.
				$numberOfItems = $updatesfeed->get_item_quantity();
				$items = $updatesfeed->get_items();

				$lastDate = get_option('wpr_updates_lastdate');
				$dateOfLatestPost = $lastDate;


				$postToDeliver = false;

				//this loop loops through all the items in the feed and then delivers the latest possible post.


				foreach ($items as $item)
				{
					$itemDate = $item->get_date();
					$itemDateStamp = strtotime($itemDate);
					if ($dateOfLatestPost < $itemDateStamp) //
					{
						$dateOfLatestPost = $itemDateStamp;
						$postToDeliver = $item;
					}
					$debug .= "\nNope..";
				}//end for loop to loop through the feed items.

				if ($postToDeliver != false)
				{
					//deliver the latest post.
					$title = $postToDeliver->get_title();
					$theBody = $postToDeliver->get_content();
					$notificationEmail = getNotificationEmailAddress();

					$mail = array(   'to' => $notificationEmail,
								  	 'from'=> get_bloginfo('admin_email'),
									 'fromname'=> 'WP Responder Updates',
									 'subject'=>$title,
									 'htmlbody'=>$theBody,
									 'htmlenabled'=>1,
									 'attachimages'=>1
								);
					//ob_start();
					try {
						dispatchEmail($mail);
					}
					catch (Swift_RfcComplianceException $exception) //invalidly formatted email.
					{
						return false;
					}
					delete_option('wpr_updates_lastdate');
					add_option('wpr_updates_lastdate',$dateOfLatestPost);
					return true;
				}//end - if the post is to be delivered.

			}//end - if the field is available


		}//end if updates are on.
		else
		{
			return false;
		}
	}//end defintion of wpr_process_updates

	function getNotificationEmailAddress()
	{
		$emailAddress = get_option('wpr_notification_custom_email');
		if (empty($emailAddress))
		{
                    add_option('wpr_notification_custom_email','admin_email');
		}
		if ($emailAddress != 'admin_email')
			return $emailAddress;
		else
			return get_bloginfo('admin_email');
	}
