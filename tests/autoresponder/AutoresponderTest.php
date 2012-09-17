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
    }
    
    public function tearDown() {
    	parent::tearDown();
    	global $wpdb;
    	$truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponders;");
    	$wpdb->query($truncateAutorespondersTable);
    	$truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_newsletters;");
    	$wpdb->query($truncateAutorespondersTable);
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
    	
    	
    	$autoresponderDefinitions = array(
    			     array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			     array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    				 array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    				 array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime()))
    			);
    	
    	foreach ($autoresponderDefinitions as $currentAutoresponder) {
    		$this->addAutoresponder($currentAutoresponder["nid"], $currentAutoresponder["name"]);
    	}
    	
    	$autoresponders = Autoresponder::getAllAutoresponders();
    	
    	$this->assertEquals(count($autoresponderDefinitions), count($autoresponders));
    	
    	
    	
    	
    }
    /**
     * @expectedException NonExistentAutoresponderException
     */
    public function testNonExistentAutoresponderInitializationException() {
    	new Autoresponder(1);
    }
    
    public function testGetAutoresponderById() {
	
    	$autoresponder = array('nid'=> $this->newsletterId,
    						   'name'=> "Autoresponder_1" 
    			);
    	
    	$autoresponder = $this->addAutoresponder($autoresponder['nid'], $autoresponder['name']);
    	$autoresponderResultant = Autoresponder::getAutoresponderById(intval($autoresponder->id));
    	
    	$this->assertEquals($autoresponder->nid, $autoresponderResultant->getNewsletterId(), "Newsletter ID is the same as input");
    	$this->assertEquals($autoresponder->name, $autoresponderResultant->getName(),"Name is same as input");
    	
    }
    
    public function testValidateAutoresponders() {
    	
    	$autoresponder = array();
    	$this->assertFalse(Autoresponder::whetherValidAutoresponder($autoresponder), "Test to see if the argument array has the keys that this method expects to validate autoresponder results in failure when it isn't");
    	
    	//no empty names;
    	$autoresponder = array("name"=> "");
    	$this->assertFalse(Autoresponder::whetherValidAutoresponder($autoresponder),"Test to see if a empty autoresponder name is validated as invalid");

    	$autoresponder = array("name"=> "      ");
    	$this->assertFalse(Autoresponder::whetherValidAutoresponder($autoresponder), "Test to see if just white space is validated as invalid");
    	
    	
    	$autoresponder = array("name"=> '"\'');
    	$this->assertFalse(Autoresponder::whetherValidAutoresponder($autoresponder),"Test to see if autoresponder name containing a slash is marked invalid");
    	
    	
    	//TODO: The autoresponder field should have a nid field if not, the below must become an exception
    	$autoresponder = array("name"=>'Sample Autoresponder 1234 ');
    	$this->assertTrue(Autoresponder::whetherValidAutoresponder($autoresponder),"Test to see if a valid autoresponder is marked as valid");
    }
    
    /**
     * @expectedException InvalidAutoresponderTypeArgumentException
     */
    public function testWhetherInvalidDataTypeResultsInException() {
    	Autoresponder::whetherValidAutoresponder(null);
    }
    /**
     * @expectedException InvalidAutoresponderTypeArgumentException
     */
    public function testWhetherLackOfArgumentForWhetherValidAutoresponderResultsInException() {
    	Autoresponder::whetherValidAutoresponder("");
    }
    
    //TODO: Add autoresponder
    
    /**
     * @expectedException NonExistentNewsletterAutoresponderAdditionException
     */
    public function testAddingNewsletterToNonExistentNewsletterCausesFailure() {
    	$autoresponder = array("nid"=> 9801, "name"=>"Bottle of Water");
    	Autoresponder::addAutoresponder($autoresponder["nid"],$autoresponder['name']);	
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddingAutoresponderWithInvalidArguments() {
    	Autoresponder::addAutoresponder($this->newsletterId, "");
    }
    
    
    public function testAddingAValidAutoresponderProducesAValidObject() {
    	
    	$autoresponderDef = array("nid"=> $this->newsletterId, "name"=>"Bottle of Water");
    	$autoresponder = Autoresponder::addAutoresponder($autoresponderDef["nid"],$autoresponderDef['name']);
    	
    	$this->assertEquals($autoresponder->getNewsletterId(), $autoresponderDef['nid']);
    	$this->assertEquals($autoresponder->getName(), $autoresponderDef['name']);
    }
    
    //     - Test the case where you use invalid names and other inputs for autoresponder
    
    //TODO: Delete autoresponder
    
    
    //TODO: Get autoresponder by id
    
}

