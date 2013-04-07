<?php

require_once __DIR__ . "/../../src/models/autoresponder_message.php";

class AutoresponderMessagesTest extends WP_UnitTestCase {

    public function setUp() {
        parent::setUp();
        global $wpdb;

        $truncateNewsletterTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_newsletters");
        $wpdb->query($truncateNewsletterTable);

        $createNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (name, fromname, fromemail) VALUES ('%s', '%s', '%s')", "Test Newsletter", "test", "test@gmail.com");
        $wpdb->query($createNewsletterQuery);

        $truncateAutorespondresTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponders;");
        $wpdb->query($truncateAutorespondresTable);

        $truncateAutoresponderMessagesTableQuery = "TRUNCATE {$wpdb->prefix}wpr_autoresponder_messages";
        $wpdb->query($truncateAutoresponderMessagesTableQuery);
    }


    public function testWhetherGetNextMessageFetchesTheNextMessage() {
        global $wpdb;

        $insertNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters ( `name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s', '%s', '%s', '%s')", $wpdb->prefix, 'Test', 'flare@gmail.com', 'Raj', 'flarecore@gmail.com');
        $wpdb->query($insertNewsletterQuery);

        $this->newsletter_id = $wpdb->insert_id;


        $createAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d, 'xperia');", $wpdb->prefix, $this->newsletter_id);
        $this->assertEquals(1, $wpdb->query($createAutoresponderQuery));

        $autoresponder_id = $wpdb->insert_id;


        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 1', '@@Text 1@@', '@@Html 1@@', 0)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["0"] = $wpdb->insert_id;



        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 2', '@@Text @@', '@@Html @@', 1)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["1"] = $wpdb->insert_id;



        $message = AutoresponderMessage::getMessage($message_ids["0"]);

        $nextMessage = $message->getNextMessage();

        $this->assertEquals($message_ids["1"], $nextMessage->getId());

    }

    public function testWhetherGetPreviousMessageFetchesThePreviousMessage() {
        global $wpdb;

        $insertNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters ( `name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s', '%s', '%s', '%s')", $wpdb->prefix, 'Test', 'flare@gmail.com', 'Raj', 'flarecore@gmail.com');
        $wpdb->query($insertNewsletterQuery);

        $this->newsletter_id = $wpdb->insert_id;


        $createAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d, 'xperia');", $wpdb->prefix, $this->newsletter_id);
        $this->assertEquals(1, $wpdb->query($createAutoresponderQuery));

        $autoresponder_id = $wpdb->insert_id;


        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 1', '@@Text 1@@', '@@Html 1@@', 0)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["0"] = $wpdb->insert_id;



        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 2', '@@Text @@', '@@Html @@', 1)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message_ids["1"] = $wpdb->insert_id;



        $message = AutoresponderMessage::getMessage($message_ids["1"]);

        $nextMessage = $message->getPreviousMessage();


        $this->assertEquals($message_ids["0"], $nextMessage->getId());

    }


    public function testWhetherGetPreviousMessageDayOffsetFetchesThePreviousMessage() {

        global $wpdb;

        $insertNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters ( `name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s', '%s', '%s', '%s')", $wpdb->prefix, 'Test', 'flare@gmail.com', 'Raj', 'flarecore@gmail.com');
        $wpdb->query($insertNewsletterQuery);

        $this->newsletter_id = $wpdb->insert_id;


        $createAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d, 'xperia');", $wpdb->prefix, $this->newsletter_id);
        $this->assertEquals(1, $wpdb->query($createAutoresponderQuery));

        $autoresponder_id = $wpdb->insert_id;


        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 1', '@@Text 1@@', '@@Html 1@@', 0)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message = AutoresponderMessage::getMessage((int)$wpdb->insert_id);

        $this->assertEquals(-1, $message->getPreviousMessageDayNumber());



        $insertAutoresponderMessageQuery= sprintf("INSERT INTO %swpr_autoresponder_messages (aid, `subject`, textbody, htmlbody, sequence) VALUES (%d, 'Subject 2', '@@Text @@', '@@Html @@', 1)", $wpdb->prefix, $autoresponder_id);

        $this->assertEquals(1, $wpdb->query($insertAutoresponderMessageQuery));

        $message = AutoresponderMessage::getMessage((int) $wpdb->insert_id);

        $this->assertEquals(0, $message->getPreviousMessageDayNumber());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherAutoresponderFactoryTakesOnlyIntegers() {
           AutoresponderMessage::getMessage("1");
    }

    /**
     * @expectedException NonExistentMessageException
     */
    public function testWhetherFetchingNonExistentAutoresponderThrowsException() {
        $autoresponder = AutoresponderTestHelper::addAutoresponderAndFetchRow(1, "TESTTEST");
        AutoresponderMessage::getMessage(3);
    }


    /**
     * @expectedException NonExistentAutoresponderException
     */
    public function testWhetherAutoresponderFactoryDoesntProvideMessagesOfAutorespondersThatDontExist() {

        global $wpdb;
        $autoresponder =  AutoresponderTestHelper::addAutoresponderAndFetchRow(1, "test");

        $addNonExistentAutoresponderMessage = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponder_messages (aid, subject) VALUES (4, 'Test Test')");
        $wpdb->show_errors();
        $wpdb->query($addNonExistentAutoresponderMessage);

        AutoresponderMessage::getMessage($wpdb->insert_id);
    }

    public function testWhetherAutoresponderFactoryFetchesTheAppropriateAutoresponderMessage() {

        global $wpdb;
        $autoresponder =  AutoresponderTestHelper::addAutoresponderAndFetchRow(1, "test");
        $addAutoresponderMessageQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponder_messages (aid, subject, sequence) VALUES (%d, '%s', %d)", $autoresponder->id, 'Test Subject', 1);
        $wpdb->query($addAutoresponderMessageQuery);
        $autoresponder_message_id = $wpdb->insert_id;
        $message = AutoresponderMessage::getMessage($autoresponder_message_id);

        $this->assertEquals('Test Subject', $message->getSubject());
        $this->assertEquals($autoresponder_message_id, $message->getId());
        $this->assertEquals(1, $message->getDayNumber());

    }

    public function tearDown() {
        parent::tearDown();
    }


}
