<?php

include "forms.lib.php";

function wpr_subscriptionforms()

{

	if (_wpr_no_newsletters("To create subscription forms"))
		return;

	switch ($_GET['action'])

	{

		case 'create':

		_wpr_subscriptionforms_create();

		break;

		case 'form':

		$id = $_GET['fid'];

		$form = _wpr_subscriptionform_get($id);

		_wpr_subscriptionform_getcode($form,"'".$form->name."' Form HTML Code");

		return;

		break;
		
		
		case 'delete':
		
		if (isset($_POST['forms']))
		{
			$formsToDelete = $_POST['forms'];
			if (count($formsToDelete) >0)
			{
				_wpr_subscriptionforms_delete($formsToDelete);
				_wpr_subscriptionform_delete_done();
			}
			else
			{
				_wpr_subscriptionform_delete_notfound();
			}
		}
		else
		{
			_wpr_subscriptionform_delete_notfound();
			
		}

		
		break;

		case 'edit':

                    
		$id = (int) $_GET['fid'];



		$form = _wpr_subscriptionform_get($id);



		if (isset($_POST['fid']))

		{

			$checkList = array("name"=>"Name field is required","confirm_subject"=>"E-Mail Confirmation Subject Field is required","confirm_body"=>"E-Mail Confirmation Body field","confirmed_subject"=>"Confirmed Subscription subject field is required","confirmed_body"=>"Confirmed subscription body field is required");

			$errors = array();

			foreach ($checkList as $field=>$errorMessage)

			{
				$theValue = $_POST[$field];
				$theValue = trim($theValue);
				if (empty($theValue))
				{
					$errors[] = $checkList[$field];
				}

			}			

			if (count($errors) == 0)

			{		

				$info['id'] = $_POST['fid'];

				$info['name'] = $_POST['name'];

				$info['return_url'] = $_POST['return_url'];
				
				if (preg_match("@autoresponder_[0-9]+@",$_POST['followup']))
				{
					$followup = "autoresponder";
					$followupid = str_replace("autoresponder_","",$_POST['followup']);
				}
				else if (preg_match("@postseries_[0-9]+@",$_POST['followup']))
				{
					$followup = "postseries";
					$followupid = str_replace("postseries_","",$_POST['followup']);
				}
				else
				{
					$followup = "none";
					$followupid = 0;
				}

				$info['followup_type'] = $followup;

				$info['followup_id'] = $followupid;
				
				switch ($_POST['blogsubscription'])
				{
					case 'none':
					case 'all':
						$blogSubscription = $_POST['blogsubscription'];
						break;
					default:				
						if (preg_match("@category_[0-9]+@",$_POST['blogsubscription']))
						{
							$blogSubscription = "cat";
							$blogCategory = str_replace("category_","",$_POST['blogsubscription']);
						}
					
				}

				$info['blogsubscription_type'] = $blogSubscription;

				$info['blogsubscription_id'] = $blogCategory;
				
				$info['submit_button'] = $_POST['submit_value'];

				$info['custom_fields'] = (isset($_POST['custom_fields']) && is_array($_POST['custom_fields']))?implode(",",$_POST['custom_fields']):"";

				$info['confirm_subject'] = $_POST['confirm_subject'];

				$info['confirm_body'] = $_POST['confirm_body'];

				$info['nid'] = $_POST['newsletter'];

				$info['confirmed_subject'] = $_POST['confirmed_subject'];

				$info['confirmed_body'] = $_POST['confirmed_body'];

				_wpr_subscriptionform_update($info);

				$form = _wpr_subscriptionform_get($info['id']);

				_wpr_subscriptionform_getcode($form,"Form Saved");

				return;

			}

			else 

			$form = (object) $_POST;

		}		

		_wpr_subscriptionform_form($form,$errors);		

		break;

		default:

		_wpr_subscriptionforms_list();

	}

}

function _wpr_subscriptionform_delete_notfound()
{
	?>

<div class="wrap">
  <h2>Invalid Input: No forms were specified.</h2>
  Did you visit this page directly? Click on the button below to go to the subscription forms list.<br />
  <input type="button" onclick="window.location='admin.php?page=wpresponder/subscriptionforms.php';" class="button-primary" value="&laquo; Back To Subscription Forms List">
</div>
<?php
}
function _wpr_subscriptionform_delete_done()
{
	?>
<div class="wrap">
  <h2>The Selected Subscription Forms Have Been Deleted</h2>
  <br />
  <br />
  <a href="admin.php?page=wpresponder/subscriptionforms.php" class="button-primary">&laquo; Back To Subscription Forms List</a> </div>
<?php
}

