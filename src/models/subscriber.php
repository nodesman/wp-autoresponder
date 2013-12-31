<?php


class SubscriberNotFoundException extends Exception
{
}

class InvalidSubscriberIDException extends Exception
{
}

class Subscriber
{

    private $confirmed;

    function getHash()
    {
        return $this->hash;
    }

    function Subscriber($id)
    {
        global $wpdb;
        $sid = intval($id);
        if ($sid == 0)
            throw new InvalidSubscriberIDException("Invalid Subscriber ID: $sid");
        $getSubscriberInfoQuery = sprintf("SELECT * FROM %swpr_subscribers WHERE id=%d", $wpdb->prefix, $sid);
        $queryResults = $wpdb->get_results($getSubscriberInfoQuery);

        if (0 == count($queryResults))
            throw new SubscriberNotFoundException();

        $subscriber = $queryResults[0];
        $this->id = $subscriber->id;
        $this->nid = $subscriber->nid;
        $this->name = $subscriber->name;
        $this->email = $subscriber->email;
        $this->date_of_subscription = $subscriber->date;
        $this->active = $subscriber->active;
        $this->confirmed = $subscriber->confirmed;
        $this->fid = $subscriber->fid;
        $this->hash = $subscriber->hash;
    }

    public function getId()
    {
        return $this->id;
    }


    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getDateOfSubscription()
    {
        return $this->date_of_subscription;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getConfiremd()
    {
        return $this->confirmed;
    }

    public function isUnsubscribed()
    {
        return ($this->active == 0 && $this->confirmed == 1);
    }

    public function isUnconfirmed()
    {
        return ($this->active == 1 && $this->confirmed == 0);
    }

    public function isMoved()
    {
        return ($this->active == 3 && $this->confimed == 1);
    }

    public function getNewsletterId()
    {
        return ($this->nid);
    }

    public static function replaceCustomFieldValues($string, Subscriber $subscriber)
    {
        $name = $subscriber->getName();

        $string = str_replace("[!name!]", $name, $string);
        $values = $subscriber->getCustomFieldValuesByLabels();

        foreach ($values as $name=>$value) {
            $string = str_replace("[!$name!]", $value, $string);
        }

        return $string;
    }

    public function getCustomFieldValuesByLabels()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $getCustomFieldsQuery = sprintf("SELECT fields.name name, fields.label label, fields.id cid, `values`.value value FROM `%swpr_custom_fields` `fields`, `%swpr_custom_fields_values` `values` WHERE `fields`.`nid`=%d AND `fields`.`id`=`values`.`cid` AND `values`.`sid`=%d", $prefix, $prefix, $this->getNewsletterId(), $this->getId());
        $customFields = $wpdb->get_results($getCustomFieldsQuery);

        $result = array();
        if (count($customFields) > 0) {
            foreach ($customFields as $field) {
                $result[$field->name] = $field->value;
            }
        }
        return $result;

    }

    function getNewsletter()
    {
        $newsletter = Newsletter::getNewsletter($this->getNewsletterId());
        return $newsletter;
    }


    public static function getSubscribersOfNewsletter($nid) {

        if (false == Newsletter::whetherNewsletterIDExists($nid)) {
            throw new NewsletterNotFoundException();
        }

        throw new BadMethodCallException("Subscribers of newsletter get yet to be implemented   ");

    }

    public function getUnsubscriptionUrl() {
        $unsuburl = wpr_get_unsubscription_url($this->getId());
        return $unsuburl;
    }

}