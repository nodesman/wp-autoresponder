<?php
include_once __DIR__."/../src/processes/email_delivery_processor.php";

class EmailDeliveryProcessorTest extends WP_UnitTestCase
{
    private $newsletter;
    private $emailIds;
    private $subscriber;
    
    public function setUp()
    {
        global $javelinQueue;
        JavelinTestHelper::deleteAllEmailsFromQueue();
        JavelinTestHelper::deleteAllSubscribers();
        JavelinTestHelper::deleteAllNewsletters();
        JavelinTestHelper::deleteAllNewsletterBroadcasts();
        $this->newsletter = JavelinTestHelper::createNewsletter();
        $this->subscriber = JavelinTestHelper::createSubscriber($this->newsletter);

        $this->emailIds = array();
        for ($iter = 0; $iter < 10; $iter++)
        {
            $mail = array (
                'subject' => "This is a test {$iter}",
                'htmlbody' => "This is a test {$iter}",
                'meta_key' => "TEST-TEST-{$iter}"
            );

            $emails[$iter] = $javelinQueue->enqueue($this->subscriber, $mail);
            $this->emailIds[] = $emails[$iter]->getId();

            $sentMail = array(
                'subject' => "This is a test {$iter} sent",
                'htmlbody' => "This is a test {$iter} sent",
                'meta_key' => "TEST-TEST-{$iter}-sent",
                'sent' => 1
            );
            $javelinQueue->enqueue($this->subscriber, $sentMail);
        }
    }

    public function testDummy() {

    }


    public function tearDown() {

    }
} 