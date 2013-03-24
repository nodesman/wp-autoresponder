<?php

$wpresponder_path =  __DIR__."/../../src/wpresponder.php";

include_once $wpresponder_path;

$testWhetherAutorespondersListHeaderWasCalled = false;

function _wprtest_testWhetherBeforeAutorespondersListHeaderIsCalled() {
    global $testWhetherAutorespondersListHeaderWasCalled;
    $testWhetherAutorespondersListHeaderWasCalled = true;
}

class AutorespondersManageViewTest extends WP_UnitTestCase
{

    public static $whetherCalled=false;
    public function testWhetherBeforeAutorespondersListHeaderIsCalled() {
        global $testWhetherAutorespondersListHeaderWasCalled;
        add_action("_wpr_autoresponders_manage_list_header", "_wprtest_testWhetherBeforeAutorespondersListHeaderIsCalled");
        _wpr_setview("autoresponders_home");
        _wpr_set("autoresponders", array());
        _wpr_set("number_of_pages", 10);
        ob_start();
        _wpr_render_view();
        ob_clean();
        $this->assertTrue($testWhetherAutorespondersListHeaderWasCalled);
    }

}
