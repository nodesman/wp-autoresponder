<?php

class AutoresponderTest extends WP_UnitTestCase {
    public $plugin_slug = 'my-plugin';

    public function setUp() {
        parent::setUp();
    }
    
    public function testGetAllAutoresponders() {
       $this->go_to("http://localhost/freeness/?p=1");
       global $wpdb;
       

    }
}

