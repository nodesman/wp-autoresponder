<?php
class Broadcast {

    private $id;
    private $whether_sent;
    private $subject;
    private $htmlbody;
    private $sent;
    private $newsletter_id;

    public function __construct($broadcast_id) {
        global $wpdb;
        $getBroadcastQuery = sprintf("SELECT * FROM %swpr_newsletter_mailouts WHERE id=%d", $broadcast_id);
        $broadcast = $wpdb->get_row($getBroadcastQuery);
        $this->subject = $broadcast->subject;
        $this->htmlbody = $broadcast->htmlbody;
        $this->textbody = $broadcast->textbody;
        $this->newsletter_id = $broadcast->nid;
    }

    public function deliver()
    {
        $confirmedAndActiveNewsletterSubscribers = new ConfirmedNewsletterSubscribersList($nid);
        foreach ($confirmedAndActiveNewsletterSubscribers as $subscriber)
        {
            $email = array(
                "subject" => $this->subject,
                "textbody" => $this->textbody,
                "htmlbody" => $this->htmlbody,
                "htmlenabled"=> $this->isHtmlEnabled(),
                "meta_key"=> $this->getMetaKey($subscriber->getId())
            );
            EmailQueue::enqueue($subscriber, $email);
        }
        $this->expireBroadcast();
    }

    private function getMetaKey($sid)
    {
        return sprintf("BR-%s-%s-%s", $sid, $this->id, $this->getNewsletterId());
    }

    public function getNewsletterId()
    {
        return $this->newsletter_id;
    }

    public function isSent()
    {
        return $this->sent;
    }
   
    private function expireBroadcast()
    {
        global $wpdb;
        $markAsSentQuery = sprintf("UPDATE %swpr_newsletter_mailouts SET sent=1 WHERE id=%d", $this->id);
        $wpdb->query($markAsSentQuery);
        $this->sent = true;
    }

    private function isHtmlEnabled()
    {
        return (empty($this->htmlbody));
    }
}