function _wpr_subscriptionforms_delete($list)
{
	global $wpdb;
	$formItems = implode(",",$list);
	$formItems = "($formItems)";
	$query = "DELETE FROM ".$wpdb->prefix."wpr_subscription_form where id in $formItems;";
	$wpdb->query($query);
	
}

function _wpr_subscriptionforms_list()
{
	global $wpdb;
        $tprefix = $wpdb->prefix;
	$query = "SELECT a.* FROM ".$tprefix."wpr_subscription_form a, ".$tprefix."wpr_newsletters b where a.nid=b.id;";
	$forms = $wpdb->get_results($query);

	?>
<div class="wrap">
  <h2>Subscription Forms</h2>
</div>
<script>
function selectAllFormsCheckBox(state)
{
	jQuery(".forms_check").attr({ checked: state});
}
</script>
<ul style="padding:20px;">
   <li>Click on <em>Create New Form</em> button below to create a new subscription form.  To place the newly created subscription form in the sidebar of your blog, go to the <a href="widgets.php">Widgets section</a>. To place the subscription form in a separate page or another website, copy the generated HTML code for the form and paste the code it in your own HTML page. 
</ul>
<form name="formslist" action="admin.php?page=wpresponder/subscriptionforms.php&action=delete" method="post">
  <table class="widefat" style="margin: 10px; margin:10px 0px;;">
    <thead>    <tr>
    <th><input type="checkbox" name="selectall" value="1" onclick="selectAllFormsCheckBox(this.checked);" /></th>
      <th scope="col">Name</th>
      <th>Newsletter</th>
      <th>Follow-Up</th>
      <th>Blog Subscription</th>
      <th scope="col">Actions</th>

    </tr>      </thead>
    <?php
	
	if (count($forms) > 0 )
	{
		foreach ($forms as $form)
		{
	
			?>
		<tr>
		  <td  align="center"width="20"><input type="checkbox" name="forms[]" class="forms_check" value="<?php echo $form->id ?>" /></td>
		  <td><?php echo $form->name ?></td>
		  <td><a href="admin.php?page=wpresponder/subscribers.php&action=nmanage&nid=<?php echo $form->nid ?>">
			<?php
		$newsletter = _wpr_newsletter_get($form->nid);       
		echo $newsletter->name;	
		?>
			</a></td>
		  <td><?php
		
		switch ($form->followup_type)
		{
			case 'postseries':
			$postseries = _wpr_postseries_get($form->followup_id);
			echo "Subscribe to the '".$postseries->name."' post series";
			break;
			
			case 'autoresponder':
			$autoresponder = _wpr_autoresponder_get($form->followup_id);
			echo "Subscribe to the '".$autoresponder->name."' autoresponder.";
			break;
			
			case 'none':
			echo "None";		
			break;
		}
		?></td>
		  <td><?php
		switch ($form->blogsubscription_type)
		{
			case 'cat':
			
			$category = get_category($form->blogsubscription_id);
			echo "Posts in the ".$category->name." category";
			break;
			
			case 'all':
			echo "All Blog Posts ";
			break;
			case 'none':
			echo "No blog subscription";
			break;
			
		}
		
		?>
		  <td><a href="admin.php?page=wpresponder/subscriptionforms.php&action=edit&fid=<?php echo $form->id ?>" class="button">Edit</a>&nbsp;<a href="admin.php?page=wpresponder/subscriptionforms.php&action=form&fid=<?php echo $form->id ?>" class="button">Get Form HTML</a></td>
		</tr>
		<?php
	
		}
	}
	else
	{
		?>
        <tr>
        <td colspan="10"><div align="center"><big>--No subscription forms defined. <a href="admin.php?page=wpresponder/subscriptionforms.php&action=create">Click here</a> to create one now--</big>
        </div></td>
        </tr>
        <?php
		
	}

?>
</td></td>
  </table>
  <input type="submit" name="submit" value="Delete Forms" class="button" onclick="return confirm('Are you sure you want to delete the selected subscription forms?');" />
  <input type="button" onclick="window.location='admin.php?page=wpresponder/subscriptionforms.php&action=create';" class="button" value="Create New Form">
</form>
<?php

}



