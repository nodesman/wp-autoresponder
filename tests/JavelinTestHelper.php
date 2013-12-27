<?php
class JavelinTestHelper
{
    public static function deleteAllNewsletters()
    {
        global $wpdb;
        $truncateNewsletterTable = sprintf("TRUNCATE %swpr_newsletters;", $wpdb->prefix);
        $wpdb->query($truncateNewsletterTable);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_newsletters AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }
    public static function deleteAllNewsletterBroadcasts()
    {
        global $wpdb;
        $truncateNewsletterTable = sprintf("TRUNCATE %swpr_newsletter_mailouts;", $wpdb->prefix);
        $wpdb->query($truncateNewsletterTable);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_newsletter_mailouts AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
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

    public static function createNewsletter($newsletterInfo = array())
    {
        if (0 == count($newsletterInfo))
        {
            $newsletterInfo = array(
                'name' => md5(microtime()."newsletter_name"),
                'fromname' => md5(microtime()."fromname"),
                'fromemail' => md5(microtime().'somename').'@'.md5(microtime()."somedomain").".com"
            );
        }

        $createNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters (`name`, `fromname`, `fromemail`) VALUES ('%s', '%s', '%s')", $wpdb->prefix, $newsletterInfo['name'], $newsletterInfo['fromname'], $newsletterInfo['fromemail']);
        $wpdb->query($createNewsletterQuery);
        $newsletterId = $wpdb->insert_id;

        return new Newsletter($newsletterId);
    }

}