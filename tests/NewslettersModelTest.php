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

    public function testWhetherNoNewslettersExistChecker() {
        global $wpdb;
        $result = Newsletter::whetherNoNewslettersExist();
        $this->assertEquals(true, $result);
    }


    public function testGetAllNewsletters() {
        global $wpdb;
        //load 5 newsletters
        $list = array();
        $newsletterNamesList = array();

        for ($iter =0 ; $iter < 5; $iter++) {
            $current = array(
                "name" => "Autoresponder_".microtime(),
                "reply_to" => "flarecore@gmail.com",
                "fromname" => "Test",
                "fromemail"  => "testest@".microtime()."test.com"
            );

            $list[] = $current;
            $newsletterNamesList[] = $current['name'];
        }


        foreach ($list as $newsletter) {

            $addNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s', '%s','%s', '%s');",$newsletter['name'], $newsletter['reply_to'], $newsletter['fromname'], $newsletter['fromemail']);
            $wpdb->query($addNewsletterQuery);
        }

        $newsletters = Newsletter::getAllNewsletters();

        $namesReceived = array();
        foreach ($newsletters as $newsletter) {
            $namesReceived[] = $newsletter->getName();
        }

        $diff = array_diff($newsletterNamesList, $namesReceived);
        $this->assertEquals(0, count($diff));
    }

	public function testWhetherNewsletterExists() {
		
		$whetherNewsletterExists = Newsletter::whetherNewsletterIDExists(9801);
		$this->assertFalse($whetherNewsletterExists);

	}

    public function tearDown() {
        global $wpdb;
        $truncateNewslettersTableQuery = sprintf("TRUNCATE %swpr_newsletters",$wpdb->prefix);
        $wpdb->query($truncateNewslettersTableQuery);
        parent::tearDown();
    }


}
