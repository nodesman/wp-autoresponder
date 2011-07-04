
<style>
    td {
        padding: 10px;
}
</style>
<?php
if (count($errors) > 0)
{
?>
<div class="error fade">
    <ol><?php

$error_text = implode("<li>",$errors);

echo "<li>$error_text"; ?></ol></div>
<?php
}
?>
<div class="wrap">
    <blockquote>
  <h2><?php echo $heading ?></h2>

<form action="<?php print $_SERVER['REQUEST_URI'] ?>" method="post">
  <table width="1000" border="0" cellspacing="0" cellpadding="10">
    
    <tr>
      <td><b>Name</b>:<br>
        <small>Enter a name for the newsletter. This will be shown to subscribers on when they unsubscribe and to you in this admin panel.</small></td>
      <td><?php
      if (isset($edit) && $edit == true) { ?> <input type="hidden" name="name" id="name" value="<?php echo  $parameters->name ?>" /> <?php echo  $parameters->name ?>
      <?php
      }
      else
      {?>
        <input type="text" size="45" name="name" id="name" value="<?php echo  $parameters->name ?>" />
 <?php } ?></td>
    </tr>
    <tr>
      <td><strong>From Name:</strong><br/>
        <small><?php _e("When subscribers of this newsletter receive any email (follow up , broadcasts, blog emails), they will see what you set here in the From column in their mail client."); ?>

           

        </td>
      <td><input type="text" name="fromname" value="<?php echo $parameters->fromname ?>" size="40" maxlength="30"></td>
    </tr>
    <tr>
      <td><strong>From Email:</strong><br/>
        <small>The email address from which the email is marked as sent. If not set, the email address will be sent from <?php echo get_option("admin_email"); ?> (The email set at the administrator's <a href="profile.php">profile page</a>)
<br/>
<p><strong style="font-weight: bold; color: #f00">Warning:</strong> Set this value to an e-mail address that is on a domain name hosted on this server. Most web hosts disallow sending e-mails from e-mail addresses on domains other than the ones hosted on local server. Otherwise broadcasts to this newsletter will not be delivered.
            </p></td>
      <td><input type="text" name="fromemail" value="<?php echo $parameters->fromemail ?>" size="40"></td>
    <tr>
      <td><strong>Reply To:</strong> <br>
        <small>When subscribers choose to reply to your email, they will be able to reply to this address.</small></td>
      <td><label for="name"></label>
        <input size="45" type="text" name="reply_to" id="reply_to" value="<?php echo  $parameters->reply_to ?>" /></td>
    </tr>
    <tr>
      <td><strong>Public Description: (optional)</strong>
        <p>This is a description that will be used in the unsubscription page to describe the newsletter when listing all the subscriptions of that subscriber.</p></td>
      <td><label for="description"></label>
        <textarea name="description" id="description" cols="45" rows="5"><?php echo $parameters->description ?></textarea></td>
    </tr>

        

    <tr>
      <td><label for="button"></label>
          <input type="hidden" name="wpr_form" value="<?php echo $wpr_form ?>"
        <input type="hidden" name="id" value="<?php echo $parameters->id ?>"  />
        <input class="button" type="submit" name="button" id="button" value="<?php echo $button_text ?>" />
        <input class="button" type="button" onclick="window.location='<?php echo _wpr_admin_url("newsletter") ?>'" name="button" id="button" value="Cancel" /></td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form></blockquote>