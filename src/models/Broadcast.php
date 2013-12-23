<?php
class Broadcast {


    private $subject;
    private $htmlbody;
    private $newsletter_id;
    private $dispatch_time;
    private $whether_sent;

    public function __construct($broadcast_id) {

        global $wpdb;

        $getBroadcastQuery = sprintf("SELECT * FROM %swpr_newsletter_mailouts WHERE id=%d", $broadcast_id);
        $broadcast = $wpdb->get_row($getBroadcastQuery);

    }

}
