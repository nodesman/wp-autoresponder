<?php


/*

This function returns all the transfer rules where $destId is the destination newsletter.
*/
function _wpr_get_transfer_rules_for_destination($destId)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscriber_transfer where dest='$destId';";
	$results = $wpdb->get_results($query);
	return $results;
}


function _wpr_get_transfer_rule($source,$dest)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscriber_transfer where source='$source' and dest='$dest';;";
	$results = $wpdb->get_results($query);
	return $results;
}

function _wpr_get_transfer_rules()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscriber_transfer;";
	$results = $wpdb->get_results($query);
	return $results;
}

function _wpr_subscriber_add_rule_post()
{
	
	global $wpdb;
	if (current_user_can("manage_newsletters"))
	{						 
		$source = $_POST['source'];
		$dest = $_POST['destination'];
		$transferRules = _wpr_get_transfer_rule($source,$dest);
		
		if (count($transferRules) > 0)
		{
			$response[]= "EXISTS";
			$response[]="";			
		}
		else
		{
			$query = "INSERT INTO ".$wpdb->prefix."wpr_subscriber_transfer (source, dest) values ('$source','$dest')";
			$wpdb->query($query);
			
			$rule = _wpr_get_transfer_rule($source,$dest);
			
			$id = $rule[0]->id;
			$response[]='SUCCESS';
			$response[]=$id;
			
		}
		echo json_encode($response);
		exit;
		
	}
}


function _wpr_subscriber_transfer_rule_delete($id)
{
	global $wpdb;
	$id = intval($id);
	$deleteRuleQuery = "DELETE FROM ".$wpdb->prefix."wpr_subscriber_transfer where id=$id;";
	$wpdb->query($deleteRuleQuery);	
}

function _wpr_subscriber_delete_rule_post()
{
	$list = $_POST['rulesList'];
	$ids = explode(",",$list);
	foreach ($ids as $rule)
	{
		_wpr_subscriber_transfer_rule_delete($rule);
	}
	$response = array('SUCCESS','');
	$responseText = json_encode($response);
	echo $responseText;
	exit;
}

add_action('_wpr_sub_transfer_add_rule_post','_wpr_subscriber_add_rule_post',10);
add_action('_wpr_sub_transfer_delete_form_post','_wpr_subscriber_delete_rule_post',10);

