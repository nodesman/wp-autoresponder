<div class="wrap">
  <h2>Global Newsletters Settings</h2>
<script>
var custom_validation_functions = new Array();
</script>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" name="settingsform" method="post">
<?php wp_nonce_field("_wpr_settings"); ?>
  <table width="900">
    <tr>
      <td colspan="2" valign="top" align="left" width="50"><strong>Address</strong>: <br>
        Attached to every e-mail in complaince with CAN-SPAM act in USA.</td>
    </tr>
    <tr>
      <td colspan="2"><textarea name="address" rows="6" cols="60"><?php echo $canspam_address ?></textarea></td>
    </tr>
    <tr>
       <td colspan="10">
<?php do_action("_wpr_settings_form"); ?>
      </td>
    </tr>
    <tr>
      <td colspan="2"><table width="100%">
          <tr>
            <td colspan="2"><h2>Notifications</h2>
            WP Responder may send notification of problems and important events from time to time. Instructions to use the plugin, important updates, and other notifications will need to be acted upon immediately from time to time. Specify the e-mail address to which these notifications should be sent. Ensure that this is a valid e-mail address that you check often.<br/><br/></td>
          </tr>
          
          <tr>          
          <td valign="top" width="300"><strong>Notification e-mail Address:</strong><br />
            <br />
             <br />
            <br /></td>
          <td valign="top" style="padding-left:20px;">
          
          <input type="radio" name="notification_email" value="adminemail" <?php if ($notification_custom_email_is_admin_email == true) { echo 'checked="checked"'; }?> id="adminemail" />
          <label for="adminemail">Send to the administrator account's email address(<?php echo $admin_email ?>)</label>
          <br />
          <br />
          <input type="radio" name="notification_email" <?php if (!$notification_custom_email_is_admin_email) { echo 'checked="checked"'; } ?> onclick="document.settingsform.notification_custom_email.focus();" value="customemail" id="customemail" />
          <label for="customemail">
          
          Send to this email address:
          <input type="text" name="notification_custom_email" size="30" value="<?php echo $notification_email_address;  ?>" />
          </td>
          
          </tr>
          
          <tr>
            <td valign="top"><strong>Enable instructional emails after activation:</strong>:</td>
            <td valign="top"><input type="radio" value="enabled" <?php if ($tutorial_on) { echo 'checked="checked"'; } ?> name="tutorialenable" id="tutorial_enable" />
              <label for="tutorial_enable"> Enable </label>
              <input type="radio" value="disabled" name="tutorialenable" <?php if (!$tutorial_on) { echo 'checked="checked"'; } ?> id="tutorial_disable"  />
              <label for="tutorial_disable"> Disable</label>
              <br />
              <br />
              <br /></td>
          </tr>
          <tr>
            <td valign="top"><strong>Enable imporant update news to the notification e-mail address:</strong></td>
            <td><input type="radio" value="enabled" <?php if ($updates_on) { echo 'checked="checked"'; } ?>  name="updatesenable" id="updates_enable" />
              <label for="updates_enable"> Enable</label>
              <input type="radio" value="disabled" <?php if (!$updates_on) { echo 'checked="checked"'; } ?> name="updatesenable" id="updates_disable" />
              <label for="updates_disable">Disable</label>
              <br />
              <br />
              <br /></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td colspan="2"><h2>E-mail Limit</h2>
        <hr size="1" /></td>
    </tr>
    <tr>
      <td colspan="2"><strong>Hourly Email Limit:</strong> <br />
        <small>The maximum number of emails that can be sent by WP Responder in an hour. Enter 0 for no limit.</small><br />
        <input type="text" name="hourly" value="<?php echo $hourly_limit; ?>" />
        <br />
        <em>This sets the limit on the number of emails sent by WP Responder in an hour. </em><br />
        <br />
        <strong> Important: </strong>Leave a margin of atleast 50 emails between your server's actual hourly limit and the hourly limit you set here. WP Responder cannot track all of the e-mails sent by all WordPress plugins and other web applications on your server. If the hourly limit is set too close to the hourly limit then some e-mails may go undelivered permanently. </td>
    </tr>
    <tr>
      <td><br />
        <br /></td>
    </tr>
    <tr>
      <td colspan="2"><hr size="1"  />
        <h2>Optional SMTP Settings</h2>
