<?php

function _wpr_autoresponders_handler() {
    $autorespondersController = new AutorespondersController();
    $autorespondersController->autorespondersListPage();
}//end _wpr_autoresponders_handler


class AutorespondersController
{

    public function autorespondersListPage()
    {

        $numberOfAutorespondersPerPage = 10;
        $start = (int)(true===isset($_GET['start'])) ? $_GET['start'] : 0;
        $start = ($start > 0) ? $start : 0;

        $autoresponders = Autoresponder::getAllAutoresponders();
        $numberOfPages = ceil(count($autoresponders) / $numberOfAutorespondersPerPage);

        _wpr_set('number_of_pages', $numberOfPages);
        _wpr_set('autoresponders', $autoresponders);
        _wpr_setview('autoresponders_home');

    }//end autorespondersListPage


}//end class

