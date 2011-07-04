<?php


function _wpr_newsletter_form_validate(&$info,&$errors,$whetherToValidateNameUniqueness=true)
{
    $errors = array();
    if (empty($_POST['name']))
    {
        $errors[] = __("The newsletter name field was left empty. Please fill it in to continue.");
        $info['name'] = '';
    }
    else if ($whetherToValidateNameUniqueness===true && checkIfNewsletterNameExists($_POST['name']))
    {
        $errors[] = __("A newsletter with that name already exists. ");
        $info['name'] = '';
    }
    else
    {
        $info['name'] = $_POST['name'];
    }
    if (empty($_POST['fromname']))
    {
        $errors[] = __("The 'From Name' field was left empty. Please fill it in to continue. ");
        $info['fromname'] = '';
    }
    else
    {
        $info['fromname'] = $_POST['fromname'];
    }

     if (empty($_POST['fromemail']))
     {
        $errors[] = __("The 'From Email' field was left empty. Please fill it in to continue. ");
        $info['fromemail'] = '';
     }
     else if (!validateEmail($_POST['fromemail']))
     {
         $errors[] = __("The email address provided for 'From Email' is not a valid e-mail address. Please enter a valid email address.");
         $info['fromemail'] = '';
     }
     else
     {
         $info['fromemail'] = $_POST['fromemail'];
     }

     if (empty($_POST['reply_to']))
     {
         $errors[] = _("The 'Reply-To' field was left empty. Please fill in an email address in the reply-to field.");
         $info['reply_to'] ='';
     }
     else if (!validateEmail($_POST['reply_to']))
     {
         $errors[] = _("The 'Reply-To' field was filled with an invalid e-mail address. Please fill in a valid email address.");
         $info['reply_to'] ='';
     }
     else
     {
        $info['reply_to'] = $_POST['reply_to'];
     }
    
     $info['id'] = $_POST['id'];
     
     $info['description'] = $_POST['description'];
     $info['fromname'] = $_POST['fromname'];
     $info['fromemail'] = $_POST['fromemail'];

     $info = apply_filters("_wpr_newsletter_form_validation",$info);
     $errors = apply_filters("_wpr_newsletter_form_validation_errors",$errors);

}

function _wpr_newsletter_edit()
{
    _wpr_setview("newsletter_form");

    if (!_wpr_isset("parameters"))
    {
        $id = $_GET['nid'];
        $newsletter = _wpr_newsletter_get($id);
        _wpr_set("parameters",$newsletter);
    }    
    _wpr_set("heading",__("Edit Newsletter"));
    _wpr_set("edit",true);
    _wpr_set("button_text",__("Save Changes"));
    _wpr_set("wpr_form","newsletter_edit_form");
}

function _wpr_newsletter_edit_form_post_handler()
{
    $info = array();
    $errors = array();
    
    _wpr_newsletter_form_validate($info,$errors,false);
    
    if (count($errors) ===0)
    {
        _wpr_newsletter_update($info);
        $newsletter_home = _wpr_admin_url("newsletter");
        wp_redirect($newsletter_home);
        exit;
    }
    $info = (object) $info;
    _wpr_set("parameters",$info);
    _wpr_set("errors",$errors);
}


function _wpr_newsletter_create_form_post_handler()
{
    $info = array();
    $errors = array();

    _wpr_newsletter_form_validate($info,$errors);

    if (count($errors) ===0)
    {
        _wpr_newsletter_create($info);
        $newsletter_home = _wpr_admin_url("newsletter");
        wp_redirect($newsletter_home);
        exit;
    }
    $info = (object) $info;
    _wpr_set("parameters",$info);
    _wpr_set("errors",$errors);

}


function checkIfNewsletterNameExists($name)
{
    $name = trim($name);
    global $wpdb;
    $query = "SELECT COUNT(*) num FROM ".$wpdb->prefix."wpr_newsletters where name='$name'";
    $results = $wpdb->get_results($query);
    return ($results[0]->num !=0); 
}


function _wpr_newsletter_add()
{
    _wpr_setview("newsletter_form");
    if (!_wpr_isset("parameters"))
    {
        $id = $_GET['nid'];
        $newsletter = _wpr_newsletter_get($id);
        _wpr_set("parameters",$newsletter);
    }
    _wpr_set("heading",__("Create Newsletter"));
    _wpr_set("edit",false);
    _wpr_set("button_text",__("Create Newsletter"));
    _wpr_set("wpr_form","newsletter_create_form");
}

function _wpr_newsletter_home()
{
    global $wpdb;
     _wpr_set("_wpr_view","newsletter_home");
    $getNewslettersListQuery = "SELECT id, name, reply_to, fromname, fromemail FROM ".$wpdb->prefix."wpr_newsletters";
    $newsletterList = $wpdb->get_results($getNewslettersListQuery);

/*    $actual_list = array();
    foreach ($newsletterList as $newsletter)
    {
        $item = array();
        $item['Id'] = $newsletter->id;
        $item['Name'] = $newsletter->name;
        $item['Reply-To'] = $newsletter->reply_to;
        $item['From Name'] = $newsletter->fromname;
        $item['From E-mail'] = $newsletter->fromemail;
        array_push($actual_list,$item);
    }*/ 

    $newsletterList = apply_filters("_wpr_newsletter_home_list",$newsletterList);
    _wpr_set("newsletterList",$newsletterList);

}


function _wpr_newsletter_handler()
{
    $action = @$_GET['act'];
	switch ($action)
	{
            case 'add':
                _wpr_newsletter_add();
                break;
            case 'edit':

                _wpr_newsletter_edit();

                    break;

            case 'delete':

               _wpr_newsletter_delete();

            case 'forms':

               _wpr_newsletter_forms();

             default:
                
                 _wpr_newsletter_home();
	}



}



function _wpr_newsletter_get($id)
{
	global $wpdb;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters where id=$id";

	$result = $wpdb->get_results($query);

	return $result[0];



}

function _wpr_newsletters_get()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";
	$newslettersResult = $wpdb->get_results($query);
	return $newslettersResult;
}

function _wpr_newsletter_update($info)

{

	global $wpdb;

	$info = (object) $info;

	$query = "UPDATE  ".$wpdb->prefix."wpr_newsletters SET name='$info->name', reply_to='$info->reply_to', description='$info->description', confirm_subject='$info->confirm_subject', confirm_body='$info->confirm_body',confirmed_subject='$info->confirmed_subject',confirmed_body='$info->confirmed_body', `fromname`='$info->fromname', `fromemail`='$info->fromemail' where id='$info->id';";

	$result = $wpdb->query($query);
}

function _wpr_newsletter_create($info)

{

	global $wpdb;

	$info = (object) $info;

	$query = "INSERT INTO ".$wpdb->prefix."wpr_newsletters (name,reply_to, description,confirm_subject,confirm_body,confirmed_subject,confirmed_body,fromname,fromemail) values ('$info->name','$info->reply_to','$info->description','$info->confirm_subject','$info->confirm_body','$info->confirmed_subject','$info->confirmed_body','$info->fromname','$info->fromemail');";

	$wpdb->query($query);
}

function _wpr_get_newsletters()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";
	$newsletters = $wpdb->get_results($query);
	if (count($newsletters) > 0)
		return $newsletters;
	else
		return false;
}