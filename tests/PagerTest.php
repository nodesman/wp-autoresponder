<?php


class PagerTest extends WP_UnitTestCase {


    public function setUp() {

        parent::setUp();

    }

    public function testEnsureOnlyOnePageAppearsByDefault() {

        $pages =  array();

        Pager::getPageNumbers(0, $pages, $number_of_pages);

        $this->assertEquals($pages['start'], 0);
        $this->assertEquals($pages['end'], 0);
        $this->assertEquals(1, $number_of_pages);

    }

    public function testGettingIndicesBasedOnArguments() {
        $_GET['p'] = 3;
        $_GET['pp'] = 40;

        $start = Pager::getStartIndexOfRecordSet();
        $this->assertEquals(80, $start);

        $_GET['p'] = 1;
        $_GET['pp'] = 30;
        $start = Pager::getStartIndexOfRecordSet();
        $this->assertEquals(0, $start);

        unset($_GET['p']);
        unset($_GET['pp']);
        $start = Pager::getStartIndexOfRecordSet();
        $this->assertEquals(0, $start);





    }


    public function tearDown() {

        parent::tearDown();
    }


}