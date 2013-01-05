<?php

add_action("_wpr_maintenance","_wpr_maintenance");

function _wpr_maintenance()
{
    global $wpdb;
    //delete delivery records older than 30 days.
    $tableStatus = _wpr_delivery_record_size();
    if ($tableStatus > WPR_MAX_DELIVERY_RECORD_TABLE_SIZE)
    {
        $deleteDeliveryRecordsOfNonExistentBlogEmails = sprintf("DELETE FROM %swpr_delivery_record LIMIT 50000;",$wpdb->prefix);
        $wpdb->query($deleteDeliveryRecordsOfNonExistentBlogEmails);
    }   
    
    
    
    //delete unsubscribed emails not sent.
    $findNumberOfEmailsToDelete = sprintf("SELECT COUNT(*) number FROM %swpr_queue q, %swpr_subscribers s WHERE q.sid=s.id AND q.sent=0 AND s.active=0 AND s.confirmed=1;",$wpdb->prefix,$wpdb->prefix);
    $res = $wpdb->get_results($findNumberOfEmailsToDelete);
    $numberToDelete = $res[0]->number;
    
    $iterationCount = ceil($numberToDelete/100);
    for ($iter=0;$iter<$iterationCount;$iter++)
    {
        $findEmailsToDeleteQuery= sprintf("SELECT q.id id FROM %swpr_queue q, %swpr_subscribers s WHERE q.sid=s.id AND q.sent=0 AND s.active=0 AND s.confirmed=1 LIMIT 100;",$wpdb->prefix,$wpdb->prefix);
        $ids = $wpdb->get_results($findEmailsToDeleteQuery);
        
        foreach ($ids as $id)
        {
            $deleteFromQueue = sprintf("DELETE FROM %swpr_queue WHERE id=%d",$wpdb->prefix,$id->id);
            $wpdb->query($deleteFromQueue);
        }
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
