<?php

require_once __DIR__."/../../src/processes/autoresponder_process.php";
require_once __DIR__."/../../src/models/autoresponder.php";

class AutoresponderProcessTimingTest extends WP_UnitTestCase {
    private $newsletter_id;
    private $autoresponder_id;
    private $timeOfSubscription = 1358610262;

    public $numberOfSubscribersAdded = 5;

    public function setUp() {

        //truncate all relevant tables
        global $wpdb;

        $wpdb->show_errors();

        $this->truncateAllRelevantTables();


        $insertNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters ( `name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s', '%s', '%s', '%s')", $wpdb->prefix, 'Test', 'flare@gmail.com', 'Raj', 'flarecore@gmail.com');
        $wpdb->query($insertNewsletterQuery);

        $this->newsletter_id = $wpdb->insert_id;

        $insertAutoresponderQuery = sprintf("INSERT INTO `%swpr_autoresponders` (`nid`, `name`) VALUES (%d, '%s') ", $wpdb->prefix, $this->newsletter_id, 'Test Newsletter');
        $wpdb->query($insertAutoresponderQuery);


        $this->autoresponder_id = $wpdb->insert_id;


        $insertMessagesQuery = sprintf("INSERT INTO `%swpr_autoresponder_messages` (`aid`, `subject`, `htmlenabled`, `sequence`) VALUES (%d, 'Day 0 Message', 1, 0), (%d, 'Day 1 Message', 1, 1), (%d, 'Day 5 Message', 1, 5) ; ", $wpdb->prefix, $this->autoresponder_id, $this->autoresponder_id, $this->autoresponder_id);
        $wpdb->query($insertMessagesQuery);

        for ($iter =0 ; $iter< $this->numberOfSubscribersAdded; $iter++) {

            $insertSubscribersQuery = sprintf("INSERT INTO %swpr_subscribers (nid, `name`, `email`, `active`, `confirmed`, `hash`) VALUES (%d, 'name%d', 'email%d@domain%d.com', 1, 1, %s)", $wpdb->prefix, $this->newsletter_id, $iter, $iter, $iter, time(), md5($iter.microtime()));
            $wpdb->query($insertSubscribersQuery);
            $subscriber_id = $wpdb->insert_id;

            $addSubscriptionQuery = sprintf("INSERT INTO %swpr_followup_subscriptions (sid, type, eid, sequence, last_date, last_processed, doc) VALUES (%d, 'autoresponder', %d, -1, 0, 0, %d)", $wpdb->prefix, $subscriber_id, $this->autoresponder_id, $this->timeOfSubscription); //Jan 19, 2013
            $wpdb->query($addSubscriptionQuery);
        }
    }

    public function truncateAllRelevantTables()
    {
        global $wpdb;
        $truncateAutorespondersQuery = sprintf("TRUNCATE %swpr_autoresponders;", $wpdb->prefix);
        $wpdb->query($truncateAutorespondersQuery);

        $truncateAutorespondersQuery = sprintf("TRUNCATE %swpr_autoresponder_messages;", $wpdb->prefix);
        $wpdb->query($truncateAutorespondersQuery);

        $truncateNewslettersQuery = sprintf("TRUNCATE %swpr_newsletters;", $wpdb->prefix);
        $wpdb->query($truncateNewslettersQuery);

        $truncateQueueQuery = sprintf("TRUNCATE %swpr_queue", $wpdb->prefix);
        $wpdb->query($truncateQueueQuery);

        $truncateSubscriptionsQuery = sprintf("TRUNCATE %swpr_followup_subscriptions;", $wpdb->prefix);
        $wpdb->query($truncateSubscriptionsQuery);

        $truncateSubscribersQuery = sprintf("TRUNCATE %swpr_subscribers;", $wpdb->prefix);
        $wpdb->query($truncateSubscribersQuery);

        $truncateSubscribersQuery = sprintf("TRUNCATE %swpr_queue;", $wpdb->prefix);
        $wpdb->query($truncateSubscribersQuery);
    }

    public function testWhetherDayZeroDeliveryResultsInDayZeroEmails() {

        global $wpr_autoresponder_processor, $wpdb;
        $timeOfRun = $this->timeOfSubscription+rand(1,300); //within the 5 minutes following
        $wpr_autoresponder_processor->run_for_time(new DateTime(sprintf("@%s",$timeOfRun)));

        $getMessageForDayZeroId = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE `aid`=%d AND `sequence`=%d", $this->autoresponder_id, 0);
        $messageRes = $wpdb->get_results($getMessageForDayZeroId);
        $message = $messageRes[0];

        $meta_key = sprintf("AR-%s-%%%%-%s-0", $this->autoresponder_id, $message->id);
        $getMessagesQuery = sprintf("SELECT * FROM %swpr_queue WHERE meta_key LIKE '%s';", $wpdb->prefix, $meta_key);
        $messagesDelivered = $wpdb->get_results($getMessagesQuery);

        $numberOfMessages = count($messagesDelivered);

        $this->assertEquals($this->numberOfSubscribersAdded, $numberOfMessages);

    }

    public function testWhetherDayOneDeliveryResultsInDayOneEmails() {

        global $wpr_autoresponder_processor, $wpdb;
        $currentDayNumber = "1";
        $timeOfRun = $this->timeOfSubscription+(86400*$currentDayNumber); //within the 5 minutes following
        $wpr_autoresponder_processor->run_for_time(new DateTime(sprintf("@%s",$timeOfRun)));

        $getMessageForDayZeroId = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE `aid`=%d AND `sequence`=%d", $this->autoresponder_id, $currentDayNumber);
        $messageRes = $wpdb->get_results($getMessageForDayZeroId);
        $message = $messageRes[0];

        $meta_key = sprintf("AR-%s-%%%%-%s-{$currentDayNumber}", $this->autoresponder_id, $message->id);
        $getMessagesQuery = sprintf("SELECT * FROM %swpr_queue WHERE meta_key LIKE '%s';", $wpdb->prefix, $meta_key);
        $messagesDelivered = $wpdb->get_results($getMessagesQuery);

        $numberOfMessages = count($messagesDelivered);

        $this->assertEquals($this->numberOfSubscribersAdded, $numberOfMessages);

        //check whether running the cron again doesn't enqueue the same messages again.
        $truncateQueueQuery = sprintf("TRUNCATE %swpr_queue", $wpdb->prefix);
        $wpdb->query($truncateQueueQuery);

        $nextRunOnSameDay = $timeOfRun + 200;
        $wpr_autoresponder_processor->run_for_time(new DateTime(sprintf("@%s",$nextRunOnSameDay)));

        $getMessagesQuery = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE meta_key LIKE '%s';", $wpdb->prefix, $meta_key);
        $numberOfMessagesDeliveredRes = $wpdb->get_results($getMessagesQuery);
        $numberEnqueuedOnSecondRunOnSameDay = $numberOfMessagesDeliveredRes[0]->num;

        $this->assertEquals(0, $numberEnqueuedOnSecondRunOnSameDay);

    }

    public function tearDown() {
        $this->truncateAllRelevantTables();

    }
}
