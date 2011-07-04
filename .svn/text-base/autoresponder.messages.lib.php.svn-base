<?php
function _wpr_get_autoresponder_message($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_autoresponder_messages where id=".$id;
	$result = $wpdb->get_results($query);
	return $result[0];
}
