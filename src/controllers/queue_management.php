<?php

function _wpr_queue_management_handler()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	if (isset($_GET['subact']))
	{
		$act = $_GET['subact'];
		switch ($act)
		{
			case 'truncate_queue':
				if (check_admin_referer("_wpr_truncate_queue"))
				{
					_wpr_queue_truncate();
					wp_redirect("admin.php?page=_wpr/queue_management");
				}
			break;
			case 'delete_sent_emails':
				if (check_admin_referer("_wpr_delete_sent_mail"))
				{
					_wpr_queue_delete_sentmail();
					wp_redirect("admin.php?page=_wpr/queue_management");
				}
			break;
		}
		
	}
	$number_of_pending = _wpr_queue_get_pendingmail_count();
	$number_of_sent = _wpr_queue_get_sentmail_count();
	$queue_table_size = _wpr_queue_size();
	
	$numberOfEmailsPerPage = intval($_GET['emails_per_page']);
	
	$numberOfEmailsPerPage = ($numberOfEmailsPerPage==0)?50:$numberOfEmailsPerPage;
	$start = intval($_GET['start']);
	$start = ($start != 0)?$start:1;
	
	/*$getEmailsFromQueue = sprintf("SELECT * FROM {$prefix}_wpr_queue ORDER BY id desc LIMIT $start,$numberOfEmailsPerPage;");
	$emailsInQueue = $wpdb->query($getEmailsFromQueue);*/
	
	_wpr_set("number_of_pending",$number_of_pending);
	_wpr_set("emails_in_queue",$emailsInQueue);
	_wpr_set("number_of_sent",$number_of_sent);
	_wpr_set("queue_table_size",$queue_table_size);
    _wpr_setview("queue_management");
}