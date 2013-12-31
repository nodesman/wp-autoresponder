<?php

require_once __DIR__ . "/../../src/processes/background_process.php";
require_once __DIR__."/../../src/processes/autoresponder_process.php";
require_once __DIR__."/../../src/models/autoresponder.php";
require_once __DIR__ . "/../JavelinTestHelper.php";

class AutoresponderProcessTest extends WP_UnitTestCase {

    private $newsletter1_id;
    private $newsletter2_id;

    public function setUp() {
        parent::setUp();
        //create newsletters
        global $wpdb;

        JavelinTestHelper::deleteAllNewsletters();
        JavelinTestHelper::deleteAllAutoresponders();
        JavelinTestHelper::deleteAllAutoresponderMessages();
        JavelinTestHelper::deleteAllMessagesFromQueue();

        $createNewsletterOneQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `reply_to`, `fromname`, `fromemail`) VALUES (%s, %s , %s, %s);", md5(microtime()."name1"), 'raj@wpresponder.com', '', 'raj', 'raj@wpresponder.com');
        $wpdb->query($createNewsletterOneQuery);

        $this->newsletter1_id= $wpdb->insert_id;

        $createNewsletterOneQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `reply_to`, `fromname`, `fromemail`) VALUES (%s, %s , %s, %s);", md5(microtime()."name2"), 'raj@wpresponder.com', '', 'raj', 'raj@wpresponder.com');
        $wpdb->query($createNewsletterOneQuery);

        $this->newsletter2_id= $wpdb->insert_id;

    }
    public function testFetchingAllMessagesOfAllAutorespondersAndNewslettersThatExist() {
        //define an autoresponder
        global $wpdb;

        //add an autoresponder that has a newsletter associated with it.
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletter1_id, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder1_id = $wpdb->insert_id;

        for ($iter=0;$iter< 12; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, $autoresponder1_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
                 $wpdb->query($addAutoresponderMessageQuery);
                 $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        //add another autoresponder that has another newsletter associated with it.
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletter2_id, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder2_id = $wpdb->insert_id;

        for ($iter=0;$iter< 13; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, $autoresponder2_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        //add a autoresponder with no newsletter associated with it.

        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, 9801, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder3_id = $wpdb->insert_id;

        for ($iter=0;$iter< 13; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, $autoresponder3_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }


        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, 9801, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder3_id = $wpdb->insert_id;

        for ($iter=0;$iter< 13; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, $autoresponder3_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }


        //add messages for an autoresponder that doesn't exist
        for ($iter=0;$iter< 13; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, 9000,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        $messages = AutoresponderMessage::getAllMessages();

        $responders = array();

        foreach ($messages as $message) {
            $responders[] = $message->getAutoresponder()->getId();
        }

        $responders = array_unique($responders);
        $expected = array($autoresponder1_id, $autoresponder2_id);

        foreach($responders as $index=>$value) {
            $responders[$index] = intval($value);
        }

        $intersect = array_intersect($responders, $expected);
        $diff = array_diff($intersect, $expected);

        $this->assertEquals(2, count($responders));
        $this->assertEquals(0, count($diff));
        $this->assertEquals(25, count($messages));

    }
    public function testCountingAllMessagesOfAllAutorespondersAndNewslettersThatExist() {
        //define an autoresponder
        global $wpdb;

        //add an autoresponder that has a newsletter associated with it.
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletter1_id, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder1_id = $wpdb->insert_id;


        for ($iter=0;$iter< 12; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, $autoresponder1_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        //add another autoresponder that has another newsletter associated with it.
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletter2_id, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder2_id = $wpdb->insert_id;

        for ($iter=0;$iter< 13; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, $autoresponder2_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        //add a autoresponder with no newsletter associated with it.

        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, 9801, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder3_id = $wpdb->insert_id;

        for ($iter=0;$iter< 13; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, $autoresponder3_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, 9801, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder3_id = $wpdb->insert_id;

        for ($iter=0;$iter< 13; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, $autoresponder3_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        //add messages for an autoresponder that doesn't exist
        for ($iter=0;$iter< 13; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                     VALUES (%d, '%s', '%s', %d)"
                ,$wpdb->prefix, 9000,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        $count = (int) AutoresponderMessage::getAllMessagesCount();
        $this->assertEquals(25, $count);

    }

    public function testFetchingPagedListOfAutoresponders() {

        global $wpdb;

        $autoresponder1_id = $this->createAAutoresponderWithRandomName();

        $numberOfMessagesInAutoresponderOne = 100;

        $autoresponderMessagesIds = $this->createMessagesForAutoresponder($numberOfMessagesInAutoresponderOne, $autoresponder1_id);
        $messages = AutoresponderMessage::getAllMessages(20, 30);

        $this->assertEquals(30, count($messages));

        $received_ids = $this->getMessageIds($messages);
        $expected = array_slice($autoresponderMessagesIds, 20, 30);

        $intersect = array_intersect($received_ids, $expected);
        $this->assertEquals(count($intersect), count($expected));
    }


    public function testWhetherCustomFieldValuesAreSubstituted() {

        global $wpdb;
        $currentTime = time();
        $custom_field_placeholder="lname";
        $custom_field_value = "12345";
        //create autoresponder

        $createAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d, 'xperia');", $wpdb->prefix, $this->newsletter1_id);
        $this->assertEquals(1, $wpdb->query($createAutoresponderQuery));

        $autoresponder_id = $wpdb->insert_id;

        //create a custom field

        $createCustomFieldQuery = sprintf("INSERT INTO  %swpr_custom_fields (nid, type, name, label, enum) VALUES (%d, 'text', '{$custom_field_placeholder}', 'Last Name','');", $wpdb->prefix, $this->newsletter1_id);
        $this->assertEquals(1, $wpdb->query($createCustomFieldQuery));

        $custom_field_id = $wpdb->insert_id;

        //insert a subscriber

        $insertSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (`nid`, `name`, `email`, `date`, `active`, `confirmed`, `hash`) VALUES (%d, 'raj', 'flarecore@gmail.com', '324242424', 1, 1, '32asdf42');", $wpdb->prefix, $this->newsletter1_id);
        $this->assertEquals(1, $wpdb->query($insertSubscriberQuery));

        $subscriber_id = $wpdb->insert_id;

        //insert the value for the custom field for a specific subscriber


        $insertCustomFieldValue = sprintf("INSERT INTO  %swpr_custom_fields_values (`nid`, `sid`, `cid`, `value`) VALUES (%d, %d, %d, '$custom_field_value');", $wpdb->prefix, $this->newsletter1_id, $subscriber_id, $custom_field_id);
        $this->assertEquals(1, $wpdb->query($insertCustomFieldValue));

        //insert a message to the autoresponder with the custom field value in the html, text bodies and subject


        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject [!%s!] [!name!]', '@@Text [!%s!] [!name!]@@', '@@Html [!%s!] [!name!]@@', 0)", $wpdb->prefix, $autoresponder_id, $custom_field_placeholder, $custom_field_placeholder, $custom_field_placeholder);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));



        //add a subscription for the above subscriber such that running the process will result in that message being enqueued.

        $insertSubscriptionQuery = sprintf("INSERT INTO %swpr_followup_subscriptions (eid, type, sid, doc, last_processed, last_date, sequence) VALUES (%d, 'autoresponder', %d, %d, %d, 0, -1);",$wpdb->prefix, $autoresponder_id, $subscriber_id, $currentTime, $currentTime);
        $this->assertEquals(1, $wpdb->query($insertSubscriptionQuery));

        //run the process

        $processor  = AutoresponderProcessor::getProcessor();
        $processor->run();

        //fetch the delivered email for the target subscriber

        $getEmailsQuery = sprintf("SELECT * FROM %swpr_queue;", $wpdb->prefix);
        $emails = $wpdb->get_results($getEmailsQuery);

        $this->assertEquals(1, count($emails));

        $email = $emails[0];

        preg_match_all("#@@[^@]+@@#", $email->htmlbody, $matches );

        $match = $matches[0][0];
        $this->assertEquals(sprintf("@@Html %s raj@@", $custom_field_value), $match);

        preg_match_all("#@@[^@]+@@#", $email->textbody, $matches );
        $match = $matches[0][0];
        $this->assertEquals(sprintf("@@Text %s raj@@", $custom_field_value), $match);

        $this->assertEquals(sprintf("Subject %s raj", $custom_field_value), $email->subject);
        //assert whether that field was substituted in the delivered message

    }


    /*
    public function testEnsureThatAutoresponderIsAbleToDeliver100kSubscribersAtATime() {

        global $wpdb;
        $createAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d, 'xperia');", $wpdb->prefix, $this->newsletter1_id);
        $this->assertEquals(1, $wpdb->query($createAutoresponderQuery));

        $autoresponder_id = $wpdb->insert_id;

        $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                     VALUES (%d, '%s', '%s', %d)"
            ,$wpdb->prefix, $autoresponder_id,  md5(rand(1,1000) . microtime()."auto"), md5(microtime().rand(1,1000).'test'), 0);
        $wpdb->query($addAutoresponderMessageQuery);

        $truncateSubscribers = sprintf("TRUNCATE %swpr_subscribers", $wpdb->prefix);
        $wpdb->query($truncateSubscribers);


        for ($iter=0;$iter< 100000; $iter++) {

            $insertSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (`nid`, `name`, `email`, `date`, `active`, `confirmed`, `hash`) VALUES (%d, 'raj', 'flarecore{$iter}@gmail.com', '324242424', 1, 1, '32asdf42');", $wpdb->prefix, $this->newsletter1_id);
            $wpdb->query($insertSubscriberQuery);

            $subscriber_id = $wpdb->insert_id;

            $insertSubscriptionQuery = sprintf("INSERT INTO %swpr_followup_subscriptions (eid, type, sid, doc, last_processed, last_date, sequence) VALUES (%d, 'autoresponder', %d, %d, %d, 0, -1);",$wpdb->prefix, $autoresponder_id, $subscriber_id, time(), 0);
            $wpdb->query($insertSubscriptionQuery);

        }

        $ensure100ThousandSubscribersQuery = sprintf("SELECT COUNT(*) num FROM %swpr_subscribers", $wpdb->prefix);
        $num = $wpdb->get_results($ensure100ThousandSubscribersQuery);
        $number = $num[0]->num;

        $this->assertEquals(100000, $number);


        $ensure100ThousandSubscribersQuery = sprintf("SELECT COUNT(*) num FROM %swpr_followup_subscriptions", $wpdb->prefix);
        $num = $wpdb->get_results($ensure100ThousandSubscribersQuery);
        $number = $num[0]->num;
        $this->assertEquals(100000, $number);

        $processor = AutoresponderProcessor::getProcessor();

        $processor->run_for_time(new DateTime());
        $getEmailsCount = $wpdb->get_results(sprintf("SELECT COUNT(*) num FROM %swpr_queue", $wpdb->prefix));
        $count = $getEmailsCount[0]->num;
        $this->assertEquals(100000, $count);
    }

    */
    

    public function tearDown() {
        parent::tearDown();
    }

    public function createMessagesForAutoresponder($numberOfMessagesInAutoresponderOne, $autoresponder1_id)
    {
        global $wpdb;
        $autoresponderMessagesIds = array();
        for ($iter = 0; $iter < $numberOfMessagesInAutoresponderOne; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence)
                                                      VALUES (%d, '%s', '%s', %d)"
                , $wpdb->prefix, $autoresponder1_id, md5($iter . microtime() . "auto"), md5(microtime() . $iter . 'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }
        return $autoresponderMessagesIds;
    }

    public function createAAutoresponderWithRandomName()
    {
        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletter1_id, md5(microtime()));
        $wpdb->query($addAutoresponderQuery);
        $autoresponder1_id = $wpdb->insert_id;
        return $autoresponder1_id;
    }

    private function getMessageIds($messages)
    {
        $received_ids = array();
        foreach ($messages as $message) {
            $received_ids[] = $message->getId();
        }
        return $received_ids;
    }
}