If instead of using your server's mail server that has a hourly limit on the number of outgoing e-mails you wish to use an external SMTP server that can send all of your e-mails out very quickly enter the connection information here. SMTP relay services are provided by websites like <a href="http://www.smtp.com/">SMTP.com</a>. These have a very high limit on the number of emails that can be sent in an hour.<br />
        <br /></td>
    <tr>
      <td><input type="checkbox" <?php if ($smtp_enabled) { echo 'checked="checked"'; } ?> name="enablesmtp" id="enablesmtp" value="1">
        <label for="enablesmtp">Use External SMTP Server to send email.</label></td>
    </tr>
    <tr>
      <td>SMTP Server Hostname: </td>
      <td><input name="smtphostname" type="text" value="<?php echo $smtp_hostname; ?>" size="50"></td>
    </tr>
    <tr>
      <td>SMTP Server Port: </td>
      <td><input name="smtpport" type="text" value="<?php echo ($smtp_port)?$smtp_port:25; ?>" size="50"></td>
    </tr>
    <tr>
      <td><input type="checkbox" id="smtpauth" name="smtprequireauth" <?php if (get_option("wpr_smtprequireauth")==1){ echo 'checked="checked"'; } ?> value="1" id="smtpauth">
        <label for="smtpauth">SMTP Server Requires Authentication</label></td>
    </tr>
    <tr>
      <td>SMTP Username: </td>
      <td><input name="smtpusername" type="text" value="<?php echo $smtp_username;  ?>" size="50"></td>
    </tr>
    <tr>
      <td>SMTP Password</td>
      <td><input name="smtppassword" type="text" value="<?php echo $smtp_password; ?>" size="50"></td>
    </tr>
    <tr>
      <td colspan="2">Use encryption:
        <input type="radio" id="ssl" name="securesmtp" value="ssl" <?php if ($is_smtp_secure_ssl) echo 'checked="checked"'; ?>>
        <label for="ssl">SSL</label>
        <input type="radio" value="tls" name="securesmtp"  <?php if ($is_smtp_secure_tls) echo 'checked="checked"'; ?> id="tls">
        <label for="tls">TLS</label>
        <input type="radio" value="none"  <?php if ($is_smtp_secure_none) echo 'checked="checked"'; ?> name="securesmtp" id="nones">
        <label for="nones">None</label>
        <br/>
        <small><strong>Important Note:</strong> Set the port in the field provided above appropriately. It is risky to not use any form of encryption. </td>
    </tr>
    <tr>
      <td colspan="2">
      <input type="hidden" name="wpr_form" value="settings" />
      <input type="submit" onclick="return validateSettingsForm();" class="button-primary" value="Save Settings" /></td>
    </tr>
  </table>
</form>
<script>




function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function validateSettingsForm()
{
	var address = document.settingsform.address.value;
	//the address should have a value.
	if (trim(address).length==0)
	{
		alert('Please enter your address in the address field');
		document.settingsform.address.focus();
		return false;
	}
	//validate the SMTP settings
	
	var smtpSettingsEnabledField = document.settingsform.enablesmtp;
	
	if (smtpSettingsEnabledField.checked == true)
	{
		var smtpfield = document.settingsform.smtphostname
		var smtphostname = trim(smtpfield.value);
		if (smtphostname.length==0)
		{
			alert("You have enabled external SMTP settings. Please specify a SMTP hostname. ");
			smtpfield.focus();
		    return false;	
		}
		
		if (smtphostname == "smtp.gmail.com")
		{
			alert("Please! Please! Please! Don't use Gmail to send outgoing e-mail. Your Gmail account can get deleted!");
		}
		
		var smtpportfield = document.settingsform.smtpport;
		var smtpport = trim(smtpportfield.value);
		if (smtpport.length==0)
		{
			alert("You have enabled external SMTP settings. Please specify a SMTP port number. ");
			smtpportfield.focus();
			return false;
		}
		
		
		smtpServerAuthenticationField = document.settingsform.smtprequireauth;
		
		if (smtpServerAuthenticationField.checked == true)
		{
			var smtpUsernameField = document.settingsform.smtpusername;
			var smtpusername = trim(smtpUsernameField.value);
			
			if (smtpusername.length==0)
			{
				alert('You have specified that the SMTP server requires authentication. Please specify the username.');
				smtpUsernameField.focus();
				return false;	
			}
			
			var smtpPasswordField = document.settingsform.smtppassword;
			var smtppassword = trim(smtpPasswordField.value);
			
			if (smtppassword.length == 0)
			{
				alert('You have specified that the SMTP server requires authentication. Please specify the password');
				smtpPasswordField.focus();
				return false;
			}
			
		}
	}
	
	//if the smtp settings are enabled, then all the other fields should be set. 
	
	
	
	if (document.getElementById('customemail').checked==true)
	{
		var theemailfield = document.settingsform.notification_custom_email
		var theemailaddress = trim(theemailfield.value);
		var re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;		
			
		if (theemailaddress.length==0 || !theemailaddress.match(re))
		{
			alert('Please specify a valid notification email address.');
			theemailfield.value='';
			theemailfield.focus();
			return false;
		}
		
	}
	
	
	//run all the custom validation functions
	if (custom_validation_functions.length > 0 )
	{
		var currentFunction;
		for (iter=0;iter<custom_validation_functions.length;iter++)
		{
			currentFunction = custom_validation_functions[0]
			if (false == currentFunction())
				return false;
		}
	}
	
	return true;

	
}
</script>
</div>
