<?php


function wpresponder_deactivate()
{
	//remove the schedules for the cronjob	
<<<<<<< HEAD
=======
	wp_clear_scheduled_hook('wpr_cronjob');
	
	//remove the capability
	global $wp_roles;
	$wp_roles->remove_cap( 'administrator', 'manage_newsletters' );
>>>>>>> bbe6bc727c48a57e62a5320185a341f7726a325e
}
