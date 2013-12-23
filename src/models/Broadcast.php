<?php
class Broadcast {


    private $id;
    private $newsletter_id;
    private $dispatch_time;
    private $whether_sent;
    private $subject;
    private $htmlbody;

    /**
     * @return mixed
     */
    public function send()
    {

        $newsletter = Newsletter::getNewsletter($nid);

        $confirmedAndActiveNewsletterSubscribers = new ConfirmedNewsletterSubscribersList($nid);

        if (0 < count($confirmedAndActiveNewsletterSubscribers))
        {
            foreach ($confirmedAndActiveNewsletterSubscribers as $subscriber)
            {
                $meta_key = $this->getMetaKey($subscriber->getId());
                $emailParameters = array( "subject" => $this->subject,
                    "fromname"=> $newsletter->getFromName(),
                    "fromemail"=> $newsletter->getFromEmail(),
                    "textbody" => $this->textbody,
                    "htmlbody" => $this->htmlbody,
                    "htmlenabled"=> (empty($html_body))?0:1,
                    "meta_key"=> $meta_key
                );

                foreach ($emailParameters as $index=>$value) {
                    $emailParameters[$index] = Subscriber::replaceCustomFieldValues($value, $subscriber->getId());
                }

                sendmail($subscriber->getId(),$emailParameters);
            }
        }

        $this->expire_broadcast();
    }

    private function getMetaKey($sid)
    {
        return sprintf("BR-%s-%s-%s", $sid, $this->id, $this->getNewsletterId());
    }

    /**
     * @param mixed $htmlbody
     */

    /**
     * @return mixed
     */
    public function getNewsletterId()
    {
        return $this->newsletter_id;
    }

    /**
     * @param mixed $whether_sent
     */
    public function is_sent($whether_sent)
    {
        $this->whether_sent = $whether_sent;
    }

    public function __construct($broadcast_id) {

        global $wpdb;
        $getBroadcastQuery = sprintf("SELECT * FROM %swpr_newsletter_mailouts WHERE id=%d", $broadcast_id);
        $broadcast = $wpdb->get_row($getBroadcastQuery);

    }

    private function expire_broadcast()
    {
        global $wpdb;
        $markAsSentQuery = sprintf("UPDATE %swpr_newsletter_mailouts SET sent=1 WHERE id=%d", $this->id);
        $wpdb->query($markAsSentQuery);

    }


}
