<?php

function _wpr_subscriber_hash_generate() {

	$hash = "";
	for ($i=0;$i<6;$i++)
	{
		$a[] = rand(65,90);
		$a[] = rand(97,123);
		$a[] = rand(48,57);

		$whichone = rand(0,2);
		$currentCharacter = chr($a[$whichone]);

		$hash .= $currentCharacter;
		unset($a);

	}
     $hash .= time();
	//insert into subscribers list
	return $hash;
}

function _wpr_increment_hourly_email_sent_count()
{
	$email_sent_this_hour = get_option("_wpr_dq_emails_sent_this_hour");
	$email_sent_this_hour++;
	update_option("_wpr_dq_emails_sent_this_hour",$email_sent_this_hour);
}


function _wpr_non_wpr_email_sent($params)
{
	_wpr_increment_hourly_email_sent_count();
	return $params;
}

/*
 *
 * $sid   - Id of the subscriber who should get the email
 * $params - The parameter array for the email that is to be sent
 *
 * $params  = array(  "subject" => String - The subject of the email.
 *                    "htmlbody"=> String - The HTML body of the email.
 *                    "textbody"=> String - The text body of the email.
 *                    "attachimages" => Boolean - Whether the images in the HTML body should be attached with the email.
 *            		  "fromname"  => String - The name of the email sender
					  "htmlenabled" => boolean ( 1 or 0 ) - Whether the html body of this message is enabled.
					  "fromemail" => String - The email address of the sender
 *
 *
 * $footerMessage - The optional footer message that is to be appended
 *                  at the bottom of the email after the email body and
 *                  before the Sender's address.
 *
 */
function sendmail($sid,$params,$footerMessage="")
{
	global $wpdb;
	$parameters = _wpr_process_sendmail_parameters($sid,$params,$footerMessage);

    $parameters['subject'] = Subscriber::replaceCustomFieldValues($parameters['subject'], $sid);
    $parameters['htmlbody'] = Subscriber::replaceCustomFieldValues($parameters['htmlbody'], $sid);
    $parameters['textbody'] = Subscriber::replaceCustomFieldValues($parameters['textbody'], $sid);

	extract($parameters);
	$tableName = $wpdb->prefix."wpr_queue";
	$query = "INSERT INTO $tableName (`from`,`fromname`, `to`, `reply_to`, `subject`, `htmlbody`, `textbody`, `headers`,`attachimages`,`htmlenabled`,`email_type`,`delivery_type`,`meta_key`,`hash`,`sid`) values ('$from','$fromname','$to','$reply_to','$subject','$htmlbody','{$parameters['textbody']}','$headers',1,'$htmlenabled','$email_type','$delivery_type','$meta_key','$hash','$sid');";


	$wpdb->query($query);

}

