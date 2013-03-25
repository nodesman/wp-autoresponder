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
                $this->deliver($subscribers[$iter], $message);
            }
        }

        private function deliver($subscriber, AutoresponderMessage $message) {

            global $wpdb;
            $htmlBody = $message->getHTMLBody();
            $htmlenabled = (!empty($htmlBody))?1:0;
            $params= array(
                'meta_key'=> sprintf('AR-%d-%d-%d-%d', $message->getAutoresponder()->getId(), $subscriber->id, $message->getId(), $message->getDayNumber()),
                'htmlbody' => $message->getHTMLBody(),
                'textbody' => $message->getTextBody(),
                'subject' => $message->getSubject(),
                'htmlenabled'=> $htmlenabled
            );

            sendmail($subscriber->id, $params);

            $updateSubscriptionMarkingItAsProcessedForCurrentDay = sprintf("UPDATE %swpr_followup_subscriptions SET sequence=%d WHERE sid=%d AND eid=%d", $wpdb->prefix, $message->getDayNumber(), $subscriber->id, $message->getAutoresponder()->getId());
            $wpdb->query($updateSubscriptionMarkingItAsProcessedForCurrentDay);

        }

        private function getRecipientSubscribers(AutoresponderMessage $message, $currentTime) {

            global $wpdb;
            $dayOffsetOfMessage = $message->getDayNumber();
            $getSubscribersQuery = sprintf("SELECT subscribers.* FROM %swpr_subscribers subscribers, %swpr_followup_subscriptions subscriptions
                                                                 where subscribers.id=subscriptions.sid AND
                                                                 FLOOR((%d-subscriptions.doc)/86400)=%d AND
                                                                 subscribers.active=1 AND
                                                                 subscribers.confirmed=1 AND
                                                                 sequence<> %d;", $wpdb->prefix, $wpdb->prefix, $currentTime, $dayOffsetOfMessage, $message->getDayNumber());


            throw new Exception("Note to self: The test has a couple of statements in line 43 to add a few unsubscribed subscribers. Adding these lines should not affect the tests but adding unusbscribed subscribers causes the tests to fail. Look into why this is happening");
            $subscribers = $wpdb->get_results($getSubscribersQuery);

            return $subscribers;

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
