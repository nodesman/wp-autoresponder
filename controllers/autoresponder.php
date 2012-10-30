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

    public static function getRowsPerPage() {

    }

    public static function getPageNumbers($number_of_autoresponders, &$pages, &$nuumberOfPages) {

        $pages['start'] = 1;
        $pages['end']  = 10;
        $pages['current_page'] = 1;

        $nuumberOfPages = 10;
    }


    private function getNumberOfAutorespondersPerPage()
    {
        return $this->defaultAutorespondersPerPage;
    }
    //end autorespondersListPage

}//end class

