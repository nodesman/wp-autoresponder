<?php
$parameters_to_files = array(
							 	"jqui.js"=>"jqueryui.js",
								"tabber.css"=>"tabber.css",
								"tabber.js"=>"tabber.js",								
								"jqui.css"=>"jqueryui.css",
								"widget-help.png" => "images/widget-help.png"
							 );
if (isset($_GET['wpr-file']))
{
	$name = $_GET['wpr-file'];
	if (isset($parameters_to_files[$name]))
	{
		
		$plugindir = str_replace(basename(__FILE__),"",__FILE__);
		$plugindir = rtrim($plugindir,"/");
		if (ereg(".*\.js",$parameters_to_files[$name]))
			header("Content-Type: text/javascript");
		else if (ereg(".*\.css",$parameters_to_files[$name]))
			header("Content-Type: text/css");					   
		else if (ereg(".*\.png",$parameters_to_files[$name]))
			header("Content-Type: image/png");					   

		$file_path = $plugindir."/".$parameters_to_files[$name];
		readfile($file_path);
		exit;
	}
}


?>