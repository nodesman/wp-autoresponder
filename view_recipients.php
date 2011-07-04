<?php
include "wp-load.php";
global $wpdb;
get_currentuserinfo();	
if (!current_user_can('level_8'))
{
 	  exit;
}

if (!isset($_GET['string']) || !isset($_GET['nid']))
{
	?>
This page should not be visited directly. The necessary arguments are missing.
    <?php
}

function getCustomFieldLabel($nid,$name)
{
	global $wpdb;
	$query = "SELECT label from ".$wpdb->prefix."wpr_custom_fields where name='$name' and nid='$nid'";
	$results = $wpdb->get_results($query);
	$label = $results[0]->label;
	return $label;
}
// get the parameters from the query string.
$thestring = $_GET['string'];
$thestring = base64_decode($thestring);
//form the query

$sections = explode(" ",$thestring);
$size = count($sections);
$count=0;
$nid = (int) $_GET['nid'];

if ($nid == 0)
{
	header("HTTP/1.0 404 Not Found");
	exit;

}

wpr_create_temporary_tables($nid);	  //this creates the tables based on which a bigger table will be created		
wpr_make_subscriber_temptable($nid);  //this table will be used for getting the user list.

$comparisonOpers = array("equal","notequal","lessthan","greaterthan");
$stringOperators = array("startswith","endswith","contains");

$final ="";
for ($count=0;$count<$size;)
{
	$condition = "";

	if ($count != 0)
	{
		$conjunction = " ".$sections[$count]." ";
	}
	else
	{
	   $conjunction = "";
	  $count = -1; //to adjust for the indices i have used below below..
	}
	  
	$field = $sections[$count+1];
	$equality = $sections[$count+2];
	$value = $sections[$count+3];
	

	if (in_array($equality,$comparisonOpers))
	{
		
		switch ($equality)
		{
			case 'equal':
			  $condition = "`$field` = '$value'";
			  break;
			case 'notequal':
			   $condition= "`$field` <> '$value'";
			   break;
			case 'lessthan':
			   $condition = "`$field` < '$value'";
			   break;
			case 'greaterthan':
			   $condition = "`$field` > '$value'";
		}
	}
	else if ($equality == "notnull")
	{
		$condition = "`$field` IS NOT NULL";
	}
	else if (in_array($equality,$stringOperators))
	{
		switch ($equality)
		{
			case 'startswith':
				$condition = "`$field` like '$value%'";
				break;
			case 'contains':
				$condition = "`$field` like '%$value%'";
				break;
			case 'endswith':
			    $condition = "`$field` like '%$value'";
				break;
		}
	}
	else if (in_array($equality,array("before","after")) && $field == "dateofsubscription")
	{
			$thetime = strtotime($value);
			
			switch ($equality)
			{
				case 'before':
					$condition = "date < $thetime";
					break;
				case 'after':
				    $condition = "date > $thetime";
					break;
			}
	}
	else if ($equality == "rlike")
	{
		$condition = "`$field` rlike '$value'"; 
	}

	
	$final .= $conjunction." ".$condition;
			 		 
	if ($count == 0) //the first element is not a conjunction
	{
		$count+=3;
	}
	else
	{
		$count +=4;
	}
	
}
$final = trim($final);
if (empty($final))
{
   ?>
   <center><h3 style="font-family:Arial, Helvetica, sans-serif;">No Conditions Specified</h3></center>
   <?php
   exit;
   
}
$tableName = $wpdb->prefix."wpr_subscribers_".$nid;
$query = "SELECT * FROM $tableName where $final and `active`=1 and `confirmed`=1";
$subscribers = $wpdb->get_results($query);

if (count($subscribers)==0)
{
	?>
    <h2 align="center" style="font-family:Arial, Helvetica, sans-serif">No Confirmed Subscribers Were Found Matching The Specified Conditions</h2>
    <?php
	exit;
}

$keys = array_keys( (array) $subscribers[0]);

$knownColumns = array("name","email","active","confirmed","date","id");
$skipColumns = array("id","confirmed","active");
?>
<table width="100%" style="font-family:Verdana, Geneva, sans-serif; font-size:12px;">
<tr bgcolor="#cccccc" style="color: #000; font-family:Verdana, Geneva, sans-serif;">
  <?php 
  foreach ($keys as $fieldname)
  {
	  if (in_array($fieldname,$skipColumns))
	  	continue;
	  if (in_array($fieldname,$knownColumns))
	  {
		  if ($fieldname == "date")
		  {
			  $fieldname = "Date of Joining";
		  }
		  ?><td style="padding: 10px;"><?php echo ucwords($fieldname) ?></td><?php		  
		  continue;
	  }
	  else
	  {
		  ?><td style="padding: 10px;"><?php echo getCustomFieldLabel($nid,$fieldname) ?></td><?php
	  }
  }
  ?>
</tr>
<?php

foreach ($subscribers as $subscriber)
{
	$color = ($color == "#ffffff")?"#f0f0f0":"#ffffff";
	?>
    <tr bgcolor="<?php echo $color ?>">
    <?php
	$sub = (array) $subscriber;
	foreach ($sub as $key=>$value)
	{
		 if (in_array($key,$skipColumns))
			continue;
		switch ($key)
		{
			case 'name':
			case 'email':
			$string = $value;
			break;
			
			case 'date':
			$string = date("d F Y",$value);
			break;
			
			default:
			$string = $value;
		}
		?>
        <td style="padding:5px;"><?php echo ($string)?$string:'<span style="background-color: red; color: #fff">[EMPTY]</div>'; ?></td>
        <?php
		
	}
	?>
    </tr>
    <?php
}
?>
</table>
<?php
//drop it.


