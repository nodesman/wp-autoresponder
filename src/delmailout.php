<?php
include "wp-load.php";

global $wpdb;

if (!current_user_can("manage_newsletters") )
{
	header("HTTP/1.0 404 Not Found");
	exit;
}
$id = $_GET['mid'];

$query = "DELETE from ".$wpdb->prefix."wpr_newsletter_mailouts WHERE id=$id";
$wpdb->query($query);