function wpr_actions()
{

	?>
    
<div class="wrap">
<h2>Automatic Inter-Newsletter Subscriber Transfer</h2>

<script>
function checkAll(state)
{
	jQuery(".rulecheck").attr({checked: state});
}
</script>

Here you can define rules such that if someone subscribes to newsletter B, they will be automatically unsubscribed from Newsletter A.
<h3>Subscriber Transfer Rules</h3>
<p>
<table id="rulelist" class="widefat" width="1100">

<thead><tr>
<th> <input type="checkbox" name="checker" onChange="checkAll(this.checked)" value="all"></th>
<th> Unsubscribe a subscriber from newsletter....</th>
<th> ...if they subscribe to newsletter</th>
</tr>
</thead>

<tr>
<?php
$rules = _wpr_get_transfer_rules();

foreach ($rules as $name=>$rule)
{
?>
<tr id="rule_<?php echo $rule->id ?>">
   <td ><input class="rulecheck" type="checkbox" name="rules[]" value="<?php echo $rule->id ?>"></td>
   <td ><?php $sourceNewsletter = _wpr_newsletter_get($rule->source); 
   echo $sourceNewsletter->name;
   ?></td>
   <td><?php $destNewsletter = _wpr_newsletter_get($rule->dest); 
   echo $destNewsletter->name; ?></td>
	<?php
}
?>
</table>
<p></p>
<input type="submit" name="submit" onclick="deleteSelected();" value="Delete" class="button-primary" />
<script>
</script>

<script>
var existingRules;

function deleteSelected()
{
	var rulesToDelete = new Array();
        if (!window.confirm('Are you sure you want to delete the selected rules?'))
            {
                jQuery(".rulecheck").each (function() {
                    this.checked=false;
                });
                return false;
            }
	jQuery(".rulecheck").each ( function() {
			var id = this.value;
                        if (this.checked)
                            rulesToDelete.push(id);
	});

	rulesString = rulesToDelete.join(",");
	
	jQuery.post('admin.php?page=wpresponder/actions.php',
				{
					rulesList: rulesString,
					wpr_form:  "sub_transfer_delete_form"
				},
				function(data)
				{
					var result = eval(data);
					if (result[0] == 'SUCCESS')
					{
						removeRows(rulesString);
					}
					else if (result[0] == 'ERROR')
					{
						alert('An unknown error occured. Plesae try again later.');
					}
				});
	
	
}

var debu;
function removeRows(rulesList)
{
	var rulesToDelete = rulesList.split(",");
	debu = rulesToDelete;
	if (rulesToDelete.length > 0)
	{
		for (var i in rulesToDelete)
		{
			rowName = "#rule_"+rulesToDelete[i];
			jQuery(rowName).remove();
		}
		return true;
	}
	else
		return false;					 
}


function dest()
{
	 return document.getElementById('destination');
}
function source()
{
	 return document.getElementById('source');
}
function addRule()
{
	var sid = source().options[source().selectedIndex].value;
	var did = dest().options[dest().selectedIndex].value;
	
	if (sid == did)
	{
		alert("The source and destination newsletters cannot be the same. Please select a different combination");
		return false;
	}
	
	jQuery.post('admin.php?page=wpresponder/actions.php', 
		{
			source: sid,
			destination: did,
			wpr_form: "sub_transfer_add_rule"
		},
		
		function(data) 
		{
			var response = eval(data);
			if (response[0] == 'SUCCESS')
			{

				addRowToTable(response[1],sid,did);
			}
			else if (response[0]=='EXISTS')
			{
				alert("That rule already exists.");
			}
			else
			{
				alert("An unknown internal error occured. The response from the server was not recognized.");
			}
		});
																															  
	
}

function Newsletter(id,name)
{
	this.id=id;
	this.name=name;
}

var Newsletters = new Array();
<?php
$newsletters = _wpr_get_newsletters();

foreach ($newsletters as $newsletter)
{
?>
Newsletters[<?php echo $newsletter->id ?>] = new Newsletter(<?php echo $newsletter->id ?>,"<?php echo $newsletter->name ?>");
<?php
}

?>

function ge(id)
{
	return document.getElementById(id);
}

function addRowToTable(id,sid,did)
{
	var tr = ce("tr");
	tr.setAttribute("id","rule_"+id);
	var td1 = ce("td");
	var input = ce("input");
	
	input.setAttribute("name","rules[]");
	input.setAttribute("value",id.toString());
	input.setAttribute("type","checkbox");
		input.setAttribute("class","rulecheck");
	td1.appendChild(input);
	tr.appendChild(td1);
	
	var td2 = ce("td");
	td2.innerHTML = Newsletters[sid].name;
	
	tr.appendChild(td2);
	
	var td3 = ce("td");
	td3.innerHTML = Newsletters[did].name;
	
	tr.appendChild(td3);
	
	ge('rulelist').appendChild(tr);
}

function ce(tagname)
{
	return document.createElement(tagname);
}




</script>


<h3>Add Rule</h3>

<?php

if (count($newsletters) > 0)
{
?>
<form name="subscriber_transfer">
<table>
    
  <tr>
     <td>Unsubscribe a subscriber from newsletter 
     <select name="source_nid" id="source">
     <?php
	 $newsletters = _wpr_get_newsletters();
	 foreach ($newsletters as $newsletter)
	 {
		?><option value="<?php echo $newsletter->id ?>"><?php echo $newsletter->name ?></option><?php 
	 }
	 ?>
     </select> if they subscribe to newsletter 
      <select name="destination_nid" id="destination">
     <?php
	 $newsletters = _wpr_get_newsletters();
	 foreach ($newsletters as $newsletter)
	 {
		?><option value="<?php echo $newsletter->id ?>"><?php echo $newsletter->name ?></option><?php 
	 }
	 ?>
     </select>.
</td>
</tr>
</table>
<input type="button" class="button-primary" onClick="addRule();" value="Add Rule">
</form>
<?php
}
else
{
?>
You must first <a href="admin.php?page=_wpr/newsletter&act=add">create a newsletter</a> before adding a rule.<?php
}?>

</div>
    <?php	
}

function _wpr_move_subscriber($nid,$email)
{
	global $wpdb;
	$transfer_rules = _wpr_get_transfer_rules_for_destination($nid);	
	foreach ($transfer_rules as $rule)
	{
		$source = $rule->source;
		$query = "UPDATE ".$wpdb->prefix."wpr_subscribers set active=2 WHERE nid=$source and email='$email'";
		$wpdb->query($query);
	}
}

?>