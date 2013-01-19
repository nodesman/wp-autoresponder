<?php

require __DIR__."/../../src/processes/autoresponder_process.php";
require __DIR__."/../../src/models/autoresponder.php";

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

        


    }
}