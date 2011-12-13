<?php


function wpresponder_deactivate()
{
	//remove the schedules for the cronjobs	
	foreach ($GLOBALS['_wpr_crons'] as $cron)
    {
		wp_clear_scheduled_hook($cron);
    }

	//remove the capability
	global $wp_roles;
	$wp_roles->remove_cap( 'administrator', 'manage_newsletters' );
}
