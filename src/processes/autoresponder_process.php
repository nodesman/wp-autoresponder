<?php


    class AutoresponderProcessor extends WPRBackgroundProcess
    {
        private static $processor;

        public function run() {

            $time = new DateTime();
            $this->run_for_time($time);
        }

        public function run_for_time(DateTime $time) {
            $this->process_messages($time);
        }

        public function day_zero_for_subscriber($sid) {
            global $wpdb;
            $getSubscriptionQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_followup_subscriptions WHERE sid=%d AND type='autoresponder' AND sequence=-1;", $sid);
            $subscriptions = $wpdb->get_results($getSubscriptionQuery);

            foreach ($subscriptions as $subscription) {

                $fetchDayZeroEmailOfAutoresponder = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE aid=%d AND sequence=0", $subscription->eid);
                $messageToBeSentOnDayZero = $wpdb->get_results($fetchDayZeroEmailOfAutoresponder);

                if (0 == count($messageToBeSentOnDayZero)) {
                    return;
                }

                $messageId = (int) $messageToBeSentOnDayZero[0]->id;
                $message = AutoresponderMessage::getMessage($messageId);
                $currentTime = new DateTime();
                self::getProcessor()->deliver($subscription, $message, $currentTime );

            }

        }

        private function getNumberOfAutoresponderMessages() {
            return AutoresponderMessage::getAllMessagesCount();
        }

        private function process_messages(DateTime $currentTime) {

            $number_of_messages = $this->getNumberOfAutoresponderMessages();
            $number_of_iterations = ceil($number_of_messages/ $this->autoresponder_messages_loop_iteration_size());

            $this->recordAutoresponderProcessHeartbeat();

            for ($iter=0;$iter< $number_of_iterations; $iter++) {

                $this->recordAutoresponderProcessHeartbeat();

                $start = ($iter*$this->autoresponder_messages_loop_iteration_size());
                $messages = AutoresponderMessage::getAllMessages($start, $this->autoresponder_messages_loop_iteration_size());

                $this->recordAutoresponderProcessHeartbeat();

                foreach ($messages as $message) {
                    $this->deliver_message($message, $currentTime);
                }
            }

            $this->recordAutoresponderProcessHeartbeat();
        }

        private function recordAutoresponderProcessHeartbeat()
        {
            update_option("_wpr_autoresponder_process_status", time());
        }

        private function autoresponder_messages_loop_iteration_size()
        {
            //this will be dynamic later on.
            return 10;
        }

        private function deliver_message(AutoresponderMessage $message, DateTime $time) {

            $numberOfSubscribers = $this->getNumberOfRecipientSubscribers($message, $time);
            $numberOfIterations = ceil($numberOfSubscribers/ $this->subscribers_processor_iteration_size());

            for ($iter=1; $iter <= $numberOfIterations; $iter++ ) {

                $subscribers = $this->getNextRecipientBatch($message, strtotime($time->format("Y-m-d H:i:s")), $this->subscribers_processor_iteration_size());

                $this->recordAutoresponderProcessHeartbeat();
                for ($subiter=0;$subiter< count($subscribers); $subiter++) {
                    $this->deliver($subscribers[$subiter], $message, $time);
                }

                $this->recordAutoresponderProcessHeartbeat();

            }
        }

        private function subscribers_processor_iteration_size()
        {
            return 1000;
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

            $updateSubscriptionMarkingItAsProcessedForCurrentDay = sprintf("UPDATE %swpr_followup_subscriptions SET sequence=%d, last_date=%d WHERE id=%d", $wpdb->prefix, $message->getDayNumber(), strtotime($time->format("Y-m-d H:i:s")), $subscriber->id);

            $wpdb->query($updateSubscriptionMarkingItAsProcessedForCurrentDay);

        }

        private function getNumberOfRecipientSubscribers(AutoresponderMessage $message, DateTime $time) {

            global $wpdb;

            $columnUsedForReference = $this->getColumnUsedForReference($message);
            $additionalCondition = $this->additionalConditionsForQuery($message);
            $dayOffsetOfMessage = $message->getDayNumber();
            $previous_message_offset = $message->getPreviousMessageDayNumber();

            $currentTime = strtotime($time->format("Y-m-d H:i:s"));
            $getSubscribersQuery = sprintf("SELECT COUNT(*) num  FROM %swpr_followup_subscriptions subscriptions, %swpr_subscribers subscribers
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

            $numbers = $wpdb->get_results($getSubscribersQuery);
            $number = $numbers[0];
            return $number->num;
        }

        private function getNextRecipientBatch(AutoresponderMessage $message, $currentTime, $size=-1) {

            global $wpdb;

            $columnUsedForReference = $this->getColumnUsedForReference($message);
            $additionalCondition = $this->additionalConditionsForQuery($message);

            $dayOffsetOfMessage = $message->getDayNumber();
            $previous_message_offset = $message->getPreviousMessageDayNumber();

            $limitClause = '';
            if ($size > 0) {
                $limitClause = "LIMIT {$size}";
            }

            $getSubscribersQuery = sprintf("SELECT subscriptions.*  FROM %swpr_followup_subscriptions subscriptions, %swpr_subscribers subscribers
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
                                                                 `subscriptions`.`sequence` <> %d
                                                                 ORDER BY sid ASC
                                                                 %s;",  $wpdb->prefix, $wpdb->prefix, $currentTime, $dayOffsetOfMessage, $currentTime, $dayOffsetOfMessage, $previous_message_offset, $message->getAutoresponder()->getId(), $dayOffsetOfMessage, $limitClause );


            $subscribers = $wpdb->get_results($getSubscribersQuery);
            $this->recordAutoresponderProcessHeartbeat();

            return $subscribers;

        }

        private function additionalConditionsForQuery($message)
        {
            $additionalCondition = '';
            if ($this->whetherFirstMessageOfAutoresponder($message)) {
                $additionalCondition = " last_date = 0 AND";
                return $additionalCondition;
            }
            return $additionalCondition;
        }

        private function getColumnUsedForReference($message)
        {
            $columnUsedForReference = 'last_date';
            if ($this->whetherFirstMessageOfAutoresponder($message)) {
                $columnUsedForReference = 'doc';
                return $columnUsedForReference;
            }
            return $columnUsedForReference;
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
