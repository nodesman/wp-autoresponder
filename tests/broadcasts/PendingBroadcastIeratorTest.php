<?php

class PendingBroadcastIteratorTest extends WP_UnitTestCase
{
    private $broadcastList;
    private $newsletterId;
    private $someOtherNewsletterId;
    private $futurePendingBroadcastList;
    private $pastPendingBroadcastList;
    private $expiredPastBroadcastList;
    private $expiredCurrentBroadcastList;

    public function setUp()
    {
        JavelinTestHelper::deleteAllNewsletterBroadcasts();
    }

    public function testWhetherIteratorReturnsAListOfPendingBroadcasts() {

        global $wpdb;
        $createNewsletterOneQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `reply_to`, `fromname`, `fromemail`) VALUES (%s, %s , %s, %s);", md5(microtime()."name1"), 'raj@wpresponder.com', '', 'raj', 'raj@wpresponder.com');
        $wpdb->query($createNewsletterOneQuery);

        $this->newsletterId = $wpdb->insert_id;

        //insert another newsletter
        $createNewsletterOneQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_newsletters (`name`, `reply_to`, `fromname`, `fromemail`) VALUES (%s, %s , %s, %s);", md5(microtime()."name1"), 'raj@wpresponder.com', '', 'raj', 'raj@wpresponder.com');
        $wpdb->query($createNewsletterOneQuery);

        $this->someOtherNewsletterId = $wpdb->insert_id;

        //insert 2 broadcasts for the newsletter that have not expired and are in the past
        for ($iter = 0; $iter < 2; $iter++)
        {
            $this->pastPendingBroadcastList[$iter] = array (
                "subject" => md5(microtime()."subject"),
                "htmlbody" => md5(microtime()."htmlbody"),
                "textbody" => md5(microtime()."textbody"),
                "time" => time()-86400,
                "status" => 0
            );

            $this->createBroadcast($this->pastPendingBroadcastList[$iter], $this->someOtherNewsletterId);
        }

        //insert 2 broadcasts for the newsletter that have not expired and are in the past
        for ($iter = 2; $iter < 4; $iter++)
        {
            $this->pastPendingBroadcastList[$iter] = array (
                "subject" => md5(microtime()."subject"),
                "htmlbody" => md5(microtime()."htmlbody"),
                "textbody" => md5(microtime()."textbody"),
                "time" => time()-86400,
                "status" => 0
            );

            $this->createBroadcast($this->pastPendingBroadcastList[$iter], $this->newsletterId);
        }

        //insert 2 broadcasts for the newsletter
        for ($iter=0; $iter<2; $iter++)
        {
            $this->broadcastList[$iter] = array (
                "subject" => md5(microtime()."subject"),
                "htmlbody" => md5(microtime()."htmlbody"),
                "textbody" => md5(microtime()."textbody"),
                "time" => time(),
                "status" => 0
            );

            $this->createBroadcast($this->broadcastList[$iter], $this->newsletterId);
        }

        //insert 2 broadcasts for the newsletter that are expired and are in the past
        for ($iter=0; $iter<2; $iter++)
        {
            $this->expiredPastBroadcastList[$iter] = array (
                "subject" => md5(microtime()."subject"),
                "htmlbody" => md5(microtime()."htmlbody"),
                "textbody" => md5(microtime()."textbody"),
                "time" => (time()-86400),
                "status" => 1
            );

            $this->createBroadcast($this->expiredPastBroadcastList[$iter], $this->newsletterId);
        }

        //insert 2 broadcasts for the newsletter that are in the future and pending
        for ($iter=0; $iter<2; $iter++)
        {
            $this->futurePendingBroadcastList[$iter] = array (
                "subject" => md5(microtime()."subject"),
                "htmlbody" => md5(microtime()."htmlbody"),
                "textbody" => md5(microtime()."textbody"),
                "time" => time()+86400,
                "status" => 1
            );

            $this->createBroadcast($this->futurePendingBroadcastList[$iter], $this->newsletterId);
        }

        //insert 2 broadcasts for the newsletter that are for now but they have already been sent
        for ($iter=0; $iter<2; $iter++)
        {
            $this->expiredCurrentBroadcastList[$iter] = array (
                "subject" => md5(microtime()."subject"),
                "htmlbody" => md5(microtime()."htmlbody"),
                "textbody" => md5(microtime()."textbody"),
                "time" => time(),
                "status" => 1
            );

            $this->createBroadcast($this->expiredCurrentBroadcastList[$iter], $this->newsletterId);
        }

        $time = new DateTime(sprintf("@%s",time()));
        $pendingBroadcasts = new PendingBroadcasts($time);

        $this->assertEquals(6, count($pendingBroadcasts));


        foreach ($pendingBroadcasts as $index=>$broadcast) {

            if (0 == $index) {
                $this->assertEquals($this->pastPendingBroadcastList[0]['subject'], $broadcast->getSubject());
            }

            if (3 == $index) {
                $this->assertEquals($this->pastPendingBroadcastList[3]['subject'], $broadcast->getSubject());
            }

            if (5 == $index) {
                $this->assertEquals($this->broadcastList[1]['subject'], $broadcast->getSubject());
            }

            $broadcast->expire();
        }
    }

    private function createBroadcast($broadcastInfo, $newsletterId)
    {
        global $wpdb;
        $createBroadcastQuery = sprintf("INSERT INTO `%swpr_newsletter_mailouts`
                                    (`nid`, `subject`, `htmlbody`, `textbody`, `time`, `status`) VALUES
                                    (   %d,      '%s',       '%s',       '%s',     %d,       %d);
            ", $wpdb->prefix, $newsletterId, $broadcastInfo['subject'], $broadcastInfo['htmlbody'], $broadcastInfo['textbody'], $broadcastInfo['time'], $broadcastInfo['status']);
        $wpdb->query($createBroadcastQuery);
    }
} 