<?php

if (!current_user_can("manage_newsletters"))
{
	exit;
}


$currentDir = str_replace("preview_email.php","",__FILE__);
include "$currentDir/lib/swift_required.php";



global $wpdb;
//The arguments that are to be give to this page: (via Javascript)
/*
 * 1. The email html body
 * 2. The email text body
 * 3. The subject of the email
 * 4. Whether the email should attach images with the email body
 * 5. From name to be used
 * 6. From email to be used
 */


//die if this is not a adminstrator's session
if (! current_user_can("manage_newsletters") )
{
    echo "You are not authorized to view this page.";
    exit;
}

if (!isset($_GET['nid']))
    {
    echo "Invalid Request. No newsletter specified";
    exit;
}


$nid = $_GET['nid'];
$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters where id=".$nid;
$results = $wpdb->get_results($query);
if (count($results)==0)
    {
    echo "The specified newsletter doesn't exist";
    exit;
}

/*
This function is used to send the email.
$params 

'to' =  The email address to which the email is to be sent
'from' = The email address from which the email is set to be sent
'subject' = The subject of the email
'textbody' = The text body of the email
'htmlbody' = The html body of the email
'htmlenable' = Whether html email is enabled.
'attachimages' = Whether the images are attached with the body of the message
*/
function sendTheEmail($parameters)
{

   //replace the custom field placeholders with their values.

    $fieldsToSubstitute = $parameters;

    //the following fields should not be in the fields to substitute array
    $fieldsToRemove = array("to","from","fromname","htmlenabled","attachimages","textbody","htmlbody");
	
	//we want the 'to' - the email address of the receipient to actually be named 'email'
	$fieldsToSubstitute['email'] = $fieldsToSubstitute['to'];
	
    foreach ($fieldsToRemove as $fieldToRemove)
        {
        unset($fieldsToSubstitute[$fieldToRemove]);
    }
	

	

    foreach ($fieldsToSubstitute as $fieldName=>$value)
    {
        substitutePlaceHoldersWithValues($parameters['subject'],$fieldsToSubstitute);
        substitutePlaceHoldersWithValues($parameters['htmlbody'],$fieldsToSubstitute);
        substitutePlaceHoldersWithValues($parameters['textbody'],$fieldsToSubstitute);
    }
    //finsihed replacing the custom fields placeholers with their values.   
  
  dispatchEmail($parameters);

}


/*
 * This function is used to repalce all occurances of
 * the placeholders which are or the form [!placeholdername!]
 * with their values
 *
 * The $message argument is the string that has the occurances
 * The $parameters array is a array of name-value pairs.
 * The name is the placeholdername without the [! and !]
 * The value is the value to be substituted
 */
function substitutePlaceHoldersWithValues(&$message,$parameters)
{
    foreach ($parameters as $name=>$value)
    {
        $message = str_replace("[!$name!]",$value,$message);
    }
}

/*

function showPreviewForm()
This function generates the form for the preview email dialog window.

$params = 

'textbody' = The text body of the email
'htmlbdoy' = The html body of the email
'attachimages' = Whether the images are to be attached to the email.
'nid' =  newsletter id

*/



function showPreviewForm($params)
{
   global $wpdb;
   $textbody = base64_encode($params['textbody']);
   $attachImages = $params['attachimages'];
   $htmlbody = base64_encode($params['htmlbody']);
   $htmlenable = $params['htmlenable'];
   
   //get a list of custom fields for this newsletter
   
   
   $nid = $params['nid'];
   $query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid=$nid";
   $listOfCustomFields = $wpdb->get_results($query);

   //get the newsletter record so that we can get the from name and from email

    $query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters where id=$nid";
    $newsletterResults = $wpdb->get_results($query);
    $newsletter = $newsletterResults[0];
    
?>
<style>
    * {
        font-family:  Verdana;
    }
}
    </style>
<script>


    function createHiddenField(name,value)
    {
        var theField = document.createElement("input");
        theField.setAttribute("type", "hidden");
        theField.setAttribute("name",name);
        theField.setAttribute("value", value);
        return theField;
    }
function addHiddenElement(theElement)
{
    document.getElementById("hiddenfields").appendChild(theElement);

}
function load()
{
    var htmlbody = opener.wpr_GetHtmlBody();
    var textbody = opener.wpr_GetTextBody();
    var whetherHtmlEnabled = opener.wpr_GetWhetherHtmlEnabled();
    var whetherToAttachImages = opener.wpr_CheckWhetherImagesShouldBeAttached();
    var subject = opener.wpr_GetSubject();
    addHiddenElement(createHiddenField("subject",subject));
    addHiddenElement(createHiddenField("htmlbody",htmlbody));
    addHiddenElement(createHiddenField("textbody",textbody));
    addHiddenElement(createHiddenField("htmlenabled",whetherHtmlEnabled));
    addHiddenElement(createHiddenField("attachimages",whetherToAttachImages));
    
}


