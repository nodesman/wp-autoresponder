<?php
require_once __DIR__."/../models/newsletter.php";

class NewsletterTest extends WP_UnitTestCase {
	
	
	public function setUp() {
		global $wpdb;
		$truncateNewslettersTableQuery = sprintf("TRUNCATE %swpr_newsletters",$wpdb->prefix);
		$wpdb->query($truncateNewslettersTableQuery);
	}
	
	public function tearDown() {
		global $wpdb;
		$truncateNewslettersTableQuery = sprintf("TRUNCATE %swpr_newsletters",$wpdb->prefix);
		$wpdb->query($truncateNewslettersTableQuery);
	}
	
	
	public function testWhetherNewsletterExists() {
		
		$whetherNewsletterExists = Newsletter::whetherNewsletterIDExists(9801);
		$this->assertFalse($whetherNewsletterExists);
		
		//TODO: Add a newsletter and then get the 
		
		
		
		
	}
	
	
}
