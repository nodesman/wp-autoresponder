<?php
/**
 * Created by JetBrains PhpStorm.
 * User: raj
 * Date: 10/4/12
 * Time: 12:13 AM
 */

require_once __DIR__. '/../router.php';

class DefaultActionCalledException extends Exception {

}


class SpecificRouteItemCalledException extends Exception {

}

class RoutesTest  extends WP_UnitTestCase {


    public function setUp() {
        //set up the routes global variable
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

    public function tearDown() {

    }

}