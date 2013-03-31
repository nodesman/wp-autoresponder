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


            $insertSubscribersQuery = sprintf("INSERT INTO %swpr_subscribers (nid, `name`, `email`, `active`, `confirmed`, `hash`) VALUES (%d, 'name%d', 'email2dd%d@domain%d.com', 1, 0, %s)", $wpdb->prefix, $this->newsletter_id, $iter, $iter, $iter, time(), md5($iter.microtime()));
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

        $this->truncateMessagesDefinitions($wpdb);

        $truncateNewslettersQuery = sprintf("TRUNCATE %swpr_newsletters;", $wpdb->prefix);
        $wpdb->query($truncateNewslettersQuery);

        $this->truncateQueue($wpdb);

        $truncateSubscriptionsQuery = sprintf("TRUNCATE %swpr_followup_subscriptions;", $wpdb->prefix);
        $wpdb->query($truncateSubscriptionsQuery);

        $truncateSubscribersQuery = sprintf("TRUNCATE %swpr_subscribers;", $wpdb->prefix);
        $wpdb->query($truncateSubscribersQuery);

    }

    public function truncateMessagesDefinitions()
    {
        global $wpdb;
        $truncateAutorespondersQuery = sprintf("TRUNCATE %swpr_autoresponder_messages;", $wpdb->prefix);
        $wpdb->query($truncateAutorespondersQuery);
    }

    public function truncateQueue()
    {
        global $wpdb;
        $truncateQueueQuery = sprintf("TRUNCATE %swpr_queue", $wpdb->prefix);
        $wpdb->query($truncateQueueQuery);
    }

    public function testWhetherDayZeroDeliveryResultsInDayZeroEmailsOnlyToSubscribedSubscribers() {

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

    public function testWhetherDayOneDeliveryResultsInDayOneEmailsOnlyToSubscribedSubscribers() {

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

    public function testWhetherRunningCronOnActivationFollowingDeactivationResultsInCronResumingFromLastProcessingPoint() {

        global $wpdb;

        $currentTime = time();

        $this->truncateQueue();
        $this->truncateMessagesDefinitions();



        $createAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d, 'xperia');", $wpdb->prefix, $this->newsletter_id);
        $this->assertEquals(1, $wpdb->query($createAutoresponderQuery));

        $autoresponder_id = $wpdb->insert_id;


        //insert a subscriber

        $insertSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (`nid`, `name`, `email`, `date`, `active`, `confirmed`, `hash`) VALUES (%d, 'raj', 'flarecore@gmail.com', '324242424', 1, 1, '32asdf42');", $wpdb->prefix, $this->newsletter_id);
        $this->assertEquals(1, $wpdb->query($insertSubscriberQuery));

        $subscriber_id = $wpdb->insert_id;

        //insert a message to the autoresponder with the custom field value in the html, text bodies and subject

        $message_ids = array();

        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 1', '@@Text 1@@', '@@Html 1@@', 0)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["0"] = $wpdb->insert_id;



        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 2', '@@Text @@', '@@Html @@', 1)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["1"] = $wpdb->insert_id;


        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 5', '@@Text 5@@', '@@Html 5@@', 5)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["5"] = $wpdb->insert_id;


        //add a subscription for the above subscriber such that running the process will result in that message being enqueued.

        $insertSubscriptionQuery = sprintf("INSERT INTO %swpr_followup_subscriptions (eid, type, sid, doc, last_processed, last_date, sequence) VALUES (%d, 'autoresponder', %d, %d, %d, 0, -1);",$wpdb->prefix, $autoresponder_id, $subscriber_id, $currentTime, $currentTime);
        $this->assertEquals(1, $wpdb->query($insertSubscriptionQuery));

        $processor  = AutoresponderProcessor::getProcessor();
        $timeObject = new DateTime();
        $timeObject->setTimestamp($currentTime+400);
        $processor->run_for_time($timeObject);

        //assert if this is the day zero email.

        $getQueueEmailQuery = sprintf("SELECT * FROM wp_wpr_queue;");
        $emails = $wpdb->get_results($getQueueEmailQuery);
        $this->assertEquals(1, count($emails));

        $first_email = $emails[0];

        $whetherMatches = preg_match(sprintf("#AR-%d-%d-%d-%d#",$autoresponder_id, $subscriber_id, $message_ids["0"], 0), $first_email->meta_key);
        $this->assertEquals(1, $whetherMatches);

        $this->truncateQueue();

        //run the cron after 7 days - simulated downtime.

        $timeObject = new DateTime();
        $timeObject->setTimestamp($currentTime+(86400*7));
        $processor->run_for_time($timeObject);

        $getQueueEmailQuery = sprintf("SELECT * FROM wp_wpr_queue;");
        $emails = $wpdb->get_results($getQueueEmailQuery);
        $this->assertEquals(1, count($emails));

        $second_email = $emails[0];

        $this->assertEquals(sprintf("AR-%d-%d-%d-%d", $autoresponder_id, $subscriber_id, $message_ids["1"], 1), $second_email->meta_key);


        $timeObject = new DateTime();
        $timeObject->setTimestamp($currentTime+(86400*14));
        $processor->run_for_time($timeObject);

        $getQueueEmailQuery = sprintf("SELECT * FROM wp_wpr_queue;");
        $emails = $wpdb->get_results($getQueueEmailQuery);
        $this->assertEquals(1, count($emails));

        $third_email = $emails[0];

        $this->assertEquals(sprintf("AR-%d-%d-%d-%d", $autoresponder_id, $subscriber_id, $message_ids["1"], 5), $third_email->meta_key);


        $this->assertTrue(false);
    }

    public function tearDown() {
        $this->truncateAllRelevantTables();

    }
}
