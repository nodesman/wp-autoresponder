<?php


class BroadcastProcessorTest extends WP_UnitTestCase {

    private $newsletterId;

    public function setUp() {
        JavelinTestHelper::deleteAllMessagesFromQueue();
        JavelinTestHelper::deleteAllNewsletters();
        JavelinTestHelper::deleteAllSubscribers();
        $this->newsletterId = $this->createNewsletter();
    }

    public function testWhetherSchedulingBroadcastsOnSpecifiedDateResultsInDeliveryOnSaidDate() {

        global $wpdb;
        $time = new DateTime();
        $subscriberId = $this->createSubscriber();

        $timestampTomorrow = new DateTime(sprintf("@%d",$time->getTimestamp() + 86400));
        $this->createBroadcast($timestampTomorrow);
        $broadcastId = $wpdb->insert_id;

        BroadcastProcessor::run($time);
        $numberOfEmailsInQueue = $this->getNumberOfEmailsEnqueued();
        $this->assertEquals(0, $numberOfEmailsInQueue);

        $randomOffset = rand(1000,10000);
        $timeObjectForTomorrowsRun = new DateTime(sprintf("@%d",$timestampTomorrow->getTimestamp()+ $randomOffset));

        BroadcastProcessor::run($timeObjectForTomorrowsRun);

        $checkNumberOfEmailsInQueueQuery = sprintf("SELECT * FROM %swpr_queue;", $wpdb->prefix);
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

    private function createBroadcast($timestampTomorrow)
    {
        global $wpdb;
        $createNewsletterBroadcastQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletter_mailouts (nid, subject, textbody, htmlbody, time, status) VALUES (%d, 'Subject', 'Textbody', 'Htmlbody', '%s', 0);", $this->newsletterId, $timestampTomorrow->getTimestamp());
        $wpdb->query($createNewsletterBroadcastQuery);
    }

    private function getNumberOfEmailsEnqueued()
    {
        global $wpdb;
        $checkNumberOfEmailsInQueueQuery = sprintf("SELECT COUNT(*) num from {$wpdb->prefix}wpr_queue;");
        $numberOfSubscribersResultSet = $wpdb->get_results($checkNumberOfEmailsInQueueQuery);
        $numberOfEmailsInQueue = $numberOfSubscribersResultSet[0]->num;
        return $numberOfEmailsInQueue;
    }

}