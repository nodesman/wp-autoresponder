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

    public static function deleteAllSubscribers()
    {
        global $wpdb;
        $truncateSubscribersQuery = sprintf("TRUNCATE {$wpdb->prefix}wpr_subscribers;");
        $wpdb->query($truncateSubscribersQuery);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_subscribers AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public static function deleteAllEmailsFromQueue()
    {
        global $wpdb;
        $truncateQueueQuery = sprintf("TRUNCATE %swpr_queue;", $wpdb->prefix);
        $wpdb->query($truncateQueueQuery);

        $updateAutoIncrementStartIndex = sprintf("ALTER TABLE %swpr_queue AUTO_INCREMENT=%d;", $wpdb->prefix, rand(1000, 9000));
        $wpdb->query($updateAutoIncrementStartIndex);
    }

    public static function createNewsletter($newsletterInfo = array())
    {
        global $wpdb;
        if (0 == count($newsletterInfo))
        {
            $newsletterInfo = array(
                'name' => md5(microtime()."newsletter_name"),
                'fromname' => md5(microtime()."fromname"),
                'fromemail' => md5(microtime().'somename').'@'.md5(microtime()."somedomain").".com",
                'reply_to' => substr(self::randomString("reply"), 0, 5) . '@' . substr(self::randomString("domain"), 0, 5).".com"
            );
        }

        $createNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters (`name`, `fromname`, `fromemail`, `reply_to`) VALUES ('%s', '%s', '%s', '%s')", $wpdb->prefix, $newsletterInfo['name'], $newsletterInfo['fromname'], $newsletterInfo['fromemail'], $newsletterInfo['reply_to']);
        $wpdb->query($createNewsletterQuery);
        $newsletterId = $wpdb->insert_id;

        return Newsletter::getNewsletter($newsletterId);
    }

    public static function createBroadcast(Newsletter $newsletter, $broadcastInfo = null)
    {
        global $wpdb;
        if (null == $broadcastInfo) {
            $broadcastInfo = array(
                'nid' => $newsletter->getId(),
                'subject' => md5(microtime()."subject"),
                'textbody' => md5(microtime()."textbody"),
                'htmlbody' => md5(microtime().'htmlbody'),
                'time' => time(),
                'status' => 0
            );
        }

        $createNewsletterBroadcastQuery = sprintf("INSERT INTO `%swpr_newsletter_mailouts` (`nid`, `subject`, `textbody`, `htmlbody`, `time`, `status`) VALUES
                                                 (%d, '%s', '%s', '%s', '%s', %d);",
            $wpdb->prefix,
            $broadcastInfo['nid'],
            $broadcastInfo['subject'],
            $broadcastInfo['textbody'],
            $broadcastInfo['htmlbody'],
            $broadcastInfo['time'],
            $broadcastInfo['status']
        );
        $wpdb->query($createNewsletterBroadcastQuery);

        $broadcastId = $wpdb->insert_id;
        return new Broadcast($broadcastId);
    }

    public static function createSubscriber(Newsletter $newsletter, ArrayObject $subscriber = null)
    {
        global $wpdb;
        if (null == $subscriber)
        {
            $subscriber = array(
                'name' => self::randomString("name"),
                'email' => self::randomString("user") . '@' .self::randomString("domain") . '.com',
                'nid' => $newsletter->getId(),
                'date' => time(),
                'active' => 1,
                'confirmed' => 1,
                'hash' => self::randomString(self::randomString())
            );
        }

        $createSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (`nid`,`name`, `email`, `date`, `active`, `confirmed`, `hash` ) VALUES (%d, '%s', '%s','%s','%s','%s','%s');",
            $wpdb->prefix,
            $subscriber['nid'],
            $subscriber['name'],
            $subscriber['email'],
            $subscriber['date'],
            $subscriber['active'],
            $subscriber['confirmed'],
            $subscriber['hash']
        );
        $wpdb->query($createSubscriberQuery);
        $subscriberId = $wpdb->insert_id;
        return new Subscriber($subscriberId);
    }

    private static function randomString($string = null)
    {
        $string = (null == $string)?"":$string;
        return md5(microtime().$string);
    }
}