function _wpr_process_sendmail_parameters($sid, $params,$footerMessage="")
{

    global $wpdb;
    $subscriber = new Subscriber($sid);
    $newsletter = _wpr_newsletter_get($subscriber->getNewsletterId());

    //if the fromname field is set in the newsletter, then use that value otherwise use the blog name.
    $fromname = (!empty($params['fromname']))?$params['fromname']:(!empty($newsletter->fromname))?$newsletter->fromname:get_bloginfo("name");

    if ($newsletter->reply_to)
        $replyto = $newsletter->reply_to;
    $unsuburl = wpr_get_unsubscription_url($sid);
    $subject = $params['subject'];

    $address = get_option("wpr_address");
    $textUnSubMessage = "\n\n$address\n\n".__("To unsubscribe or change subscription options visit",'wpr_autoresponder').":\r\n\r\n$unsuburl";
    $reply_to = $newsletter->reply_to;
    $htmlbody = trim($params['htmlbody']);
    $textbody = $params['textbody'];
    $subject = $params['subject'];


    //append the address and the unsub link to the email
    $address = "<br>
<br>
".nl2br(get_option("wpr_address"))."<br>
<br>
";
    $htmlUnSubscribeMessage = "<br><br>".$address."<br><br>".__("To unsubscribe or change subscriber options visit:",'wpr_autoresponder')."<br />
\r\n <a href=\"$unsuburl\">$unsuburl</a>";
    $htmlenabled = ($params['htmlenabled'])?1:0;
    if (!empty($htmlbody))
    {
        if ($footerMessage && (!empty($htmlbody)) )
        {
            $htmlbody .= nl2br($footerMessage)."<br>
<br>
";
        }

        if (strstr($htmlbody,"[!unsubscribe!]"))
        {
            $htmlbody = str_replace("[!unsubscribe!]", $unsuburl, $htmlbody);
        }
        else
        {
            $htmlbody .= $htmlUnSubscribeMessage;
        }
    }

    if ($footerMessage)
        $params['textbody'] .= $footerMessage."\n\n";
    if (strstr($params['textbody'],"[!unsubscribe!]"))
        $textbody = str_replace("[!unsubscribe!]",$unsuburl,$textbody);
    else
        $textbody = $params['textbody'].$textUnSubMessage;

    $textbody = addslashes($textbody);
    $htmlbody = addslashes($htmlbody);
    $subject = addslashes($subject);
    $time = time();


    $subject = str_replace("[!name!]", $subscriber->getName(), $subject);
    $textbody = str_replace("[!name!]", $subscriber->getName(), $textbody );
    $htmlbody = str_replace("[!name!]", $subscriber->getName(), $htmlbody );


    $delivery_type = (!empty($params['delivery_type']))?$params['delivery_type']:0;
    $email_type = (!empty($params['email_type']))?$params['email_type']:'misc';
    $attachImages = (isset($params['attachimages']))?1:0;
    $meta_key = (!empty($params['meta_key']))?$params['meta_key']:"Misc-$sid-$time";
    $hash = make_hash(array_merge(array('sid'=>$sid),$params));
    $from = (!empty($params['fromemail']))?$params['fromemail']:(!empty($newsletter->fromemail))?$newsletter->fromemail:get_bloginfo('admin_email');

    $parameters = array(
        'from'=> $from,
        'fromname' => $fromname,
        'to'=> $subscriber->email,
        'reply_to'=>$reply_to,
        'subject' => $subject,
        'htmlbody'=>$htmlbody,
        'textbody' => $textbody,
        'headers'=> '',
        'attachimages'=>$attachImages,
        'htmlenabled'=>$htmlenabled,
        'delivery_type' => $delivery_type,
        'email_type'=>$email_type,
        'meta_key' =>$meta_key,
        'hash'=> $hash
    );

    return $parameters;
}



function make_hash($params)
{
	extract($params);
	return md5($sid.$htmlbody.$textbody.$subject);
}

function _wpr_send_and_save($sid, $params, $footerMessage="")
{
	global $wpdb;

	$parameters = _wpr_process_sendmail_parameters($sid,$params,$footerMessge);

	dispatchEmail($parameters);

	$queue_table_name = $wpdb->prefix."wpr_queue";
	$emailQuery = $wpdb->prepare("INSERT INTO $queue_table_name (`from`, `fromname`, `to`, `subject`, `htmlbody`, `textbody`, `headers`, `sent`, `delivery_type`, `email_type`, `htmlenabled`, `attachimages`,`meta_key`)
																VALUES
																('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');",
									$parameters['from'],
									$parameters['fromname'],
									$parameters['to'],
									$parameters['subject'],
									$parameters['htmlbody'],
									$parameters['textbody'],
									$parameters['headers'],
									'1',
									'1',
									$parameters['email_type'],
									$parameters['htmlenabled'],
									$parameters['attachimages'],
									$parameters['meta_key']
								);

	$wpdb->query($emailQuery);
}

function wpr_processqueue()
{
	global $wpdb;

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
	$timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_NEWSLETTER_PROCESS_EXECUTION_TIME;
	if (!empty($last_cron_status) && $last_cron_status != "stopped")
	{
		$last_cron_status = intval($last_cron_status);
		if ($last_cron_status !=0 && ($last_cron_status > $timeMaximumExecutionTimeAgo))
		{
			return;
		}
	}


	update_option("_wpr_queue_delivery_status",$timeOfStart);

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

	$hourlyLimit = getNumberOfEmailsToDeliver();
	$hourlyLimit = (int) $hourlyLimit;
	$limitClause = ($hourlyLimit ==0)?"":" limit ".$hourlyLimit;
	$query = $wpdb->escape("SELECT * FROM ".$wpdb->prefix."wpr_queue where sent=0 $limitClause ");
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

		$current_cron_status = get_option("_wpr_queue_delivery_status");
		if ($current_cron_status != $timeOfStart)
			return;
	}

	update_option("_wpr_queue_delivery_status","stopped");
}

