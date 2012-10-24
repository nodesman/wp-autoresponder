<?php
/**
 * Created by JetBrains PhpStorm.
 * User: raj
 * Date: 10/4/12
 * Time: 12:13 AM
 */

require_once __DIR__. '/../helpers/routing.php';

class DefaultActionCalledException extends Exception {

}


class SpecificRouteItemCalledException extends Exception {

}

class RoutesTest  extends WP_UnitTestCase {


    public function setUp() {
        //set up the routes global variable
        parent::setUp();
        global $wpr_routes;

        $wpr_routes = array(
            '_wpr/autoresponders' => array(
                'default' => '_wpr_autoresponders_home',
                'add' => '_wpr_autoresponders_add'
            )
        );


    }

    /**
     * @expectedException DefaultActionCalledException
     */
    function testWhetherDefaultActionGetsCalled() {
        $_GET['page'] = '_wpr/autoresponders';


        function _wpr_autoresponders_home() {
            throw new DefaultActionCalledException();
        }
        do_action('init');
    }

    /**
     * @expectedException SpecificRouteItemCalledException
     */
    function testWhetherSpecificActionGetsCalled() {
        $_GET['page'] = '_wpr/autoresponders';
        $_GET['action'] = 'add';

        function _wpr_autoresponders_add() {
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

    function testWhetherAutoresponderListPageGetsInvokedWhenVisited() {

        global $wpr_routes;

        $wpr_routes = array(
            array(
            'page_title' => 'Autoresponders',
            'menu_title' => 'Autoresponders',
            'controller' => '_wpr_autoresponders_handler',
            'capability' => 'manage_newsletters',
            'legacy' => 0,
            'menu_slug' => '_wpr/autoresponders',
            'callback' => '_wpr_render_view',
            'children' => array (
                'manage' => '_wpr_autoresponder_manage',
            ))
        );

        $_GET['page'] =  "_wpr/autoresponders";
        do_action("init");
        $currentlyRenderingView = _wpr_get('_wpr_view');
        $this->assertEquals('autoresponders_home', $currentlyRenderingView);

    }

    public function tearDown() {
        parent::tearDown();
    }

}

class ExpectedInvocationException extends Exception {

}