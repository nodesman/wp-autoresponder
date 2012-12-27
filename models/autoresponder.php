<?php
class Autoresponder
{
    private $id;
    private $name;

    private function __construct($id)
    {
        global $wpdb;

        if ("integer" !== gettype($id))
            throw new InvalidArgumentException("Autoresponder has invalid argument type of '" . gettype($id) . "' where 'integer' expected");

        $getAutoresponderInformationQuery = sprintf("SELECT AR.* FROM {$wpdb->prefix}wpr_autoresponders AR, {$wpdb->prefix}wpr_newsletters NS WHERE NS.id=AR.nid AND AR.id=%d", $id);
        $results = $wpdb->get_results($getAutoresponderInformationQuery);
        if (0 == count($results))
            throw new NonExistentAutoresponderException();
        $autoresponder = $results[0];
        $this->id = $autoresponder->id;
        $this->nid = $autoresponder->nid;
        $this->name = $autoresponder->name;
    }


    public function addMessage($args) {

        global $wpdb;
        //ensure that all required arguments are present
        $this->validateAutoresponderMessage($args);

        $addAutoresponderMessageQuery = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpr_autoresponder_messages (`aid`, `subject`, `htmlbody`, `textbody`, `sequence` , `attachimages`) VALUES
                        (%d, %s, %s, %s, %d, 1);", $this->getId(), $args['subject'], $args['htmlbody'], $args['textbody'], $args['offset']);

        $wpdb->query($addAutoresponderMessageQuery);
        $autoresponder_message_id = $wpdb->insert_id;
        $getAutoresponderMessageQuery = sprintf("SELECT * FROM %swpr_autoresponder_messages WHERE id=%d", $wpdb->prefix, $autoresponder_message_id);
        $message = $wpdb->get_row($getAutoresponderMessageQuery);

        return AutoresponderMessage::getMessage($autoresponder_message_id);
    }