function _wpr_subscriptionform_getcode($form,$title)

{

		?>
<div class="wrap">
  <h2><?php echo $title ?></h2>

The form has been saved. 

<h3>Now place the subscription form in the sidebar to start gathering subscribers.</h3>

<a href="widgets.php"><img src="<?php echo get_bloginfo("url"); ?>/?wpr-file=widget-help.png" title="Click to go to Widgets Section" border="0"/></a>

<h3>Click on image to go to Widgets Section</h3>

<h2>Alternatively...</h2>

Copy and paste the code in the box below on the page where you want the subscription form to appear.

<h3>Form Code:</h3>
<?php $code = _wpr_subscriptionform_code($form); ?>
<textarea rows="20" cols="70" id="wpr_code"><?php echo $code ?></textarea>
<br />
<div style="display:none" id="preview"> <?php echo $code ?> </div>
<script>

var preview;

function preview()

{

	preview = window.open('about:blank','previewWindow','top=20,left=20,width=300,height=500');

	preview.document.write(document.getElementById('preview').innerHTML);

}

</script>
<a href="admin.php?page=wpresponder/subscriptionforms.php" class="button">&laquo; Back To Forms</a>&nbsp;
<input type="button" value="Select All" onclick="document.getElementById('wpr_code').select();" class="button"/>
<input type="button" onclick="preview();" value="Preview" class="button" />
</div>
<?php
}



function _wpr_subscriptionform_code($form,$enableForm=false)
{
	$url = get_bloginfo('home');			
	ob_start();
		?>
<form action="<?php echo $url?>/?wpr-optin=1" method="post">
  <span class="wpr-subform-hidden-fields">
  <input type="hidden" name="blogsubscription" value="<?php echo $form->blogsubscription_type ?>" />
  <?php if ($form->blogsubscription_type == "cat") { ?>
  <input type="hidden" name="cat" value="<?php echo $form->blogsubscription_id ?>" />
  <?php

} 

if (!empty($form->followup_type) && $form->followup_type != "none")

{ 

?>
  <input type="hidden" name="followup" value="<?php echo $form->followup_type ?>" />
  <input type="hidden" name="responder" value="<?php echo $form->followup_id ?>" />
  <input type="hidden" name="comment" value="" style="display:none" />
  <?php

} ?>
  <input type="hidden" name="newsletter" value="<?php echo $form->nid ?>" />
  <?php if (isset($form->id)) { ?>
    <input type="hidden" name="fid" value="<?php echo $form->id ?>" />
    <?php } ?>
  </span>
  <table>
    <tr>
      <td><span class="wprsfl wprsfl-name">Name:</span></td>
      <td><span class="wprsftf wpr-subform-textfield-name">
        <input type="text" name="name" /></td>
    </tr>
    <tr>
      <td><span class="wprsfl wprsfl-email">E-Mail Address:</span></td>
      <td><span class="wprsftf wpsftf-email">
        <input type="text" name="email" />
        </span>
    </tr>
    <?php
	if (!empty($form->custom_fields))

	{

		$formItems = array();

		$formItems = explode(",",$form->custom_fields);

		foreach ($formItems as $field)

		{

			$theField = _wpr_newsletter_custom_fields_get($field);

			
                       $fieldName = str_replace('"','',$theField->id);
                       
			switch ($theField->type)

			{

				case 'enum':

				   $choices = explode(",",$theField->enum);

				   ?>
    <tr>
      <td><span class="wprsfl wprsfl-<?php echo $fieldName ?> wprsfl-<?php echo $fieldName ?>-<?php echo $form->id ?>"><?php echo $theField->label ?></span></td>
      <td><span class="wprsfsf wprsf-<?php echo $fieldName ?>">
        <select name="cus_<?php echo base64_encode($theField->name) ?>">
          <?php
foreach ($choices as $choice)
{
?>
          <option><?php echo $choice ?></option>
          <?php
}

				   ?>
        </select>
        </span></td>
    </tr>
    <?php

				 break;

				case 'text':

				?>
    <tr>
      <td><span class="wprsfl wprsfl-<?php echo $fieldName ?> wprsfl-<?php echo $fieldName ?>-<?php echo $form->id ?>"><?php echo $theField->label ?></td>
      <td><span class="wprsftf wprsftf-<?php echo $fieldName ?> wprsftf-<?php echo $fieldName ?>-<?php echo $form->id ?>">
        <input type="text" name="cus_<?php echo base64_encode($theField->name) ?>" />
    </tr>
    <?php

				

				break;

				case 'hidden':

				?>
    <input type="hidden" class="wprsfhf wprsfhf-<?php echo $fieldName ?> wprsfhf-<?php echo $fieldName ?>-<?php echo $form->id ?>">
    " name="cus_<?php echo base64_encode($theField->name); ?>" value="<?php echo $_POST['field_'.$theField->id."_value"] ?>" />
    <?php

				break;

			}
		}
	}

	?>
    <tr>
      <td colspan="2" align="center"><input type="submit" value="<?php echo (empty($form->submit_button))?"Subscribe":$form->submit_button; ?>" /></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><?php if ($enableForm) { echo base64_decode("PGEgc3R5bGU9ImZvbnQtZmFtaWx5OlZlcmRhbmEsIEdlbmV2YSwgc2Fucy1zZXJpZjtmb250LXNpemU6IDlweDsiIGhyZWY9Imh0dHA6Ly93d3cud3ByZXNwb25kZXIuY29tIj5FbWFpbCBNYXJrZXRpbmcgYnkgV1AgQXV0b3Jlc3BvbmRlcjwvYT4="); } ?></td>
    </tr>
  </table>
</form>
<?php

    $form = ob_get_clean();

	return $form;

}



