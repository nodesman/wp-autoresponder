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

        $createNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (name, fromname, fromemail) VALUES ('%s', '%s', '%s')", "Test Newsletter", "test", "test@gmail.com");
        $wpdb->query($createNewsletterQuery);

        $this->autoresponderController = new AutorespondersController();
        $this->newsletterId = 1;
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


    public function tearDown()
    {
        parent::tearDown();
    }

}
