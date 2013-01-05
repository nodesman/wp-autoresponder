<?php
include __DIR__. "/../src/models/subscriber.php";

class SubscriberTest extends WP_UnitTestCase {


    var $newsletter_info = array(
      "nid" => 1,
      "name" => "Test Newsletter",
      "reply_to" => "test@test.com",
      "fromname" => "Test",
      "fromemail" => "test@test.com"
    );

    public function setUp() {
        parent::setUp();
        global $wpdb;
        //create newsletter
        $truncateNewsletterTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_newsletters");
        $wpdb->query($truncateNewsletterTable);

        $insertNewsletterQuery  =sprintf('INSERT INTO `%swpr_newsletters` (`id`, `name`, `reply_to`, `fromname`, `fromemail`) VALUES (%d, "%s", "%s", "%s", "%s")', $wpdb->prefix, $this->newsletter_info['nid'], $this->newsletter_info['name'], $this->newsletter_info['reply_to'], $this->newsletter_info['fromname'], $this->newsletter_info['fromemail']);
        $wpdb->query($insertNewsletterQuery);

    }

    /**
     * @expectedException NewsletterNotFoundException
     */
    public function testFetchingFromNonExistentNewsletterResultsInException() {
        Subscriber::getSubscribersOfNewsletter(9801);
    }



    public function  tearDown() {
        parent::tearDown();
        global $wpdb;
        $truncateNewsletterTable = sprintf('TRUNCATE %swpr_newsletters', $wpdb->prefix);
        $wpdb->query($truncateNewsletterTable);

    }
	
}
