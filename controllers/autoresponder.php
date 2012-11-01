<?php

function _wpr_autoresponders_handler() {
    $autorespondersController = new AutorespondersController();
    $autorespondersController->autorespondersListPage();
}//end _wpr_autoresponders_handler


function _wpr_autoresponder_add() {
	
	AutorespondersController::add();
}

class AutorespondersController
{


    private $defaultAutorespondersPerPage = 10;
    public function autorespondersListPage()
    {
        $numberOfPages = 1;
        $pages = array();

        $start = (int)(true === isset($_GET['p'])) ? $_GET['p'] : 0;
        $start = ($start > 0) ? $start : 0;

        $autoresponders = Autoresponder::getAllAutoresponders($start, $this->getNumberOfAutorespondersPerPage());

        $this->getPageNumbers(Autoresponder::getNumberOfAutorespondersAvailable(), $pages, $numberOfPages);
        $current_page = $pages['current_page'];

        _wpr_set('number_of_pages', $numberOfPages);
        _wpr_set('autoresponders', $autoresponders);
        _wpr_set('current_page',$current_page);

        _wpr_set('pages', $pages);
        _wpr_setview('autoresponders_home');
    }


    public static function getPageNumbers($number_of_autoresponders, &$pages, &$numberOfPages) {

        $pages = array();

        if ($number_of_autoresponders == 0)
        {
            $pages['start'] = 0;
            $pages['end']   = 0;
            $pages['current_page'] = 0;
            $numberOfPages = 1;
            return;
        }

        $current_page = self::getCurrentPageNumber();

        $rowsPerPage = self::getRowsPerPage();
        $numberOfPages = ceil($number_of_autoresponders/$rowsPerPage);

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

    public  static function getRowsPerPage()
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

    private static function getCurrentPageNumber()
    {
        $pageParameter = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pageParameter = (0 < $pageParameter) ?$pageParameter:1;
        return $pageParameter;
    }


    private function getNumberOfAutorespondersPerPage()
    {
        return $this->defaultAutorespondersPerPage;
    }
    //end autorespondersListPage
    
    
    public static function add() {
	    global $wpdb;
	    
	    _wpr_setview("autoresponder_add");
	    
    }

}//end class

