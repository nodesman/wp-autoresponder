<?php
global $wpdb;
if (current_user_can('manage_newsletters'))
{
	$id = intval($_GET['wpr-vb']);
	if ($id == 0)
		exit;
	
	$prefix = $wpdb->prefix;
	$tableName = $prefix."wpr_newsletter_mailouts";	
		
	$getBroadcastQuery = "SELECT * FROM $tableName WHERE id=$id";
	$broadcastResult = $wpdb->get_results($getBroadcastQuery);
	$broadcast = $broadcastResult[0];
	$htmlbody = $broadcast->htmlbody;
	if (empty($htmlbody))
	{
		echo __("The HTML body is empty. No HTML body specified.");
	}
	else
	{
		echo $htmlbody;
	}
}
