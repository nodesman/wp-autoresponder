<div align="center" style="color: red"><strong><?php echo $error ?></strong></div>
<div class="wrap">
  <h2><?php echo $title ?></h2>
</div>
<form name="custom_field_form" action="<?php print $_SERVER['REQUEST_URI'] ?>"	 method="post">
  <table>
    <tr>
      <td><strong>Name:</strong><br>
      <small>Should NOT contain spaces or special characters</small></td>
      <td><?php if (!$nameIsHidden) 
	  {
		  
	  ?>
        <input type="text" name="name" value="<?php echo (!empty($parameters->name))?$parameters->name:"" ?>" />
        <?php 
		} else 
		{ ?>
        <input type="hidden" name="name" value="<?php echo ($parameters->name)?$parameters->name:"" ?>" />
        <?php echo ($parameters->name)?$parameters->name:"" ?>
        <?php 
		} ?></td>
    </tr>
    <tr>
      <td><strong>Label:</strong><br/>
      <small>The label that will be used in the subscription form for this field.</strong></td>
      <td><input type="text" name="label" value="<?php echo (!empty($parameters->label))?$parameters->label:"" ?>" /></td>
    </tr>
    <tr>
      <td><strong>Type:</strong><br/>
<small>      Choose whether the user has to enter the value, or choose<br/> a value from a set of values in a drop down</small></td>
      <td><select name="type">
          <option value="text" <?php if (isset($parameters->type)  && $parameters->type == "text") {
			  echo "selected=\"selected\"";
			  } ?>>One Line Text</option>
          <option value="enum" <?php if (isset($parameters->type ) && $parameters->type == "enum") { echo "selected=\"selected\""; } ?>>Multiple Choice</option>
        </select></td>
    </tr>
    <tr>
    <td></td>
    </tr>
    <tr>
      <td><br/><br/><strong>The choices (if multiple choice):</strong><br />
      <small>If you chose multiple choice for type, then enter<br/> the choices the user can choose separated by<br/> commas. No spaces.
        <small>Comma separated. No spaces.<br />
        For example: male,female</small></td>
      <td><input type="hidden" name="id" value="<?php echo (!empty($parameters->id))?$parameters->id:""; ?>" />
        <input type="text" id="enum" name="enum" value="<?php echo (!empty($parameters->enum))?$parameters->enum:""; ?>" /></td>
    </tr>
  </table>
  <input type="submit" value="<?php echo $buttontext ?>" class="button" />
</form>