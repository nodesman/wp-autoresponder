<?php
require_once __DIR__."/../../models/autoresponder.php";

class AutoresponderTest extends WP_UnitTestCase {
    public $plugin_slug = 'my-plugin';

    private $autoresponder;
    
    private $newsletterId=1000; 
    
    public function setUp() {
        parent::setUp();
        global $wpdb;
        
        $deleteAllNewsletters = sprintf("TRUNCATE {$wpdb->prefix}wpr_newsletters;");
        $wpdb->query($deleteAllNewsletters);
        //create a newsletter
        $createNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (`id`, `name`, `reply_to`, `fromname`, `fromemail`) VALUES ({$this->newsletterId}, 'Test Newsletter', 'test@automated.com', 'Test', 'test@automatedfrom.com');");
        $wpdb->query($createNewsletterQuery);
        $truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponders;");
        $wpdb->query($truncateAutorespondersTable);        
        $this->autoresponder = new Autoresponder();
    }
    
    public function tearDown() {
    	parent::tearDown();
    	global $wpdb;
    	$truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponders;");
    	$wpdb->query($truncateAutorespondersTable);
    	$truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_newsletters;");
    	$wpdb->query($truncateAutorespondersTable);
    }
    
    public function createAutoresponders($count) {
    	$autoresponders = array();
    	global $wpdb;
    	for ($iter = 0; $iter < $count; $iter++)
    	{
    		$current =  array(
    				"nid" => $this->newsletterId,
    				"name" => "Autoresponder_{$iter}"  
    				);
    		$autoresponders[] = $current;
    		$insertAutoresponderQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponders (`nid`, `name`) VALUES (%d, '%s');",$current['nid'], $current['name']);
    		$wpdb->query($insertAutoresponderQuery);
    	}
    	return $autoresponders;
    }
    
    public function testGetAllAutoresponders() {
    	
    	$autoresponders = $this->autoresponder->getAllAutoresponders();
    	$numberOfAutoresponders = count($autoresponders);
    	$this->assertEquals($numberOfAutoresponders,0);
    	
    	
		$this->createAutoresponders(10);
		$autoresponders = $this->autoresponder->getAllAutoresponders();

		$numberOfAutoresponders = count($autoresponders);
		$this->assertEquals($numberOfAutoresponders, 10);
		
		
    }
    
}

