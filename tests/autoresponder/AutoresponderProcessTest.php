<?php

require __DIR__."/../../processes/autoresponder_process.php";

class AutoresponderProcessTest extends WP_UnitTestCase {

    private $newsletter1_id;
    private $newsletter2_id;

    public function setUp() {
        parent::setUp();
        //create newsletters
        global $wpdb;

        $truncateNewsletterTable = sprintf("TRUNCATE %swpr_newsletters;", $wpdb->prefix);
        $wpdb->query($truncateNewsletterTable);


        $truncateAutoresponderTable = sprintf("TRUNCATE %swpr_autoresponders;", $wpdb->prefix);
        $wpdb->query($truncateAutoresponderTable);

        $truncateAutoresponderMessagesTable = sprintf("TRUNCATE %swpr_autoresponder_messages;", $wpdb->prefix);
        $wpdb->query($truncateAutoresponderMessagesTable);

        $truncateQueueTable = sprintf("TRUNCATE %swpr_queue;", $wpdb->prefix);
        $wpdb->query($truncateQueueTable);

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


    public function tearDown() {
        parent::tearDown();
    }


}