</script>


<h3 style="font-family: Georgia; font-size: 20px; ">Preview Email</h3>

Fill out the form below to send a test email to see how your email will
look to your subscribers. Fields marked * are mandatory.


<p>You are previewing an email to be sent to a subscribers of the <strong>'<?php echo $newsletter->name ?>'</strong> newsletter.</p>
<form name="previewform" action="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" method="post">
<table>
<r>
<td>Name*:</td>
<td><input type="text" name="name"></td>
</tr>
<tr><td>Email Address*</td><td><input type="text" name="email">
</td></tr>
<?php

if (count($listOfCustomFields))
	foreach ($listOfCustomFields as $field)
	{
?>
<tr>
  <td><?php echo $field->label ?></td>
  <td><?php
  if ($field->type == "enum")
   {
   ?>
   <select name="cust_<?php echo base64_encode($field->name) ?>">
   <?php
   $options = explode(",",$field->enum);
   foreach ($options as $option)
   {
      ?>
      <option><?php echo $option ?></option>
      <?php
   }
   ?>  
   </select>
   <?php
   }
   else
   {
   ?>
  <input type="text" name="cust_<?php echo base64_encode($field->name); ?>">
  <?php
  }
  ?>
  </td>
</tr>

<?php
	}
        ?>
<tr>
   <td>
       <div id="hiddenfields">
       </div>
   <input type="hidden" name="fromname" value="<?php echo $newsletter->fromname ?>">
   <input type="hidden" name="fromemail" value="<?php echo $newsletter->fromemail ?>">
   <input type="submit" name="Submit" value="Send Preview Email"></td>
</table>
</form>



        <script>
            load();
        </script><?php

}


function formSubmitted()
{
    return isset($_POST['email']);
}


/*
 * This function is used to fetch the posted 
 * form data, validate it and return as an array
 */
function validateAndReturnFormData(&$error)
{
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    if (!$name || !$email) //if either name or email address is empty, return false.
        {
        $error = (!$name)?"The name is empty. Please enter your name":(!$email)?"The email address is empty.":"";
        return false;
    }

    $fromName = $_POST['fromname'];
    if (empty($fromname))
        $fromname = get_bloginfo("name");

    $fromemail = $_POST['fromemail'];
    if (empty ($fromemail))
    {
        $fromemail = get_bloginfo("admin_email");
    }
    $subject = stripslashes($_POST['subject']);
    $textbody = $_POST['textbody'];



 
   $htmlenabled = $_POST['htmlenabled'];

    if ($htmlenabled == 1)
    {
        $htmlbody = $_POST['htmlbody'];
        //make sure there is something in the html body
        //having removed html tags and white space, if there is any content,
        //then enable the html body
        $temp = strip_tags($htmlbody);
        $temp = trim($temp);
        if (strlen($temp)==0)
            $htmlenabled=0;
    }
    $attachimages = $_POST['attachimages'];
    
	$htmlbody = stripslashes($htmlbody);
	$textbody = stripslashes($textbody);
    $arguments = array("name"=>$name,
                       "to"=>$email,
                       "subject"=>$subject,
                       "fromname"=>$fromname,
                       "from"=>$fromemail,
                       "htmlenabled"=>$htmlenabled,
					   "htmlbody"=>$htmlbody,
					   "textbody"=>$textbody,
                       "attachimages"=>$attachimages
                        );

    //the custom fields
    foreach ($_POST as $name=>$value)
        {
        if (preg_match("@cust_@",$name))
                {
            $actual_name = base64_decode(str_replace("cust_","",$name));//remove the cust_ and base64 decode the rest of the name to get the real form name
            $arguments[$actual_name]= $_POST[$name];
        }

    }
    return $arguments;
}
function showError($error)
{
   echo '<span style="font-family:Verdana; font-size:14px; font-weight:bold; display:block; padding: 10px; border: 1px solid #000; background-color: #eee; color: #f00; ">'.$error.'</span>';
}

$nid = (int) $_GET['nid'];
$params['nid']= $nid;
if (formSubmitted())
    {
    $error="";
    
    if ($arguments = validateAndReturnFormData($error))
    {
		
		/*
		The following line is a hack. The swiftmailer library 
		throws an error when the email being sent has an image 
		in it and the src of the image is a different server - an http:// URL. 
		
		The swiftmailer is invoked by one of the functions that
		sendTheEmail invokes.
		*/
		error_reporting(~E_ALL);
		/*End Hack*/
		
        sendTheEmail($arguments);
		displayEmailSentMessage();
    }
    else
    {
        showError($error);
        showPreviewForm($params);
    }
}
else
    {
    
    showPreviewForm($params);
}


function displayEmailSentMessage()
{
	?>
<div align="center">
<h1 style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; font-weight: normal;">Preview Email Sent</h1>

The preview email has been sent to the email address you specified.
</div>
    <?php
}