/*
 * This is the function that sends the email out.
 * Arguments : $mail = array(
 *                             to = The recipient's email address
 *                             from = The from email address
 *                             fromname = The nice name from which the email is sent
 *                             htmlbody = The html body of the email
 *                             textbody = The text body of the email
 *                             htmlenabled = Whether the html body of the email is enabled
 *                                           1 = Yes, the html body is enabled
 *                                           0 = No, the html body is disabled.
 *                             attachimages = Whether the images are to be attached to the email
 *                                           1 = Yes, attach the images
 *                                           0 = No,  don't attach the images
 *
 */
function dispatchEmail($mail)
{
	try {

		$transport = getMailTransport();
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance($mail['subject']);
		$message->setFrom(array($mail['from']=>$mail['fromname']));
		$message->setTo(array($mail['to']));

		if (!empty($mail['reply_to']) && validateEmail($mail['reply_to']))
		{
			$message->setReplyTo($mail['reply_to']);
		}

		$mail['textbody'] = stripslashes($mail['textbody']);
		if ($mail['htmlenabled']==1 && !empty($mail['htmlbody']))
		{

			$mail['htmlbody'] = stripslashes($mail['htmlbody']);
			if ($mail['attachimages'] == 1)
			{
				attachImagesToMessageAndSetBody($message,$mail['htmlbody']);
			}
			else
			{
				$message->setBody($mail['htmlbody'],'text/html');
			}
			$message->addPart($mail['textbody'],'text/plain');
		}
		else
		{
			$message->setBody($mail['textbody'],'text/plain');
		}
		$mailer->send($message);
		_wpr_increment_hourly_email_sent_count();
	}
	catch (Exception $exp)
	{
		//do something here..
	}
}

function attachImagesToMessageAndSetBody(&$message,$body)
{

	$imagesInMessage = getImagesInMessage($body);
	foreach ($imagesInMessage as $imageUrl)
	{
		try{
			$cid = $message->embed(Swift_Image::fromPath($imageUrl));
			$body = str_replace($imageUrl,$cid,$body);
		}
		catch (Exception $exp)
		{

		}
	}

	$message->setBody($body,'text/html');
}

function getImagesInMessage($message)

{

	$startPos = 0;

	$list = array();
		$message = " $message"; //if the image tag is at position 0, the loop will not even start.




	while (strpos($message,"<img",$startPos))
	{


		$start = strpos($message,"<img",$startPos);

		$end = strpos($message,">",$start+4);

		$startPos = $end;


			//find the src="

		if ($end)

		{

			$begin = strpos($message,"src=\"",$start);

			$end = strpos($message,"\"",$begin+5);

			$theURL = substr($message,$begin+5,$end-$begin-5);

			if ($theURL[0] == "/") //then we have a relative path. attach the blog's hostname in the beginning.

			{

				$url = str_replace("http://","",get_option("siteurl"));

				$url = explode("/",$url);



				$theURL = "http://".$url[0].$theURL;

			}

			else if (strpos($theURL,"http://") > 0) //probably a relative path to the blog root.

			{

				$theURL = get_option("siteurl")."/".$theURL;

			}

			$list[] = $theURL;

		}

		else

		{

			$startPos = $start+4; // an opening image tag without a closing '>' ? then we skip that image.

			continue;

		}

	}


	return array_unique($list);

}

function email($to,$subject,$body)
{

	$transport = getEmailTransport();

	$message = Swift_message::newInstance($subject);

	$message->setFrom(array(get_option("admin_email")=>get_option("blogname")));

	$message->setBody($body);

	if(!is_array($to) || (count($to)<2)) {
            $message->setTo($to);
            $message->send();
        } else {
            foreach($to as $address => $name) {
                if(is_int($address)) {
                    $message->setTo($name);
                } else {
                    $message->setTo(array($address => $name));
                }
                $message->send();
            }
        }
}



