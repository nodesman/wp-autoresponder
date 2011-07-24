<?php

class Routing
{
	static function url($string,$arguments=array())
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
	
	static function newsletterHome()
	{
		return Routing::url("newsletter");
	}

}


?>