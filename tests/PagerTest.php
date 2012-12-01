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


    public function tearDown() {

        parent::tearDown();
    }


}