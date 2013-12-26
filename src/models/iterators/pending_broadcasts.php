<?php
include_once __DIR__."/../broadcast.php";

class PendingBroadcasts implements Iterator, Countable {

    private $index;
    private $dispatchDeadline;
    private $length;
    private $broadcasts;

    public function __construct(DateTime $time) {
        $this->dispatchDeadline = $time;
        $this->initialize();
    }

    public function current()
    {
        return $this->broadcasts[$this->index];
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
        return ( ( $this->index < $this->length ) && ( $this->index >= 0 ) );
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

    private function getSize()
    {
        global $wpdb;
        $getLengthQuery = sprintf("SELECT COUNT(*) number_of_broadcasts FROM %swpr_newsletter_mailouts WHERE status=0 AND `time` <= %d;", $wpdb->prefix, $this->dispatchDeadline->getTimestamp());
        $this->length = $wpdb->get_var($getLengthQuery);
    }

    private function initialize()
    {
        global $wpdb;
        $this->index = -1;
        $this->getSize();
        $getCurrentObjectQuery = sprintf("SELECT * FROM %swpr_newsletter_mailouts WHERE `time`<=%d AND `status`=0 ORDER BY `time` ASC;", $wpdb->prefix, $this->dispatchDeadline->getTimestamp(), $this->index);
        $results = $wpdb->get_results($getCurrentObjectQuery);

        $this->broadcasts = array();
        foreach ($results as $index => $broadcast) {
            $this->broadcasts[$index] = new Broadcast($broadcast->id);
        }
    }
}