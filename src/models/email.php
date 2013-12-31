<?php
class JEmail {

    private $subject;

    private $htmlbody;
    private $textbody;
    private $subscriber;
    private $isHtml;
    private $isSent;
    private $meta_key;

    public function __construct($id) {
        global $wpdb;
        $id = intval ($id);
        if (0 == $id)
            throw new InvalidArgumentException("Invalid ID provided for email.");
        $getEmailQuery = sprintf("SELECT * FROM `%swpr_queue` WHERE `id`=%d;", $wpdb->prefix, $id);
        $email = $wpdb->get_row($getEmailQuery);
        $this->subject = $email->subject;
        $this->htmlbody = $email->htmlbody;
        $this->textbody = $email->textbody;
        $this->subscriber = ($email->sid == 0) ? new Subscriber($email->sid) : NULL;
        $this->meta_key = $email->meta_key;
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
    public function getIsHtml()
    {
        return $this->isHtml;
    }

    /**
     * @return mixed
     */
    public function getIsSent()
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