<?php

require_once __DIR__."/../../src/processes/autoresponder_process.php";
require_once __DIR__."/../../src/models/autoresponder.php";
class AutoresponderProcessTest extends WP_UnitTestCase {

    private $newsletter1_id;
    private $newsletter2_id;

    public function setUp() {
        parent::setUp();
        //create newsletters
        global $wpdb;

        AutoresponderProcessTestHelper::deleteAllNewsletters();
        AutoresponderProcessTestHelper::deleteAllAutoresponders();
        AutoresponderProcessTestHelper::deleteAllAutoresponderMessages();
        AutoresponderProcessTestHelper::deleteAllMessagesFromQueue();

        $createNewsletterOneQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `reply_to`, `description`, `fromname`, `fromemail`) VALUES (%s, %s, %s , %s, %s);", md5(microtime()."name1"), 'raj@wpresponder.com', '', 'raj', 'raj@wpresponder.com');
        $wpdb->query($createNewsletterOneQuery);

        $this->newsletter1_id= $wpdb->insert_id;

        $createNewsletterOneQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `reply_to`, `description`, `fromname`, `fromemail`) VALUES (%s, %s, %s , %s, %s);", md5(microtime()."name2"), 'raj@wpresponder.com', '', 'raj', 'raj@wpresponder.com');
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

        $count = AutoresponderMessage::getAllMessagesCount();
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
        $subject_format = "Subject [!%s!]";



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


        $html_message_format = "@@Html [!%s!]@@";
        $text_message_format = "@@Text [!%s!]@@";
        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, '$subject_format', '$text_message_format', '$html_message_format', 0)", $wpdb->prefix, $autoresponder_id, $custom_field_placeholder, $custom_field_placeholder, $custom_field_placeholder);

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
        $this->assertEquals(sprintf("$html_message_format", $custom_field_value), $match);

        preg_match_all("#@@[^@]+@@#", $email->textbody, $matches );
        $match = $matches[0][0];
        $this->assertEquals(sprintf("$text_message_format", $custom_field_value), $match);

        $this->assertEquals(sprintf("$subject_format", $custom_field_value), $email->subject);
        //assert whether that field was substituted in the delivered message

    }
    

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
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder1_id = $wpdb->insert_id;
        return $autoresponder1_id;
    }

    private function addUnsubscribedSubscriber($iter = -1)
    {
        global $wpdb;
        $iter = ( -1 == $iter)?rand(1,9999):$iter;
        $insertSubscriberQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_subscribers (`nid`, `name`, `email`, `date`, `active`, `confirmed`, `hash`) VALUES (%d, '%s', '%s', '%s', 1, 0, '%s');",
            $this->newsletter1_id, md5(microtime() . "name1" . $iter), md5(microtime() . "email{$iter}") . "@hotmail.com", time(), md5(microtime() . "test"));
        $wpdb->query($insertSubscriberQuery);
        $subscriber_id = $wpdb->insert_id;
        return $subscriber_id;
    }

    private  function createDateObjectOfRandomHourOnSameDayAsSubscription($subscribersDateOfSubscription)
    {
        $currentIterationDate = clone $subscribersDateOfSubscription;
        $currentIterationDate->setTime(rand(1, 12), 0, 0);
        return $currentIterationDate;
    }

    private function addAutoresponderSubscription($subscriber_id, $responder_id, $dateObject)
    {
        $insertSubscriptionQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_followup_subscriptions (`sid`, `type`, `eid`, `sequence`, `last_date`, `last_processed`, `doc`) VALUES (%d, 'autoresponder', %d, -1, 0, 0, %d)", $subscriber_id, $responder_id, $currentIterationDate->getTimestamp(), $currentIterationDate->getTimestamp());
        $wpdb->query($insertSubscriptionQuery);
        return $insertSubscriptionQuery;
    }

    private function addConfirmedSubscriber()
    {
        $iter = rand(1,9999);
        $insertSubscriberQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_subscribers (`nid`, `name`, `email`, `date`, `active`, `confirmed`, `hash`) VALUES (%d, '%s', '%s', '%s', 1, 1, '%s');",
            $this->newsletter1_id, md5(microtime() . "name1" . $iter), md5(microtime() . "email{$iter}") . "@hotmail.com", time(), md5(microtime() . "test".rand(1,10000)));
        $wpdb->query($insertSubscriberQuery);
        return $wpdb->insert_id;
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

class AutoresponderProcessTestHelper {
    
    public static function deleteAllNewsletters()
    {
        global $wpdb;
        $truncateNewsletterTable = sprintf("TRUNCATE %swpr_newsletters;", $wpdb->prefix);
        $wpdb->query($truncateNewsletterTable);
    }

    public static function deleteAllMessagesFromQueue()
    {
        global $wpdb;
        $truncateQueueTable = sprintf("TRUNCATE %swpr_queue;", $wpdb->prefix);
        $wpdb->query($truncateQueueTable);
    }

    public static function deleteAllAutoresponderMessages()
    {
        global $wpdb;
        $truncateAutoresponderMessagesTable = sprintf("TRUNCATE %swpr_autoresponder_messages;", $wpdb->prefix);
        $wpdb->query($truncateAutoresponderMessagesTable);
    }

    public static function deleteAllAutoresponders()
    {
        global $wpdb;
        $truncateAutoresponderTable = sprintf("TRUNCATE %swpr_autoresponders;", $wpdb->prefix);
        $wpdb->query($truncateAutoresponderTable);
    }
    
}
