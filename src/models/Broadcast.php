<?php
include_once __DIR__."/email_queue.php";
class Broadcast
{
    private $id;

    private $subject;
    private $htmlbody;
    private $textbody;
    private $sent;
    private $newsletter_id;

    public function getId()
    {
        return $this->id;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getHtmlBody()
    {
        return $this->htmlbody;
    }

    public function getTextBody()
    {
        return $this->textbody;
    }

    public function __construct($broadcastId)
    {
        global $wpdb;
        $broadcastId  = intval($broadcastId);
        $getBroadcastQuery = sprintf("SELECT * FROM %swpr_newsletter_mailouts WHERE id=%d;", $wpdb->prefix, $broadcastId);
        $broadcast = $wpdb->get_row($getBroadcastQuery);

        if (NULL == $broadcast)
            throw new NonExistentBroadcastException($broadcastId);

        $this->subject = $broadcast->subject;
        $this->htmlbody = $broadcast->htmlbody;
        $this->textbody = $broadcast->textbody;
        $this->newsletter_id = $broadcast->nid;
        $this->id = $broadcastId;
    }

    public function deliver()
    {
        global $javelinQueue;
        $confirmedAndActiveNewsletterSubscribers = new ConfirmedNewsletterSubscribersList($this->newsletter_id);
        foreach ($confirmedAndActiveNewsletterSubscribers as $subscriber)
        {
            $email = array(
                "subject" => $this->subject,
                "textbody" => $this->textbody,
                "htmlbody" => $this->htmlbody,
                "htmlenabled"=> $this->isHtmlEnabled(),
                "meta_key"=> $this->getMetaKey($subscriber->getId())
            );

            $javelinQueue->enqueue($subscriber, $email);
        }
        $this->expire();
    }

    private function getMetaKey($subscriber_id)
    {
        return sprintf("BR-%s-%s-%s", $subscriber_id, $this->id, $this->getNewsletterId());
    }

    public function getNewsletterId()
    {
        return $this->newsletter_id;
    }

    public function isSent()
    {
        return $this->sent;
    }

    public function expire()
    {
        global $wpdb;
        $markAsSentQuery = sprintf("UPDATE %swpr_newsletter_mailouts SET status=1 WHERE id=%d", $wpdb->prefix, $this->id);
        $wpdb->query($markAsSentQuery);
        $this->sent = true;
    }

    private function isHtmlEnabled()
    {
        return (empty($this->htmlbody));
    }
}

class NonExistentBroadcastException extends Exception
{
    private $broadcast_id;

    public function __construct($broadcast_id)
    {
        $this->broadcast_id = $broadcast_id;
        parent::__construct();
    }

    public function __toString()
    {
        return sprintf("Attempted to create a non existent broadcast with ID '%d'", $this->broadcast_id);
    }
}