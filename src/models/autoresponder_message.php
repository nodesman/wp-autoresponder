<?php


class AutoresponderMessage
{
    private $subject;
    private $id;
    private $htmlbody;
    private $textbody;
    private $autoresponder_id;

    private function __construct($autoresponder_result_row) {

        $this->subject = $autoresponder_result_row->subject;
        $this->id = (int) $autoresponder_result_row->id;
        $this->htmlbody = $autoresponder_result_row->htmlbody;
        $this->textbody = $autoresponder_result_row->textbody;
        $this->offset = $autoresponder_result_row->sequence;
        $this->autoresponder_id = (int) $autoresponder_result_row->aid;

    }

    public function getNextMessage() {

        global $wpdb;
        //get message

        $getAutoresponderMessageQuery = sprintf("SELECT id FROM %swpr_autoresponder_messages WHERE aid=%d AND sequence > %d LIMIT 1; ", $wpdb->prefix, $this->getAutoresponder()->getId(), $this->getDayNumber());
        $emailResult = $wpdb->get_results($getAutoresponderMessageQuery);

        if (0 == count ($emailResult))
            return false;

        $id = $emailResult[0]->id;

        return AutoresponderMessage::getMessage((int) $id);
    }

    public function getPreviousMessage() {

        global $wpdb;
        $getAutoresponderMessageQuery = sprintf("SELECT id FROM %swpr_autoresponder_messages WHERE aid=%d AND sequence < %d ORDER BY sequence DESC LIMIT 1; ", $wpdb->prefix, $this->getAutoresponder()->getId(), $this->getDayNumber());

        $emailResult = $wpdb->get_results($getAutoresponderMessageQuery);

        if (0 == count ($emailResult))
            return false;

        $id = $emailResult[0]->id;

        return AutoresponderMessage::getMessage((int) $id);
    }

    public function getPreviousMessageDayNumber() {

            if (0 == $this->getDayNumber() || false == $this->getPreviousMessage()) {
                return -1;
            }
            else {
                return $this->getPreviousMessage()->getDayNumber();
            }
    }



    public function getId() {
        return $this->id;
    }
    public function getSubject() {
        return $this->subject;
    }
    public function getHTMLBody() {
        return $this->htmlbody;
    }
    public function getTextBody() {
        return $this->textbody;
    }
    public function getAutoresponder() {
        return Autoresponder::getAutoresponder($this->autoresponder_id);
    }

    public function getDayNumber() {
        return $this->offset;
    }

    public static function getMessage($message_id) {

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
            throw new NonExistentAutoresponderException();
        }

        $message = new AutoresponderMessage($results[0]);
        return $message;
    }


    public static function getAllMessages($start =0, $length=-1) {
        global $wpdb;

        if  (0 < $length) {
            $limitClause = sprintf("LIMIT %d, %d", $start, $length);
        }
        else
            $limitClause = '';

        $getAllValidAutoresponderMessagesQuery = sprintf("SELECT AM.* FROM %swpr_autoresponder_messages AM, %swpr_newsletters N, %swpr_autoresponders AU
                        WHERE AM.aid=AU.id AND AU.nid=N.id %s;", $wpdb->prefix,$wpdb->prefix,$wpdb->prefix, $limitClause);
        $messagesResults = $wpdb->get_results($getAllValidAutoresponderMessagesQuery);


        $message_array = array();
        foreach ($messagesResults as $message_item) {
            $message_array[] = self::getMessage((int)$message_item->id);
        }

        return $message_array;

    }

    public static function getAllMessagesCount() {
        global $wpdb;
        $getAllValidAutoresponderMessagesQuery = sprintf("SELECT COUNT(*) num FROM %swpr_autoresponder_messages AM, %swpr_newsletters N, %swpr_autoresponders AU
                        WHERE AM.aid=AU.id AND AU.nid=N.id;", $wpdb->prefix,$wpdb->prefix,$wpdb->prefix);
        $messagesResults = $wpdb->get_results($getAllValidAutoresponderMessagesQuery);


        return $messagesResults[0]->num;


    }
}


class NonExistentMessageException extends Exception {

}