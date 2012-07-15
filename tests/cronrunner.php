<?php


function runCronJobs($nid,$startDate)
{
	global $wpdb;	
	$getAutorespondersQuery = sprintf("select * from {$wpdb->prefix}wpr_autoresponders WHERE nid=%d",$nid);
	$autoresponders = $wpdb->get_results( $getAutorespondersQuery);
	$max = 0;
	
	
	$output_report = array();
	
	
	
	foreach ($autoresponders as $autoresponder)
	{
		$getAutoresponderMaxQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE aid=%d ORDER BY `sequence` DESC LIMIT 1",$autoresponder->id);
		$results = $wpdb->get_results($getAutoresponderMaxQuery);
		
		$autoresponder = $results[0];
		if ($autoresponder->sequence > $max)
		   $max = $autoresponder->sequence;	
	}
	$final_output = array();
	
	for ($day =0 ;$day <= $max+2; $day++)
	{
		for ($hour=0;$hour<=23;$hour++)
		{
			addSubscribers($nid,$startDate);	
			runcron();
			$output_report = (object) array();
			updateOutputRecords($output_report);
			$final_output = $output_report;
			moveTimeForwardOneHour();
		}
	}
	
	return $final_output;
}


function updateOutputRecords(&$output_report)
{
	global $wpdb;
	
		
	
	
}


function addSubscribers($nid,$startDate)
{
	global $wpdb;
	$start_date = $GLOBALS['cron_start_date'];
	$wpdb->show_errors();
	$getAutorespondersQuery = sprintf("select * from {$wpdb->prefix}wpr_autoresponders WHERE nid=%d",$nid);
	$autoresponders = $wpdb->get_results( $getAutorespondersQuery);
	$max = 0;
	foreach ($autoresponders as $autoresponder)
	{
		$getAutoresponderMaxQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE aid=%d ORDER BY `sequence` DESC LIMIT 1",$autoresponder->id);
		$results = $wpdb->get_results($getAutoresponderMaxQuery);
		
		$autoresponder = $results[0];
		if ($autoresponder->sequence > $max)
		   $max = $autoresponder->sequence;	
	}
	
	echo "The maximum autoresponder sequence is $max...\r\n";
	
	$deadline = $max/2;
	
	echo "The deadline is {$deadline} days from start...";
	$daysSinceStart = floor((time() - $startDate)/86400);
	
	echo "Days since start : {$daysSinceStart} ... ";
	$numberOfSubscribersToInsert = ceil(1000/$deadline);
	
	
	echo "Number of subscribers to insert is {$numberOfSubscribersToInsert} ...";
	foreach ($autoresponders as $autoresponder)
	{
		print "Inserting subscribers for autoresponder $autoresponder->name ...\r\n";
		for ($itr=0;$itr<$numberOfSubscribersToInsert; $itr++)
		{
			$name = "";
			for ($iter=0;$iter<10;$iter++)
			{
				$name .= chr(rand(97,122));
			}
			
			$email = "";
			for ($iter=0;$iter<10;$iter++)
			{
				$email .= chr(rand(97,122));
			}
			
			$email.="@";
			
			for ($iter=0;$iter<10;$iter++)
			{
				$email .= chr(rand(97,122));
			}
			
			$email.= ".com";
			
			$hash = "";
			for ($iter=0;$iter<10;$iter++)
			{
				$hash.= chr(rand(97,122));
			}
			
			$insertSubscriberQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_subscribers (nid, name, email, active, confirmed, date, hash) VALUES (%d, '%s','%s',1,1,'%s','%s');",$nid, $name, $email, time(), $hash);
			$wpdb->query($insertSubscriberQuery);
			
			$sid = $wpdb->insert_id;
			
			$insertAutoresponderSubscrioption = sprintf("INSERT INTO {$wpdb->prefix}wpr_followup_subscriptions (sid, type, eid, sequence, last_date, last_processed, doc) VALUES (%d, 'autoresponder', %d, %d, 0, 0, %d);",$sid, $autoresponder->id, -1, time());
			$wpdb->query($insertAutoresponderSubscrioption);
		}
	}
}

function runcron()
{
	do_action("_wpr_autoresponder_process");
}

function getOutputRecords()
{
	
}