<?php
class EmailQueueTest extends WP_UnitTestCase
{
    private $newsletter;
    private $subscriber;

    function setUp()
    {
        //create a newsletter

        $this->newsletter = JavelinTestHelper::createNewsletter();
        $this->subscriber = JavelinTestHelper::createSubscriber($this->newsletter);
        //create a subscriber
    }

    public function testWhetherEnqueueAddsEmailToQueue()
    {
        $email = array(
            "subject" => "This is a test email",
            "htmlbody" => "This is a test html body",
            "textbody" => "This is a test text body",
            "meta_key" => "TEST-TEST-TEST"
        );

        $emailRecordId = EmailQueue::getInstance()->enqueue($this->subscriber, $email);
    }

    public function testWhetherTheQueueInfersValuesOtherParametersWhenLeftOut()
    {
        //check if the htmlenabled parameter is automatically
    }

    function tearDown()
    {
        parent::tearDown();
    }
}