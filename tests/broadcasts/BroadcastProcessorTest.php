<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rajasekharan
 * Date: 26/04/13
 * Time: 6:36 PM
 * To change this template use File | Settings | File Templates.
 */

class BroadcastProcessorTest extends WP_UnitTestCase {

    private $nid;

    public function setUp() {
        WPRTestHelper::deleteAllMessagesFromQueue();
        WPRTestHelper::deleteAllNewsletters();
        WPRTestHelper::deleteAllSubscribers();
        $this->nid = $this->createNewsletter();
    }

    public function testWhetherSchedulingBroadcastsOnSpecifiedDateResultsInDeliveryOnSaidDate() {


        global $wpdb;

        $time = new DateTime();

        $createSubscriberQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_subscribers (nid, name, email, active, confirmed, hash) VALUES (%d, 'Raj', 'flare@gmail.com', 1, 1, MD5(UNIX_TIMESTAMP()))", $this->nid);
        $wpdb->query($createSubscriberQuery);

        $sid = $wpdb->insert_id;

        $timestampTomorrow = new DateTime(sprintf("@%d",$time->getTimestamp() + 86400));
        $createNewsletterBroadcastQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletter_mailouts (nid, subject, textbody, htmlbody, time, status) VALUES (%d, 'Subject', 'Textbody', 'Htmlbody', '%s', 0);", $this->nid, $timestampTomorrow->getTimestamp());
        $wpdb->query($createNewsletterBroadcastQuery);

        $bid = $wpdb->insert_id;

        BroadcastProcessor::run_for_time($time);

        $checkNumberOfEmailsInQueueQuery = sprintf("SELECT COUNT(*) num from {$wpdb->prefix}wpr_queue;");
        $numSubs = $wpdb->get_results($checkNumberOfEmailsInQueueQuery);
        $num = $numSubs[0]->num;

        $this->assertEquals(0, $num);

        $timeOfRunTomorrow = new DateTime(sprintf("@%d",$timestampTomorrow->getTimestamp()+5000));

        $i = Newsletter::getNewsletter($this->nid);

        BroadcastProcessor::run_for_time($timeOfRunTomorrow);

        $checkNumberOfEmailsInQueueQuery = sprintf("SELECT *from {$wpdb->prefix}wpr_queue;");
        $numSubs = $wpdb->get_results($checkNumberOfEmailsInQueueQuery);
        $num = count($numSubs);

        $this->assertEquals(1, $num);


        $email = $numSubs[0];
        $meta_key = sprintf("BR-%s-%s-%s",$sid, $bid, $this->nid);

        $this->assertEquals($meta_key, $email->meta_key);

    }

    /**
     * @param $wpdb
     * @return mixed
     */
    private function createNewsletter()
    {
        global $wpdb;
        $createNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `fromname`, `fromemail`) VALUES ('Test', 'Raj', 'flarecore@gmail.com')");
        $wpdb->query($createNewsletterQuery);

        $nid = $wpdb->insert_id;
        return $nid;
    }

}