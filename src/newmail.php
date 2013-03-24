<?php

include_once __DIR__."/mail.lib.php";



function wpr_newmail()
{
	global $wpdb;
	if (isset($_POST['subject']))
	{
		date_default_timezone_set("UTC");
	    $subject = $_POST['subject'];
		$nid = $_POST['newsletter'];
		$textbody = trim($_POST['body']);
		$htmlbody = trim($_POST['htmlbody']);

		$whentosend = $_POST['whentosend'];	

		$date = $_POST['date'];

		$htmlenabled  = ($_POST['htmlenabled'] == "on");

		$recipients = $_POST['recipients'];

		$hour = $_POST['hour'];
        $shouldAttachImages = (isset($_POST['attachimages']))?1:0;
		
		$timezoneOffset = $_POST['timezoneoffset'];
	
		$min = $_POST['minute'];

		if ($whentosend == "now")

			$timeToSend = time();

		else

		{

			if (empty($date))
			{
				$error = "The date field is required";
			}
			else
			{
				$sections = explode("/",$date);
				$timeToSend = mktime($hour,$min,0,$sections[0],$sections[1],$sections[2]); 
				$timeToSend = $timeToSend-$timezoneOffset;
			}

		}

		if (!(trim($subject) && trim($textbody)))
		{
			$error = "Subject and the Text Body are mandatory.";
		}
		if ($timeToSend < time()  && !$error)
		{
			$error = "The time mentioned is in the past. Please enter a time in the future.";
			if ($htmlenabled && !$error)
			{	
				if (empty($htmlbody))
				{
					$error = "HTML Body is empty. Enter the HTML body of this email";
				}
			}

		}

		if (!$htmlenabled)
		   $htmlbody="";

		if (!$error)
		{
			$query = "insert into ".$wpdb->prefix."wpr_newsletter_mailouts (nid,subject,textbody,htmlbody,time,status,recipients,attachimages) values ('$nid','$subject','$textbody','$htmlbody','$timeToSend',0,'$recipients','$shouldAttachImages');";
			$wpdb->query($query);
			_wpr_mail_sending();
			return;
		}
	}

	$param = (object)  array("nid"=>$nid,"textbody"=>$textbody,"subject"=>$subject,"htmlbody"=>$htmlbody,"htmlenabled"=>1,"whentosend"=>$whentosend,"date" => $date,"hour"=>$hour,"minute"=>$min,"title"=>"New Mail");
	//There are no newsletters. Ask to create one before sending mailouts

	if (Newsletter::whetherNoNewslettersExist()) {
        ?>
        <h2>No Newsletters Found</h2>
            You need to create a newsletter before you can send a broadcast.
    <?php
    return;
    }
	wpr_mail_form($param,"new",$error);
}

function _wpr_mail_sending($nowOrLater="now")
{
	?>

<div class="wrap"><h2>Email Broadcast Scheduled.</h2></div>

The mail broadcast has been scheduled and will be delivered at the specified time. 
<p>
<a href="admin.php?page=wpresponder/allmailouts.php" class="button-primary">View All Broadcasts</a> <a href="admin.php?page=wpresponder/wpresponder.php" class="button">Go to Dashboard</a>
</p>
    <?php



}
