<?php
include_once __DIR__ . "/../../src/lib/framework.php";
include_once __DIR__ . "/../../src/controllers/autoresponder.php";
include_once __DIR__."/AutoresponderTestHelper.php";


class AutoresponderControllerTest extends WP_UnitTestCase
{

    private $autoresponderController;

    private $newsletterId;

    public function setUp()
    {
        parent::setUp();
        global $wpdb;

        $truncateNewsletterTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_newsletters");
        $wpdb->query($truncateNewsletterTable);

        $createNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (name, fromname, fromemail) VALUES ('%s', '%s', '%s')", "Test Newsletter", "test", "test@gmail.com");
        $wpdb->query($createNewsletterQuery);

        $truncateAutorespondresTable = sprintf("TRUNCATE {$wpdb->prefix}wpr_autoresponders;");
        $wpdb->query($truncateAutorespondresTable);

        $this->autoresponderController = new AutorespondersController();
        $this->newsletterId = 1;

        $_GET = array();
    }

    public function  testDefaultPageLoadInvocationFetchesOnlyTheFirstTenAutorespondersInOrderOfCreation()
    {

        $this->newsletterId = $this->newsletterId;
        $autorespondersRowList = AutoresponderTestHelper::addAutoresponderObjects($this->newsletterId, 20);
        $this->autoresponderController->autorespondersListPage();
        $numberOfPagesInAutorespondersList = intval(_wpr_get("number_of_pages"));

        $autorespondersListToRender = _wpr_get("autoresponders");

        $first10Autoresponders = array_slice($autorespondersRowList, 0, 10);
        $autoresponderNamesFromRows = self::getAutoresponderNamesFromRows($first10Autoresponders);

        $autoresponderNamesFromObjects = self::getAutoresponderNamesFromAutoresponderObjects($autorespondersListToRender);

        $difference = array_diff($autoresponderNamesFromRows, $autoresponderNamesFromObjects);
        $numberOfDifferingRows = count($difference);

        $viewToRender = _wpr_get("_wpr_view");

        $this->assertEquals("integer", getType($numberOfPagesInAutorespondersList));
        $this->assertEquals(2, $numberOfPagesInAutorespondersList);
        $this->assertEquals(0, $numberOfDifferingRows);
        $this->assertEquals("autoresponders_home", $viewToRender);
    }


    public function testFetchingAddFormPostData() {
        $_POST['nid'] = 9801;
        $_POST['autoresponder_name'] = "Test Autoresponder";
        $data = AutorespondersController::getAddAutoresponderFormPostedData();
        $this->assertEquals(9801, $data['nid']);
        $this->assertEquals("Test Autoresponder", $data['name']);
        $this->assertEquals(2, count($data));
    }

    public function testValidationAutoresponderFormData() {

        $post_data['nid'] = '';
        $post_data['name'] = '';
        $errors = array();
        AutorespondersController::validateAddFormPostData($post_data, $errors);
        $this->assertEquals(2 , count($errors));

        unset($errors);
        unset($post_data);

        $post_data['nid']= 1;
        $post_data['name'] = '';
        AutorespondersController::validateAddFormPostData($post_data, $errors);
        $this->assertEquals(1 , count($errors));


        unset($errors);
        unset($post_data);
        $post_data['nid']=91;
        $post_data['name'] = 'Test Autoresponder';
        AutorespondersController::validateAddFormPostData($post_data, $errors);
        $this->assertEquals(1 , count($errors));


        unset($errors);
        unset($post_data);
        $post_data['nid']=1;
        $post_data['name'] = 'Test Autoresponder';
        AutorespondersController::validateAddFormPostData($post_data, $errors);
        $this->assertEquals(0 , count($errors));
    }

    public function testWhetherAutoresponderIsAdded() {
        $_POST['nid'] = 1;
        $responderName = 'Test Autoresponder 5432';
        $_POST['autoresponder_name'] = $responderName;

        try {
            AutorespondersController::add_post_handler();
        }   catch (Exception $e) {
            //until the day I figure out what to do with that wp_redirect call failing every test...
        }



        $autoresponders = Autoresponder::getAllAutoresponders();
        $found = false;

        foreach ($autoresponders as $responder) {
            $name = $responder->getName();
            if ($name == $responderName)
                $found=true;
        }

        $this->assertEquals(true, $found);
    }

    private static function getAutoresponderNamesFromAutoresponderObjects($autorespondersListToRender)
    {
        $autoresponderNamesFromObjects = array();
        foreach ($autorespondersListToRender as $autoresponderObject) {
            $autoresponderNamesFromObjects[] = $autoresponderObject->getName();
        }
        return $autoresponderNamesFromObjects;
    }

    private static function getAutoresponderNamesFromRows($autorespondersRowList)
    {
        $autoresponderNamesFromRows = array();
        foreach ($autorespondersRowList as $responder) {
            $autoresponderNamesFromRows[] = $responder->name;
        }
        return $autoresponderNamesFromRows;
    }

    public function testAutoresponderAddAssignsNewsletters() {

        global $wpdb;
        $truncateNewslettersQuery = sprintf("TRUNCATE {$wpdb->prefix}wpr_newsletters;");
        $wpdb->query($truncateNewslettersQuery);
        $newsletters = array(

            array(
                "name"=> "Newsletter2",
                "reply_to"=> "test@test.com",
            ),
            array(
                "name"=> "Newsletter3",
                "reply_to"=> "test@test.com",
            )
        );

        $namesUsed = array();
        foreach ($newsletters as $newsletter) {
            $addNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (name) VALUES ('%s')",$newsletter['name']);
            $wpdb->query($addNewsletterQuery);
            $namesUsed[] = $newsletter['name'];
        }

        AutorespondersController::add();
        $newslettersReceived = _wpr_get("newsletters");

        $receivedNames = array();
        foreach ($newslettersReceived as $newsletter) {
            $receivedNames[] = $newsletter->getName();
        }

        $diff = array_diff($namesUsed,$receivedNames);
        $this->assertEquals(0, count($diff));


    }

    public function tearDown()
    {
        parent::tearDown();
    }

}
