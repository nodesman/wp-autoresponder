<?php

function _wpr_queue_management_handler()
{
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
	
	_wpr_set("number_of_pending",$number_of_pending);
	_wpr_set("number_of_sent",$number_of_sent);
	_wpr_set("queue_table_size",$queue_table_size);
}