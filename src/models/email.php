<?php
class JEmail {

    private $subject;
    private $id;

    private $htmlbody;
    private $textbody;
    private $subscriber;
    private $isHtml;
    private $isSent;
    private $meta_key;
    private $reply_to;
    public function __construct($id) {
        global $wpdb;
        $inted_id = intval ($id);
        if (0 == $inted_id)
            throw new InvalidArgumentException("Invalid ID provided for email.");
        $getEmailQuery = sprintf("SELECT * FROM `%swpr_queue` WHERE `id`=%d;", $wpdb->prefix, $inted_id);
        $email = $wpdb->get_row($getEmailQuery);
        $this->subject = $email->subject;
        $this->id = $email->id;
        $this->htmlbody = $email->htmlbody;
        $this->textbody = $email->textbody;
        $this->subscriber = ($email->sid != 0) ? new Subscriber($email->sid) : NULL;
        $this->meta_key = $email->meta_key;
        $this->reply_to = $email->reply_to;
        $this->isSent = (int) $email->sent;
        $this->isHtml = (int) $email->htmlenabled;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getReplyTo()
    {
        return $this->reply_to;
    }

    /**
     * @return mixed
     */
    public function getHtmlBody()
    {
        return $this->htmlbody;
    }

    /**
     * @return mixed
     */
    public function isHtmlEnabled()
    {
        return $this->isHtml;
    }

    /**
     * @return mixed
     */
    public function isSent()
    {
        return $this->isSent;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return null|\Subscriber
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @return mixed
     */
    public function getTextBody()
    {
        return $this->textbody;
    }

    /**
     * @return mixed
     */
    public function getMetaKey()
    {
        return $this->meta_key;
    }
}