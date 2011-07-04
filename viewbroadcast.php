<?php
$id = $_GET['id'];
$id = intval($id);
$query = "select * from ".$wpdb->prefix."wpr_newsletter_mailouts where id=$id";
$mailout = $wpdb->get_results($query);
$mailout = $mailout[0];
$output["Subject"] = $mailout->subject;
$output["Text Body"] = "<pre>".$mailout->textbody."</pre>";
$output["HTML Body"] = $mailout->htmlbody;
$output["Sent At"] = date("H:ia \o\\n dS F Y",$mailout->time);
$newsletter = _wpr_newsletter_get($mailout->nid);
$output["Newsletter"] = $newsletter->name;
$output["Recipients"] = (!$mailout->recipients)?"All Subscribers":$mailout->recipients;
?>
<h2>Viewing Broadcast</h2>
<table width="800" border="1" style="border: 1px solid #ccc;" cellpadding="10">
  <tr>
    <td ><strong>Subject Of E-Mail:</strong></td>
    <td ><?php echo $output["Subject"] ?></td>
  </tr>
  <tr>
    <td><strong>Newsletter:</strong></td>
    <td><?php echo $output["Newsletter"] ?></td>
  </tr>
  <tr>
    <td  colspan="2"><h2>Text Body:</h2><br>
      <div style="height: 400px; overflow:scroll">
        <pre>
		<?php echo $output["Text Body"] ?>
        
        </pre>
        </div>
</td>
  </tr>
  <tr>
    <td colspan="2"> <h2>HTML Body:</h2>      
      <iframe width="100%" height="400" scrolling="yes" frameborder="0" border="0" src="<?php echo get_bloginfo("url"); ?>/?wpr-vb=<?php echo $id ?>" id="htmlbodyframe">
      </iframe>
     </td>
  </tr>
  <tr >
    <td >Recipients:</td>
    <td ><?php echo $output["Recipients"]?></td>
  </tr>
  <tr>
    <td>Sent At:</td>
    <td><?php echo $output["Sent At"] ?></td>
  </tr>
</table><br />
<br />

<a href="admin.php?page=wpresponder/allmailouts.php" class="button-primary" style="margin:10px; margin-top:20px;">&laquo; Back</a>
