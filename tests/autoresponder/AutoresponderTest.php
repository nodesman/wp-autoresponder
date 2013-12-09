<?php
include_once __DIR__ . "/../../src/models/autoresponder.php";

class AutoresponderTest extends WP_UnitTestCase {

    public $plugin_slug = 'wp-autoresponder';

    private $autoresponder;
    
    private $newsletterId=1000;

    public $autoresponder_id;

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

        $truncateAutorespondersMessagesTable= sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponder_messages;");
        $wpdb->query($truncateAutorespondersMessagesTable);


        $truncateQueue= sprintf("TRUNCATE {$wpdb->prefix}wpr_queue;");
        $wpdb->query($truncateQueue);
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


    public function testGettingNewsletterOfAutoresponder() {
        //create an autoresponder
        $responder = AutoresponderTestHelper::addAutoresponderAndFetchRow($this->newsletterId, "TEst");

        $autoresponder = Autoresponder::getAutoresponder(intval($responder->id));

        $newsletter = $autoresponder->getNewsletter();

        $this->assertEquals($this->newsletterId, $newsletter->getId());


    }


    /**
     * @expectedException NonExistentAutoresponderException
     */
    public function testNonExistentAutoresponderInitializationException() {
    	Autoresponder::getAutoresponder(1);
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

        if (!isset($options['aid']) || !isset($options['subject']) || !isset($options['htmlbody']) || !isset($options['textbody']) || !isset($options['sequence'])) {
            throw new InvalidArgumentException();
        }



        $addAutoresponderMessageQuery = sprintf("INSERT INTO `%swpr_autoresponder_messages` (`aid`, `subject`, `htmlbody`, `textbody`, `sequence`, `htmlenabled`) VALUES (%d, '%s','%s','%s', %d,        %d);",
                                                                                    $wpdb->prefix, $options['aid'], $options['subject'], $options['htmlbody'], $options['textbody'], $options['sequence'], $options['htmlenabled']);

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
            ),
            array(
                "aid"          => $autoresponder->id,
                "subject"      => md5(microtime().rand(1,2000000)),
                "htmlbody"     => md5(microtime().rand(1,2000000)),
                "textbody"     => "",
                "htmlenabled" => 1,
                "sequence"     => 1,
            ),
            array(
                "aid"          => $autoresponder->id,
                "subject"      => md5(microtime().rand(1,2000000)),
                "htmlbody"     => md5(microtime().rand(1,2000000)),
                "textbody"     => "",
                "htmlenabled" => 1,
                "sequence"     => 2,
            ),
            array(
                "aid"          => $autoresponder->id,
                "subject"      => md5(microtime().rand(1,2000000)),
                "htmlbody"     => md5(microtime().rand(1,2000000)),
                "textbody"     => "",
                "htmlenabled" => 1,
                "sequence"     => 3,
            )
        );
        $originalSubjects = array();

        foreach ($autoresponderMessages as $responder) {
            $autoresponderMessages = $this->addAutoresponderMessage($responder);
            $originalSubjects[] = $responder['subject'];
        }



        $autoresponderObj = Autoresponder::getAutoresponder(intval($autoresponder->id));

        $autoresponderMessagesRes = $autoresponderObj->getMessages();


        $this->assertEquals(count($autoresponderMessagesRes), count($originalSubjects));

        foreach ($autoresponderMessagesRes as $message) {
            $receivedMessageSubjects[] = $message->getSubject();
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
        );

        $autoresponderRow = AutoresponderTestHelper::addAutoresponderAndFetchRow($this->newsletterId, "Sample Autoresponder");
        $testObj = Autoresponder::getAutoresponder((int) $autoresponderRow->id);

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

    public function testDeleteAutoresponderDeletesIntendedAutoresponder() {
        global $wpdb;
        $autoresponder_id = 1;
        $addAutoresponderQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponders (`nid`, `id`, `name`) VALUES (%d,%d, 'Test Autoresponder')",$this->newsletterId, $autoresponder_id);
        $wpdb->query($addAutoresponderQuery);
        $addAutoresponderQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponders (`nid`, `id`, `name`) VALUES (%d,%d, 'Test Autoresponder2')",$this->newsletterId, 2);
        $wpdb->query($addAutoresponderQuery);


        $this->assertTrue(Autoresponder::whetherAutoresponderExists($autoresponder_id));
        $this->assertTrue(Autoresponder::whetherAutoresponderExists(2));


        Autoresponder::delete(Autoresponder::getAutoresponder($autoresponder_id));


        $this->assertFalse(Autoresponder::whetherAutoresponderExists($autoresponder_id));
        $this->assertTrue(Autoresponder::whetherAutoresponderExists(2));
    }

    public function testDeleteAutoresponderMessagesWhenDeletingAutoresponder() {

        global $wpdb;

        $addAutoresponderQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponders (`nid`, `id`, `name`) VALUES (%d, 2, 'Test Test');",$this->newsletterId);
        $wpdb->query($addAutoresponderQuery);

        for ($iter=0 ; $iter < 5; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponder_messages (aid, subject, htmlenabled, textbody, htmlbody, sequence) VALUES (2, '%s', 1, '%s', '%s', %d)", md5("Test".microtime().$iter), md5("Apple".microtime().$iter), md5("Test".microtime().$iter), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
        }

        $addAnotherQuery = sprintf("INSERT INTO %swpr_autoresponders (`nid`, `id`, `name`) VALUES (%d, 3, 'Test Another')",$wpdb->prefix, $this->newsletterId);
        $wpdb->query($addAnotherQuery);

        Autoresponder::getAutoresponder(3);

        for ($iter=0 ; $iter < 5; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_autoresponder_messages (aid, subject, htmlenabled, textbody, htmlbody, sequence) VALUES (3, '%s', 1, '%s', '%s', %d)", md5("Test".microtime().$iter), md5("Apple".microtime().$iter), md5("Test".microtime().$iter), $iter  );
            $wpdb->query($addAutoresponderMessageQuery);
        }

        $one = Autoresponder::getAutoresponder(2);
        $this->assertEquals(5, count($one->getMessages()));

        $two = Autoresponder::getAutoresponder(3);
        $this->assertEquals(5, count($two->getMessages()));

        Autoresponder::delete(Autoresponder::getAutoresponder(2));

        $deleteAutoresponderMessagesQuery = sprintf("DELETE FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE aid=%d",2);
        $getDeletedAutoresponderMessagesQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE aid=%d",2);
        $results = $wpdb->get_results($getDeletedAutoresponderMessagesQuery);
        $this->assertEquals(0, count($results));

        $getNonDeletedAutoresponderMessagesQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_autoresponder_messages WHERE aid=%d",3);
        $results = $wpdb->get_results($getNonDeletedAutoresponderMessagesQuery);

        $this->assertEquals(5, count($results));
    }

    public function testDeletionOfAutoresponderResultsInCorrespondingAutoresponderSubscriptionsBeingDeleted() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, id, name) VALUES (%d, 1, '%s')",$wpdb->prefix, $this->newsletterId, 'Test');
        $wpdb->query($addAutoresponderQuery);

        $first_autoresponder_id = $wpdb->insert_id;

        for ($iter =0; $iter < 30; $iter++) {
            $addAutoresponderSubscription = sprintf("INSERT INTO %swpr_followup_subscriptions (sid, type, eid, sequence, last_date, last_processed, doc) VALUES (%d, 'autoresponder', 1, %d, %d, %d, %d)", $wpdb->prefix, $iter, -1, time()-5000, time(), time()-30000, time()-50000);
            $wpdb->query($addAutoresponderSubscription);
        }

        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, id, name) VALUES (%d, 2, '%s')",$wpdb->prefix, $this->newsletterId, 'Test 2');
        $wpdb->query($addAutoresponderQuery);

        $second_autoresponder_id = $wpdb->insert_id;

        for ($iter =0; $iter < 30; $iter++) {
            $addAutoresponderSubscription = sprintf("INSERT INTO %swpr_followup_subscriptions (sid, type, eid, sequence, last_date, last_processed, doc) VALUES (%d, 'autoresponder', 2, %d, %d, %d, %d)", $wpdb->prefix, $iter, -1, time()-5000, time(), time()-30000, time()-50000);
            $wpdb->query($addAutoresponderSubscription);
        }

        Autoresponder::delete(Autoresponder::getAutoresponder($first_autoresponder_id));

        $getAutoresponderSubscriptionsQuery = sprintf("SELECT COUNT(*) num FROM %swpr_followup_subscriptions WHERE eid=%d AND type='autoresponder';", $wpdb->prefix, 1);
        $numberRes = $wpdb->get_results($getAutoresponderSubscriptionsQuery);
        $numberOfResults = $numberRes[0]->num;

        $this->assertEquals(0, $numberOfResults);


        $getAutoresponderSubscriptionsQuery = sprintf("SELECT COUNT(*) num FROM %swpr_followup_subscriptions WHERE eid=%d AND type='autoresponder';", $wpdb->prefix, 2);
        $numberRes = $wpdb->get_results($getAutoresponderSubscriptionsQuery);
        $numberOfResults = $numberRes[0]->num;

        $this->assertEquals(30, $numberOfResults);
    }

    public function testDeletionOfAutoresponderResultsInCorrespondingQueueEmailsPendingDeliveryBeingDeleted() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, id, name) VALUES (%d, 1, '%s')",$wpdb->prefix, $this->newsletterId, 'Test');
        $wpdb->query($addAutoresponderQuery);
        for ($iter =0; $iter < 30; $iter++) {
            $addAutoresponderSubscription = sprintf("INSERT INTO %swpr_followup_subscriptions (sid, type, eid, sequence, last_date, last_processed, doc) VALUES (%d, 'autoresponder', 1, %d, %d, %d, %d)", $wpdb->prefix, $iter, -1, time()-5000, time(), time()-30000, time()-50000);
            $wpdb->query($addAutoresponderSubscription);
        }
        for ($iter=0; $iter < 50; $iter++) {
            $addAutoresponderEmailsQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_queue (meta_key, hash) VALUES ('AR-%d-%d-%d-%d', '%s');", 1,  $iter, $iter, $iter, md5(microtime().$iter) );
            $wpdb->query($addAutoresponderEmailsQuery);
        }
        $getAutoresponderEmails = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE meta_key LIKE 'AR-%d-%%';",$wpdb->prefix, 1);
        $emailsPendingDelivery = $wpdb->get_results($getAutoresponderEmails);
        $num = $emailsPendingDelivery[0]->num;

        $this->assertEquals(50, $num);
        Autoresponder::delete(Autoresponder::getAutoresponder(1));

        $getAutoresponderEmails = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE meta_key LIKE 'AR-%d-%%';",$wpdb->prefix, 1);
        $emailsPendingDelivery = $wpdb->get_results($getAutoresponderEmails);
        $num = $emailsPendingDelivery[0]->num;

        $this->assertEquals(0, $num);
    }


    public function testFetchingRangesOfAutoresponderMessages() {
        global $wpdb;
        //add an autoresponder
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);

        $autoresponder_id = $wpdb->insert_id;
        //add hundred messages
        for ($iter=0;$iter< 100; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence) VALUES (%d, '%s', '%s', %d)",$wpdb->prefix, $autoresponder_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }

        $autoresponder = Autoresponder::getAutoresponder($autoresponder_id);

        //default case
        $messagesReturned = $autoresponder->getMessages(); // returns from 1 to 10

        //ensure that the number of messages is 10
        $this->assertEquals(100, count($messagesReturned));

        $returnedMessageIds = array();
        for ($iter = 0; $iter < count($messagesReturned); $iter++) {
            $returnedMessageIds[] = $messagesReturned[$iter]->getId();
        }

        $difference = array_diff($returnedMessageIds, $autoresponderMessagesIds);
        $this->assertEquals(0, count($difference));


        $messagesReturned = $autoresponder->getMessages(10, 50);
        $this->assertEquals(50, count($messagesReturned));

        $returnedMessageIds = array();
        for ($iter = 0; $iter < count($messagesReturned); $iter++) {
            $returnedMessageIds[] = $messagesReturned[$iter]->getId();
        }

        $difference = array_diff($returnedMessageIds, array_slice($autoresponderMessagesIds, 10, 50));
        $this->assertEquals(0, count($difference));


    }

    public function testGetNumberOfMessagesTest() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);



        $autoresponder_id = $wpdb->insert_id;
        //add hundred messages
        $size = rand(1, 100);
        for ($iter=0;$iter< $size; $iter++) {
            $addAutoresponderMessageQuery = sprintf("INSERT INTO %swpr_autoresponder_messages (aid, subject, textbody, sequence) VALUES (%d, '%s', '%s', %d)",$wpdb->prefix, $autoresponder_id,  md5($iter . microtime()."auto"), md5(microtime().$iter.'test'), $iter);
            $wpdb->query($addAutoresponderMessageQuery);
            $autoresponderMessagesIds[] = $wpdb->insert_id;
        }


        $responder = Autoresponder::getAutoresponder($autoresponder_id);

        $number = $responder->getNumberOfMessages();

        $this->assertEquals($size, $number);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotMentioningSubjectThrowsInvalidArgumentException() {

        //empty subject

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);


        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'offset' => 3,
        );

        $autoresponder->addMessage($autoresponder_message);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotMentioningHTMLBodyThrowsInvalidArgumentException() {

        //empty subject

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);


        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'offset' => 3,
            'subject' => ''
        );

        $autoresponder->addMessage($autoresponder_message);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotMentioningOffsetThrowsInvalidArgumentException() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'htmlbody'=> 'test',
            'subject' => 'test',
            'textbody' => 'tests',
        );

        $autoresponder->addMessage($autoresponder_message);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotMentioningTextBodyThrowsInvalidArgumentException() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'offset' => 3,
            'htmlbody'=> 'test',
            'subject' => 'test'
        );

        $autoresponder->addMessage($autoresponder_message);
    }


    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testAddingAAutoresponderMessageWithoutSubjectThrowsException() {

        //empty subject

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
          'subject' => '',
          'textbody' => 'This is a test',
          'htmlbody' => 'This is a <test>body</test>',
          'offset' => 3,
        );

        $autoresponder->addMessage($autoresponder_message);
    }


    public function testAddingAAutoresponderMessageWithoutSubjectThrowsExceptionCode4000() {

        //empty subject

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => '',
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'offset' => 3,
        );

        try {
            $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            $code = $exc->getCode();
            $this->assertEquals(4000, $code);
        }
    }

    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testAddingAAutoresponderMessageWithoutAnyBodyThrowsException() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => 'test',
            'textbody' => '',
            'htmlbody' => '',
            'offset' => 3,
        );

        $autoresponder->addMessage($autoresponder_message);
    }

    public function testAddingAInvalidAutoresponderMessageThrowsExceptionCode4002() {

        //empty subject

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => 'Test',
            'textbody' => '',
            'htmlbody' => '',
            'offset' => 3,
        );

        try {
            $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            $code = $exc->getCode();
            $this->assertEquals(4002, $code);
        }
    }

    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testAddingAAutoresponderMessageWithoutAValidSequenceThrowsException() {


        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => 'test',
            'textbody' => 'test',
            'htmlbody' => 'test',
            'offset' => '',
        );
        $autoresponder->addMessage($autoresponder_message);
    }

    public function testAddingAInvalidAutoresponderMessageThrowsExceptionCode4004() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => 'Test',
            'textbody' => 'Test',
            'htmlbody' => 'Test',
            'offset' => '',
        );

        try {
            $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            $code = $exc->getCode();
            $this->assertEquals(4004, $code);
        }
    }

    public function testAddingAAutoresponderMessageResultsInAddition() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject"),
            'textbody' => md5(microtime()."textbody"),
            'htmlbody' => md5(microtime()."htmlbody"),
            'offset' => 0,
        );

        $message = $autoresponder->addMessage($autoresponder_message);


        $this->assertEquals($autoresponder_message['subject'], $message->getSubject());
        $this->assertEquals($autoresponder_message['htmlbody'], $message->getHTMLBody());
        $this->assertEquals($autoresponder_message['textbody'], $message->getTextBody());
        $this->assertEquals($autoresponder_message['offset'], $message->getDayNumber());

    }
    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testAddingAAutoresponderMessageWhenOneAlreadyExistsForSameDayResultsInFailure() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject"),
            'textbody' => md5(microtime()."textbody"),
            'htmlbody' => md5(microtime()."htmlbody"),
            'offset' => 0,
        );

        $message = $autoresponder->addMessage($autoresponder_message);

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject2"),
            'textbody' => md5(microtime()."textbody2"),
            'htmlbody' => md5(microtime()."htmlbody2"),
            'offset' => 0,
        );

        $message = $autoresponder->addMessage($autoresponder_message);
    }


    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testAddingAAutoresponderMessageWithInvalidOffsetShouldFail() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject"),
            'textbody' => md5(microtime()."textbody"),
            'htmlbody' => md5(microtime()."htmlbody"),
            'offset' => 'a',
        );

        $message = $autoresponder->addMessage($autoresponder_message);
    }


    /*       Tests for update message                    */

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotMentioningSubjectInUpdateThrowsInvalidArgumentException() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'offset' => 3,
        );

        $autoresponder->updateMessage($message->getId(), $autoresponder_message);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotMentioningHTMLBodyInUpdateThrowsInvalidArgumentException() {


        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);


        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }


        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'offset' => 3,
            'subject' => ''
        );

        $autoresponder->updateMessage($message->getId(), $autoresponder_message);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotMentioningOffsetInUpdateThrowsInvalidArgumentException() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);


        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'htmlbody'=> 'test',
            'subject' => 'test',
            'textbody' => 'tests',
        );

        $autoresponder->updateMessage($message->getId(), $autoresponder_message);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotMentioningTextBodyInUpdateThrowsInvalidArgumentException() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'offset' => 3,
            'htmlbody'=> 'test',
            'subject' => 'test'
        );

        $autoresponder->updateMessage($message->getId(), $autoresponder_message);
    }


    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testUpdatingAAutoresponderMessageWithoutSubjectThrowsException() {

        //empty subject

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => '',
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'offset' => 3,
        );

        $autoresponder->updateMessage($message->getId(), $autoresponder_message);
    }


    public function testUpdatingAAutoresponderMessageWithoutSubjectThrowsExceptionCode4000() {


        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);



        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => '',
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'offset' => 3,
        );

        try {
            $autoresponder->updateMessage($message->getId(), $autoresponder_message);
        }
        catch (Exception $exc) {
            $code = $exc->getCode();
            $this->assertEquals(4000, $code);
        }
    }

    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testUpdatingAAutoresponderMessageWithoutAnyBodyThrowsException() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => 'test',
            'textbody' => '',
            'htmlbody' => '',
            'offset' => 3,
        );

        $autoresponder->updateMessage($message->getId(), $autoresponder_message);
    }

    public function testUpdatingAInvalidAutoresponderMessageThrowsExceptionCode4002() {
        global $wpdb;

        //add a autoresponder
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);
        //add a message
        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );
        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }


        //define a update for the message
        $autoresponder_message = array(
            'subject' => 'Test',
            'textbody' => '',
            'htmlbody' => '',
            'offset' => 3,
        );

        try {
            $autoresponder->updateMessage($message->getId(), $autoresponder_message);
        }
        catch (Exception $exc) {
            $code = $exc->getCode();
            $this->assertEquals(4002, $code);
        }
    }

    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testUpdatingAAutoresponderMessageWithoutAValidSequenceThrowsException() {


        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => 'test',
            'textbody' => 'test',
            'htmlbody' => 'test',
            'offset' => '',
        );
        $autoresponder->updateMessage($message->getId(), $autoresponder_message);
    }

    public function testUpdatingAInvalidAutoresponderMessageThrowsExceptionCode4004() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);


        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => 'Test',
            'textbody' => 'Test',
            'htmlbody' => 'Test',
            'offset' => '',
        );

        try {
            $autoresponder->updateMessage($message->getId(), $autoresponder_message);
        }
        catch (Exception $exc) {
            $code = $exc->getCode();
            $this->assertEquals(4004, $code);
        }
    }

    public function testUpdatingAAutoresponderMessageResultsInUpdate() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject"),
            'textbody' => md5(microtime()."textbody"),
            'htmlbody' => md5(microtime()."htmlbody"),
            'offset' => 0,
        );

        $message = $autoresponder->updateMessage($message->getId(), $autoresponder_message);


        $this->assertEquals($autoresponder_message['subject'], $message->getSubject());
        $this->assertEquals($autoresponder_message['htmlbody'], $message->getHTMLBody());
        $this->assertEquals($autoresponder_message['textbody'], $message->getTextBody());
        $this->assertEquals($autoresponder_message['offset'], $message->getDayNumber());

    }

    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testUpdatingAAutoresponderMessageWhenOneAlreadyExistsForSameDayResultsInFailure() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject"),
            'textbody' => md5(microtime()."textbody"),
            'htmlbody' => md5(microtime()."htmlbody"),
            'offset' => 0,
        );

        try {
            $message1 = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message2 = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject2"),
            'textbody' => md5(microtime()."textbody2"),
            'htmlbody' => md5(microtime()."htmlbody2"),
            'offset' => 0,
        );

        $message = $autoresponder->updateMessage($message2->getId(), $autoresponder_message);
    }
    
    



    /**
     * @expectedException InvalidAutoresponderMessageException
     */
    public function testUpdatingAAutoresponderMessageWithInvalidOffsetShouldFail() {

        global $wpdb;
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject"),
            'textbody' => md5(microtime()."textbody"),
            'htmlbody' => md5(microtime()."htmlbody"),
            'offset' => 'a',
        );

        $message = $autoresponder->updateMessage($message->getId(), $autoresponder_message);
    }

    /**
     * @expectedException NonExistentMessageException
     */
    public function testDeletingAutoresponderMessageDeletesTheMessage() {

        global $wpdb;
        
        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        $autoresponder_message = array(
            'subject' => md5(microtime()."subject"),
            'textbody' => md5(microtime()."textbody"),
            'htmlbody' => md5(microtime()."htmlbody"),
            'offset' => 'a',
        );

        $message_id = $message->getId();

        $autoresponder->deleteMessage($message);

        AutoresponderMessage::getMessage($message_id);
    }


    public function testDeleteOperationDeletesAutoresponderMessagePendingDelivery() {

        global $wpdb;

        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }

        //add 10 of these messages in the queue.
        for ($iter = 0; $iter < 10; $iter++) {
            $addMessageToQueueQuery = sprintf("INSERT INTO %swpr_queue (`to`, `meta_key`, `sent`, `hash`) VALUES ('%s','AR-%d-%d-%d-%d', 0, '%s')", $wpdb->prefix, 'test'.microtime().'@test.com', $autoresponder->getId(), $iter, $message->getId(), $iter, md5(microtime()));
            $wpdb->query($addMessageToQueueQuery);
        }


        $getNumberOfUnsentMessagesForMessage = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE sent=0 AND meta_key LIKE 'AR-%%%%-%%%%-%d-%%';",$wpdb->prefix, $message->getId());
        $resultSet = $wpdb->get_results($getNumberOfUnsentMessagesForMessage);
        $numOfUnsentMessages = $resultSet[0]->num;
        $this->assertEquals(10, $numOfUnsentMessages);


        //add 10 of these messages in the queue.
        for ($iter = 10; $iter < 20; $iter++) {
            $addMessageToQueueQuery = sprintf("INSERT INTO %swpr_queue (`to`, `meta_key`, `sent`, `hash`) VALUES ('%s','AR-%d-%d-%d-%d', 1, '%s')", $wpdb->prefix, 'test'.microtime().'@test.com', $autoresponder->getId(), $iter, $message->getId(), $iter, md5(microtime()));
            $wpdb->query($addMessageToQueueQuery);
        }

        $getNumberOfUnsentMessagesForMessage = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE sent=1 AND meta_key LIKE 'AR-%%%%-%%%%-%d-%%';",$wpdb->prefix, $message->getId());
        $resultSet = $wpdb->get_results($getNumberOfUnsentMessagesForMessage);
        $numOfUnsentMessages = $resultSet[0]->num;
        $this->assertEquals(10, $numOfUnsentMessages);

        $autoresponder->deleteMessage($message);

        $getNumberOfUnsentMessagesForMessage = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE sent=0 AND meta_key LIKE 'AR-%%%%-%%%%-%d-%%';",$wpdb->prefix, $message->getId());
        $resultSet = $wpdb->get_results($getNumberOfUnsentMessagesForMessage);
        $numOfUnsentMessages = $resultSet[0]->num;
        $this->assertEquals(0, $numOfUnsentMessages);


        $getNumberOfUnsentMessagesForMessage = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE sent=1 AND meta_key LIKE 'AR-%%%%-%%%%-%d-%%';",$wpdb->prefix, $message->getId());
        $resultSet = $wpdb->get_results($getNumberOfUnsentMessagesForMessage);
        $numOfUnsentMessages = $resultSet[0]->num;
        $this->assertEquals(10, $numOfUnsentMessages);

    }

    public function testDeleteAutoresponderMessageDeletesOnlyItsOwnFromQueue() {

        global $wpdb;

        $addAutoresponderQuery = sprintf("INSERT INTO %swpr_autoresponders (nid, name) VALUES (%d,'%s' )", $wpdb->prefix, $this->newsletterId, md5(microtime()) );
        $results = $wpdb->query($addAutoresponderQuery);
        $autoresponder = Autoresponder::getAutoresponder($wpdb->insert_id);

        $autoresponder_message = array(
            'textbody' => 'This is a test',
            'htmlbody' => 'This is a <test>body</test>',
            'subject' => 'Test Subject',
            'offset' => 3,
        );

        try {
            $message = $autoresponder->addMessage($autoresponder_message);
        }
        catch (Exception $exc) {
            throw new BadFunctionCallException();
        }




        //add 10 of these messages in the queue.
        for ($iter = 0; $iter < 10; $iter++) {
            $addMessageToQueueQuery = sprintf("INSERT INTO %swpr_queue (`to`, `meta_key`, `sent`, `hash`) VALUES ('%s','AR-%d-%d-%d-%d', 0, '%s')", $wpdb->prefix, 'test'.microtime().'@test.com', $autoresponder->getId(), $iter, $message->getId(), $iter, md5(microtime()));
            $wpdb->query($addMessageToQueueQuery);
        }


        $getNumberOfUnsentMessagesForMessage = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE sent=0 AND meta_key LIKE 'AR-%%%%-%%%%-%d-%%';",$wpdb->prefix, $message->getId());
        $resultSet = $wpdb->get_results($getNumberOfUnsentMessagesForMessage);
        $numOfUnsentMessages = $resultSet[0]->num;
        $this->assertEquals(10, $numOfUnsentMessages);


        //add 10 of these messages in the queue.
        for ($iter = 10; $iter < 20; $iter++) {
            $addMessageToQueueQuery = sprintf("INSERT INTO %swpr_queue (`to`, `meta_key`, `sent`, `hash`) VALUES ('%s','AR-%d-%d-%d-%d', 1, '%s')", $wpdb->prefix, 'test'.microtime().'@test.com', $autoresponder->getId(), $iter, 9801, $iter, md5(microtime()));
            $wpdb->query($addMessageToQueueQuery);
        }

        $getNumberOfUnsentMessagesForMessage = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE sent=1 AND meta_key LIKE 'AR-%%%%-%%%%-%d-%%';",$wpdb->prefix, 9801);
        $resultSet = $wpdb->get_results($getNumberOfUnsentMessagesForMessage);
        $numOfUnsentMessages = $resultSet[0]->num;
        $this->assertEquals(10, $numOfUnsentMessages);

        $autoresponder->deleteMessage($message);

        $getNumberOfUnsentMessagesForMessage = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE sent=0 AND meta_key LIKE 'AR-%%%%-%%%%-%d-%%';",$wpdb->prefix, $message->getId());
        $resultSet = $wpdb->get_results($getNumberOfUnsentMessagesForMessage);
        $numOfUnsentMessages = $resultSet[0]->num;
        $this->assertEquals(0, $numOfUnsentMessages);


        $getNumberOfUnsentMessagesForMessage = sprintf("SELECT COUNT(*) num FROM %swpr_queue WHERE sent=1 AND meta_key LIKE 'AR-%%%%-%%%%-%d-%%';",$wpdb->prefix, 9801);
        $resultSet = $wpdb->get_results($getNumberOfUnsentMessagesForMessage);
        $numOfUnsentMessages = $resultSet[0]->num;
        $this->assertEquals(10, $numOfUnsentMessages);

    }






}

