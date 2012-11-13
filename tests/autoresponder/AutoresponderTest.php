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

        $truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponder_messages;");
        $wpdb->query($truncateAutorespondersTable);
    }
    
    public function tearDown() {
    	parent::tearDown();
    	global $wpdb;
    	$truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponders;");
    	$wpdb->query($truncateAutorespondersTable);
    	$truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_newsletters;");
    	$wpdb->query($truncateAutorespondersTable);

        $truncateAutorespondersTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponder_messages;");
        $wpdb->query($truncateAutorespondersTable);
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
    		AutoresponderTestHelper::addAutoresponderAndFetchRow($currentAutoresponder["nid"], $currentAutoresponder["name"]);
    	}
    	
    	$autoresponders = Autoresponder::getAllAutoresponders();
    	
    	$this->assertEquals(count($autoresponderDefinitions), count($autoresponders));
    	
    	$responderNames = array();
    	foreach ($autoresponders as $res) {
    		$responderNames[] = $res->getName();
    	}
    	
    	$defNames = array();
    	foreach ($autoresponderDefinitions as $def) {
    		$defNames[] = $def['name'];
    	}
    	
    	$difference = array_diff($responderNames, $defNames);
    	$this->assertEquals(count($difference),0);
    	
    	
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
    	
    	$autoresponder = AutoresponderTestHelper::addAutoresponderAndFetchRow($autoresponder['nid'], $autoresponder['name']);
    	$autoresponderResultant = Autoresponder::getAutoresponderById(intval($autoresponder->id));
    	
    	$this->assertEquals($autoresponder->nid, $autoresponderResultant->getNewsletterId(), "Newsletter ID is the same as input");
    	$this->assertEquals($autoresponder->name, $autoresponderResultant->getName(),"Name is same as input");
    	
    }
    
    public function testValidateAutoresponders() {
    	
    	//no empty names;
    	$autoresponder = array("name"=> "");
    	$this->assertFalse(Autoresponder::whetherValidAutoresponderName($autoresponder),"Test to see if a empty autoresponder name is validated as invalid");

    	$autoresponder = array("name"=> "      ");
    	$this->assertFalse(Autoresponder::whetherValidAutoresponderName($autoresponder), "Test to see if just white space is validated as invalid");
    	
    	
    	$autoresponder = array("name"=> '"\'');
    	$this->assertFalse(Autoresponder::whetherValidAutoresponderName($autoresponder),"Test to see if autoresponder name containing a slash is marked invalid");
    	
    	
    	//TODO: The autoresponder field should have a nid field if not, the below must become an exception
    	$autoresponder = array("name"=>'Sample Autoresponder 1234 ');
    	$this->assertTrue(Autoresponder::whetherValidAutoresponderName($autoresponder),"Test to see if a valid autoresponder is marked as valid");
    }
    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherMissingFieldsResultInException() {
    	$autoresponder = array();
    	Autoresponder::whetherValidAutoresponderName($autoresponder);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherInvalidDataTypeResultsInException() {
    	Autoresponder::whetherValidAutoresponderName(null);
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhetherLackOfArgumentForWhetherValidAutoresponderResultsInException() {
    	Autoresponder::whetherValidAutoresponderName("");
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

    /**
     * @expectedException NonExistentNewsletterException
     */
    public function testGettingAutorespondersOfNonExistentNewsletter() {
    	Autoresponder::getAutorespondersOfNewsletter(9801);
    }
    
    private function addNewsletter($params) {
    	global $wpdb;
    	$addNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (name, fromname, fromemail, reply_to) VALUES ('%s', '%s', '%s', '%s'); ",$params['name'], $params['fromname'], $params['fromemail'], $params['reply_to']);
    	$wpdb->query($addNewsletterQuery);
    	return $wpdb->insert_id;
    }
    
    
    public function testGettingAutorespondersOfNewsletter() {
    	
    	$autoresponderDefinitions = array(
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$this->newsletterId,"name"=>"Autoresponder_".md5(microtime()))
    	);
    	
    	$newNewsletter = $this->addNewsletter(array("name"=> "Autoresponder Second",
    			"fromname"=> "Someone",
    			"fromemail"=>"Someemail@somedomain.com",
    			"reply_to"=>"somereply@replysome.com"));
    	
    	$autoresponderDefinitionsOfSecondNewsleter = array(
    			array("nid"=>$newNewsletter,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$newNewsletter,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$newNewsletter,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$newNewsletter,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$newNewsletter,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$newNewsletter,"name"=>"Autoresponder_".md5(microtime())),
    			array("nid"=>$newNewsletter,"name"=>"Autoresponder_".md5(microtime()))
    	);
    	 
    	foreach ($autoresponderDefinitions as $currentAutoresponder) {
    		AutoresponderTestHelper::addAutoresponderAndFetchRow($currentAutoresponder["nid"], $currentAutoresponder["name"]);
    	}
    	
    	foreach ($autoresponderDefinitionsOfSecondNewsleter as $currentAutoresponder) {
    		AutoresponderTestHelper::addAutoresponderAndFetchRow($currentAutoresponder["nid"], $currentAutoresponder["name"]);
    	}
    	 
    	$autoresponders = Autoresponder::getAutorespondersOfNewsletter($this->newsletterId);
    	 
    	$this->assertEquals(count($autoresponderDefinitions), count($autoresponders));
    	 
    	$responderNames = array();
    	foreach ($autoresponders as $res) {
    		$responderNames[] = $res->getName();
    	}
    	 
    	$defNames = array();
    	foreach ($autoresponderDefinitions as $def) {
    		$defNames[] = $def['name'];
    	}
    	 
    	$difference = array_diff($responderNames, $defNames);
    	$this->assertEquals(count($difference),0);
    }

    private function addAutoresponderMessage(array $options) {
        global $wpdb;

        if (!isset($options['aid']) || !isset($options['subject']) || !isset($options['htmlbody']) || !isset($options['textbody']) || !isset($options['sequence']) || !isset($options['attachimages'])) {
            throw new InvalidArgumentException();
        }



        $addAutoresponderMessageQuery = sprintf("INSERT INTO `%swpr_autoresponder_messages` (`aid`, `subject`, `htmlbody`, `textbody`, `sequence`, `htmlenabled`, `attachimages`) VALUES (%d, '%s','%s','%s', %d, %d, %d);",
                                                                                    $wpdb->prefix, $options['aid'], $options['subject'], $options['htmlbody'], $options['textbody'], $options['sequence'], $options['htmlenabled'], $options['attachimages']);

        $wpdb->query($addAutoresponderMessageQuery);
        $insert_id = $wpdb->insert_id;
        $autoresponderMessage = $wpdb->get_results(sprintf("SELECT * FROM %swpr_autoresponder_messages WHERE id=%d",$wpdb->prefix, $insert_id));
        if (count($autoresponderMessage) > 0) {
            return $autoresponderMessage[0];
        }
        else
        {
            throw new Exception("Unable to fetch added autoresponder message");
        }
    }

    public function testGettingLimitedNumberOfAutoresponderMessages() {

        $NUMBER_OF_AUTORESPONDERS_QUERIED = 5;

        $autoresponderRowsAdded = AutoresponderTestHelper::addAutoresponderObjects($this->newsletterId, 10);
        $autorespondersList = Autoresponder::getAllAutoresponders(0, $NUMBER_OF_AUTORESPONDERS_QUERIED);
        $difference = AutoresponderTestHelper::getDifferenceInAutoresponders($autorespondersList, $NUMBER_OF_AUTORESPONDERS_QUERIED, $autoresponderRowsAdded);
        $this->assertEquals(0, count($difference));

    }



    public function testGettingMessagesOfAutoresponders() {

        $autoresponder = AutoresponderTestHelper::addAutoresponderAndFetchRow($this->newsletterId, "Sample Newsletter");

        $autoresponderMessages = array(

            array(
                "aid"          => $autoresponder->id,
                "subject"      => md5(microtime().rand(1,2000000)),
                "htmlbody"     => md5(microtime().rand(1,2000000)),
                "textbody"     => "",
                "htmlenabled" => 1,
                "sequence"     => 0,
                "attachimages" => 1
            ),
            array(
                "aid"          => $autoresponder->id,
                "subject"      => md5(microtime().rand(1,2000000)),
                "htmlbody"     => md5(microtime().rand(1,2000000)),
                "textbody"     => "",
                "htmlenabled" => 1,
                "sequence"     => 1,
                "attachimages" => 1
            ),
            array(
                "aid"          => $autoresponder->id,
                "subject"      => md5(microtime().rand(1,2000000)),
                "htmlbody"     => md5(microtime().rand(1,2000000)),
                "textbody"     => "",
                "htmlenabled" => 1,
                "sequence"     => 2,
                "attachimages" => 2
            ),
            array(
                "aid"          => $autoresponder->id,
                "subject"      => md5(microtime().rand(1,2000000)),
                "htmlbody"     => md5(microtime().rand(1,2000000)),
                "textbody"     => "",
                "htmlenabled" => 1,
                "sequence"     => 3,
                "attachimages" => 3
            )
        );
        $originalSubjects = array();

        foreach ($autoresponderMessages as $responder) {
            $autoresponderMessages = $this->addAutoresponderMessage($responder);
            $originalSubjects[] = $responder['subject'];
        }


        $autoresponderObj = new Autoresponder(intval($autoresponder->id));

        $autoresponderMessagesRes = $autoresponderObj->getMessages();

        $this->assertEquals(count($autoresponderMessagesRes), count($originalSubjects));

        foreach ($autoresponderMessagesRes as $message) {
            $receivedMessageSubjects[] = $message->subject;
        }

        $difference = array_diff($receivedMessageSubjects, $originalSubjects);
        $this->assertEquals(count($difference), 0);
    }


    public function testGettingIdOfAutoresponderObject() {
        $responder = array(
            "subject"      => md5(microtime().rand(1,2000000)),
            "htmlbody"     => md5(microtime().rand(1,2000000)),
            "textbody"     => "",
            "htmlenabled" => 1,
            "sequence"     => 2,
            "attachimages" => 2
        );

        $autoresponderRow = AutoresponderTestHelper::addAutoresponderAndFetchRow($this->newsletterId, "Sample Autoresponder");
        $testObj = new Autoresponder((int) $autoresponderRow->id);

        $id = $testObj->getId();
        $this->assertEquals($id, $autoresponderRow->id);

    }



    public function testGetNumberOfAutoresponders() {


        $NUMBER_OF_AUTORESPONDERS_ADDED = 7;

        AutoresponderTestHelper::addAutoresponderObjects($this->newsletterId, $NUMBER_OF_AUTORESPONDERS_ADDED);

        $numberOfAutorespondersReturned = Autoresponder::getNumberOfAutorespondersAvailable();

        $this->assertEquals($NUMBER_OF_AUTORESPONDERS_ADDED, $numberOfAutorespondersReturned);



    }

    public function testGetNumberOfAutorespondersShouldNotReturnAutorespondersWhenNewsletterIsDeleted() {

        global $wpdb;
        $deleteNewsletterQuery = sprintf("DELETE FROM {$wpdb->prefix}wpr_newsletters WHERE id=%d",$this->newsletterId);
        $wpdb->query($deleteNewsletterQuery);
        AutoresponderTestHelper::addAutoresponderObjects($this->newsletterId, 20);

        $numberOfAutorespondersAvailable = Autoresponder::getNumberOfAutorespondersAvailable();

        $this->assertEquals(0, $numberOfAutorespondersAvailable);
    }


    
    
    //TODO: Delete autoresponder    
}

