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

function _wpr_no_newsletters() {

    _wpr_setview("no_newsletter");
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

function _wpr_render_view()
{
        global $wpr_globals;
        $plugindir = $GLOBALS['WPR_PLUGIN_DIR'];

        $currentView = _wpr_get("_wpr_view");

        extract($wpr_globals);

        $viewfile ="$plugindir/views/".$currentView.".php";
        if (is_file($viewfile)) {
	        require($viewfile); // this statement is necessarily a require and not an include. we want feedback when the file is not found.
	    }
        else
            throw new ViewFileNotFoundException();
}

class UnknownControllerInvokeRequested extends Exception {

    public function  __construct($message){
        parent::__construct($message, 0, null);
    }
}


class Routing {


    public static function run_controller() {
        global $wpr_routes;

        _wpr_handle_post();
        $path = $_GET['page'];


        if (self::is_admin_popup())
            self::render_admin_screen_popup();

        if (self::is_template_html_request())
            self::render_template_html();


        if (self::whetherCurrentPathRequiresAtleastOneNewsletterToExistToBeAccessible($wpr_routes, $path)) {
            _wpr_setview("no_newsletter");
            return;
        }

        if (self::whetherLegacyURL($_GET['page'])) {
            return;
        }

        $method_to_invoke = self::getMethodToInvoke();

        if (self::whetherControllerMethodExists($method_to_invoke)) {
            self::callControllerMethod($method_to_invoke);
        }
        else
	        throw new UnknownControllerInvokeRequested("Unknown control invoked - '{$method_to_invoke}''");

    }

    public static function is_subscription_management_page_request()
    {
        return isset($_GET['wpr-manage']);
    }

    public static function whetherCurrentPathRequiresAtleastOneNewsletterToExistToBeAccessible($wpr_routes, $path)
    {
        return isset($wpr_routes[$path]['require_newsletters']) && $wpr_routes[$path]['require_newsletters'] == true && Newsletter::whetherNoNewslettersExist();
    }

    private static function getMethodToInvoke()
    {
        global $wpr_routes;
        $method_to_invoke = "";

        $current_path = trim($_GET['page']);

        if (self::whetherPathExists($current_path)) {

            $method_to_invoke = $wpr_routes[$current_path]['controller'];

            if (self::whetherSubPageRequested()) {

                $subpage_name = self::getSubPageName();

                if (self::whetherSubPageExists($current_path, $subpage_name)) {
                    $method_to_invoke = $wpr_routes[$current_path]['children'][$subpage_name];
                }
                else
                    throw new UnknownSubPageRequestedException("Unknown sub page requested");
            }
        }
        else
            throw new DestinationControllerNotFoundException("Unknown destination invoked: $current_path");

        return $method_to_invoke;
    }

    public static function serve_file()
    {
        global $wpr_files;
        $name = $_GET['wpr-file'];
        if (self::is_js_file($wpr_files, $name))
            header("Content-Type: text/javascript");
        else if (self::is_css_file($wpr_files, $name))
            header("Content-Type: text/css");
        else if (self::is_png_file($wpr_files, $name))
            header("Content-Type: image/png");
        $file_path = WPR_DIR . "/{$wpr_files[$name]}";
        readfile($file_path);
        exit;
    }


    public static function render_admin_screen_popup()
    {

        switch ($_GET['wpr-admin-action']) {
            case 'preview_email':
                include "preview_email.php";
                exit;
                break;
            case 'filter':
                include("filter.php");
                exit;
                break;
            case 'delete_mailout':
                include WPR_DIR."/delmailout.php";
                exit;
                break;
        }
    }

    public static function is_template_html_request()
    {
        $outcome =  ((isset($_GET['wpr-template'])) && preg_match("@[0-9a-zA-Z_]\.htm[l]?@", $_GET['wpr-template']));
        return $outcome;
    }

    public static function is_admin_popup()
    {

        $whetherAdminPopup = isset($_GET['wpr-admin-action']);
        return $whetherAdminPopup;
    }

    public static function is_js_file($wpr_files, $name)
    {
        return preg_match("@.*\.js@", $wpr_files[$name]);
    }

    public static function is_css_file($wpr_files, $name)
    {
        return preg_match("@.*\.css@", $wpr_files[$name]);
    }

    public static function is_png_file($wpr_files, $name)
    {
        return preg_match("@.*\.png@", $wpr_files[$name]);
    }


    public static function whether_file_request()
    {
        global $wpr_files;
        $outcome = (isset($_GET['wpr-file']) && isset($wpr_files[$_GET['wpr-file']]));
        return $outcome;
    }


    public static function render_template_html()
    {

        if (isset($_GET['wpr-template']))
        {
            $template = $_GET['wpr-template'];

            if (0 == preg_match("@^[a-zA-Z0-9_]+\.html$@", $template))
                return;

            $filePath = WPR_DIR."/htmltemplates/$template";
            if (file_exists($filePath))
                readfile($filePath);

            exit;
        }
    }

    public  static function whetherLegacyURL($current_path)
    {
        $result = preg_match("@^wpresponder/@", $current_path);
        return 0 != $result;
    }

    private static function whetherSubPageExists($current_path, $action)
    {
        global $wpr_routes;
        return isset($wpr_routes[$current_path]['children'][$action]);
    }

    private static function getSubPageName()
    {
        $action = $_GET['action'];
        $action = preg_replace('@[^a-zA-Z0-9_]@', '', $action);
        return $action;
    }

    private static function whetherSubPageRequested()
    {
    	$whether = isset($_GET['action']);
        return $whether;
    }

    private static function whetherPathExists($current_path)
    {
        global $wpr_routes;
        return isset($current_path) && isset($wpr_routes[$current_path]) && isset($wpr_routes[$current_path]['controller']);
    }

    public static function whetherControllerMethodExists($methodToCall)
    {
        return function_exists($methodToCall);
    }

    private  static function callControllerMethod($methodToCall)
    {
        do_action('_wpr_router_pre_callback');
        call_user_func($methodToCall);
        do_action('_wpr_router_post_callback');
    }

    public static function isWPRAdminPage() {
        if (!isset($_GET['page'])) {
            return false;
        }

        $result = isset($_GET['page']) && ( 0 != preg_match("@^wpresponder/.*@",$_GET['page']) || preg_match("@^_wpr/.*@",$_GET['page']));
        return $result;
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


class DestinationControllerNotFoundException extends Exception
{

}


class UnknownSubPageRequestedException extends Exception {

}

class ViewFileNotFoundException extends Exception {

}
