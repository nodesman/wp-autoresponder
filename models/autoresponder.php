<?php
class Autoresponder
{
	
	public function getAllAutoresponders() {
		global $wpdb;
		$getAllAutorespondersQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponders");
		return $wpdb->get_results($getAllAutorespondersQuery);
	}
	
	
}