function getMailTransport()

{

	 $isSmtpOn = (get_option("wpr_smtpenabled")==1)?true:false;

		//get the proper email transport to use.

	 if ($isSmtpOn)

			{



			$smtphostname = get_option("wpr_smtphostname");

			$smtpport = get_option("wpr_smtpport");



			$doesSmtpRequireAuth = (get_option("wpr_smtprequireauth")==1)?true:false;

			$isSecureSMTP = (in_array(get_option("wpr_smtpsecure"),array("ssl","tls")))?true:false;

			$smtpsecure = get_option("wpr_smtpsecure");
			$transport = Swift_SmtpTransport::newInstance();
			$transport->setHost($smtphostname);
			$transport->setPort($smtpport);

			if ($doesSmtpRequireAuth)
				{
					$smtpusername = get_option("wpr_smtpusername");
					$smtppassword = get_option("wpr_smtppassword");
					$transport->setUsername($smtpusername);
					$transport->setPassword($smtppassword);
				}

				if ($isSecureSMTP)
				{

					$transport->setEncryption(get_option('wpr_smtpsecure'));

				}

		}

		else

			{



				$transport = Swift_MailTransport::newInstance();

			}





				return $transport;

}




function wpr_get_unsubscription_url($sid)
{
	$baseURL = get_bloginfo("url");
	$subscriber = _wpr_subscriber_get($sid);
	$newsletter = _wpr_newsletter_get($subscriber->nid);
	$nid = $newsletter->id;
	$string = $sid."%$%".$nid."%$%".$subscriber->hash;
	$codedString = base64_encode($string);
	$unsubscriptionUrl = $baseURL."/?wpr-manage=$codedString";
	return $unsubscriptionUrl;
}

function sendConfirmedEmail($id)
{
	global $wpdb;
	$query = "select * from ".$wpdb->prefix."wpr_subscribers where id=$id";
	$sub = $wpdb->get_results($query);
	$sub  = $sub[0];
	//get the confirmation email and subject from newsletter

	$newsletter = _wpr_newsletter_get($sub->nid);

	$confirmed_subject = $newsletter->confirmed_subject;

	$confirmed_body = $newsletter->confirmed_body;

	//if a registered form was used to subscribe, then override the newsletter's confirmed email.

	$sid = $sub->id; //the susbcriber id
	$unsubscriptionURL = wpr_get_unsubscription_url($sid);

	$unsubscriptionInformation = "\n\n" . sprintf(__("To manage your email subscriptions or to unsubscribe click on the URL below:\n%s\n\nIf the above URL is not a clickable link simply copy it and paste it in your web browser.",'wpr_autoresponder'),$unsubscriptionURL);


	$fid = $args[2];
	$query = "SELECT a.* from ".$wpdb->prefix."wpr_subscription_form a, ".$wpdb->prefix."wpr_subscribers b  where a.id=b.fid and b.id=$sid;";

	$form = $wpdb->get_results($query);
	if (count($form))
	{
		 $confirmed_subject = $form[0]->confirmed_subject;
		 $confirmed_body = $form[0]->confirmed_body;
	}

	$confirmed_body .= $unsubscriptionInformation;

	$params = array($confirmed_subject,$confirmed_body);

	wpr_place_tags($sub->id,$params);

	$fromname = $newsletter->fromname;
	if (!$fromname)
	{
		$fromname = get_bloginfo('name');
	}

	$fromemail = $newsletter->fromemail;
	if (!$fromemail)
	{
		$fromemail = get_bloginfo('admin_email');
	}

	$email = $sub->email;
	$emailBody = $params[1];
	$emailSubject = $params[0];
	$mailToSend = array(
							'to'=>$email,
							'fromname'=>  $fromname,
							'from'=> $fromemail,
							'textbody' => $emailBody,
							'subject'=> $emailSubject,
						);
		try {
			dispatchEmail($mailToSend);
		}
		catch (Swift_RfcComplianceException $exception) //invalidly formatted email.
		{
			//disable all subscribers with that email.
			$email = $mailToSend['to'];
			$query = "UPDATE ".$wpdb->prefix."wpr_subscribers set active=3, confirmed=0 where email='$email'";
			$wpdb->query($query);
		}

}