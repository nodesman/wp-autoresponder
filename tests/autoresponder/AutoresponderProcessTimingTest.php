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


        $insertMessagesQuery = sprintf("INSERT INTO `%swpr_autoresponder_messages` (`aid`, `subject`, `htmlenabled`,`htmlbody`, `textbody`, `sequence`) VALUES
                                                                                    (%d, 'Day 0 Message', 1, 'Test','Test', 0),
                                                                                    (%d, 'Day 1 Message', 1, 'Test','Test', 1),
                                                                                    (%d, 'Day 5 Message', 1, 'Test','Test', 5); ", $wpdb->prefix, $this->autoresponder_id, $this->autoresponder_id, $this->autoresponder_id);
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

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_autoresponders AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000,9000));
        $wpdb->query($updateAutoIncrementStartIndex);

        $this->truncateMessagesDefinitions($wpdb);

        $truncateNewslettersQuery = sprintf("TRUNCATE %swpr_newsletters;", $wpdb->prefix);
        $wpdb->query($truncateNewslettersQuery);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_newsletters AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000,9000));
        $wpdb->query($updateAutoIncrementStartIndex);

        $this->truncateQueue($wpdb);

        $this->truncateSubscriptionsToFollowups();

        $this->truncateSubscribers();

    }

    public function truncateSubscriptionsToFollowups()
    {
        global $wpdb;
        $truncateSubscriptionsQuery = sprintf("TRUNCATE %swpr_followup_subscriptions;", $wpdb->prefix);
        $wpdb->query($truncateSubscriptionsQuery);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_followup_subscriptions AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000,9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public function truncateSubscribers()
    {
        global $wpdb;
        $truncateSubscribersQuery = sprintf("TRUNCATE %swpr_subscribers;", $wpdb->prefix);
        $wpdb->query($truncateSubscribersQuery);


        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_subscribers AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000,9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public function truncateMessagesDefinitions()
    {
        global $wpdb;
        $truncateAutorespondersQuery = sprintf("TRUNCATE %swpr_autoresponder_messages;", $wpdb->prefix);
        $wpdb->query($truncateAutorespondersQuery);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_autoresponder_messages AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000,9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public function truncateQueue()
    {
        global $wpdb;
        $truncateQueueQuery = sprintf("TRUNCATE %swpr_queue", $wpdb->prefix);
        $wpdb->query($truncateQueueQuery);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_queue AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000,9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public function testWhetherDayZeroDeliveryResultsInDayZeroEmailsOnlyToSubscribedSubscribers() {

        global $wpdb;

        $processor = AutoresponderProcessor::getProcessor();
        $timeOfRun = $this->timeOfSubscription+rand(1,300); //within the 5 minutes following

        $dayZeroRunTimeObject = new DateTime();
        $dayZeroRunTimeObject->setTimestamp($timeOfRun);
        $processor->run_for_time($dayZeroRunTimeObject);

        $getMessageForDayZeroId = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE `aid`=%d AND `sequence`=%d", $this->autoresponder_id, 0);
        $messageRes = $wpdb->get_results($getMessageForDayZeroId);
        $message = $messageRes[0];

        $meta_key = sprintf("AR-%s-%%%%-%s-0", $this->autoresponder_id, $message->id);
        $getMessagesQuery = sprintf("SELECT * FROM %swpr_queue WHERE meta_key LIKE '%s';", $wpdb->prefix, $meta_key);
        $messagesDelivered = $wpdb->get_results($getMessagesQuery);

        $numberOfMessages = count($messagesDelivered);

        $this->assertEquals($this->numberOfSubscribersAdded, $numberOfMessages);

        $getSubscriptionsQuery = sprintf("SELECT * FROM %swpr_followup_subscriptions WHERE eid=%d AND type='autoresponder' LIMIT 1;", $wpdb->prefix, $this->autoresponder_id);
        $subscriptionsResult = $wpdb->get_results($getSubscriptionsQuery);

        $this->assertEquals($timeOfRun, $subscriptionsResult[0]->last_date);


    }



    public function testWhetherInvokingAutoresponderDeliveryForIndividualSubscriberResultsInThatSubscriberReceivingAMessage() {

        global $wpdb;

        $getRandomSubscriberId = sprintf("SELECT * FROM {$wpdb->prefix}wpr_subscribers ORDER BY RAND() LIMIT 1");
        $randomSubscriberResult = $wpdb->get_results($getRandomSubscriberId);

        $sid = $randomSubscriberResult[0]->id;

        do_action("_wpr_autoresponder_process_subscriber_day_zero", $sid);


        $getQueueSize = sprintf("SELECT * FROM {$wpdb->prefix}wpr_queue;");
        $queueSizeCountRes = $wpdb->get_results($getQueueSize);
        $count = count($queueSizeCountRes);
        $this->assertEquals(1, $count);

        $getAutoresponderMessageId = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE aid=%d AND sequence=0;", $this->autoresponder_id);
        $responderIdRes = $wpdb->get_results($getAutoresponderMessageId);

        $this->assertEquals(1, count($responderIdRes));

        $message_id = $responderIdRes[0]->id;

        $expectedMetaKey = sprintf("AR-%d-%d-%d-%d", $this->autoresponder_id, $sid, $message_id,  0);

        $this->assertEquals($expectedMetaKey, $queueSizeCountRes[0]->meta_key);

    }

    public function testWhetherDayOneDeliveryResultsInDayOneEmailsOnlyToSubscribedSubscribers() {

        global $wpdb;
        $this->truncateQueue();

        $timeWhenDayZeroEmailWasDelivered = $this->timeOfSubscription + rand(1, 300);
        $setLastProcessedDate = sprintf("UPDATE %swpr_followup_subscriptions SET last_date=%d, sequence=0 WHERE eid=%d AND type='autoresponder'", $wpdb->prefix, $timeWhenDayZeroEmailWasDelivered, $this->autoresponder_id);
        $wpdb->query($setLastProcessedDate);


        $getSubscriptionsQuery = sprintf("SELECT * FROM %swpr_followup_subscriptions WHERE eid=%d AND type='autoresponder' LIMIT 1;", $wpdb->prefix, $this->autoresponder_id);
        $subscriptionsResult = $wpdb->get_results($getSubscriptionsQuery);


        $this->assertEquals($subscriptionsResult[0]->last_date, $timeWhenDayZeroEmailWasDelivered);


        $currentDayNumber = 1;

        $timeOfRun = $timeWhenDayZeroEmailWasDelivered+(86400*$currentDayNumber); //within the 5 minutes following


        $timeOfRunForDayOne = new DateTime(sprintf("@%s", $timeOfRun));

        $processor = AutoresponderProcessor::getProcessor();
        $processor->run_for_time($timeOfRunForDayOne);

        $getMessageForDayZeroId = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE `aid`=%d AND `sequence`=%d;", $this->autoresponder_id, $currentDayNumber);
        $messageRes = $wpdb->get_results($getMessageForDayZeroId);

        $this->assertEquals(count($messageRes), 1);

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
        $processor = AutoresponderProcessor::getProcessor();
        $processor->run_for_time(new DateTime(sprintf("@%s",$nextRunOnSameDay)));

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
        $this->truncateSubscribers();
        $this->truncateSubscriptionsToFollowups();


        //print "Resume from last run... \r\n\r\n\r\n...";


        //create an autoresponder

        $createAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d, 'xperia');", $wpdb->prefix, $this->newsletter_id);
        $this->assertEquals(1, $wpdb->query($createAutoresponderQuery));

        $autoresponder_id = $wpdb->insert_id;

        //insert a subscriber

        $insertSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (`nid`, `name`, `email`, `date`, `active`, `confirmed`, `hash`) VALUES (%d, 'raj', 'flarecore@gmail.com', '324242424', 1, 1, '32ajkckfkfksdf42');", $wpdb->prefix, $this->newsletter_id);
        $this->assertEquals(1, $wpdb->query($insertSubscriberQuery));

        $subscriber_id = $wpdb->insert_id;

        $numberOfSubscribers = sprintf("SELECT * FROM %swpr_subscribers", $wpdb->prefix);
        $subscribers = $wpdb->get_results($numberOfSubscribers);

        $this->assertEquals(1, count($subscribers));

        //insert a message to that autoresponder for day 0 - immediately after subscription

        $message_ids = array();

        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 1', '@@Text 1@@', '@@Html 1@@', 0)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["0"] = $wpdb->insert_id;


        //insert a message to that autoresponder for day 1 - one day after subscription

        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 2', '@@Text @@', '@@Html @@', 1)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["1"] = $wpdb->insert_id;

        //inesrt a message to that autoresponder for day 5 - 5 days after subscription


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

        $getQueueEmailQuery = sprintf("SELECT * FROM %swpr_queue;",$wpdb->prefix);
        $emails = $wpdb->get_results($getQueueEmailQuery);

        $this->assertEquals(1, count($emails));

        $first_email = $emails[0];

        $whetherMatches = preg_match(sprintf("#AR-%d-%d-%d-%d#",$autoresponder_id, $subscriber_id, $message_ids["0"], 0), $first_email->meta_key);
        $this->assertEquals(1, $whetherMatches);

        $this->truncateQueue();


        //run the cron after 7 days - simulated downtime. this should result in the delivery of email for day 1 when run on this day.

        $timeObject = new DateTime();
        $timeObject->setTimestamp($currentTime+(86400*7));

        $processor->run_for_time($timeObject);

        $getQueueEmailQuery = sprintf("SELECT * FROM %swpr_queue;",$wpdb->prefix);
        $emails = $wpdb->get_results($getQueueEmailQuery);

        $this->assertEquals(1, count($emails));

        $second_email = $emails[0];

        $this->assertEquals(sprintf("AR-%d-%d-%d-%d", $autoresponder_id, $subscriber_id, $message_ids["1"], 1), $second_email->meta_key);

        $this->truncateQueue();

        $getSequenceValueQuery = sprintf("SELECT sequence FROM %swpr_followup_subscriptions WHERE sid=%d AND eid=%d;", $wpdb->prefix, $subscriber_id, $autoresponder_id);
        $sequenceValueResults = $wpdb->get_results($getSequenceValueQuery);

        $value = $sequenceValueResults[0];

        $sequenceValue = $value->sequence;

        $this->assertEquals(1, $sequenceValue);
        //run the cron after 7 more days - that is the next email is on day 5 but we're again looking at a interim down time for 2 days past the intended date for next email

        $timeObject = new DateTime();
        $timeObject->setTimestamp($currentTime+(86400*14));

        $processor->run_for_time($timeObject);


        $getQueueEmailQuery = sprintf("SELECT * FROM %swpr_queue;",$wpdb->prefix);
        $emails = $wpdb->get_results($getQueueEmailQuery);
        $this->assertEquals(1, count($emails));

        $third_email = $emails[0];

        $this->assertEquals(sprintf("AR-%d-%d-%d-%d", $autoresponder_id, $subscriber_id, $message_ids["5"], 5), $third_email->meta_key);

    }

    public function tearDown() {
        $this->truncateAllRelevantTables();

    }
}
