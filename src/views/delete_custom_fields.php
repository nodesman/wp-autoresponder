<div class="wrap">
  <h1>Delete Custom Field </h1></div>
  This will also delete:
  <ol>
    <li>The data for the field for all subscribers</li>
    <li>The form fields in all subscription forms that are connected to this field</li>
  </ol>
  Are you sure you want to delete '<?php echo $field->name ?>' field?<br /><br />
  
<a href="<?php echo $_SERVER['REQUEST_URI'] ?>&confirm=true" class="button"> Delete </a> &nbsp;&nbsp;&nbsp;<a href="javascript:window.history.go(-1);" class="button">Cancel</a><br />
