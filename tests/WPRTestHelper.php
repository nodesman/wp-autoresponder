<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rajasekharan
 * Date: 26/04/13
 * Time: 7:04 PM
 * To change this template use File | Settings | File Templates.
 */
class WPRTestHelper
{

    public static function deleteAllNewsletters()
    {
        global $wpdb;
        $truncateNewsletterTable = sprintf("TRUNCATE %swpr_newsletters;", $wpdb->prefix);
        $wpdb->query($truncateNewsletterTable);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_newsletters AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public static function deleteAllMessagesFromQueue()
    {
        global $wpdb;
        $truncateQueueTable = sprintf("TRUNCATE %swpr_queue;", $wpdb->prefix);
        $wpdb->query($truncateQueueTable);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_queue AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public static function deleteAllAutoresponderMessages()
    {
        global $wpdb;
        $truncateAutoresponderMessagesTable = sprintf("TRUNCATE %swpr_autoresponder_messages;", $wpdb->prefix);
        $wpdb->query($truncateAutoresponderMessagesTable);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_autoresponder_messages AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public static function deleteAllAutoresponders()
    {
        global $wpdb;
        $truncateAutoresponderTable = sprintf("TRUNCATE %swpr_autoresponders;", $wpdb->prefix);
        $wpdb->query($truncateAutoresponderTable);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_autoresponders AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public static function deleteAllSubscribers() {
        global $wpdb;

        $truncateSubscribersQuery = sprintf("TRUNCATE {$wpdb->prefix}wpr_subscribers;");
        $wpdb->query($truncateSubscribersQuery);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_subscribers AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
        $wpdb->query($updateAutoIncrementStartIndex);

    }

}