<?php

class AutoresponderProcessor
{

    public function run() {
        $time = new DateTime();
        $this->run_for_time($time);
    }

    public function run_for_time(DateTime $time) {
        $this->process_messages($time);
    }

    public function process_messages(DateTime $currentTime) {

        $number_of_messages = self::getNumberOfAutoresponderMessages();
        $batch_size = WPR_Config::$AutoresponderMessagesBatchSize;
        $number_of_iterations = ceil($number_of_messages/$batch_size);

        for ($iter=0;$iter< $number_of_iterations; $iter++) {

            $start = ($iter*$batch_size);
            $messages = AutoresponderMessage::getAllMessages($start, $batch_size);

            foreach ($messages as $message) {
                $this->deliver_message($message, $currentTime);
            }
        }
    }

    public function deliver_message(AutoresponderMessage $message, DateTime $time) {

        $subscribers = $this->getRecipientSubscribers($message, $currentTime);
        for ($iter=0;$iter< count($subscribers); $iter++) {

        }
    }



}