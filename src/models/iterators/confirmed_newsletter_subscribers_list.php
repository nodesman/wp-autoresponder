<?php

class ConfirmedNewsletterSubscribersList implements Iterator, Countable {

    private $nid;
    private $index;
    private $length;

    public function __construct($nid) {
        $this->nid = intval($nid);
        $this->initialize();
    }

    public function current()
    {
        global $wpdb;
        $getSubscriberQuery = sprintf("SELECT id FROM `%swpr_subscribers` WHERE `nid`=%d AND `confirmed`=1 AND `active`=1 ORDER BY id LIMIT %d, 1;", $wpdb->prefix, $this->nid, $this->index);
        $subscriber_id = $wpdb->get_var($getSubscriberQuery);
        return new Subscriber($subscriber_id);
    }

    public function next()
    {
        $this->index += 1;
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return ($this->index < $this->length && $this->index >= 0);
    }

    public function rewind()
    {
        $this->initialize();
        $this->next();
    }

    public function count()
    {
        return $this->length;
    }

    private function getRecordsetSize()
    {
        global $wpdb;
        $getNumberOfSubscribersQuery = sprintf("SELECT COUNT(*) number_of_subscribers FROM `%swpr_subscribers` WHERE `nid`=%d AND `active`=1 AND `confirmed`=1;", $wpdb->prefix, $this->nid);
        $this->length = $wpdb->get_var($getNumberOfSubscribersQuery);
    }

    private function initialize()
    {
        $this->getRecordsetSize();
        $this->index = -1;
    }
}