<?php

class ConfirmedNewsletterSubscribersList implements Iterator, Countable {

    private $nid;
    private $index;
    private $length;

    public function __construct($nid) {
        $this->nid = intval($nid);
        $this->initialize();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        global $wpdb;
        $getSubscriberQuery = sprintf("SELECT id FROM `%swpr_subscribers` WHERE `nid`=%d AND `confirmed`=1 AND `active`=1 ORDER BY id LIMIT %d, 1;", $wpdb->prefix, $this->nid, $this->index);
        $subscriber_id = $wpdb->get_var($getSubscriberQuery);
        return new Subscriber($subscriber_id);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->index += 1;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */

    public function valid()
    {
        return ($this->index < $this->length && $this->index >= 0);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->initialize();
        $this->next();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
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