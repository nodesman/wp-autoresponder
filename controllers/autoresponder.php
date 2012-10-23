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

        $start = (int)(true === isset($_GET['start'])) ? $_GET['start'] : 0;
        $start = ($start > 0) ? $start : 0;

        $autoresponders = Autoresponder::getAllAutoresponders($start, $this->getNumberOfAutorespondersPerPage());
        $numberOfPages = ceil(Autoresponder::getNumberOfAutorespondersAvailable() / $this->getNumberOfAutorespondersPerPage());
        _wpr_set('number_of_pages', $numberOfPages);
        _wpr_set('autoresponders', $autoresponders);
        _wpr_setview('autoresponders_home');
    }


    private function getNumberOfAutorespondersPerPage()
    {
        return $this->defaultAutorespondersPerPage;
    }
    //end autorespondersListPage

}//end class

