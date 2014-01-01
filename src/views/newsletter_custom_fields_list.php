<div class="wrap">
<h2>Custom Fields For '<?php echo $newsletter->name ?>' Newsletter</h2>
<table class="widefat">
  <tr>
  <thead>
  <th scope="col">Name</th>
    <th width="100" scope="col">Type</th>
    <th scope="col">Label</th>
    <th scope="col">Actions</th>
    </thead>
  </tr>
  <?php
		  foreach ($newsletterCustomFieldList as $field)
		  {
			  ?>
  <tr>
    <td><?php echo $field->name ?></td>
    <td><?php echo _wpr_custom_field_name($field->type,$field->enum); ?></td>
    <td><?php echo $field->label; ?></td>
    <td><input type="button" value="Edit" onclick="window.location='admin.php?page=_wpr/custom_fields&nid=<?php echo $newsletter->id ?>&cfact=edit&cid=<?php echo $field->id ?>'" class="button-primary" />
      <input type="button" value="Delete" onclick="window.location='admin.php?page=_wpr/custom_fields&nid=<?php echo $newsletter->id ?>&cfact=delete&cid=<?php echo $field->id ?>';" class="button-primary" /></td>
  </tr>
  <?php
		  }
		  ?>
</table>
<input type="button" value="Add New Field" class="button" onclick="window.location='admin.php?page=_wpr/custom_fields&nid=<?php echo $newsletter->id ?>&cfact=create';" /> <input type="button" value="&laquo; Back To Custom Fields" style="float:left" onclick="window.location='admin.php?page=_wpr/custom_fields';" class="button" />
</div>