<?php

require_once "../../procs/autoresponder.php";


class AutorespodnerProcessTest extends WP_UnitTestCase {


    public function setUp() {
        parent::setUp();

        $truncateNewslettersTable = sprintf('TRUNCATE %swpr_newsletters', $wpdb->prefix);

        $createNewsletterQuery = sprintf("INSERT INTO %swpr_newsletters ()");


    }

    public function testWhetherSubscribersReceiveEmailImmediatelyAfterSubscription() {




    }

}