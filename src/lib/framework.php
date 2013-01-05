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
    $url = sprintf("%s/wp-admin/admin.php?page=",get_bloginfo("wpurl"), $path);
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