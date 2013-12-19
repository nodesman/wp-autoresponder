<?php
include __DIR__ . "/../src/models/iterators/ConfirmedNewsletterSubscribersList.php";

class ConfirmedNewsletterSubscribersIteratorTest extends WP_UnitTestCase {

    private $newsletter_id;
    private $subscribers;

    public function setUp() {

        //create newsletter
        global $wpdb;

        $newsletter = array(
            "name" => "1".md5(microtime()),
            "reply_to" => 'flarecore@'.md5(microtime()).'.com',
            "fromname" => 'Test',
            "fromemail" => 'flare@'.md5(microtime()).'.com'
        );

        $addNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters (`name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s','%s', '%s', '%s')", $wpdb->prefix, $newsletter['name'], $newsletter['reply_to'], $newsletter['fromname'], $newsletter['fromemail']);
        $wpdb->query($addNewsletterQuery);
        $another_newsletter_id = $wpdb->insert_id;

        $this->subscribers = array();

        for ($iter=0; $iter<5; $iter++) {
            $current = array(
                "nid" => $this->newsletter_id,
                "name" => md5("sub".microtime().$iter),
                "email" => md5('email'.microtime().$iter),
                "hash" => md5("hash".microtime().$iter)
            );
            $this->subscribers[] = $current;
            $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, hash, active, confirmed) VALUES (%d, '%s','%s', '%s', 1, 1);", $wpdb->prefix, $another_newsletter_id, $current['name'] , $current['email'] , $current['hash'] );
            $wpdb->query($addSubscriberQuery);
        }

        $newsletter = array(
            "name" => md5(microtime()),
            "reply_to" => 'flarecore@'.md5(microtime()).'.com',
            "fromname" => 'Test',
            "fromemail" => 'flare@'.md5(microtime()).'.com'
        );

        $addNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters (`name`, `reply_to`, `fromname`, `fromemail`) VALUES ('%s','%s', '%s', '%s')", $wpdb->prefix, $newsletter['name'], $newsletter['reply_to'], $newsletter['fromname'], $newsletter['fromemail']);
        $wpdb->query($addNewsletterQuery);
        $this->newsletter_id = $wpdb->insert_id;

        for ($iter=0; $iter<5; $iter++) {
            $current = array(
                "nid" => $this->newsletter_id,
                "name" => md5("sub".microtime().$iter),
                "email" => md5('email'.microtime().$iter),
                "hash" => md5("hash".microtime().$iter)
            );
            $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, hash, active, confirmed) VALUES (%d, '%s','%s', '%s', 1, 0);", $wpdb->prefix, $this->newsletter_id,$current['name'] , $current['email'] , $current['hash'] );
            $wpdb->query($addSubscriberQuery);
        }

        for ($iter=0; $iter<5; $iter++) {
            $current = array(
                "nid" => $this->newsletter_id,
                "name" => md5("sub".microtime().$iter),
                "email" => md5('email'.microtime().$iter),
                "hash" => md5("hash".microtime().$iter)
            );
            $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, hash, active, confirmed) VALUES (%d, '%s','%s', '%s', 0, 1);", $wpdb->prefix, $this->newsletter_id,$current['name'] , $current['email'] , $current['hash'] );
            $wpdb->query($addSubscriberQuery);
        }

        $this->subscribers = array();

        for ($iter=0; $iter<5; $iter++) {
            $current = array(
                "nid" => $this->newsletter_id,
                "name" => md5("sub".microtime().$iter),
                "email" => md5('email'.microtime().$iter),
                "hash" => md5("hash".microtime().$iter)
            );
            $this->subscribers[] = $current;
            $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, hash, active, confirmed) VALUES (%d, '%s','%s', '%s', 1, 1);", $wpdb->prefix, $this->newsletter_id,$current['name'] , $current['email'] , $current['hash'] );
            $wpdb->query($addSubscriberQuery);
        }


    }

    public function testWhetherFetchingFirstItemFetchesTheFirstItem() {

        $ns_iterator = new ConfirmedNewsletterSubscribersList($this->newsletter_id);

        $this->assertEquals(5, count($ns_iterator));

        foreach ($ns_iterator as $index=> $value) {

            if ($index == 0) {
                $this->assertEquals($this->subscribers[0]['name'], $value->getName());
            }

            if ($index == 4) {
                $this->assertEquals($this->subscribers[4]['name'], $value->getName());
            }
        }
    }

} 