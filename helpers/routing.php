<?php

function wpr_admin_menu()
{
	global $wpr_routes;
	add_menu_page('Newsletters','Newsletters','manage_newsletters',__FILE__);
	
	//TODO: Refactor to use the new standard template rendering function for all pages.
	add_submenu_page(__FILE__,'Dashboard','Dashboard','manage_newsletters',__FILE__,"wpr_dashboard");

	$admin_pages_definitions = $wpr_routes;
	$admin_pages_definitions = apply_filters("_wpr_menu_definition",$admin_pages_definitions);
	foreach ($admin_pages_definitions as $definition)
	{
		add_submenu_page(__FILE__,$definition['page_title'],$definition['menu_title'],$definition['capability'],$definition['menu_slug'],$definition['callback']);
	}
}


function _wpr_handle_post()
{
        if (count($_POST)>0 && isset($_POST['wpr_form']))
        {
            $formName = $_POST['wpr_form'];
            $actionName = "_wpr_".$formName."_post";
            $default_handler_name = $actionName."_handler";
            add_action($actionName,$default_handler_name);
            do_action($actionName);
        }
}

function _wpr_run_controller()
{
    $page = $_GET['page'];
    $parts = explode("/",$page);
    $action = $parts[1];
    $arguments= array_splice($parts,1, count($parts));
    $actionName = "_wpr_".$action."_handle";
    _wpr_set("_wpr_view",$action);
    do_action($actionName,$arguments);
}


function _wpr_render_view()
{
        global $wpr_globals;
        $plugindir = $GLOBALS['WPR_PLUGIN_DIR'];

        $currentView = _wpr_get("_wpr_view");

        foreach ($wpr_globals as $name=>$value)
        {
                ${$name} = $value;
        }
        
        $viewfile ="$plugindir/views/".$currentView.".php";
        if (is_file($viewfile)) {
	        include $viewfile;
	    }
        $wpr_globals = array();
}



class Routing {

    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    
    private static function legacyInit() {
    	global $wpr_routes;
	    $admin_page_definitions = $wpr_routes;
	    
		foreach ($admin_page_definitions as $item)
		{
			if (isset($item['legacy']) && $item['legacy']===0)
			{
				$slug = str_replace("_wpr/","",$item['menu_slug']);
				$actionName = "_wpr_".$slug."_handle";
				$handler = "_wpr_".$slug."_handler";
				add_action($actionName,$handler);
			}
		}

    }
    

    public static function init() {
    
        global $wpr_routes;
        
        Routing::legacyInit();
        
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
    
    public static function url($string,$arguments=array())
	{
		$queryString = "";
		if (count($arguments) > 0)
		{
			foreach ($arguments as $name=>$value)
			{
				$queryString .= sprintf("&%s=%s",$name,$value);
			}
		}
		return "admin.php?page=_wpr/".$string.$queryString;
	}
	
	public static function newsletterHome()
	{
		return Routing::url("newsletter");
	}

}

$wpr_router = new Routing();