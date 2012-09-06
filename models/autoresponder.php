<?php
class Autoresponder
{
	
	public static function getAllAutoresponders() {
		global $wpdb;
		$getAllAutorespondersQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponders");
		return $wpdb->get_results($getAllAutorespondersQuery);
	}
	
	public static function getAutoresponderById($autoresponder_id) {
		global $wpdb;
		$getAutoresponderQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_subscribers WHERE id=%d",$autoresponder_id);
		$autoresponder = $wpdb->get_results($getAutoresponderQuery);
		if (count($autoresponder) == 0)
			return null;
		return $autoresponder;
	}
	
	
	public static function whetherValidAutoresponder($autoresponderInfo) {
		
		if ("array" != gettype($autoresponderInfo)) {
			throw new InvalidAutoresponderTypeArgumentException();
		}
		
		if (!isset($autoresponderInfo['name'])) {
			return false;
		}
		
		$name = trim($autoresponderInfo['name']);
		
		if (preg_match("@[\"']@",$name)) {
			return false;
		}
		
		if (0 == strlen($name)) {
			return false;
		}
		
		return true;
		
	}
	
	
}



class InvalidAutoresponderTypeArgumentException extends Exception {

}