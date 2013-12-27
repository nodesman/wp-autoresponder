<?php
include __DIR__ . "/../src/models/iterators/confirmed_newsletter_subscribers_list.php";

class ConfirmedNewsletterSubscribersIteratorTest extends WP_UnitTestCase {

    private $newsletterId;
    private $subscribers;

    public function setUp()
    {
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
        $someOtherNewsletterId = $wpdb->insert_id;

        $this->subscribers = array();

        for ($iter=0; $iter<5; $iter++)
        {
            $current = array(
                "nid" => $this->newsletterId,
                "name" => md5("sub".microtime().$iter),
                "email" => md5('email'.microtime().$iter),
                "hash" => md5("hash".microtime().$iter)
            );
            $this->subscribers[] = $current;
            $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, hash, active, confirmed) VALUES (%d, '%s','%s', '%s', 1, 1);", $wpdb->prefix, $someOtherNewsletterId, $current['name'] , $current['email'] , $current['hash'] );
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
        $this->newsletterId = $wpdb->insert_id;

        //add in some unconfirmed subscribers
        for ($iter=0; $iter<5; $iter++)
        {
            $current = array(
                "nid" => $this->newsletterId,
                "name" => md5("sub".microtime().$iter),
                "email" => md5('email'.microtime().$iter),
                "hash" => md5("hash".microtime().$iter)
            );
            $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, hash, active, confirmed) VALUES (%d, '%s','%s', '%s', 1, 0);", $wpdb->prefix, $this->newsletterId,$current['name'] , $current['email'] , $current['hash'] );
            $wpdb->query($addSubscriberQuery);
        }

        //add in some unsubscribed subscribers
        for ($iter=0; $iter<5; $iter++)
        {
            $current = array(
                "nid" => $this->newsletterId,
                "name" => md5("sub".microtime().$iter),
                "email" => md5('email'.microtime().$iter),
                "hash" => md5("hash".microtime().$iter)
            );
            $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, hash, active, confirmed) VALUES (%d, '%s','%s', '%s', 0, 1);", $wpdb->prefix, $this->newsletterId,$current['name'] , $current['email'] , $current['hash'] );
            $wpdb->query($addSubscriberQuery);
        }

        //add in some real subscribers we are going to check for in the result
        $this->subscribers = array();
        for ($iter=0; $iter<5; $iter++)
        {
            $current = array(
                "nid" => $this->newsletterId,
                "name" => md5("sub".microtime().$iter),
                "email" => md5('email'.microtime().$iter),
                "hash" => md5("hash".microtime().$iter)
            );
            $this->subscribers[] = $current;
            $addSubscriberQuery = sprintf("INSERT INTO %swpr_subscribers (nid, name, email, hash, active, confirmed) VALUES (%d, '%s','%s', '%s', 1, 1);", $wpdb->prefix, $this->newsletterId,$current['name'] , $current['email'] , $current['hash'] );
            $wpdb->query($addSubscriberQuery);
        }
    }

    public function testWhetherIterationsFetchObjectsInCorrectOrder()
    {
        $ns_iterator = new ConfirmedNewsletterSubscribersList($this->newsletterId);
        $this->assertEquals(5, count($ns_iterator));

        foreach ($ns_iterator as $index=> $value)
        {
            if ($index == 0)
            {
                $this->assertEquals($this->subscribers[0]['name'], $value->getName());
            }

            if ($index == 4)
            {
                $this->assertEquals($this->subscribers[4]['name'], $value->getName());
            }
        }
    }

} 