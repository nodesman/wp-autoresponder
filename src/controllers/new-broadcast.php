<?php

function _wpr_new_broadcast_handler()
{
	$newsletters = _wpr_get_newsletters();

	_wpr_set("newsletterList",$newsletters);
	_wpr_set("send","immediately");
	_wpr_set("timezone","+00:00");
}

function _wpr_new_broadcast_post_handler()
{
	//security check
	if (!check_admin_referer("new_broadcast_form"))
	{
		header('HTTP/1.0 404 Not Found'); 
		exit;
	}
	
	$errors = array();
	$newsletter = intval($_POST['newsletter']);
	$newsletter_obj = _wpr_get_newsletter($newsletter);
	//ensure that this newsleter exists.
	
	if (false == $newsletter_obj)	
	{
		$errors[] = __("The selected newsletter doesn't exist.",'wpr_autoresponder');	
		//then again.. what are the odds of the newsletter not existing..  
	}
	
	$subject = wpr_sanitize($_POST['subject']);
	$content = wpr_sanitize($_POST['content'],false);
	$textbody = wpr_sanitize($_POST['textbody']);
	
	$send = $_POST['send'];
	if ("later"==$send)
	{
		$date = wpr_sanitize($_POST['send_date']);
		$date_parts = explode("/", $date);
		try 
		{
			 
			if (3 != count($date_parts))
				throw new Exception(__("The date is entered in an invalid format. Please enter a valid date in MM/DD/YYYY format.",'wpr_autoresponder'));
				
			foreach ($date_parts as $index=>$parts)
				$date_parts[$index] = intval($parts);
			
			if (in_array(0,$date_parts))
				throw new Exception(__("The date is entered in an invalid format. Please enter a valid date in MM/DD/YYYY format.",'wpr_autoresponder'));
				
			list($month,$date,$year) = split("/",$date);				
				
			if (!checkdate($month,$date,$year))
				throw new Exception(__("The date entered in the date field is invalid. Please enter a valid date.",'wpr_autoresponder'));
				
			$send_hour = intval($_POST['send_hour']);
			$send_minutes = intval($_POST['send_minute']);
			
			//what follows is the most questionable piece of code i ever wrote. don't ask why, just run with it.
			
			//get the timezone offset in seconds
			$timezone = $_POST['timezone'];			
			list($timezone_offset_hour, $timezone_offset_minute) = split(":",$timezone);
			$whetherToAddTimezoneOffset = strstr($timezone,"+");			
			$timezoneOffsetInSeconds = abs($timezone_offset_hour)*3600+abs($timezone_offset_minute)*60;
			$timezoneOffsetInSeconds = (!$whetherToAddTimezoneOffset)?-$timezoneOffsetInSeconds:$timezoneOffsetInSeconds;

			$epoch_of_scheduled_time = mktime($send_hour,$send_minutes, 0,$month,$date,$year);
		
			if (false===$epoch_of_scheduled_time)
			 	throw new Exception("The date and time combination you have selected is invalid. Please enter a valid date-time.");

			$epoch_of_scheduled_time += $timezoneOffsetInSeconds;				
			$epochNow = time();
			
			if ($epochNow >= $epoch_of_scheduled_time)
				throw new Exception("The date and time combination you have provided is in the past. Please specify a dispatch time in the future.");			
		
		}
		catch (Exception $e)
		{
			$errors[] = $e->getMessage();			
		}
	}
	
	
	
	
	if (empty($content) && empty($textbody))
	{
		$errors[] = __("Both the HTML and text body of the broadcast are empty. Atleast one of them must be filled to send a broadcast.",'wpr_autoresponder');
	}
	
	
	
	if (count($errors) == 0 )
	{
		//go to step two.		
	}
	
	
}

