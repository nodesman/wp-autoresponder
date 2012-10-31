<?php

function _wpr_autoresponders_handler() {
    $autorespondersController = new AutorespondersController();
    $autorespondersController->autorespondersListPage();
}//end _wpr_autoresponders_handler


class AutorespondersController
{

    private $defaultAutorespondersPerPage = 10;
    public function autorespondersListPage()
    {
        $numberOfPages = 1;
        $pages = array();

        $start = (int)(true === isset($_GET['page'])) ? $_GET['page'] : 0;
        $start = ($start > 0) ? $start : 0;

        $autoresponders = Autoresponder::getAllAutoresponders($start, $this->getNumberOfAutorespondersPerPage());
        $numberOfPages = ceil(Autoresponder::getNumberOfAutorespondersAvailable() / $this->getNumberOfAutorespondersPerPage());

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
        $numberOfPages = ceil($number_of_autoresponders/10);


        $start = floor($current_page/10)+1;

        $end = ceil($current_page/10);

        if ($end > $numberOfPages)
            $end = $numberOfPages;

        $pages['start'] = $start;
        $pages['end']  = $end;
        $pages['current_page'] = $current_page;

        $nuumberOfPages = 10;
    }

    public  static function getRowsPerPage()
    {
        return (isset($_GET['pp']) && 0 < intval($_GET['pp'])) ? intval($_GET['pp']) : 10;
    }

    private static function getCurrentPageNumber()
    {
        $pageParameter = isset($_GET['page']) ? intval($_GET['page']) : 1;
        return (0 < $pageParameter) ?$pageParameter:1;;
    }


    private function getNumberOfAutorespondersPerPage()
    {
        return $this->defaultAutorespondersPerPage;
    }
    //end autorespondersListPage

}//end class

