<?php
$parameters_to_files = array(
    'jqui.js'=>'js/jqueryui.js',
    'tabber.js'=>'js/tabber.js',
    'angular.js'  => 'js/angular.js',
    'widget-help.png' => 'images/widget-help.png',
    'tabber.css'=>'css/tabber.css',
    'admin-ui.css'  => 'css/admin-ui.css',
    'jqui.css'=>'css/jqueryui.css',
);
if (isset($_GET['wpr-file']))
{
	$name = $_GET['wpr-file'];
	if (isset($parameters_to_files[$name]))
	{
		if (preg_match("@.*\.js@",$parameters_to_files[$name]))
			header("Content-Type: text/javascript");
		else if (preg_match("@.*\.css@",$parameters_to_files[$name]))
			header("Content-Type: text/css");					   
		else if (preg_match("@.*\.png@",$parameters_to_files[$name]))
			header("Content-Type: image/png");					   

		$file_path = __DIR__."/{$parameters_to_files[$name]}";
		readfile($file_path);
		exit;
	}
}


?>
