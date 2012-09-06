<?php
require_once __DIR__."/../../models/autoresponder.php";

class AutoresponderTest extends WP_UnitTestCase {
    public $plugin_slug = 'wp-autoresponder';

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
    
    
    
    public function addAutoresponder($newsletterId, $nameOfAutoresponder) {
    	global $wpdb;
    	$addAutoresponder = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponders (`nid`, `name`) VALUES(%d, '%s');", $newsletterId, $nameOfAutoresponder);
    	$wpdb->query($addAutoresponder);
    	    	
    	$getAutoresponderJustInserted = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponders WHERE name='%s' AND nid=%d", $nameOfAutoresponder, $newsletterId);
    	$autoresponder = $wpdb->get_row($getAutoresponderJustInserted);
    	return $autoresponder;
    }
    
    public function testGetAllAutoresponders() {
    	
    	$autoresponders = $this->autoresponder->getAllAutoresponders();
    	$numberOfAutoresponders = count($autoresponders);
    	$this->assertEquals($numberOfAutoresponders,0);
    	
    	
		$this->createAutoresponders(10);
		$autoresponders = Autoresponder::getAllAutoresponders();

		$numberOfAutoresponders = count($autoresponders);
		$this->assertEquals($numberOfAutoresponders, 10);
    }
    
    public function testGetAutoresponderById() {
	
    	$autoresponderElement = $this->autoresponder->getAutoresponderById(1);
    	$this->assertNull($autoresponderElement, "Attempting to retrieve a non existent autoresponder does not result in a null");
    	
    	$autoresponder = array('nid'=> $this->newsletterId,
    						   'name'=> "Autoresponder_1" 
    			);
    	$autoresponder = $this->addAutoresponder($autoresponder['nid'], $autoresponder['name']);
    	$autoresponderResultant = Autoresponder::getAutoresponderById($autoresponder->id);
    	
    	// TODO: $this->assertEquals($autoresponder->nid, $autoresponderResultant->nid);
    	// TODO: $this->assertEquals($autoresponder->name, $autoresponderResultant->name);
    }
    
    public function testValidateAutoresponders() {
    	
    	$autoresponder = array();
    	$this->assertFalse(Autoresponder::whetherValidAutoresponder($autoresponder), "Test to see if the argument array has the keys that this method expects to validate autoresponder results in failure when it isn't");
    	
    	//no empty names;
    	$autoresponder = array("name"=> "");
    	$this->assertFalse(Autoresponder::whetherValidAutoresponder($autoresponder));

    	$autoresponder = array("name"=> "      ");
    	$this->assertFalse(Autoresponder::whetherValidAutoresponder($autoresponder));
    	
    	
    	$autoresponder = array("name"=> '"\'');
    	$this->assertFalse(Autoresponder::whetherValidAutoresponder($autoresponder));
    	
    	$autoresponder = array("name"=>'Sample Autoresponder 1234 ');
    	$this->assertTrue(Autoresponder::whetherValidAutoresponder($autoresponder));
    }
    
    /**
     * @expectedException InvalidAutoresponderTypeArgumentException
     */
    public function testWhetherInvalidDataTypeResultsInException() {
    	Autoresponder::whetherValidAutoresponder("");
    	Autoresponder::whetherValidAutoresponder(1);
    	Autoresponder::whetherValidAutoresponder(null);
    }
    
    //TODO: Add autoresponder
    //     - Test the case where the user tries to add a autoresponder to a non existent newsletter
    //     - Test the case where you use invalid names and other inputs for autoresponder
    
    //TODO: Delete autoresponder
    
    
    //TODO: Get autoresponder by id
    
}

