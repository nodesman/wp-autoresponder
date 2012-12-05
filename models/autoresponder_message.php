<?php


class AutoresponderMessage
{
    private $subject;
    private $id;

    private function __construct($autoresponder_result_row) {

        global $wpdb;
        $this->subject = $autoresponder_result_row->subject;
        $this->id = $autoresponder_result_row->id;

    }

    public function getSubject() {
        return $this->subject;
    }

    public function getId() {
        return $this->id;
    }

    public function getMessage($message_id) {

        global $wpdb;

        if ("integer" != gettype($message_id)) {
            throw new InvalidArgumentException();
        }

        $getAutoresponderMessageRecordQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE id=%d", $message_id);
        $results = $wpdb->get_results($getAutoresponderMessageRecordQuery);

        if (0 == count($results))
            throw new NonExistentMessageException();

        $autoresponderId = $results[0]->aid;

        if (!Autoresponder::whetherAutoresponderExists(intval($autoresponderId))) {
            throw new NonExistentMessageException();
        }

        $message = new AutoresponderMessage($results[0]);
        return $message;
    }
}


class NonExistentMessageException extends Exception {

}