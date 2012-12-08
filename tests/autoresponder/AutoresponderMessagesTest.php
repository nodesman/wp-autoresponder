<?php

require __DIR__ . "/../../models/autoresponder_message.php";

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
     * @expectedException NonExistentMessageException
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