function _wpr_subscriptionforms_create()

{

	global $wpdb;

	$fieldsToSelect = array(); //just initializing the custom fields to be selected when the form loads..	

	if (isset($_POST['newsletter']))

	{

		

		$checkList = array("name"=>"Name field is required","confirm_subject"=>"E-Mail Confirmation Subject Field is required","confirm_body"=>"E-Mail Confirmation Body field","confirmed_subject"=>"Confirmed Subscription subject field is required","confirmed_body"=>"Confirmed subscription body field is required");

		$errors = array();

		foreach ($checkList as $field=>$errorMessage)

		{


			$theValue = trim($_POST[$field]);
			
			if (empty($theValue))
			{
				$errors[] = $checkList[$field];
			}

			

		}

		$info['name'] = $_POST['name'];

			$info['return_url'] = $_POST['return_url'];
			
			
			if (preg_match("@autoresponder_[0-9]+@",$_POST['followup']))
			{
				$followup = "autoresponder";
				$followupid = str_replace("autoresponder_","",$_POST['followup']);
			}
			else if (preg_match("@postseries_[0-9]+@",$_POST['followup']))
			{
				$followup = "postseries";
				$followupid = str_replace("postseries_","",$_POST['followup']);
			}
			else
			{
				$followup = "none";
				$followupid = 0;
			}

			$info['followup_type'] = $followup;
			$info['followup_id'] = $followupid;
			
			
			switch ($_POST['blogsubscription'])
			{
				case 'none':
				case 'all':
					$blogSubscription = $_POST['blogsubscription'];
					break;
				default:				
				    if (preg_match("@category_[0-9]+@",$_POST['blogsubscription']))
					{
						$blogSubscription = "cat";
						$blogCategory = str_replace("category_","",$_POST['blogsubscription']);
					}
				
			}
			
			$info['submit_button'] = $_POST['submit_value'];
						   
			

			$info['blogsubscription_type'] = $blogSubscription;

			$info['blogsubscription_id'] = $blogCategory;

			$info['custom_fields'] = (is_array($_POST['custom_fields']))?implode(",",$_POST['custom_fields']):"";

			$info['confirm_subject'] = $_POST['confirm_subject'];

			$info['confirm_body'] = $_POST['confirm_body'];

			$info['nid'] = $_POST['newsletter'];

			$info['confirmed_subject'] = $_POST['confirmed_subject'];

			$info['confirmed_body'] = $_POST['confirmed_body'];
			
			


		if (count($errors) == 0)

		{

			_wpr_subscriptionform_create($info);

			$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscription_form where name='".$info['name']."';";

			$form = $wpdb->get_results($query);

			$form = $form[0];

		     _wpr_subscriptionform_getcode($form,"Form Created");

			return;

		}

		$params = (object) $info;	

	}

	

	_wpr_subscriptionform_form($params,$errors);

}



