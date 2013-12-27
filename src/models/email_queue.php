<?php

class EmailQueue
{
    public static function enqueue(Subscriber $subscriber, $email)
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
}