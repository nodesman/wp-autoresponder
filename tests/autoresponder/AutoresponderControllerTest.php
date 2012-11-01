<?php
require_once __DIR__ . "/../../lib/framework.php";
require_once __DIR__ . "/../../controllers/autoresponder.php";
require_once "AutoresponderTestHelper.php";


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

    public function testEnsureOnlyOnePageAppearsByDefault() {

        $pages =  array();

        AutorespondersController::getPageNumbers(0, $pages, $number_of_pages);

        $this->assertEquals($pages['start'], 0);
        $this->assertEquals($pages['end'], 0);
        $this->assertEquals(1, $number_of_pages);

    }

    public function testEnsureOnlyOnePageWhenLessThan10AutorespondersAndDefaultRowsPerPage() {

        $pages = array();
        $number_of_pages = 0;

        AutorespondersController::getPageNumbers(1, $pages, $number_of_pages);

        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(1, $pages['end']);
        $this->assertEquals(1, $number_of_pages);

        AutorespondersController::getPageNumbers(5, $pages, $number_of_pages);

        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(1, $pages['end']);
        $this->assertEquals(1, $number_of_pages);

        AutorespondersController::getPageNumbers(10, $pages, $number_of_pages);

        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(1, $pages['end']);
        $this->assertEquals(1, $number_of_pages);

    }


    public function testWhetherPagesRunMultiplesOfTen() {

        $pages = array();
        $number_of_pages = 0;

        $_GET['p'] = 1;

        AutorespondersController::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);


        $_GET['p'] = 4;

        AutorespondersController::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);


        $_GET['p'] = 11;

        AutorespondersController::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(11, $pages['start']);
        $this->assertEquals(20, $pages['end']);


        $_GET['p'] = 20;

        AutorespondersController::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(11, $pages['start']);
        $this->assertEquals(20, $pages['end']);


        $_GET['p'] = 100;

        AutorespondersController::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(91, $pages['start']);
        $this->assertEquals(100, $pages['end']);


        $_GET['p'] = 0;

        AutorespondersController::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);

        $_GET['p'] = -1;

        AutorespondersController::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);
    }

    public function testRegulatingRowsPerPage() {

        $_GET['pp'] = 11;
        $actual = AutorespondersController::getRowsPerPage();
        $this->assertEquals(50, $actual);

        $_GET['pp'] = 8;
        $actual = AutorespondersController::getRowsPerPage();
        $this->assertEquals(10, $actual);


        $_GET['pp'] = 58;
        $actual = AutorespondersController::getRowsPerPage();
        $this->assertEquals(100, $actual);

        $_GET['pp'] = 102;
        $actual = AutorespondersController::getRowsPerPage();
        $this->assertEquals(100, $actual);

    }

    public function testValidValuesForCurrentPage() {
        $_GET['p'] = -3;
        AutorespondersController::getPageNumbers(0, $pages, $number);
        $this->assertEquals(0, $pages['current_page']);

        unset($_GET['p']);
        AutorespondersController::getPageNumbers(10, $pages, $number);
        $this->assertEquals(1, $pages['current_page']);

        $_GET['p'] = 5;
        AutorespondersController::getPageNumbers(10,$pages,$number);
        $this->assertEquals(5, $pages['current_page']);

    }

    public function testSettingPreviousNext() {
        $_GET['p'] = 4;
        AutorespondersController::getPageNumbers(240, $pages, $number_of_pages);
        $this->assertEquals(false, $pages['before']);
        $this->assertEquals(11, $pages['after']);

        $_GET['p'] = 10;
        AutorespondersController::getPageNumbers(200, $pages, $number_of_pages);
        $this->assertEquals(11, $pages['after']);
        $this->assertEquals(false, $pages['before']);

        $_GET['p'] = 11;
        AutorespondersController::getPageNumbers(240, $pages, $number_of_pages);
        $this->assertEquals(10, $pages['before']);
        $this->assertEquals(21, $pages['after']);

        $_GET['p'] = 100;
        AutorespondersController::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(90, $pages['before']);
        $this->assertEquals(false, $pages['after']);
    }

    public function testNonMultipleOfTenPagesEndInNumberOfPages() {

        $pages = array();
        $number_of_pages = 1;

        $_GET['p'] = 21;
        AutorespondersController::getPageNumbers(240, $pages, $number_of_pages);
        $this->assertEquals(21, $pages['start']);
        $this->assertEquals(24, $pages['end']);
        $this->assertEquals(24, $number_of_pages);

        $_GET['p'] = 4;
        AutorespondersController::getPageNumbers(80, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(8, $pages['end']);
        $this->assertEquals(8, $number_of_pages);

        unset($_GET['p']);
        unset($_GET['pp']);
        AutorespondersController::getPageNumbers(80, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(8, $pages['end']);
        $this->assertEquals(8, $number_of_pages);


        $_GET['p'] = 5;
        $_GET['pp'] = 50;
        AutorespondersController::getPageNumbers(480, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);
        $this->assertEquals(false, $pages['before']);
        $this->assertEquals(false, $pages['after']);
        $this->assertEquals(10, $number_of_pages);

        $_GET['p'] = 3;
        $_GET['pp'] = 50;
        AutorespondersController::getPageNumbers(400, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(false, $pages['before']);
        $this->assertEquals(false, $pages['after']);
        $this->assertEquals(8, $pages['end']);
        $this->assertEquals(8, $number_of_pages);

    }
    public function testNonMultipleOfTenPagesEndInNumberOfPagesOnAllRowsSimultaneously() {

        $pages = array();
        $number_of_pages = 1;

        $_GET['p'] = 3;
        $_GET['pp'] = 50;
        AutorespondersController::getPageNumbers(240, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(5, $pages['end']);

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

        foreach ($newsletters as $newsletter) {
            $addNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (name) VALUES ('%s')",$newsletter['name']);
            $wpdb->query($addNewsletterQuery);
        }
    }

    public function tearDown()
    {
        parent::tearDown();
    }

}
