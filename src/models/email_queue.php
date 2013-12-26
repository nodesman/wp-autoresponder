<?php
/**
 * Created by PhpStorm.
 * User: rajs
 * Date: 23/12/13
 * Time: 9:46 PM
 */

class EmailQueue {

    public static function enqueue(Subscriber $subscriber, $email) {

        foreach ($email as $index=>$value) {
            $email[$index] = Subscriber::replaceCustomFieldValues($value, $subscriber->getId());
        }

        $newsletter = $subscriber->getNewsletter();

        $email['fromname'] = $newsletter->getFromName();
        $email['fromemail'] = $newsletter->getFromEmail();

        sendmail($subscriber->getId(),$email);
    }

} 