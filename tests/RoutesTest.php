<?php
/**
 * Created by JetBrains PhpStorm.
 * User: raj
 * Date: 10/4/12
 * Time: 12:13 AM
 */

require_once __DIR__ . '/../helpers/routing.php';

class DefaultActionCalledException extends Exception {

}


class SpecificRouteItemCalledException extends Exception {

}

class RoutesTest  extends WP_UnitTestCase {

    private $beforeTestRoutesArray;

    private $autoresponderPath = '_wpr/autoresponders';

    public function setUp() {
        //set up the routes global variable
        parent::setUp();
        global $wpr_routes;
        $this->beforeTestRoutesArray = $wpr_routes;

    }

    function testWhetherChecksIfCurrentRequestIsAWPRAdminRequest() {

        $_GET['page'] = $this->autoresponderPath;
        $this->assertEquals(true, Routing::isWPRAdminPage());

        $_GET['page'] = "some_random_page.php";
        $this->assertEquals(false, Routing::isWPRAdminPage());

    }

    /**
     * @expectedException DestinationControllerNotFoundException
     */
    function testWhetherRequestingANonExistentURLResutlsInException() {
        $_GET['page'] = "_wpr/something_that_doesnt_exit";
        Routing::init();
    }

    /**
     * @expectedException UnknownSubPageRequestedException
     */
    function testWhetherRequestingANonExistentSubactionResutlsInException() {
        $_GET['page'] = "_wpr/autoresponders";
        $_GET['action'] = "doesnt_exist_something";
        Routing::init();
    }


    function testWhetherPostAndPreCallbackActionsExecuted() {


        $wpr_routes['_wpr/my_path' ]=  array(
            'page_title' => 'My Path',
            'menu_title' => 'Autoresponders',
            'controller' => '_wpr_autoresponder_testcallback',
            'capability' => 'manage_newsletters',
            'legacy'     => 0,
            'menu_slug'  => '_wpr/autoresponders',
            'callback'   => '_wpr_render_view',
            'children'   => array (
                'manage' => '_wpr_autoresponder_manage',
            )
        );
    }

    /**
     * @expectedException DefaultActionCalledException
     */
    function testWhetherDefaultActionGetsCalled() {

        $_GET['page'] = $this->autoresponderPath;
        global $wpr_routes;

        $wpr_routes[ $this->autoresponderPath ]=  array(
            'page_title' => 'Autoresponders',
            'menu_title' => 'Autoresponders',
            'controller' => '_wpr_autoresponder_testcallback',
            'capability' => 'manage_newsletters',
            'legacy'     => 0,
            'menu_slug'  => '_wpr/autoresponders',
            'callback'   => '_wpr_render_view',
            'children'   => array (
                 'manage' => '_wpr_autoresponder_manage',
            )
        );

        function _wpr_autoresponder_testcallback() {
            throw new DefaultActionCalledException();
        }

        do_action('init');
    }

    /**
     * @expectedException SpecificRouteItemCalledException
     */
    function testWhetherSpecificActionGetsCalled() {

        $_GET['page'] = $this->autoresponderPath;
        $_GET['action'] = 'manage';
        global $wpr_routes;

        $wpr_routes[$this->autoresponderPath] =  array(
            'page_title' => 'Autoresponders',
            'menu_title' => 'Autoresponders',
            'controller' => '_wpr_manage_callback',
            'capability' => 'manage_newsletters',
            'legacy' => 0,
            'menu_slug' => '_wpr/autoresponders',
            'callback' => '_wpr_render_view',
            'children' => array (
                'manage' => '_wpr_manage_callback',
            )
        );

        function _wpr_manage_callback() {
            throw new SpecificRouteItemCalledException();
        }

        do_action("init");
    }

    function testWhetherOtherURLsDontGetRouted() {
        $_GET['page'] = 'some_other_page.php';

        function _wpr_callback_test() {
            throw new BadMethodCallException();
        }

        add_action('_wpr_router_pre_callback', '_wpr_callback_test');
        do_action('init');
    }

    public function tearDown() {
        parent::tearDown();
        global $wpr_routes;
        $wpr_routes = $this->beforeTestRoutesArray;
    }

}

class ExpectedInvocationException extends Exception {

}