function _wpr_subscriptionform_form($parameters=array(),$errors=array())

{

	$parameters = (object)$parameters;
        

        if (!empty($parameters->custom_fields))

            $fieldsToSelect = explode(",",$parameters->custom_fields);

	global $wpdb;



	?>
<div class="wrap">
  <h2>Create Subscription Form</h2>
</div>
<script>



/*


The function is used to ensure that only valid inputs can be given to the autoresponder
selection field.


*/
function Autoresponder(id,name)
{
	this.id=id;
	this.name=name;
}
var AutorespondersOfNewsletters=new Array();

<?php

$listOfAutorespondersOfNewsletters = _wpr_get_newsletters();
foreach ($listOfAutorespondersOfNewsletters as $count=>$news)
{
	?>
AutorespondersOfNewsletters['<?php echo $news->id; ?>'] = new Array();
	<?php
	$autoresponders = _wpr_get_autoresponders_of_newsletter($news->id);
	foreach ($autoresponders as $autoresponder)
	{
		$aid = intval($autoresponder->id);
		$name = $autoresponder->name;
		
		if ($aid==0 || empty($name))
		{
			continue;
		}
		?>
AutorespondersOfNewsletters['<?php echo $news->id ?>'].push(new Autoresponder(<?php echo $aid?>,"<?php echo $name ?>"));
		<?php
	}
}

?>
function autoresponderDropDownBox()
{
	return document.getElementById('autoresponders_list');
}



function updateAutorespondersOption(currentNid)
{
	 if (AutorespondersOfNewsletters[currentNid]!=undefined)
	 {
		 
		 var listOfResponders = AutorespondersOfNewsletters[currentNid];

		 if (listOfResponders.length!=0)
		 {
			 //remove the options in the autoresponder series drop down box.
			 emptyAutoresponderFields();
			 
			 var countOfOptions=0;
			 for (var newopt in listOfResponders)
			 {
				 var theOpt = document.createElement("option");
				 theOpt.setAttribute("value","autoresponder_"+listOfResponders[newopt].id);
				 theOpt.innerHTML = listOfResponders[newopt].name;
				 autoresponderDropDownBox().appendChild(theOpt);
			 }
		 }
		 else
		 {
			 emptyAutoresponderFields();			 		 
		 }
		 
	 }
	 else
	 {
  		 emptyAutoresponderFields();
		 return false;
	 }
}


function _wpr_validate_subform_form_fields()
{
	var titleField =document.getElementById('formnamefield');	
	if (titleField.value.length==0)
	{
		alert("A name is required for this form. Please enter a name.");
		titleField.focus();
		return false;
	}
	
	
	var newsletterField = document.getElementById('newsletterlist');
	if (newsletterField.value=="")
	{
		alert("You must select a newsletter to which this subscription form will add subscribers.");
		newsletterField.focus();
		return false;
	}
	
	var confirmS = document.getElementById('confirms');
	if (jQuery.trim(confirmS.value).length==0)
	{
		alert("You must enter a subject for the confirm subscription e-mail.");
		confirmS.value='';
		confirmS.focus();
		return false;
	}
	
	
	var confirmB = document.getElementById('confirmb');
	if (jQuery.trim(confirmB.value).length==0)
	{
		alert("You must enter a body for the confirm subscription e-mail.");
		confirmB.value='';
		confirmB.focus();
		return false;
	}
	
	var confirmedS = document.getElementById('confirmeds');
	if (jQuery.trim(confirmedS.value).length==0)
	{
		alert("You must enter a subject for the confirmed subscription e-mail.");
		confirmedS.value='';
		confirmedS.focus();
		return false;
	}
	
	var confirmedB = document.getElementById('confirmedb');
	if (jQuery.trim(confirmedB.value).length==0)
	{
		alert("You must enter a body for the confirmed subscription e-mail.");
		confirmedB.value='';
		confirmedB.focus();
		return false;
	}
	
	var returnurlf = document.getElementById('returnurlfield');
	returnurl = returnurlf.value;
	if (jQuery.trim(returnurl).length !=0 && !checkURL(returnurl))
	{
		alert("The value in the return URL field should be a HTTP url. Please correct it or leave the field empty.");
		returnurlf.value='';
		returnurlf.focus();
		return false;	
	}
	
	return true;
	
	
	
	
}


function checkURL(value) 
{
  var urlregex = new RegExp(
        "^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.|http:\/\/){1}([0-9A-Za-z]+\.)");
  if(urlregex.test(value))
  {
    return(true);
  }
  return(false);
}


function emptyAutoresponderFields()
{
        jQuery("#autoresponders_list").children().each( function () 
													{
				jQuery(this).remove();										
														
		});
}

function Field(id,name,type,label,choices)

{

	this.name = name;
	this.id = id;
	this.type = type;
	this.label = label;
	this.choices = choices;

}




var Fields = new Array();

<?php

$query ="SELECT * FROM ".$wpdb->prefix."wpr_custom_fields";

$customfields = $wpdb->get_results($query);

$count=0;


$newsletterlist = array();

foreach ($customfields as $field)
{
	$newsletterlist[] = $field->nid;
}

if (count($newsletterlist))
	$newsletterlist = array_unique($newsletterlist);	
?>
var NewsletterFields = Array();
	<?php 
	foreach ($newsletterlist as $newsletter) 
	{ ?>
NewsletterFields['<?php echo $newsletter; ?>'] = new Array();
	<?php 
	} 
	
	foreach ($customfields as $field)
	{
		?>		
NewsletterFields['<?php echo $field->nid ?>'].push(new Field('<?php echo $field->id ?>','<?php echo addslashes($field->name) ?>','<?php echo addslashes($field->type); ?>','<?php echo addslashes($field->label); ?>','<?php echo addslashes($field->enum) ?>'));
<?php
	
	}
?>
var customFieldList = new Array();
function showFields(elements)
{
	var fieldsCode;
	if (elements && elements.length > 0)
		document.getElementById('customfields').innerHTML = '';			
	else
		return;
	for (element in elements)
	{
		field = elements[element];
		var element = document.createElement("div");
		customFieldList.push(element);
		element.setAttribute("style","border: 1px solid #ccc; padding: 10px;");



		var formelement;

		    var check = document.createElement("input");

			check.setAttribute("type","checkbox");

			check.setAttribute("name","custom_fields[]");

			check.setAttribute("value",field.id);

			check.setAttribute("id","custom_"+field.id);

			element.appendChild(check);

			element.innerHTML += " "+field.name+"<br />";

			preview = document.createElement("div");

			preview.innerHTML += field.label +":";		

			preview.setAttribute("style","background-color: #ddd; border: 1px solid #eee; padding: 10px;");

			if (field.type == "text")

			{

				element.innerHTML += "Type: One Line Text <br /><strong>Preview: <br />";

				formelement = document.createElement("input");

				formelement.setAttribute("type","text");

			}

			else

			{

				formelement = document.createElement("select");

				

				var choices = field.choices.split(",");

				element.innerHTML += "Type: Multiple Choice<br /><strong>Preview: <br />";

				for (option in choices)

				{

					optionElement = document.createElement("option");

					optionElement.text = choices[option];

					formelement.add(optionElement,null);

				}

			}

			preview.appendChild(formelement);

			element.appendChild(preview);			

			element.innerHTML += "<br>";



		document.getElementById('customfields').appendChild(element);			

	}



}

var autoresponderToBeSelected = '<?php echo ($parameters->followup_type == "postseries")?"postseries_":"autoresponder_";
echo $parameters->followup_id; ?>';


function setValueOfAutoresponderField()
{
	document.getElementById('followup_field').value=autoresponderToBeSelected;
}
function load(id)
{
	document.getElementById('customfields').innerHTML="<div align=\"center\">--None--</div>";
	showFields(NewsletterFields[id]);
}

var toSelect = new Array(); //custom field ids to select.

<?php


if (count($fieldsToSelect) > 0)

{

	?>	<?php

	foreach ($fieldsToSelect as $num=>$field)

	{

?>

toSelect[<?php echo $num; ?>] = <?php echo $field; ?>;



<?php

	}

	

}

function loadFollowUpAutoresponderList()
{
    
}

?>jQuery(document).ready(function() {

    

	var selectedNewsletter = document.getElementById('newsletterlist').options[document.getElementById('newsletterlist').selectedIndex].value;

	showFields(NewsletterFields[selectedNewsletter]);
	updateAutorespondersOption(selectedNewsletter);
	setValueOfAutoresponderField();
	
	//if this form is being used to edit, then select the fields that were saved..
	for (var i in toSelect)
	{
		document.getElementById('custom_'+toSelect[i]).checked=true;
	}

});

</script>
<?php if (count($errors) >0)

{

	?>
<div class="updated fade">
  <ul>
    <?php 

	foreach ($errors as $error)

	{

		echo '<li>'.$error.'</li>';

	}

	?>
  </ul>
</div>
<?php

}

?>
<div style="display:none">
  <?php 

$query = "SELECT id from ".$prefix."wpr_newsletters";

$newsletters  = $wpdb->get_results($query);

foreach ($newsletters as $newsletter)

{

	$nid = $newsletter->id;

	?>
  <div id="fields-<?php echo $nid?>">
    <?php 

   $query = "SELECT * FROM ".$prefix."wpr_custom_fields where nid=$nid";

   $customFields = $wpdb->get_results($query);

   foreach ($customFields as $field)

   {

?>
    <div class="field"> Name Of Field: <?php echo $field->name ?><br />
      Field Label: <?php echo $field->label ?><br />
      <?php



	   switch ($field->type)

	   {

		   case 'text':

?>
      Type: One Line Text
      
      Preview:
      <input type="text" size="30" />
      <?php

		   break;

		   case 'enum':

		   $choices = $field->enum;

		   $choices = explode(",",$choices);	   

?>
      Type: Multiple Choice<br />
      Preview:
      <select>
        <?php

 foreach ($choices as $choice)

 {

	 ?>
        <option><?php echo $choice ?></option>
        <?php

 }

 ?>
      </select>
      <?php

		   break;

		   case 'hidden':

		   ?>
      Type: Hidden<br />
      Preview: Hidden fields aren't visible on the page.
      <?php

		   break;

	   }

  ?>
    </div>
    <?php

   }

   ?>
  </div>
  <?php

}

?>
</div>
<form action="<?php print $_SERVER['REQUEST_URI'] ?>" method="post">
  <input type="hidden" value="<?php echo $parameters->id  ?>"  name="fid"/>
  <table width="700">
    <tr>
      <td><strong>Name:</strong>
        <p><small>This form's settings will be saved. This name will be used to identify the settings.</small></p></td>
      <td><input type="text" id="formnamefield" name="name" size="60" value="<?php echo $parameters->name ?>" /></td>
    </tr>
    <tr>
      <td><strong>Newsletter:</strong>
        <p><small>Select the newsletter to which subscribers will be subscribed when filling this form.</small></p></td>
      <td><select name="newsletter" id="newsletterlist" onchange="load(this.options[this.selectedIndex].value);updateAutorespondersOption(this.options[this.selectedIndex].value);">
          <?php

		  $query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";

		  $newsletters = $wpdb->get_results($query);
		  
		  if (count($newsletters)>0)
		  {
			  ?>
          <option></option>
          <?php
			  
			  foreach ($newsletters as $newsletter)
			  {
	
				  ?>
          <option value="<?php echo $newsletter->id; ?>" <?php 
	
				  if ($parameters->nid == $newsletter->id) 
	
				  {
	
					  echo 'selected="selected"';
	
				  } ?>><?php echo $newsletter->name; ?></option>
          <?php
	
			  }
		  }

		  ?>
        </select>
    </tr>
    <tr>
      <td width="300"><strong>Return URL:</strong>
        <p><small> The subscriber is sent to this url after entering their name and email address in the subscription form. </small></p></td>
      <td><input type="text" id="returnurlfield" name="return_url" size="60" value="<?php echo $parameters->return_url ?>" /></td>
    </tr>
    <tr>
      <td><strong>Blog Subscription</strong>:
        <p> <small> Specify what kind of blog subscription will those who use this form will have:</small></p></td>
        
      <td>
      
      <select name="blogsubscription">
          <option value="none" <?php if ($parameters->blogsubscription_type=="none") { echo 'selected="selected"'; } ?>>None</option>
          <option value="all" <?php if ($parameters->blogsubscription_type=="all") { echo 'selected="selected"'; } ?>>Subscribe to all new posts on
          <?php bloginfo("name") ?>
          </option>
          <optgroup label="Particular Blog Category:">
          <?php
                $args = array(
                                            'type'                     => 'post',
                                            'child_of'                 => 0,
                                            'orderby'                  => 'name',
                                            'order'                    => 'ASC',
                                            'hide_empty'               => false,
                                            'hierarchical'             => 0);

		 $categories = get_categories($args);

                 foreach ($categories as $category)
                 {
                     ?>
          <option value="category_<?php echo $category->term_id; ?>" <?php if ($parameters->blogsubscription_type=="cat" && $parameters->blogsubscription_id == $category->term_id) echo 'selected="selected"'; ?>><?php echo $category->name ?></option>
          <?php
                 }
                ?>
          </optgroup>
        </select></td>
    </tr>
    <tr>
      <td><strong>Follow Up Subscription:</strong>
        <p> <small>Select what content should follow-up a successful subscription.</small></p></td>
      <td><select name="followup" id="followup_field">
          <option value="none" <?php

		  if ($parameters->followup_type == 'none' || empty($parameters->followup_type))

		  {

			  echo 'checked="checked"';

		   }  ?> >None</option>
          <optgroup id="autoresponders_list" label="Autoresponders:"> </optgroup>
          <?php
            $query = "SELECT * FROM ".$wpdb->prefix."wpr_blog_series";
            $blogseries = $wpdb->get_results($query);

            if (count($blogseries))
            {
                ?>
          <optgroup label="Post Series:">
          <?php
                foreach ($blogseries as $bseries)
                {
                ?>
          <option value="postseries_<?php echo $bseries->id ?>" <?php if ($parameters->followup_type == "postseries" && $parameters->followup_id == $bs->id) echo 'selected="selected"'; ?>><?php echo $bseries->name ?></option>
          <?php

                }
                ?>
          </optgroup>
          <?php
            }
?>
        </select></td>
    </tr>
    <tr>
      <td><strong>Submit Button Text:</strong>
      <p>
      <small>The label that will be used for the subscription form submit button</small></p>
      </td>
      <td><input type="text" size="60" name="submit_value" value="<?php echo ($parameters->submit_button)?$parameters->submit_button:"Subscribe"; ?>" ></td>
    </tr>
    <tr>
      <td colspan="2"><div class="wrap">
          <h3>More Form Fields</h3>
          <hr size="1" color="black">
          <p>Select the custom fields that should be added to the in the opt-in form.</p>
        </div>
        <div id="customfields"> </div></td>
    </tr>
    <tr>
      <td><h3> Confirmation E-Mail:</h3>
        <table>
          <tr>
            <td>Subject:</td>
            <td><input type="text" id="confirms" name="confirm_subject" size="70" value="<?php



   if (!$parameters->confirm_subject) 

   {

		$confirm_subject = get_option('wpr_confirm_subject');

		echo $confirm_subject;

   }

   else

   {

	      echo $parameters->confirm_subject;

   }

   ?>" /></td>
          </tr>
          <tr>
            <td colspan="2"> Message Body:<br />
              <textarea id="confirmb" name="confirm_body" rows="10" cols="60" wrap="hard"><?php 

if (!$parameters->confirm_body) 

{

	$confirm_email = get_option('wpr_confirm_body');

	echo $confirm_email;

}

else

{

	echo $parameters->confirm_body;

}

	?>
</textarea></td>
          </tr>
        </table>
        <h3>Subscription Confirmed E-Mail:</h3>
        <table>
          <tr>
            <td>Subject:</td>
            <td><input id="confirmeds" type="text" name="confirmed_subject" value="<?php echo ($parameters->confirmed_subject)?$parameters->confirmed_subject:get_option("wpr_confirmed_subject"); ?>" size="60" /></td>
          </tr>
          <tr>
            <td colspan="2"> Message Body:<br />
              <textarea id="confirmedb" name="confirmed_body" rows="10" cols="60"><?php echo ($parameters->confirmed_body)?$parameters->confirmed_body:get_option("wpr_confirmed_body"); ?></textarea></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td colspan="2"><input class="button" type="submit" onclick="return _wpr_validate_subform_form_fields()" value="Create Form And Get Code" />
        &nbsp;<a class="button" href="admin.php?page=wpresponder/subscriptionforms.php">Cancel</a></td>
    </tr>
  </table>
</form>
<?php



}

