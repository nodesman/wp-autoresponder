<?php
function _wpr_autoresponder_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_autoresponders where id=$id";
	$result = $wpdb->get_results($query);
	return $result[0];
	
}
function _wpr_autoresponders_get($nid)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_autoresponders where nid=$nid";
        
	$result = $wpdb->get_results($query);
	return $result;
	
}

function _wpr_get_autoresponders_of_newsletter($nid)
{
    global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_autoresponders where nid=$nid";
	$results = $wpdb->get_results($query);
	return $results;
}

