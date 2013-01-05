<?php


function _wpr_background_procs_handler()
{
	if (isset($_GET['run']))
	{
		_wpr_background_procs_manual_run();
	}
		
		
	
}

function _wpr_background_procs_manual_run()
{
	if (isset($_GET['run']))
	{
		$background_procs_url = admin_url("admin.php?page=_wpr/background_procs");
		switch ($_GET['run'])
		{
			case 'autoresponder':
				if (check_admin_referer("_wpr_autoresponder_run"))
				{
					do_action("_wpr_autoresponder_process");                                    
					wp_redirect($background_procs_url);
				}
			break;
				case 'postseries':
				if (check_admin_referer("_wpr_postseries_run"))
				{
					do_action("_wpr_postseries_process");
					wp_redirect($background_procs_url);
				}
			break;
				case 'newsletter_process':
				if (check_admin_referer("_wpr_newsletter_process_run"))
				{
					do_action("_wpr_process_broadcasts");
					wp_redirect($background_procs_url);
				}
			break;
			
				case 'blogpost_processor':
				if (check_admin_referer("_wpr_blogpost_processor_run"))
				{
					do_action("_wpr_process_blog_subscriptions");
					wp_redirect($background_procs_url);
				}
			break;
				case 'blogcat_processor':
				if (check_admin_referer("_wpr_blogcat_processor_run"))
				{
					do_action("_wpr_process_blog_category_subscriptions");
					wp_redirect($background_procs_url);
				}
			break;
			
				case 'delivery_queue':
				if (check_admin_referer("_wpr_delivery_queue_run"))
				{
					do_action("_wpr_process_queue");                                    					
					wp_redirect($background_procs_url);
				}
			break;
			
			default:
			
		}
		
	}
}