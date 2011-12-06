<?php
include ("wp-load.php");

global $current_user;

if (current_user_can("manage_newsletters"))
{

    if (isset($_GET['wpr-template']))
    {
        $template = $_GET['wpr-template'];
		$currentDir = str_replace("templateproxy.php","",__FILE__);
		$templatesDirectory = $currentDir."/htmltemplates/";
		$filePath = $templatesDirectory."/$template";
		if (file_exists($filePath))
		{
			readfile($filePath);
		}
		else
		{
		 	echo ""; //Nothing better than 404.
		}
    }
}
else
{
	  header("HTTP/1.0 404 Not Found"); //what? who? where? how? why? 
}


