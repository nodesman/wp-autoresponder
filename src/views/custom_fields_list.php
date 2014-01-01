<div class="wrap">
<h2>Custom Fields</h2>
Custom fields allow you to add some information about your subscribers that you can place in your outgoing e-mail broadcasts. Below is a list of newsletters. Click on the <em>'Manage Custom Fields'</em> against the newsletter to add/delete custom fields for that newsletter.
 <?php

  if (count($newsletterList) >0)
  {
  ?>
   <table width="50%">
  <?php
  foreach ($newsletterList as $newsletter)
  {
      ?>
      <tr>
      <td style="height: 30px;">
     <?php echo $newsletter->name ?> </td><td><a class="button" href="admin.php?page=_wpr/custom_fields&cfact=manage&nid=<?php echo $newsletter->id ?>">Manage Custom Fields</a></td></tr>
      <?php
  }
  ?></table>
  <?php
  }
  else
  {
  ?>
<p style="padding: 20px; display:block; text-align:center; width: 600px; background-color: #fefefe;border: 1px solid #000;">There are no newsletter to which custom fields can be associated.<a href="admin.php?page=_wpr/newsletter&act=add">Create a newsletter</a> to add custom fields.</p>
<?php
  }
  ?>
  </div>