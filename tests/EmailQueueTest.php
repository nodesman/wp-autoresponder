<?php
class EmailQueueTest extends WP_UnitTestCase
{
    private $newsletter;
    private $subscriber;

    private $senderAddressSetting = "Test";

    function setUp()
    {
        //create a newsletter
        JavelinConfig::senderAddress($this->senderAddressSetting);
        $this->newsletter = JavelinTestHelper::createNewsletter();
        $this->subscriber = JavelinTestHelper::createSubscriber($this->newsletter);
        //create a subscriber
    }

    public function testWhetherEnqueueAddsEmailToQueue()
    {
        global $javelinQueue;
        $email = array(
            "subject" => "This is a test email",
            "htmlbody" => "This is a test html body",
            "textbody" => "This is a test text body",
            "meta_key" => "TEST-TEST-TEST"
        );


        $expectedHtmlBody = sprintf('%s<br />%s<br /><a href="%s">Click here to unsubscribe.</a>', $email['htmlbody'], $this->senderAddressSetting, $this->subscriber->getUnsubscriptionUrl());
        $expectedTextBody = sprintf("%s\r\n%s\r\nClick here to unsubscribe:\r\n%s", $email['textbody'], JavelinConfig::senderAddress(), $this->subscriber->getUnsubscriptionUrl());

        $emailObj = $javelinQueue->enqueue($this->subscriber, $email);
        $this->assertEquals($email['subject'], $emailObj->getSubject());
        $this->assertEquals($expectedHtmlBody, $emailObj->getHtmlBody());
        $this->assertEquals($expectedTextBody, $emailObj->getTextBody());
        $this->assertEquals($email['meta_key'], $emailObj->getMetaKey());
    }

    public function testWhetherTheQueueInfersValuesOtherParametersWhenLeftOut()
    {
        //check if the htmlenabled parameter is automatically
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherNotSendingABodyThrowsException()
    {
        global $javelinQueue;
        $javelinQueue->enqueue($this->subscriber, array(
            'subject' => 'something',
            'meta_key' => 'something'
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherEnqueueingAEmailWithNoSubjectCausesException()
    {
        global $javelinQueue;
        $javelinQueue->enqueue($this->subscriber, array(
            'htmlbody' => 'test',
            'textbody' => 'test',
            'meta_key' => 'something'
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherSendingAnEmailWithEmptySubjectCausesException()
    {
        global $javelinQueue;
        $javelinQueue->enqueue($this->subscriber, array(
            'subject'  => '',
            'htmlbody' => 'test',
            'textbody' => 'test',
            'meta_key' => 'something'
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherEnqueueingAEmptyBodyCausesException()
    {
        global $javelinQueue;
        $javelinQueue->enqueue($this->subscriber, array(
            'subject' => 'something',
            'htmlbody' => '',
            'textbody' => '',
            'meta_key' => 'something'
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherEnqueueingANoMetaKeyEmailCausesException()
    {
        global $javelinQueue;
        $javelinQueue->enqueue($this->subscriber, array(
            'subject' => 'something',
            'htmlbody' => 'TEst',
            'textbody' => 'Test'
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherEnqueueingAEmptyMetaKeyEmailCausesException()
    {
        global $javelinQueue;
        $javelinQueue->enqueue($this->subscriber, array(
            'subject' => 'something',
            'htmlbody' => 'TEst',
            'textbody' => 'Test',
            'meta_key' => ''
        ));
    }

    function tearDown()
    {
        JavelinTestHelper::deleteAllSubscribers();
        JavelinTestHelper::deleteAllNewsletters();
        JavelinTestHelper::deleteAllMessagesFromQueue();
    }
}