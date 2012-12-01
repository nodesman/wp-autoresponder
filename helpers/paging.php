<?php

class Pager {

    public static function getRowsPerPage()
    {

        $per_page =  (isset($_GET['pp']) && 0 < intval($_GET['pp'])) ? intval($_GET['pp']) : 10;

        if ($per_page > 10 && $per_page < 50)
            return 50;

        if ($per_page < 10)
            return 10;

        if ($per_page >50 && $per_page < 100)
            return 100;

        if ($per_page > 100)
            return 100;

        return $per_page;
    }


    public function testEnsureOnlyOnePageWhenLessThan10RowsAndDefaultRowsPerPage() {

        $pages = array();
        $number_of_pages = 0;

        Pager::getPageNumbers(1, $pages, $number_of_pages);

        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(1, $pages['end']);
        $this->assertEquals(1, $number_of_pages);

        Pager::getPageNumbers(5, $pages, $number_of_pages);

        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(1, $pages['end']);
        $this->assertEquals(1, $number_of_pages);

        Pager::getPageNumbers(10, $pages, $number_of_pages);

        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(1, $pages['end']);
        $this->assertEquals(1, $number_of_pages);

    }

    private static function getCurrentPageNumber()
    {
        $pageParameter = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pageParameter = (0 < $pageParameter) ?$pageParameter:1;
        return $pageParameter;
    }

    public static function getPageNumbers($numberOfRows, &$pages, &$numberOfPages) {

        $pages = array();
        if ($numberOfRows == 0)
        {
            $pages['start'] = 0;
            $pages['end']   = 0;
            $pages['current_page'] = 0;
            $numberOfPages = 1;
            return;
        }

        $current_page = self::getCurrentPageNumber();
        $rowsPerPage = self::getRowsPerPage();
        $numberOfPages = ceil($numberOfRows/$rowsPerPage);

        if ($current_page %10 == 0) {
            $start = intval((( ($current_page/10) - 1)*10)+1);
            $end=$current_page;
        }
        else {
            $start = intval(floor($current_page/10)*10+1);
            $end = intval(ceil($current_page/10)*10);
        }

        if ($end >= $numberOfPages)
        {
            $end = $numberOfPages;
        }

        $pages['start'] = $start;
        $pages['end']  = $end;
        $pages['current_page'] = $current_page;

        if ($current_page <=10) {
            $pages['before'] = false;
        }
        else
            $pages['before'] = $pages['start'] -1;

        if ($end == $numberOfPages) {
            $pages['after'] = false;
        }
        else
            $pages['after'] = $pages['end'] +1;
    }

    public function testValidValuesForCurrentPage() {
        $_GET['p'] = -3;
        Pager::getPageNumbers(0, $pages, $number);
        $this->assertEquals(0, $pages['current_page']);

        unset($_GET['p']);
        Pager::getPageNumbers(10, $pages, $number);
        $this->assertEquals(1, $pages['current_page']);

        $_GET['p'] = 5;
        Pager::getPageNumbers(10,$pages,$number);
        $this->assertEquals(5, $pages['current_page']);
    }


    public function testSettingPreviousNext() {
        $_GET['p'] = 4;
        Pager::getPageNumbers(240, $pages, $number_of_pages);
        $this->assertEquals(false, $pages['before']);
        $this->assertEquals(11, $pages['after']);

        $_GET['p'] = 10;
        Pager::getPageNumbers(200, $pages, $number_of_pages);
        $this->assertEquals(11, $pages['after']);
        $this->assertEquals(false, $pages['before']);

        $_GET['p'] = 11;
        Pager::getPageNumbers(240, $pages, $number_of_pages);
        $this->assertEquals(10, $pages['before']);
        $this->assertEquals(21, $pages['after']);

        $_GET['p'] = 100;
        Pager::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(90, $pages['before']);
        $this->assertEquals(false, $pages['after']);
    }


    public function testNonMultipleOfTenPagesEndInNumberOfPages() {

        $pages = array();
        $number_of_pages = 1;

        $_GET['p'] = 21;
        Pager::getPageNumbers(240, $pages, $number_of_pages);
        $this->assertEquals(21, $pages['start']);
        $this->assertEquals(24, $pages['end']);
        $this->assertEquals(24, $number_of_pages);

        $_GET['p'] = 4;
        Pager::getPageNumbers(80, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(8, $pages['end']);
        $this->assertEquals(8, $number_of_pages);

        unset($_GET['p']);
        unset($_GET['pp']);
        Pager::getPageNumbers(80, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(8, $pages['end']);
        $this->assertEquals(8, $number_of_pages);


        $_GET['p'] = 5;
        $_GET['pp'] = 50;
        Pager::getPageNumbers(480, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);
        $this->assertEquals(false, $pages['before']);
        $this->assertEquals(false, $pages['after']);
        $this->assertEquals(10, $number_of_pages);

        $_GET['p'] = 3;
        $_GET['pp'] = 50;
        Pager::getPageNumbers(400, $pages, $number_of_pages);
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
        Pager::getPageNumbers(240, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(5, $pages['end']);

    }

    public function testWhetherPagesRunMultiplesOfTen() {

        $pages = array();
        $number_of_pages = 0;

        $_GET['p'] = 1;

        Pager::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);


        $_GET['p'] = 4;

        Pager::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);


        $_GET['p'] = 11;

        Pager::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(11, $pages['start']);
        $this->assertEquals(20, $pages['end']);


        $_GET['p'] = 20;

        Pager::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(11, $pages['start']);
        $this->assertEquals(20, $pages['end']);


        $_GET['p'] = 100;

        Pager::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(91, $pages['start']);
        $this->assertEquals(100, $pages['end']);


        $_GET['p'] = 0;

        Pager::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);

        $_GET['p'] = -1;

        Pager::getPageNumbers(1000, $pages, $number_of_pages);
        $this->assertEquals(1, $pages['start']);
        $this->assertEquals(10, $pages['end']);
    }



    public function testRegulatingRowsPerPage() {

        $_GET['pp'] = 11;
        $actual = Pager::getRowsPerPage();
        $this->assertEquals(50, $actual);

        $_GET['pp'] = 8;
        $actual = Pager::getRowsPerPage();
        $this->assertEquals(10, $actual);


        $_GET['pp'] = 58;
        $actual = Pager::getRowsPerPage();
        $this->assertEquals(100, $actual);

        $_GET['pp'] = 102;
        $actual = Pager::getRowsPerPage();
        $this->assertEquals(100, $actual);

    }


}