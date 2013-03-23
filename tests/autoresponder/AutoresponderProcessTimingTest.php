<?php

require_once __DIR__."/../../src/models/autoresponder.php";

class AutoresponderProcessTimingTest extends WP_UnitTestCase {
    private $newsletter_id;

    public function setUp() {

        //truncate all relevant tables
        global $wpdb;

        $wpdb->show_errors();

        $truncateAutorespondersQuery = sprintf("TRUNCATE %swpr_autoresponders;", $wpdb->prefix);
        $wpdb->query($truncateAutorespondersQuery);

        $truncateNewslettersQuery = sprintf("TRUNCATE %swpr_newsletters;", $wpdb->prefix);
        $wpdb->query($truncateNewslettersQuery);

        $truncateQueueQuery = sprintf("TRUNCATE %swpr_queue", $wpdb->prefix);
        $wpdb->query($truncateQueueQuery);

        $truncateSubscriptionsQuery = sprintf("TRUNCATE %swpr_autoresponder_subscriptions;", $wpdb->prefix);
        $wpdb->query($truncateSubscriptionsQuery);

        $truncateSubscribersQuery = sprintf("TRUNCATE %swpr_subscribers;", $wpdb->prefix);
        $wpdb->query($truncateSubscribersQuery);


        $insertNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters ( `name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s', '%s', '%s', '%s')", $wpdb->prefix, 'Test', 'flare@gmail.com', 'Raj', 'flarecore@gmail.com');
        $wpdb->query($insertNewsletterQuery);

        $this->newsletter_id = $wpdb->insert_id;

        $insertAutoresponderQuery = sprintf("INSERT INTO `%swpr_autoresponders` (`nid`, `name`) VALUES (%d, '%s') ", $wpdb->prefix, $this->newsletter_id, 'Test Newsletter');
        $wpdb->query($insertAutoresponderQuery);


        $this->autoresponder_id = $wpdb->insert_id;


        $insertMessagesQuery = sprintf("INSERT INTO `%swpr_autoresponder_messages` (`aid`, `subject`, `htmlenabled`, `sequence`,) VALUES (%d, 'Day 0 Message', 1, 0) (%d, 'Day 1 Message', 1, 1) (%d, 'Day 5 Message', 1, 5) ; ", $wpdb->prefix, $this->autoresponder_id, $this->autoresponder_id, $this->autoresponder_id);
        $wpdb->query($insertMessagesQuery);

        for ($iter =0 ; $iter< 5; $iter++) {

            $insertSubscribersQuery = sprintf("INSERT INTO %swpr_subscribers (nid, `name`, `email`, `active`, `confirmed`, `hash`) VALUES (%d, 'name%d', 'email%d@domain%d.com', 1, 1, %s)", $this->newsletter_id, $iter, $iter, $iter, time(), md5($iter.microtime()));
            $wpdb->query($insertSubscribersQuery);
            $subscriber_id = $wpdb->insert_id;

            $addSubscriptionQuery = sprintf("INSERT INTO %swpr_followup_subscriptions (sid, type, eid, sequence, last_date, last_processed, doc) VALUES (%d, 'autoresponder', %d, -1, 0, 0, %d)", $wpdb->prefix, $subscriber_id, $this->autoresponder_id, 1358610262); //Jan 19, 2013
            $wpdb->query($addSubscriptionQuery);

        }


        for ($iter =6 ; $iter< 11; $iter++) {

            $insertSubscribersQuery = sprintf("INSERT INTO %swpr_subscribers (nid, `name`, `email`, `active`, `confirmed`, `hash`) VALUES (%d, 'name%d', 'email%d@domain%d.com', 1, 1, %s)", $this->newsletter_id, $iter, $iter, $iter, time(), md5($iter.microtime()));
            $wpdb->query($insertSubscribersQuery);
            $subscriber_id = $wpdb->insert_id;

            $addSubscriptionQuery = sprintf("INSERT INTO %swpr_followup_subscriptions (sid, type, eid, sequence, last_date, last_processed, doc) VALUES (%d, 'autoresponder', %d, -1, 0, 0, %d)", $wpdb->prefix, $subscriber_id, $this->autoresponder_id, 1358696662); //Jan 20, 2013
            $wpdb->query($addSubscriptionQuery);

        }


        for ($iter =6 ; $iter< 11; $iter++) {

            $insertSubscribersQuery = sprintf("INSERT INTO %swpr_subscribers (nid, `name`, `email`, `active`, `confirmed`, `hash`) VALUES (%d, 'name%d', 'email%d@domain%d.com', 1, 1, %s)", $this->newsletter_id, $iter, $iter, $iter, time(), md5($iter.microtime()));
            $wpdb->query($insertSubscribersQuery);
            $subscriber_id = $wpdb->insert_id;

            $addSubscriptionQuery = sprintf("INSERT INTO %swpr_followup_subscriptions (sid, type, eid, sequence, last_date, last_processed, doc) VALUES (%d, 'autoresponder', %d, -1, 0, 0, %d)", $wpdb->prefix, $subscriber_id, $this->autoresponder_id, 1359042262); //Jan 20, 2013
            $wpdb->query($addSubscriptionQuery);

        }

    }
}
