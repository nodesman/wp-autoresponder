<?php
function _wpr_postseries_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_blog_series where id=$id";
	$result = $wpdb->get_results($query);
	return $result[0];
	
}

function _wpr_postseries_get_all()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_blog_series";
	$result = $wpdb->get_results($query);
        if (count($result))
            return $result;
        else
            return false;

}