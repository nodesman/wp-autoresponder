<?php

    class AutoresponderProcessor
    {
        private static $processor;

        public function run() {
            $time = new DateTime();
            $this->run_for_time($time);
        }

        public function run_for_time(DateTime $time) {
            $this->process_messages($time);
        }

        private function getNumberOfAutoresponderMessages() {
            global $wpdb;
            return AutoresponderMessage::getAllMessagesCount();
        }

        private function process_messages(DateTime $currentTime) {

            $number_of_messages = $this->getNumberOfAutoresponderMessages();
            $number_of_iterations = ceil($number_of_messages/ $this->iteration_batch_size());

            for ($iter=0;$iter< $number_of_iterations; $iter++) {

                $start = ($iter*$this->iteration_batch_size());
                $messages = AutoresponderMessage::getAllMessages($start, $this->iteration_batch_size());



                foreach ($messages as $message) {
                    $this->deliver_message($message, $currentTime);
                }
            }
        }

        private function iteration_batch_size()
        {
            //this will be dynamic later on.
            return 10;
        }

        private function deliver_message(AutoresponderMessage $message, DateTime $time) {

            $subscribers = $this->getRecipientSubscribers($message, $time->getTimestamp());

            for ($iter=0;$iter< count($subscribers); $iter++) {
                $this->deliver($subscribers[$iter], $message, $time);
            }
        }


        private function deliver($subscriber, AutoresponderMessage $message, DateTime $time) {

            global $wpdb;
            $htmlBody = $message->getHTMLBody();

            $htmlenabled = (!empty($htmlBody))?1:0;



            $params= array(
                'meta_key'=> sprintf('AR-%d-%d-%d-%d', $message->getAutoresponder()->getId(), $subscriber->sid, $message->getId(), $message->getDayNumber()),
                'htmlbody' => $message->getHTMLBody(),
                'textbody' => $message->getTextBody(),
                'subject' => $message->getSubject(),
                'htmlenabled'=> $htmlenabled
            );


            sendmail($subscriber->sid, $params);



            $updateSubscriptionMarkingItAsProcessedForCurrentDay = sprintf("UPDATE %swpr_followup_subscriptions SET sequence=%d, last_date=%d WHERE id=%d AND eid=%d", $wpdb->prefix, $message->getDayNumber(), $time->getTimestamp(), $subscriber->id, $message->getAutoresponder()->getId());
            $wpdb->query($updateSubscriptionMarkingItAsProcessedForCurrentDay);

        }

        private function getRecipientSubscribers(AutoresponderMessage $message, $currentTime) {

            global $wpdb;

            $columnUsedForReference = 'last_date';
            $additionalCondition = '';
            if ($this->whetherFirstMessageOfAutoresponder($message)) {
                $columnUsedForReference = 'doc';
                $additionalCondition = " last_date = 0 AND";
            }





            if (isset($GLOBALS['test']) && $GLOBALS['test'] == 1) {
                echo "\r\n\r\nCurrent iteration for day: ".$message->getDayNumber()."\r\n\r\n";
                echo "Column used for reference is : ".$columnUsedForReference;

                print_r($wpdb->get_results("SELECT * FROM wp_wpr_followup_subscriptions"));


            }


            $dayOffsetOfMessage = $message->getDayNumber();
            $previous_message_offset = $message->getPreviousMessageDayNumber();

            $getSubscribersQuery = sprintf("SELECT *  FROM %swpr_followup_subscriptions subscriptions, %swpr_subscribers subscribers
                                                                 WHERE
                                                                 `subscriptions`.`sid`=`subscribers`.`id` AND
                                                                 (
                                                                    FLOOR((%d-`subscriptions`.`{$columnUsedForReference}`)/86400)=%d OR
                                                                    (
                                                                      FLOOR((%d-`subscriptions`.`{$columnUsedForReference}`)/86400) > %d AND
                                                                      `sequence` = %d
                                                                    )


                                                                 ) AND
                                                                 {$additionalCondition}
                                                                 `subscriptions`.`eid`=%d AND
                                                                 `type`='autoresponder' AND
                                                                 `subscribers`.active=1 AND `subscribers`.confirmed=1 AND
                                                                 `subscriptions`.`sequence` <> %d;",  $wpdb->prefix, $wpdb->prefix, $currentTime, $dayOffsetOfMessage, $currentTime, $dayOffsetOfMessage, $previous_message_offset, $message->getAutoresponder()->getId(), $dayOffsetOfMessage );



            $subscribers = $wpdb->get_results($getSubscribersQuery);

            if (isset($GLOBALS['test']) && $GLOBALS['test'] == 1) {
                echo $getSubscribersQuery;
                echo "\r\n\r\nNumber of subscribers returned is: ".count($subscribers)."\r\n\r\n";
            }



            return $subscribers;

        }

        private function whetherFirstMessageOfAutoresponder($message)
        {
            return $message->getPreviousMessageDayNumber() == -1;
        }

        private function __construct() {

        }

        public static function getProcessor() {
            if (empty(AutoresponderProcessor::$processor))
                AutoresponderProcessor::$processor = new AutoresponderProcessor();
            return AutoresponderProcessor::$processor;
        }



    }

$wpr_autoresponder_processor = AutoresponderProcessor::getProcessor();
