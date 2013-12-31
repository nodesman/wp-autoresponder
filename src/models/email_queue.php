<?php
include_once __DIR__."/email.php";
class EmailQueue
{
    private static $instance;

    private $unsubscriptionShortcode = "[!unsubscribe!]";
    private $canSpamShortCode = '[!can-spam-address!]';

    private function __construct() {
        //nothing to see. move along.
    }

    public static function getInstance()
    {
        if (empty(self::$instance))
            self::$instance = new EmailQueue();
        return self::$instance;
    }

    public function enqueue(Subscriber $subscriber, $email)
    {
        $newsletter = $subscriber->getNewsletter();

        if (empty($email['subject']))
            throw new InvalidArgumentException("Expected a subject for the email. None given.");
        if (empty($email['htmlbody']) && empty($email['textbody']))
            throw new InvalidArgumentException("Expected a HTML body or a Text body to be present. None given.");
        if (empty($email['meta_key'])) {
            throw new InvalidArgumentException("Email missing the meta key. What kind of email is this? Who is this for?");
        }

        if (isset($email['sent']) && !in_array($email['sent'], array( 0, 1)))
           throw new InvalidArgumentException("Invalid status given for sent status");

        $email['to'] = $subscriber->getEmail();
        $email['headers'] = ''; //may be some day

        if (!isset($email['textbody'])) {
            $email['textbody'] = '';
        }
        if (!isset($email['htmlbody'])) {
            $email['htmlbody'] = '';
        }

        $email['fromname'] = $newsletter->getFromName();
        $email['fromemail'] = $newsletter->getFromEmail();
        $newsletterReplyTo = $newsletter->getReplyTo();
        $email['reply_to'] = (!empty($newsletterReplyTo)) ? $newsletterReplyTo : '';
        $email['htmlenabled'] = ($this->isHtmlBodyEmpty($email)) ? 1 : 0 ;
        $email['delivery_type'] = 0;
        $email['hash'] = $this->getHash($subscriber, $email);
        $email['sent'] = ( isset($email['sent']))? $email['sent'] : 0;
        $email['sid'] = $subscriber->getId();

        $this->replaceCustomFields($subscriber, $email);
        $this->maybeAttachAddress($email);
        $this->attachUnsubscriptionUrl($subscriber, $email);
        $email_id = $this->recordEmail($email);
        return new JEmail($email_id);
    }

    public function getNumberOfPendingEmails()
    {
        global $wpdb;
        $getNumberOfEmailsQuery = sprintf("SELECT count(*) `number` FROM %swpr_queue WHERE `sent` = 0;", $wpdb->prefix);
        $number = $wpdb->get_var($getNumberOfEmailsQuery);
        return $number;
    }

    private function recordEmail($email)
    {
        global $wpdb;
        $insertEmailQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_queue
                                                            (`from`,
                                                             `fromname`,
                                                             `to`,
                                                             `reply_to`,
                                                             `subject`,
                                                             `htmlbody`,
                                                             `textbody`,
                                                             `headers`,
                                                             `htmlenabled`,
                                                             `delivery_type`,
                                                             `meta_key`,
                                                             `hash`,
                                                             `sent`,
                                                             `sid`) VALUES
                                                             (%s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %d, %d);",
                                                            $email['fromemail'],
                                                            $email['fromname'],
                                                            $email['to'],
                                                            $email['reply_to'],
                                                            $email['subject'],
                                                            $email['htmlbody'],
                                                            $email['textbody'],
                                                            $email['headers'],
                                                            $email['htmlenabled'],
                                                            $email['delivery_type'],
                                                            $email['meta_key'],
                                                            $email['hash'],
                                                            $email['sent'],
                                                            $email['sid']);
        $wpdb->query($insertEmailQuery);
        return (int) $wpdb->insert_id;
    }

    private function isHtmlBodyEmpty($email) {
        return !empty($email['htmlbody']);
    }

    /**
     * @return string
     */
    private function getHtmlAddress() {
        return "<br />" . nl2br(JavelinConfig::senderAddress()). '<br />';
    }

    private function getHtmlUnsubscriptionMessage(Subscriber $subscriber)
    {
        $unsubscriptionUrl = $subscriber->getUnsubscriptionUrl();
        $htmlUnSubscribeMessage = __(sprintf('<a href="%s">Click here to unsubscribe.</a>', $unsubscriptionUrl), JAVELIN_DEFAULT_TEXT_DOMAIN);
        return $htmlUnSubscribeMessage;
    }



    private function attachUnsubscriptionUrl(Subscriber $subscriber, &$email)
    {
        $unsubscriptionUrl = $subscriber->getUnsubscriptionUrl();

        if (strstr($email['textbody'], $this->unsubscriptionShortcode)) {
            $email['textbody'] = str_replace($this->unsubscriptionShortcode, $unsubscriptionUrl, $email['textbody']);
        }
        else {
            $email['textbody'] .= __(sprintf("\r\nClick here to unsubscribe:\r\n%s", $unsubscriptionUrl), JAVELIN_DEFAULT_TEXT_DOMAIN);
        }

        if (1 == $email['htmlenabled'])
        {
            if (strstr($email['htmlbody'], $this->unsubscriptionShortcode)) {
                $email['htmlbody'] = str_replace($this->unsubscriptionShortcode, $unsubscriptionUrl, $email['htmlbody']);
            } else {
                $email['htmlbody'] .= $this->getHtmlUnsubscriptionMessage($subscriber);
            }
        }

        return $email;
    }

    private function maybeAttachAddress(&$email)
    {

        $htmlAddress = $this->getHtmlAddress();
        if (strstr($email['htmlbody'], $this->canSpamShortCode)) {
            $email['htmlbody'] = str_replace($this->canSpamShortCode, $htmlAddress, $email['htmlbody'] );
        }
        else {
            $email['htmlbody'] .= $htmlAddress;
        }

        $textAddress = JavelinConfig::senderAddress();
        if (strstr($email['textbody'], $this->canSpamShortCode)) {
            $email['textbody'] = str_replace($this->canSpamShortCode, $textAddress, $email['textbody'] );
        }
        else {
            $email['textbody'] .= "\r\n".$textAddress;
        }
    }

    /**
     * @param Subscriber $subscriber
     * @param $email
     */
    private function replaceCustomFields(Subscriber $subscriber, &$email)
    {
        $email['subject'] = Subscriber::replaceCustomFieldValues($email['subject'], $subscriber);
        $email['htmlbody'] = Subscriber::replaceCustomFieldValues($email['htmlbody'], $subscriber);
        $email['textbody'] = Subscriber::replaceCustomFieldValues($email['textbody'], $subscriber);
    }

    private function getHash(Subscriber $subscriber, $email) {
        return md5($subscriber->getEmail().$email['subject'].$email['htmlbody'].$email['textbody']);
    }
}

$javelinQueue = EmailQueue::getInstance();