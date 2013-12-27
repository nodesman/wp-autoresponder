<?php
include_once __DIR__."/../src/models/newsletter.php";

class NewsletterTest extends WP_UnitTestCase {

	public function setUp() {
        parent::setUp();
		global $wpdb;
		$truncateNewslettersTableQuery = sprintf("TRUNCATE %swpr_newsletters",$wpdb->prefix);
		$wpdb->query($truncateNewslettersTableQuery);


        $truncateSubscribersTableQuery = sprintf("TRUNCATE %swpr_custom_fields",$wpdb->prefix);
        $wpdb->query($truncateSubscribersTableQuery);

        $truncateSubscribersTableQuery = sprintf("TRUNCATE %swpr_subscribers",$wpdb->prefix);
        $wpdb->query($truncateSubscribersTableQuery);

	}

    public function testWhetherNoNewslettersExistChecker() {
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
        global $wpdb;
		
		$whetherNewsletterExists = Newsletter::whetherNewsletterIDExists(9801);
		$this->assertFalse($whetherNewsletterExists);


        $newsletter = array(
            "name" => md5(microtime()),
            "reply_to" => 'flarecore@'.md5(microtime()).'.com',
            "fromname" => 'Test',
            "fromemail" => 'flare@'.md5(microtime()).'.com'
        );

        $addNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters (`name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s','%s', '%s', '%s')", $wpdb->prefix, $newsletter['name'], $newsletter['reply_to'], $newsletter['fromname'], $newsletter['fromemail']);
        $wpdb->query($addNewsletterQuery);
        $newsletter_id = $wpdb->insert_id;

        $this->assertTrue(Newsletter::whetherNewsletterIDExists($newsletter_id));

	}

    public function testWhetherNewsletterFetchesItsCustomFieldsNames() {

        global $wpdb;
        //add a newsletter
        $addNewsletterQuery = sprintf("INSERT INTO  %swpr_newsletters (`name`) VALUES ('%s')", $wpdb->prefix, 'Test Newsletter');
        $wpdb->query($addNewsletterQuery);

        $id = $wpdb->insert_id;

        $expected = array();

        for ($iter=0;$iter<5;$iter++) {

            $name = md5(microtime()."name".$iter);

            $expected[] = $name;
            $addCustomFieldsQuery = sprintf("INSERT INTO %swpr_custom_fields (`nid`, `type`, `name`, `label`) VALUES (%d, 'text', '%s', '%s')", $wpdb->prefix, $id, $name, $name);
            $wpdb->query($addCustomFieldsQuery);
        }

        $newsletter = Newsletter::getNewsletter($id);

        $custom_field_keys =  $newsletter->getCustomFieldKeys();

        $intersect = array_intersect($custom_field_keys, $expected);
        $diff = array_diff($intersect, $expected);
        $this->assertEquals(0, count($diff));
    }


    public function testWhetherNewsletterFetchesItsCustomFieldsKeyLabelPairs() {

        global $wpdb;
        //add a newsletter
        $addNewsletterQuery = sprintf("INSERT INTO  %swpr_newsletters (`name`) VALUES ('%s')", $wpdb->prefix, 'Test Newsletter');
        $wpdb->query($addNewsletterQuery);

        $id = $wpdb->insert_id;

        $expected = array();

        for ($iter=0;$iter<5;$iter++) {

            $name = md5(microtime()."name".$iter);

            $expected[$name] = strtoupper($name);
            $addCustomFieldsQuery = sprintf("INSERT INTO %swpr_custom_fields (`nid`, `type`, `name`, `label`) VALUES (%d, 'text', '%s', '%s')", $wpdb->prefix, $id, $name, strtoupper($name));
            $wpdb->query($addCustomFieldsQuery);
        }

        $newsletter = Newsletter::getNewsletter($id);

        $custom_field_keys =  $newsletter->getCustomFieldKeyLabelPair();

        $intersect = array_intersect($custom_field_keys, $expected);
        $diff = array_diff($intersect, $expected);
        $this->assertEquals(0, count($diff));
    }

    /**
     * @expectedException NonExistentNewsletterException
     */
    public function testGetNewsletterFactoryFetchesOnlyExistentNewsletters() {
        Newsletter::getNewsletter(9876);
    }

    public function testGetNewsletterFactoryFetchesExistentNewsletters() {

        global $wpdb;
        $addNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters (`name`) VALUES ('%s')", $wpdb->prefix, 'Test Newsletter');
        $wpdb->query($addNewsletterQuery);
        $newsletter = Newsletter::getNewsletter(intval($wpdb->insert_id));
        $this->assertEquals($wpdb->insert_id, $newsletter->getId());
    }


    public function tearDown() {
        global $wpdb;
        $truncateNewslettersTableQuery = sprintf("TRUNCATE %swpr_newsletters",$wpdb->prefix);
        $wpdb->query($truncateNewslettersTableQuery);
        parent::tearDown();
    }


}
