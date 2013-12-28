<?php
include_once __DIR__."/../src/models/broadcast.php";

class BroadcastTest extends WP_UnitTestCase
{
    private $newsletter;
    private $broadcast;
    private $broadcastInfo;
    private $subscribers;
    private $numberOfSubscribers = 10;
    private $sid_array = array();

    public function setUp()
    {
        $this->newsletter = JavelinTestHelper::createNewsletter();

        $this->broadcastInfo = array(
            "subject" => md5(microtime().'subject'),
            "textbody" => md5(microtime().'textbody'),
            "htmlbody" => md5(microtime()."htmlbody"),
            "time" => time(),
            "nid" => $this->newsletter->getId(),
            'status' => 0
        );
        $this->broadcast = JavelinTestHelper::createBroadcast($this->newsletter, $this->broadcastInfo);

        for ($iter = 0;$iter < $this->numberOfSubscribers; $iter++)
        {
            $this->subscribers[$iter] = JavelinTestHelper::createSubscriber($this->newsletter);
            $this->sid_array[] = $this->subscribers[$iter]->getId();
        }

        //set up some other newsletter for shits and giggles
        $another_newsletter = JavelinTestHelper::createNewsletter();
        $another_broadcast = JavelinTestHelper::createBroadcast($another_newsletter);

        for ($iter = 0;$iter < 10; $iter++)
        {
            JavelinTestHelper::createSubscriber($another_newsletter);
        }

        JavelinTestHelper::deleteAllEmailsFromQueue();

    }

    public function testWhetherAllGettersReturnExpectedValues()
    {
        $this->assertEquals($this->broadcastInfo['subject'], $this->broadcast->getSubject());

        $this->assertEquals($this->newsletter->getId(), $this->broadcast->getNewsletterId());

        $this->assertEquals($this->broadcastInfo['status'], $this->broadcast->isSent());

        $this->assertEquals($this->broadcastInfo['htmlbody'], $this->broadcast->getHtmlBody());

        $this->assertEquals($this->broadcastInfo['textbody'], $this->broadcast->getTextBody());
    }

    public function testWhetherExpiringABroadcastExpiresTheBroadcast()
    {
        $this->assertEquals(0, $this->broadcast->isSent());

        $this->broadcast->expire();

        $this->assertEquals(1, $this->broadcast->isSent());
    }

    /**
     * @expectedException NonExistentBroadcastException
     */
    public function testWhetherInstantiatingNonExistentBroadcastResultsInException()
    {
        new Broadcast(4938329);
        //pretty sure this isn't going to be the ID for a broadcast that we are going to create because of how test helper functions.
    }

    public function testWhetherTriggeringDeliveryResultsInEmailsBeingEnqueued()
    {
        global $wpdb;
        $this->assertEquals(0, EmailQueue::getInstance()->getNumberOfPendingEmails());
        $this->broadcast->deliver();
        $this->assertEquals($this->numberOfSubscribers, EmailQueue::getInstance()->getNumberOfPendingEmails());

        //TODO: Change the following to use the EmailQueue's API after it is written. For now hitting the database directly

        $getRecipientsOfAllEmailsQuery=sprintf("SELECT sid FROM %swpr_queue",$wpdb->prefix);
        $recipientList = $wpdb->get_col($getRecipientsOfAllEmailsQuery);
        $difference = array_diff($recipientList, $this->sid_array);
        $this->assertEquals(0, count($difference));
    }

    public function testWhetherTheBroadcastEmailIsOfTheExpectedFormat()
    {
        JavelinTestHelper::deleteAllSubscribers();

        $subscriber = JavelinTestHelper::createSubscriber($this->newsletter);

        $expected = array(
            'subject' => $this->broadcast->getSubject(),
            'textbody' => $this->broadcast->getTextBody(),
            'htmlbody' => $this->broadcast->getHtmlBody(),
            'htmlenabled' => false,
            'meta_key' => sprintf('BR-%s-%s-%s',$subscriber->getId(), $this->broadcast->getId(), $this->newsletter->getId())
        );

        $queue = $this->getMock('EmailQueue', array('enqueue'), array(), '', false);

        $ref = new ReflectionProperty('EmailQueue', 'instance');
        $ref->setAccessible(true);
        $ref->setValue(null, $queue);

        $queue->expects($this->any())
              ->method('enqueue')
              ->with($subscriber, $this->identicalTo($expected));

        $this->broadcast->deliver();

        $ref = new ReflectionProperty('EmailQueue', 'instance');
        $ref->setAccessible(true);
        $ref->setValue(null, null);
    }

    public function tearDown()
    {
        JavelinTestHelper::deleteAllNewsletters();
        JavelinTestHelper::deleteAllNewsletterBroadcasts();
        JavelinTestHelper::deleteAllMessagesFromQueue();
    }

} 