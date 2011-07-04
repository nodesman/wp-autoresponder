<?php



class WP_Subscription_Form_Widget extends WP_Widget
{
    function widget($args,$instance)
    {
        extract($args, EXTR_SKIP);
        $title = ($instance['title'])?$instance['title']:"Subscirbe via E-Mail";

        //there will be any output only if the corresponding subscription form exists.

        //generate an output only if the subscription form exists.

        if (!($subscriptionForm = _wpr_subscriptionform_get($instance['the_form'])))
        {
            return false;
        }
        echo '<span class="wprsf">';
        echo $before_widget;
        
        echo '<span class="wprsfbt">';
        echo $before_title;
        echo esc_html($instance['title']);
        echo $after_title;
        echo '</span>';
        
        echo '<span class="wprsf-beforeform">';
        echo $instance['before_form'];
        echo '</span>';


        echo '<span class="wprsf-theform">';
        echo _wpr_subscriptionform_code($subscriptionForm,true);
        echo '</span>';

        echo '<span class="wprsf-afterform">';
        echo $instance['after_form'];
        echo '</span>';
        echo $after_widget;
        echo '</span>';
    }

    function update($new_instance,$old_instance)
    {
        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['before_form'] = $new_instance['before_form'];
        $instance['after_form'] = $new_instance['after_form'];
        $instance['the_form'] = $new_instance['the_form'];

        return $instance;

    }
    
    function WP_Subscription_Form_Widget()
    {
        $widget_options = array(
            'classname'=> 'wp-subscription-form-widget',
            'description'=>'Adds a subscription form to your sidebar'
        );


        $control_options=array(
            'width'=>400,
            'height'=>500
        );
        
        $this->WP_Widget('widget_wpr_forms',__("Newsletter Subscription Form (WPR)"),$widget_options,$control_options);
    }

    function form($instance)
    {
        $forms = _wpr_subscriptionforms_get();
        if (count($forms)==0)
        {
            ?>
<em>Please <a href="admin.php?page=wpresponder/subscriptionforms.php&action=create">create a subscription form</a> before trying to add it to the sidebar.</em>
        <?php
        }
        else
        {
        ?>
<label for="<?php echo $this->get_field_id('title'); ?>">
<?php echo _e("Title"); ?>:</label>
<input size="50" type="text" value="<?php echo esc_attr($instance['title']); ?>"
       name="<?php echo $this->get_field_name('title') ?>" id="<?php echo $this->get_field_id("title"); ?>">
<p></p>
 <?php echo _e("Text To Place Before The Form") ?>:
 <p>
     <textarea rows="5" cols="45" name="<?php echo $this->get_field_name('before_form') ?>" id="<?php echo $this->get_field_id("before_form"); ?>">
<?php
                    echo esc_attr($instance['before_form']);
     ?></textarea>
    </p>
<?php echo _e("Text To Place After The Form") ?>:
 <p>
     <textarea rows="5" cols="45" name="<?php echo $this->get_field_name('after_form') ?>" id="<?php echo $this->get_field_id("after_form"); ?>">
<?php
                    echo esc_attr($instance['after_form']);
     ?></textarea>
</p>
Select the subscription form: <select name="<?php echo $this->get_field_name("the_form") ?>">
 <?php

  foreach ($forms as $form)
  {
      ?><option <?php if ($instance['the_form']== $form->id) { echo 'selected="selected"';} ?> value="<?php echo $form->id ?>"><?php echo $form->name ?></option>
      <?php
  }
?>
</select>
<p><a style="font-size: 10px;" href="admin.php?page=wpresponder/subscriptionforms.php">Click here to create subscription forms</a></p>
<?php
        }


    }
}

