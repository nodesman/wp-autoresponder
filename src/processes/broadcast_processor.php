<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rajasekharan
 * Date: 26/04/13
 * Time: 6:12 PM
 * To change this template use File | Settings | File Templates.
 */

class BroadcastProcessor extends WPRBackgroundProcess{


    public static function run() {
        $timeNow = new DateTime();
        self::run_for_time($timeNow);
    }
    private static function getBroadcasts(DateTime $time) {

        global $wpdb;
        $query = sprintf("SELECT * FROM `{$wpdb->prefix}wpr_newsletter_mailouts` WHERE `status` = 0 AND `time` <= %d;", strtotime($time->format("Y-m-d H:i:s")));
        $mailouts = $wpdb->get_results($query);
        return $mailouts;
    }

    public static function run_for_time(DateTime $time) {

        $broadcasts= self::getBroadcasts($time);

        foreach ($broadcasts as $broadcast)
        {
            $nid = intval($broadcast->nid);
            $subject = $broadcast->subject;
            $html_body = $broadcast->htmlbody;
            $newsletter = Newsletter::getNewsletter($nid);

            $confirmedAndActiveNewsletterSubscribers = new ConfirmedNewsletterSubscribersList($nid);

            if (0 < count($confirmedAndActiveNewsletterSubscribers))
            {
                foreach ($confirmedAndActiveNewsletterSubscribers as $subscriber)
                {
                    $meta_key = self::getMetaKey($subscriber->getId(), $broadcast);
                    $emailParameters = array( "subject" => $subject,
                        "fromname"=> $newsletter->getFromName(),
                        "fromemail"=> $newsletter->getFromEmail(),
                        "textbody" => $broadcast->textbody,
                        "htmlbody" => $broadcast->htmlbody,
                        "htmlenabled"=> (empty($html_body))?0:1,
                        "attachimages"=> 1,
                        "meta_key"=> $meta_key
                    );

                    foreach ($emailParameters as $index=>$value) {
                        $emailParameters[$index] = Subscriber::replaceCustomFieldValues($value, $subscriber->getId());
                    }

                    sendmail($subscriber->getId(),$emailParameters);
                }
            }

            mailout_expire($broadcast->id);
        }
    }

    /**
     * @param $sid
     * @param $broadcast
     * @return string
     */
    private static function getMetaKey($sid, $broadcast)
    {
        return sprintf("BR-%s-%s-%s", $sid, $broadcast->id, $broadcast->nid);
    }


}