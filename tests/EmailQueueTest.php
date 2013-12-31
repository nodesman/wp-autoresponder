<?php
class EmailQueueTest extends WP_UnitTestCase
{
    private $newsletter;
    private $subscriber;

    private $senderAddressSetting = "Test";

    function setUp()
    {
        JavelinTestHelper::deleteAllEmailsFromQueue();
        JavelinTestHelper::deleteAllMessagesFromQueue();
        JavelinTestHelper::deleteAllSubscribers();
        JavelinTestHelper::deleteAllNewsletterBroadcasts();
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

        $expectedHtmlBody = $this->getExpectedHtmlBody($email);
        $expectedTextBody = $this->getExpectedTextBody($email);

        $emailObj = $javelinQueue->enqueue($this->subscriber, $email);
        $this->assertEquals($email['subject'], $emailObj->getSubject());
        $this->assertEquals($expectedHtmlBody, $emailObj->getHtmlBody());
        $this->assertEquals($expectedTextBody, $emailObj->getTextBody());
        $this->assertEquals($email['meta_key'], $emailObj->getMetaKey());
    }

    public function testWhetherCustomFieldValuesAreReplaced()
    {
        global $javelinQueue, $wpdb;

        //create custom field for the newsletter
        $createCustomFieldQuery = sprintf("INSERT INTO %swpr_custom_fields (`nid`, `type`, `name`, `label`) VALUES (%d, 'text', 'test', 'Test');", $wpdb->prefix, $this->newsletter->getId());
        $wpdb->query($createCustomFieldQuery);
        $customFieldId = $wpdb->insert_id;

        //add the custom field value for the subscriber
        $customFieldValue = 'MarsellusWallace';
        $createCustomFieldValueQuery = sprintf("INSERT INTO %swpr_custom_fields_values (`nid`, `sid`, `cid`, `value`) VALUES (%d, %d, %d, '%s')",$wpdb->prefix, $this->newsletter->getId(), $this->subscriber->getId(), $customFieldId, $customFieldValue);
        $wpdb->query($createCustomFieldValueQuery);

        $params = array(
            "subject" => "This is a test email [!test!]",
            "htmlbody" => "Test [!test!]",
            "textbody" => "Test [!test!]",
            "meta_key" => "TEST-TEST-TEST",
            'sent' => 1
        );

        $expected = $params;

        $expected['textbody'] =$this->getExpectedTextBody($params);
        $expected['htmlbody'] =$this->getExpectedHtmlBody($params);

        foreach ($expected as $index=>$value) {
            $expected[$index] = str_replace('[!test!]', $customFieldValue, $value);
        }

        $email = $javelinQueue->enqueue($this->subscriber, $params);

        $this->assertEquals($expected['subject'], $email->getSubject());
        $this->assertEquals($expected['textbody'], $email->getTextBody());
        $this->assertEquals($expected['htmlbody'], $email->getHtmlBody());
    }

    public function testWhetherUnsubscriptionTagGetsReplaced()
    {
        global $javelinQueue;
        $htmlBody = "This is a test html body ";
        $textBody = "This is a test text body ";
        $params = array(
            "subject" => "This is a test email",
            "htmlbody" => "{$htmlBody}[!unsubscribe!]",
            "textbody" => "{$textBody}[!unsubscribe!]",
            "meta_key" => "TEST-TEST-TEST"
        );

        $expectedHtmlBody = sprintf('%s%s<br />%s<br />',$htmlBody, $this->subscriber->getUnsubscriptionUrl(), $this->senderAddressSetting);
        $expectedTextBody = sprintf("%s%s\r\n%s",$textBody, $this->subscriber->getUnsubscriptionUrl(), $this->senderAddressSetting);

        $email = $javelinQueue->enqueue($this->subscriber, $params);

        $this->assertEquals($params['subject'], $email->getSubject());
        $this->assertEquals($expectedHtmlBody, $email->getHtmlBody());
        $this->assertEquals($expectedTextBody, $email->getTextBody());
        $this->assertEquals(0, $email->isSent());
        $this->assertEquals(1, $email->isHtmlEnabled());
        $this->assertEquals($this->newsletter->getReplyTo(), $email->getReplyTo());
        $this->assertEquals($this->subscriber->getId(), $email->getSubscriber()->getId());
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

    public function testWhetherEnqueueingAssumesAnAppropriateSentValueAsProvided()
    {
        global $javelinQueue;
        $params = array(
            "subject" => "This is a test email",
            "htmlbody" => "Test",
            "textbody" => "Test",
            "meta_key" => "TEST-TEST-TEST",
            'sent' => 1
        );

        $email = $javelinQueue->enqueue($this->subscriber, $params);
        $this->assertEquals(1, $email->isSent());
    }

    function tearDown()
    {
        JavelinTestHelper::deleteAllSubscribers();
        JavelinTestHelper::deleteAllNewsletters();
        JavelinTestHelper::deleteAllMessagesFromQueue();
    }

    /**
     * @param $email
     * @return string
     */
    private function getExpectedHtmlBody($email)
    {
        return sprintf('%s<br />%s<br /><a href="%s">Click here to unsubscribe.</a>', $email['htmlbody'], $this->senderAddressSetting, $this->subscriber->getUnsubscriptionUrl());
    }

    /**
     * @param $email
     * @return string
     */
    private function getExpectedTextBody($email)
    {
        return sprintf("%s\r\n%s\r\nClick here to unsubscribe:\r\n%s", $email['textbody'], JavelinConfig::senderAddress(), $this->subscriber->getUnsubscriptionUrl());
    }
}