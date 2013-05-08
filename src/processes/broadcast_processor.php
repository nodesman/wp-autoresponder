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

        global $wpdb;
        $email_mailouts= self::getBroadcasts($time);


        foreach ($email_mailouts as $broadcast)
        {
            $nid = intval($broadcast->nid);
            $getSubscribersForBroadcastQuery = sprintf("SELECT subscribers.* FROM `{$wpdb->prefix}wpr_subscribers` `subscribers`, `{$wpdb->prefix}wpr_newsletters` `newsletters` WHERE `newsletters`.`id`=`subscribers`.`nid` AND  `subscribers`.`active`=1 AND `subscribers`.`confirmed`=1");
            $subscribersList = $wpdb->get_results($getSubscribersForBroadcastQuery);
            $subject = $broadcast->subject;
            $html_body = $broadcast->htmlbody;

            $newsletter = Newsletter::getNewsletter($nid);


            if (0 < count($subscribersList))
            {
                foreach ($subscribersList as $subscriber)
                {
                    $sid = $subscriber->id;
                    $meta_key = self::getMetaKey($sid, $broadcast);
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
                        $emailParameters[$index] = Subscriber::replaceCustomFieldValues($value, $sid);
                    }

                    sendmail($sid,$emailParameters);
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
    public static function getMetaKey($sid, $broadcast)
    {
        return sprintf("BR-%s-%s-%s", $sid, $broadcast->id, $broadcast->nid);
    }


}