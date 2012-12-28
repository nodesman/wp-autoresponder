<?php

class AutoresponderProcess
{
    public function process_messages() {

        $number_of_messages = self::getNumberOfAutoresponderMessages();
        $batch_size = WPR_Config::$AutoresponderMessagesBatchSize;
        $number_of_iterations = ceil($number_of_messages/$batch_size);

        for ($iter=0;$iter< $number_of_iterations; $iter++) {

            $start = ($iter*$batch_size);
            $messages = AutoresponderMessage::getAllMessages($start, $batch_size);

            foreach ($messages as $message) {
                $this->deliver_message($message);
            }

        }

    }

}