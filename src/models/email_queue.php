<?php

class EmailQueue
{
    private static $instance;

    private function __construct()
    {

    }

    public static function getInstance()
    {

        if (empty(self::$instance))
            self::$instance = new EmailQueue();

        return self::$instance;
    }

    public function enqueue(Subscriber $subscriber, $email)
    {
        foreach ($email as $index => $value)
        {
            $email[$index] = Subscriber::replaceCustomFieldValues($value, $subscriber->getId());
        }

        $newsletter = $subscriber->getNewsletter();

        $email['fromname'] = $newsletter->getFromName();
        $email['fromemail'] = $newsletter->getFromEmail();

        sendmail($subscriber->getId(),$email);
    }

    public function getNumberOfPendingEmails()
    {
        global $wpdb;
        $getNumberOfEmailsQuery = sprintf("SELECT count(*) `number` FROM %swpr_queue where `sent` = 0;", $wpdb->prefix);
        $number = $wpdb->get_var($getNumberOfEmailsQuery);
        return $number;
    }
}