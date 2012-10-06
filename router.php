<?php

class Router {

    public function __construct() {

        add_action('init', array($this, 'init'));
    }

    public function init() {
        global $wpr_routes;
        $path = $_GET['page'];
        if (0 != strpos($path, '_wpr/')) {
            return;
        }

        if (!isset($wpr_routes[$path])) {
            return;
        }

        $action = (!isset($_GET['action']))?'default':$_GET['action'];


        //just to be sure get rid of anything suspicious
        $action = preg_replace('@[^a-zA-Z0-9_]@','',$action);

        if (function_exists($wpr_routes[$path][$action])) {
            do_action('_wpr_router_pre_callback');
            call_user_func($wpr_routes[$path][$action]);
            do_action('_wpr_router_post_callback');
        }
    }



}

$wpr_router = new Router();