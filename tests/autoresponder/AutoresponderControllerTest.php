<?php
require_once __DIR__."/../../lib/framework.php";
require_once __DIR__."/../../controllers/autoresponder.php";
require_once "AutoresponderTestHelper.php";


class AutoresponderControllerTest extends WP_UnitTestCase
{

    private $autoresponderController;

    private $newsletterId;

    public function setUp() {
        parent::setUp();
        global $wpdb;

        $createNewsletterQuery = sprintf("INSERT INTO {$wpdb->prefix}wpr_newsletters (name, fromname, fromemail) VALUES ('%s', '%s', '%s')","Test Newsletter", "test", "test@gmail.com");
        $wpdb->query($createNewsletterQuery);

        $this->autoresponderController = new AutorespondersController();
        $this->newsletterId = 1;
    }

    public function  testDefaultPageLoadInvocationFetchesOnlyTheFirstTenAutorespondersInOrderOfCreation() {

        $this->newsletterId = $this->newsletterId;
        $autoresponderObject = AutoresponderTestHelper::addAutoresponderObjects($this->newsletterId,20);

        $this->autoresponderController->autorespondersListPage();

        $this->assertEquals(2, intval(_wpr_get("number_of_pages")));

        //test whether adding 10 autoresponders results in 2 pages


    }


    public function tearDown() {
        parent::tearDown();
    }

}
