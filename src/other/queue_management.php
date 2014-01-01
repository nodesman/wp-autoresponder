<?php

function _wpr_queue_delete_sentmail()
{
	global $wpdb;
	$queue_table = $wpdb->prefix."wpr_queue";
	$deleteSentEmailsQueue = "DELETE FROM $queue_table WHERE sent=1;";
	$wpdb->query($deleteSentEmailsQueue);
}

function _wpr_queue_truncate()
{
	global $wpdb;
	$queue_table = $wpdb->prefix."wpr_queue";
	$truncateQueueQuery = "TRUNCATE TABLE `$queue_table`;";
	$wpdb->query($truncateQueueQuery);
}

function _wpr_queue_get_sentmail_count()
{
	global $wpdb;
	$queue_table = $wpdb->prefix."wpr_queue";
	$truncateQueueQuery = "SELECT COUNT(*) num FROM $queue_table WHERE SENT=1";
	$queue_count = $wpdb->get_results($truncateQueueQuery);
	return $queue_count[0]->num;
}

function _wpr_queue_get_pendingmail_count()
{
	global $wpdb;
	$queue_table = $wpdb->prefix."wpr_queue";
	$truncateQueueQuery = "SELECT COUNT(*) num FROM $queue_table WHERE SENT=0";
	$queue_count = $wpdb->get_results($truncateQueueQuery);
	return $queue_count[0]->num;
}


function _wpr_reset_table_check()
{
	delete_option("_wpr_limit_reached_email_sent");
	delete_option("_wpr_limit_approaching_email_sent");
	_wpr_admin_notice_delete("_wpr_queue_limit");
}

function _wpr_queue_size()
{
	global $wpdb;
	$queue_table = $wpdb->prefix."wpr_queue";
	$getTablesStatusQuery = "SHOW TABLE STATUS;";
	$tableStatuses = $wpdb->get_results($getTablesStatusQuery);	

	foreach ($tableStatuses as $status)
	{
		if ($status->Name == $queue_table)
		{
			$table_size = $status->Data_length;
		}
	}

	return $table_size;
}

function _wpr_queue_management_cron()
{
	$whetherAnotherInstanceIsRunning = get_option("_wpr_queue_management_cron_status");
	if ($whetherAnotherInstanceIsRunning == "running")
		return;
	delete_option("_wpr_queue_management_cron_status");
	add_option("_wpr_queue_management_cron_status","running");
	

	$sizeOfQueueTable = _wpr_queue_size();
	
	if ($sizeOfQueueTable >= WPR_MAX_QUEUE_TABLE_SIZE)
	{
		_wpr_queue_delete_sentmail(); 
		//usually that fixes the problem.. 
	}
	
	delete_option("_wpr_queue_management_cron_status");
	add_option("_wpr_queue_management_cron_status","stopped");
			
}