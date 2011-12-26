<?php
function _wpr_get($name)
{
        global $wpr_globals;
        if (isset($wpr_globals[$name]))
                  return $wpr_globals[$name];
        else
                return null;
}

function _wpr_set($name,$value)
{
    global $wpr_globals;
    $wpr_globals[$name] = $value;
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
        if (is_file($viewfile))
        include $viewfile;
        foreach ($wpr_globals as $name=>$value)
        {
                unset(${$name});
        }
}


function _wpr_admin_path($path,$arguments=array())
{
    $path = "_wpr/$path";
    $queryString = "";
    foreach ($arguments as $name=>$value)
    {
        $queryString .= '&'.$name.'=';
        $queryString .= $value;
    }
    $path .= $queryString;
    return $path;
}

function _wpr_admin_url($path,$arguments=array())
{
    $path = _wpr_admin_path($path,$arguments);
    $url = get_bloginfo("url");
    $url .= '/wp-admin/admin.php?page=';
    $url .= $path;
    return $url;
}

function _wpr_setview($viewname)
{
    _wpr_set("_wpr_view",$viewname);
}

function _wpr_isset($variable_name)
{
    $value = _wpr_get($variable_name);
    if (empty($value))
        return false;
    else
        return true;
}