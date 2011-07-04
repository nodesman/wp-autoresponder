<?php


function wpresponder_deactivate()
{
	//remove the schedules for the cronjob	
	wp_clear_scheduled_hook('wpr_cronjob');
}



