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

function _wpr_autoresponder_manage() {
    AutorespondersController::manage();
}

function _wpr_autoresponder_add_message() {
    AutorespondersController::add_message();
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

    public static function delete_handler() {
        $id = $_POST['autoresponder'];
        Autoresponder::delete(Autoresponder::getAutoresponder(intval($id)));
        wp_redirect("admin.php?page=_wpr/autoresponders");
    }

    public function autorespondersListPage()
    {
        $numberOfPages = 1;
        $pages = array();
        $autoresponders = Autoresponder::getAllAutoresponders(Pager::getStartIndexOfRecordSet(), Pager::getRowsPerPage());

        Pager::getPageNumbers(Autoresponder::getNumberOfAutorespondersAvailable(), $pages, $numberOfPages);
        $current_page = $pages['current_page'];


        _wpr_set('number_of_pages', $numberOfPages);
        _wpr_set('autoresponders', $autoresponders);
        _wpr_set('current_page',$current_page);
        _wpr_set('pages', $pages);
        _wpr_set('base_url', 'admin.php?page=_wpr/autoresponders');
        _wpr_setview('autoresponders_home');
    }

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
            wp_redirect("admin.php?page=_wpr/autoresponders&action=manage&id=".$autoresponder->getId());
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

    public function autoresponderMessagesList() {

    }

    public static function manage() {

        $numberOfPages = 1;
        $autoresponder_id = $_GET['id'];

        try {
            $autoresponder = Autoresponder::getAutoresponder((int) $autoresponder_id);
        }
        catch (NonExistentAutoresponderException $exp) {
            wp_redirect("admin.php?page=_wpr/autoresponders");
        }

        Pager::getPageNumbers($autoresponder->getNumberOfMessages(), $pages, $numberOfPages);
        $current_page = $pages['current_page'];

        $messages = $autoresponder->getMessages(Pager::getStartIndexOfRecordSet(), Pager::getRowsPerPage());

        _wpr_set('number_of_pages', $numberOfPages);
        _wpr_set('current_page',$current_page);
        _wpr_set('pages', $pages);
        _wpr_set("messages", $messages);
        _wpr_set('base_url', 'admin.php?page=_wpr/autoresponders&action=manage&id='.$autoresponder_id);
        _wpr_set("autoresponder", $autoresponder);
        _wpr_setview("autoresponder_manage");
    }


    function add_message() {

        _wpr_setview("autoresponder_add_message");

    }



}//end class

add_action("_wpr_add_autoresponder_post_handler","_wpr_add_autoresponder_post_handler");
add_action("_wpr_delete_autoresponder_post_handler","_wpr_delete_autoresponder_post_handler");

function _wpr_add_autoresponder_post_handler() {

    global $wpdb;
    if (!wp_verify_nonce($_POST['_wpr_add_autoresponder'], '_wpr_add_autoresponder')) {
        return;
    }
    AutorespondersController::add_post_handler();
}

function _wpr_delete_autoresponder_post_handler() {
    global $wpdb;
    if (!wp_verify_nonce($_POST['_wpr_delete_autoresponder'], '_wpr_delete_autoresponder')) {
        return;
    }
    AutorespondersController::delete_handler();
}