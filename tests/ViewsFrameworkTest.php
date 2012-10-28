<?php
/**
 * Created by JetBrains PhpStorm.
 * User: raj
 * Date: 10/28/12
 * Time: 3:40 PM
 */


class ViewFrameworkTest  extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
    }

    /**
     * @expectedException ViewRenderedException
     */
    public function testWhetherSettingTheViewVariableResultsInViewFileBeingRendered() {
        _wpr_setview("../tests/test_view");
        _wpr_render_view();
    }

    /**
     * @expectedException ViewFileNotFoundException
     */

    public function testWhetherNonExistentViewThrowsException() {
        _wpr_setview("quick_brown_fox_sphinx_jackdaws_view");
        _wpr_render_view();
    }


    public function testWhetherValuesSetArePropagatedToView() {
        $microtime = microtime();
        _wpr_set("test_variable", );
        ob_start();
        _wpr_render_view();
        $content = ob_get_clean();
        $content = trim($content);

        $this->assertEquals($content, $microtime);
    }


    public function tearDown() {
        parent::tearDown();
    }

}

class ViewRenderedException extends Exception {

}