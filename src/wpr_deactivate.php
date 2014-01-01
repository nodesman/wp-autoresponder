<?php


function wpresponder_deactivate()
{
	//remove the schedules for the cronjob	
	wp_clear_scheduled_hook('wpr_cronjob');
	
	//remove the capability
	global $wp_roles;
	$wp_roles->remove_cap( 'administrator', 'manage_newsletters' );

}