    public function updateMessage($message_id, $args) {
        global $wpdb;

        $this->validateAutoresponderMessage($args, $message_id);

        $updateAutoresponderQuery = $wpdb->prepare("UPDATE {$wpdb->prefix}wpr_autoresponder_messages SET
                                                                `subject`=%s,
                                                                `textbody`=%s,
                                                                `htmlbody`=%s,
                                                                `sequence`=%d
                                                                WHERE
                                                                id=%d
            ", $args['subject'], $args['textbody'], $args['htmlbody'], $args['offset'], $message_id);
        $wpdb->query($updateAutoresponderQuery);

        return AutoresponderMessage::getMessage($message_id);
    }



    private function validateAutoresponderMessage($args, $message_id = -1)
    {
        $this->validateAutoresponderMessageArgumentStructure($args);
        $this->ensureValidityOfAutoresponderMessageContent($args, $message_id);
    }

    private function ensureValidityOfAutoresponderMessageContent($args, $message_id= -1)
    {
        global $wpdb;
        if (empty($args['subject']))
            throw new InvalidAutoresponderMessageException(__('Message subject is empty.'), 4000);

        if (empty($args['textbody']) && empty($args['htmlbody']))
            throw new InvalidAutoresponderMessageException(__('Both HTML and text bodies of the message are empty.'), 4002);

        $offsetVar = trim($args['offset']);

        if (0 != preg_match("@[^0-9]@", $offsetVar) || strlen($offsetVar) == 0)
            throw new InvalidAutoresponderMessageException(__('Invalid non-numeric delivery day mentioned. '), 4004);

        $args['offset'] = intval($args['offset']);

        if (0 > $args['offset'])
            throw new InvalidAutoresponderMessageException(__('Negative days after subscription schedule for autoresponder message.'), 4004);
            
        //an update operation
        if ($message_id !== -1) {
            $equalityClause = sprintf("AND id != %d", $message_id);
        }
        else {
            $equalityClause = '';
        }
        $checkWhetherAMessageExistsForThatDayOffsetQuery = $wpdb->prepare("SELECT COUNT(*) num FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE aid=%d AND sequence=%d {$equalityClause}", $this->getId(), $args['offset'], $equalityClause);
        $checkResults = $wpdb->get_row($checkWhetherAMessageExistsForThatDayOffsetQuery);

        if (0 != intval($checkResults->num)) {
            throw new InvalidAutoresponderMessageException(__('A message for the provided day offset ' . $args['offset'] . ' already exists in the autoresponder. Only one message can be sent on a day.'), 4006);
        }
        return $args;
    }

    private function validateAutoresponderMessageArgumentStructure($args)
    {
        $argument_keys = array_keys($args);
        $diff = array_diff(array('subject', 'textbody', 'htmlbody', 'offset'), $argument_keys);
        if (count($diff) > 0)
            throw new InvalidArgumentException();
    }

    public static function delete(Autoresponder $autoresponder) {
        global $wpdb;
        $deleteAutoresponderQuery = sprintf("DELETE FROM {$wpdb->prefix}wpr_autoresponders WHERE id=%d",$autoresponder->getId());
        $wpdb->query($deleteAutoresponderQuery);

        $deleteAutoresponderMessagesQuery = sprintf("DELETE FROM %swpr_autoresponder_messages WHERE aid=%d", $wpdb->prefix, $autoresponder->getId());
        $wpdb->query($deleteAutoresponderMessagesQuery);

        $deleteSubscriptionsQuery = sprintf("DELETE FROM %swpr_followup_subscriptions WHERE eid=%d AND type='autoresponder';", $wpdb->prefix, $autoresponder->getId());
        $wpdb->query($deleteSubscriptionsQuery);

        $deleteAutoresponderEmailsQuery = sprintf("DELETE FROM %swpr_queue WHERE meta_key LIKE 'AR-%d-%%'", $wpdb->prefix, $autoresponder->getId());
        $wpdb->query($deleteAutoresponderEmailsQuery);
    }

    public static function whetherAutoresponderExists($id) {
        try {
            new Autoresponder($id);
        }
        catch (NonExistentAutoresponderException $exc) {
            return false;
        }
        return true;
    }

    public static function getAutoresponder($autoresponder_id) {
        return new Autoresponder($autoresponder_id);
    }

    public static function getNumberOfAutorespondersAvailable() {
        global $wpdb;

        $getNumberOfAutorespondersQuery = sprintf("SELECT COUNT(*) NUM_OF_RESPONDERS FROM {$wpdb->prefix}wpr_autoresponders a, {$wpdb->prefix}wpr_newsletters n WHERE a.nid=n.id;");
        $countResultSet = $wpdb->get_results($getNumberOfAutorespondersQuery);
        $count = (int) $countResultSet[0]->NUM_OF_RESPONDERS;
        return $count;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNewsletterId()
    {
        return $this->nid;
    }

    public function getNewsletter()
    {
        $newsletterId = $this->getNewsletterId();
        return Newsletter::getNewsletter($newsletterId);
    }

    public function getName()
    {
        return $this->name;
    }

    public static function getAutorespondersOfNewsletter($nid)
    {

        if (!Newsletter::whetherNewsletterIDExists($nid))
            throw new NonExistentNewsletterException();

        $autoresponders = Autoresponder::getAllAutoresponders();
        $resultList = array();

        foreach ($autoresponders as $responder) {
            if ($nid == $responder->getNewsletterId())
                $resultList[] = $responder;
        }

        return $resultList;
    }


    public static function getAllAutoresponders($start=0,$numberToReturn=-1)
    {
        global $wpdb;

        $limitClause = self::getLimitClauseForRecordSetFetch($start, $numberToReturn);

        $getAllAutorespondersQuery = sprintf("SELECT autores.id FROM {$wpdb->prefix}wpr_autoresponders autores, {$wpdb->prefix}wpr_newsletters newsletters WHERE autores.nid=newsletters.id ORDER BY id ASC %s;",$limitClause);
        $respondersRes = $wpdb->get_results($getAllAutorespondersQuery);

        $autoresponders = array();
        foreach ($respondersRes as $responder) {
            $autoresponders[] = new Autoresponder(intval($responder->id));
        }
        return $autoresponders;
    }

    private static function getLimitClauseForRecordSetFetch($start, $numberToReturn)
    {
        if ($start == 0 && $numberToReturn === -1)
            return '';

        return sprintf("LIMIT %d, %d", $start, $numberToReturn);

    }

    public static function getAutoresponderById($autoresponder_id)
    {
        $resultObj = new Autoresponder($autoresponder_id);
        return $resultObj;
    }

    public static function whetherValidAutoresponderName($autoresponderInfo)
    {

        if ("array" != gettype($autoresponderInfo)) {
            throw new InvalidArgumentException("Invalid type sent as argument for autoresponder validation");
        }

        if (!isset($autoresponderInfo['name'])) {
            throw new InvalidArgumentException("Expected autoresponder to have a name");
        }

        $name = trim($autoresponderInfo['name']);

        if (preg_match("@[\"']@", $name)) {
            return false;
        }

        if (0 == strlen($name)) {
            return false;
        }

        return true;
    }

    public static function addAutoresponder($nid, $name)
    {
        global $wpdb;

        if (!Newsletter::whetherNewsletterIDExists($nid))
            throw new NonExistentNewsletterAutoresponderAdditionException();

        if (!Autoresponder::whetherValidAutoresponderName(array("nid" => $nid, "name" => $name))) {
            throw new InvalidArgumentException("Invalid autoresponder arguments");
        }

        $createAutoresponderQuery = sprintf("INSERT INTO `{$wpdb->prefix}wpr_autoresponders` (`nid`, `name`) VALUES (%d, '%s');", $nid, $name);
        $wpdb->query($createAutoresponderQuery);
        $autoresponder_id = $wpdb->insert_id;
        return new Autoresponder($autoresponder_id);
    }

    public function getMessages($start = 0, $length = -1)
    {
        global $wpdb;

        $start = (0 ==  intval($start))?0: intval($start);
        $limitClause = Autoresponder::getLimitClauseForRecordSetFetch($start, $length);

        $getMessagesQuery = sprintf('SELECT * FROM %swpr_autoresponder_messages WHERE aid=%d %s', $wpdb->prefix, $this->id, $limitClause);
        $messages = $wpdb->get_results($getMessagesQuery);

        $messageObjects = array();
        foreach ($messages as $message) {
            $messageObjects[] = AutoresponderMessage::getMessage(intval($message->id));
        }

        return $messageObjects;
    }

    public function deleteMessage(AutoresponderMessage $message) {
        global $wpdb;

        $deleteAutoresponderMessageQuery = sprintf("DELETE FROM %swpr_autoresponder_messages WHERE id=%d AND aid=%d", $wpdb->prefix, $message->getId(), $this->getId());
        $wpdb->query($deleteAutoresponderMessageQuery);


        $deleteMessagesPendingDeliveryQuery = sprintf("DELETE FROM %swpr_queue WHERE `meta_key` LIKE 'AR-%%%%-%%%%-%d-%%' AND sent=0;", $wpdb->prefix, $message->getId());
        $wpdb->query($deleteMessagesPendingDeliveryQuery);


    }

    public function getNumberOfMessages() {
        global $wpdb;
        $getNumberOfMessagesQuery = sprintf("SELECT COUNT(*) num FROM %swpr_autoresponder_messages WHERE aid=%d",$wpdb->prefix, $this->getId());
        $messagesCountRes = $wpdb->get_results($getNumberOfMessagesQuery);
        return $messagesCountRes[0]->num;
    }
}

class NonExistentNewsletterAutoresponderAdditionException extends Exception
{
    /*
     * When the user tries to create a autoresponder in a non existent newsletter
     */
}

class NonExistentNewsletterException extends Exception
{

}


class NonExistentAutoresponderException extends Exception
{

}


class InvalidAutoresponderMessageException extends Exception {

    public function __construct($message, $code) {
        parent::__construct($message, $code);
    }

}