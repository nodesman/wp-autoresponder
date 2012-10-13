<?php
require_once __DIR__."/../models/newsletter.php";

class NewsletterTest extends WP_UnitTestCase {
	
	
	public function setUp() {
        parent::setUp();
		global $wpdb;
		$truncateNewslettersTableQuery = sprintf("TRUNCATE %swpr_newsletters",$wpdb->prefix);
		$wpdb->query($truncateNewslettersTableQuery);

        $truncateSubscribersTableQuery = sprintf("TRUNCATE %swpr_subscribers",$wpdb->prefix);
        $wpdb->query($truncateSubscribersTableQuery);

	}
	

	public function testWhetherNewsletterExists() {
		
		$whetherNewsletterExists = Newsletter::whetherNewsletterIDExists(9801);
		$this->assertFalse($whetherNewsletterExists);

		
		
	}

    public function tearDown() {
        parent::tearDown();
        global $wpdb;
        $truncateNewslettersTableQuery = sprintf("TRUNCATE %swpr_newsletters",$wpdb->prefix);
        $wpdb->query($truncateNewslettersTableQuery);
    }


}
