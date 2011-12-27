<?php

add_action("_wpr_maintenance","_wpr_maintenance");

function _wpr_maintenance()
{
    //delete delivery records older than 30 days.
    $tableStatus = _wpr_delivery_record_size();
    if ($tableStatus > WPR_MAX_DELIVERY_RECORD_TABLE_SIZE)
    {
        $deleteDeliveryRecordsOfNonExistentBlogEmails = sprintf("DELETE FROM %swpr_delivery_record LIMIT 50000;",$wpdb->prefix);
        $wpdb->query($deleteDeliveryRecordsOfNonExistentBlogEmails);
    }   
}

function _wpr_delivery_record_size()
{
	global $wpdb;
	$queue_table = $wpdb->prefix."wpr_delivery_record";
	$getTablesStatusQuery = "SHOW TABLE STATUS;";
	$tableStatuses = $wpdb->get_results($getTablesStatusQuery);	

	foreach ($tableStatuses as $status)
	{
		if ($status->Name == $queue_table)
		{
		  $table_size = $status->Data_length;
                  break;
		}
	}

	return $table_size;
}
