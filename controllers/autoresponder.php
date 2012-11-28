<?php

function _wpr_autoresponders_handler() {
    $autorespondersController = new AutorespondersController();
    $autorespondersController->autorespondersListPage();
}//end _wpr_autoresponders_handler


function _wpr_autoresponder_add() {
	AutorespondersController::add();
}
function _wpr_autoresponder_delete() {
    AutorespondersController::delete();
}
class AutorespondersController
{
    private $defaultAutorespondersPerPage = 10;

    public static  function delete() {
        $autoresponder_id = intval($_GET['id']);
        $autoresponder = Autoresponder::getAutoresponder($autoresponder_id);
        _wpr_set("autoresponder", $autoresponder);
        _wpr_setview("autoresponder_delete");
    }

    public function autorespondersListPage()
    {
        $numberOfPages = 1;
        $pages = array();

        $start = $this->getStartIndexOfAutoresponderRecordSet();

        $autoresponders = Autoresponder::getAllAutoresponders($start, $this->getNumberOfAutorespondersPerPage());

        $this->getPageNumbers(Autoresponder::getNumberOfAutorespondersAvailable(), $pages, $numberOfPages);
        $current_page = $pages['current_page'];

        _wpr_set('number_of_pages', $numberOfPages);
        _wpr_set('autoresponders', $autoresponders);
        _wpr_set('current_page',$current_page);

        _wpr_set('pages', $pages);
        _wpr_setview('autoresponders_home');
    }

    private function getStartIndexOfAutoresponderRecordSet()
    {
        $start = (int)(true === isset($_GET['p'])) ? $_GET['p'] : 0;
        $start = ($start > 1) ? $start : 0;
        return $start;
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

        $newsletters = Newsletter::getAllNewsletters();
        _wpr_set("newsletters",$newsletters);
	    _wpr_setview("autoresponder_add");
	    
    }

    public function add_post_handler() {

        $post_data = self::getAddAutoresponderFormPostedData();

        self::validateAddFormPostData($post_data, $errors);

        if (0 == count($errors)) {
            $autoresponder = Autoresponder::addAutoresponder($post_data['nid'],$post_data['name']);
            wp_redirect("admin.php?page=_wpr/autoresponders&action=manage&aid=".$autoresponder->getId());
        }

        _wpr_set("_wpr_add_errors", $errors);

    }

    public static function validateAddFormPostData($post_data, &$errors)
    {
        $name = $post_data['name'];
        if (!Autoresponder::whetherValidAutoresponderName(array('name'=>$name))) {
            $errors[] = __("The name for the autoresponder you've entered is invalid");
        }

        if (!Newsletter::whetherNewsletterIDExists($post_data['nid'])) {
            $errors[] = __("The newsletter you've selected doesn't exist");
            return $errors;
        }
    }

    public static function getAddAutoresponderFormPostedData()
    {
        return array(
            'name' => $_POST['autoresponder_name'],
            'nid' => $_POST['nid']
        );
    }

}//end class


add_action("_wpr_add_autoresponder_post_handler","_wpr_add_autoresponder_post_handler");

function _wpr_add_autoresponder_post_handler() {
    global $wpdb;


    if (!wp_verify_nonce($_POST['_wpr_add_autoresponder'], '_wpr_add_autoresponder')) {
        return;
    }

    AutorespondersController::add_post_handler();


}
