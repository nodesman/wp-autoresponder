<?php

require_once __DIR__."/../../src/models/iterators/pending_broadcasts.php";

class BroadcastProcessorTest extends WP_UnitTestCase {

    private $newsletterId;

    public function setUp() {
        WPRTestHelper::deleteAllMessagesFromQueue();
        WPRTestHelper::deleteAllNewsletters();
        WPRTestHelper::deleteAllSubscribers();
        $this->newsletterId = $this->createNewsletter();
    }

    public function testWhetherSchedulingBroadcastsOnSpecifiedDateResultsInDeliveryOnSaidDate() {

        global $wpdb;
        $time = new DateTime();
        $subscriberId = $this->createSubscriber();

        $timestampTomorrow = new DateTime(sprintf("@%d",$time->getTimestamp() + 86400));
        $createNewsletterBroadcastQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletter_mailouts (nid, subject, textbody, htmlbody, time, status) VALUES (%d, 'Subject', 'Textbody', 'Htmlbody', '%s', 0);", $this->newsletterId, $timestampTomorrow->getTimestamp());
        $wpdb->query($createNewsletterBroadcastQuery);
        $broadcastId = $wpdb->insert_id;

        BroadcastProcessor::run($time);

        $checkNumberOfEmailsInQueueQuery = sprintf("SELECT COUNT(*) num from {$wpdb->prefix}wpr_queue;");
        $numberOfSubscribersResultSet = $wpdb->get_results($checkNumberOfEmailsInQueueQuery);
        $numberOfEmailsInQueue = $numberOfSubscribersResultSet[0]->num;

        $this->assertEquals(0, $numberOfEmailsInQueue);

        $timeOfRunTomorrow = new DateTime(sprintf("@%d",$timestampTomorrow->getTimestamp()+5000));

        BroadcastProcessor::run($timeOfRunTomorrow);

        $checkNumberOfEmailsInQueueQuery = sprintf("SELECT *from {$wpdb->prefix}wpr_queue;");
        $numberOfSubscribersResultSet = $wpdb->get_results($checkNumberOfEmailsInQueueQuery);
        $numberOfEmailsInQueue = count($numberOfSubscribersResultSet);

        $this->assertEquals(1, $numberOfEmailsInQueue);

        $theEmailEnqueued = $numberOfSubscribersResultSet[0];
        $emailMetaKey = sprintf("BR-%s-%s-%s",$subscriberId, $broadcastId, $this->newsletterId);
        $this->assertEquals($emailMetaKey, $theEmailEnqueued->meta_key);
    }

    private function createNewsletter()
    {
        global $wpdb;
        $createNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `fromname`, `fromemail`) VALUES ('Test', 'Raj', 'flarecore@gmail.com')");
        $wpdb->query($createNewsletterQuery);

        $newsletterId = $wpdb->insert_id;
        return $newsletterId;
    }

    private function createSubscriber()
    {
        global $wpdb;
        $createSubscriberQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_subscribers (nid, name, email, active, confirmed, hash) VALUES (%d, 'Raj', 'flare@gmail.com', 1, 1, MD5(UNIX_TIMESTAMP()))", $this->newsletterId);
        $wpdb->query($createSubscriberQuery);

        $subscriberId = $wpdb->insert_id;
        return $subscriberId;
    }

}