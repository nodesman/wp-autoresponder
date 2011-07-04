<?php

include_once 'htmltemplates.lib.php';

/* 

 * The functions in this file add the interface that customizes the blog post

 * email

 *

 */



function wpr_edit_post_save($id)

{



	$nonce = $_POST['wpr-blogemailoptions-nonce'];

	

	if (wp_verify_nonce($nonce, 'wpr-blogemailoptions-nonce'))

	{

               delete_post_meta($id,'wpr-options');

                $ncount = $_POST['wpr-newsletters-count'];

                $nid = $_POST['wpr-newsletters'];

                

                if ($ncount > 0) //if there are some newsletters at all

                    {

                    $nids = explode(",",$nid);

                    foreach ($nids as $nid) //for each newsleter, form an array for the options

                        {

                        //if we are disabling customization, we don't need to check the other fields.

                        if (isset($_POST['skipnewsletter-'.$nid]))

                            {

                            $arguments = GetDisableCompletelyForNewsletterArguments($nid);

                            $options[$nid]=$arguments;

                        }//if the disabled checkbox is enabled or the subject is left empty or if the text box and the html box both are left empty..disable customization

                        

                        else if (isset($_POST['disablecustomization-'.$nid]) || (empty($_POST['subject-'.$nid])) || (strlen(trim($_POST['textbody-'.$nid]))==0 && strlen(trim(strip_tags($_POST['htmlbody-'.$nid]) )) == 0 ))

                            {



                            $activeSubscribers = (isset($_POST['skipfollowup-'.$nid]))?1:0;

                            $arguments = GetDisabledCustomizationArguments($nid,$activeSubscribers);

                            if (strlen(trim(strip_tags($_POST['htmlbody-'.$nid])))==0 && !empty($_POST['textbody-'.$nid]))

                                $arguments['htmlenable'] = 0;

                            $options[$nid]=$arguments;



                        }

                        else

                            {

                            $currentNewsletterOptions = CreateNewsletterPostValuesArray($nid);

                            $options[$nid] = $currentNewsletterOptions;

                        }

                        //else

                        

                    }

                }

                else { //there are no newsletter to save options for. save an empty array

                    $options = array();

                }



                $wprmeta = serialize($options);

                $wprmeta = base64_encode($wprmeta);

                add_post_meta($id,"wpr-options",$wprmeta);       

	}

}



function CreateNewsletterPostValuesArray($newsletterId)

{

    $val =  array(

        "nid"=> $newsletterId,

        "subject"=>stripslashes($_POST['subject-'.$newsletterId]),

        "textbody"=> stripslashes($_POST['textbody-'.$newsletterId]),

        "htmlbody"=> ($_POST['htmlenable-'.$newsletterId] == 1)?stripslashes($_POST['htmlbody-'.$newsletterId]):"",

        "disable"=> (isset($_POST['skipnewsletter-'.$newsletterId]))?1:0,

        "skipactivesubscribers"=>(isset ($_POST['skipfollowup-'.$newsletterId]))?1:0,

        "htmlenable"=>(isset($_POST['htmlenable-'.$newsletterId]))?1:0,

        "attachimages"=>(isset($_POST['attachimages-'.$newsletterId]))?1:0,

        "nocustomization"=>(isset($_POST['disablecustomization-'.$newsletterId]))?1:0,

        "nopostseries"=>(isset($_POST['nopostseries-'.$newsletterId]))?1:0

    );

    return $val;

}





function wpresponder_meta_box_add()

{

	add_meta_box('wpresponder_skip','E-Mail Subscriber Settings','wpr_add_post_block','post','normal','high');



}



function wpr_add_post_block()

{

	global $post;
	$post_id = $post;	
	if (is_object($post_id))
	{
		$post_id = $post_id->ID;
	}



    getNewsletterOptions($post_id);

}

/*

 * This function generates the tabs interface for all the

 * newsletters. It puts the form codes into a 

 */

function getNewsletterOptions($post_id=0)

{

    global $wpdb;

    $query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";

    $results = $wpdb->get_results($query);

    if (count($results))

        {

?>

<span style="font-size: 12px; line-height: 25px;">Each of the tabs below allow you to customize the email that goes out to the blog subscribers, blog category subscribers and post series subscribers of that newsletter. The blog post will be delivered ONLY to subscribers who have subscribed to the blog or one of the blog categories under which this blog post will be published. Ordinary newsletter subscribers will not receive this blog post at all.

</span>
<input type="hidden" name="wpr-blogemailoptions-nonce" value="<?php echo wp_create_nonce('wpr-blogemailoptions-nonce') ?>" />
<input type="hidden" name="wpr-newsletters-count" value="<?php echo count($results) ?>"/>
<input type="hidden" name="wpr-newsletters" value="<?php foreach ($results as $newsletter) { $nids[] = $newsletter-> id; }

    echo implode(",",$nids); ?>"/>
<div class="tabber">
  <?php

        

    foreach ($results as $newsletter)

    {
?>
  <div class="tabbertab" id="wprtabs">
    <?php
        //Form for customizing the mailout for a single newsletter
   		$post = get_post($post_id);
		//Unpublished posts should get an id of zero. 
		if ($post->post_status == 'auto-draft')
		{
		    $post_id = 0;	
		}
        $arguments = getArguments($post_id,$newsletter);
        echo getNewsletterCustomizationFormCode($arguments,"new");

        ?>
  </div>
  <?php

    }

        }

        else

            {

            ?>
  You haven't created any newsletters. Create atleast one before
  
  setting blog post email settings.
  <?php

        }

   ?>
</div>
<?php



}



function getArguments($post_id,$newsletter)

        {
			
		
    $post_id = (int) $post_id;
	
    if ($post_id == 0)

            $arguments = GetDefaultArguments($newsletter);

        else { //get the post meta from the db

            $optionsArray = get_post_meta($post_id,"wpr-options");

            

            $arguments = unserialize(base64_decode($optionsArray[0]));

            $arguments= $arguments[$newsletter->id];

            

      }

      return $arguments;

}



function GetDefaultArguments($newsletter)

{

    return array("nid"=>$newsletter->id,

                "subject"=>"",

                "textbody"=>"",

                "htmlbody"=>"",

                "disable"=>0,

                "nocustomization"=>1,

                "htmlenable"=>1,

                "attachimages"=>1,

                "skipactivesubscribers"=>1,

                "nopostseries"=>1

        );



}



function GetDisableCompletelyForNewsletterArguments($nid)

{

    return array("nid"=>$nid,

                "subject"=>"",

                "textbody"=>"",

                "htmlbody"=>"",

                "disable"=>1,

                "nocustomization"=>0,

                "htmlenable"=>1,

                "attachimages"=>1,

                "skipactivesubscribers"=>1,

                "nopostseries"=>1

        );

}



function GetDisabledCustomizationArguments($nid,$activeSubscribers)

{

    return array("nid"=>$nid,

                "subject"=>"",

                "textbody"=>"",

                "htmlbody"=>"",

                "disable"=>0,

                "nocustomization"=>1,

                "nopostseries"=>1,

                "htmlenable"=>1,

                "attachimages"=>1,

                "skipactivesubscribers"=>$activeSubscribers

        );



}



/*

 * This function generates the email customization for the blog post for one

 * newletter

 */



function getNewsletterCustomizationFormCode($arguments,$mode="new")

{

    global $wpdb;  
    $nid = $arguments['nid'];
    $newsletter = _wpr_newsletter_get($nid);



    ?>
<h2><?php echo $newsletter->name; ?></h2>
<p>
  <input type="checkbox" <?php if ($arguments['disable']) { echo 'checked="checked"'; } ?> autocomplete="off" onchange="toggleStatus(<?php echo $nid ?>,this.checked);" id="toggleForm-<?php echo $nid; ?>" name="skipnewsletter-<?php echo $nid; ?>" value="1" id="skipnewsletter-<?php echo $nid; ?>">
  Don't deliver this blog post to blog subscribres or blog category subscribers. But use these settings for post series deliveries. </p>
<p>
  <input type="checkbox" <?php if ($arguments['nopostseries']==1) { echo 'checked="checked"'; } ?> name="nopostseries-<?php echo $nid ?>" autocomplete="off" id="nopostseries">
  <label for="nopostseries"> Don't use these settings while delivering email to post series subscribers. Use a default layout that uses the blog post's content.</label>
</p>
<div id="editorformitems-<?php echo $nid; ?>">
  <p>
    <input autocomplete="off" type="checkbox"  <?php if ($arguments['skipactivesubscribers']) { echo 'checked="checked"'; } ?> name="skipfollowup-<?php echo $nid ?>" value="1" id="skipfollowup-<?php echo $nid ?>">
    Don't deliver this blog post to subscribers who are receiving a follow up sequence associated with this newsletter.</p>
  <p>
<fieldset style="border:1px solid #909090; padding:10px;"> <legend><input type="checkbox"  <?php if ($arguments['nocustomization']) { echo 'checked="checked"'; } ?> autocomplete="off" onchange="toggleCustomization(<?php echo $nid ?>, this.checked);" name="disablecustomization-<?php echo $nid ?>" value="1" id="disablecustomization-<?php echo $nid ?>">
    Disable all customization (use default layout for the email)</p></legend>
  <div id="customizationsform-<?php echo $nid ?>">
    <div id="form-<?php echo $nid ?>">
      <p style="font-size: 17px; font-weight: bold">Subject:
        <input type="text" name="subject-<?php echo $nid ?>" value="<?php echo $arguments['subject'] ?>" size="50"></p>
      <p style="font-size: 17px; font-weight: bold">Text Body:</p>
      <textarea style="border: 1px solid #c0c0c0;" name="textbody-<?php echo $nid ?>" rows="10" cols="80"><?php echo $arguments['textbody'] ?></textarea>
      <p style="font-size: 17px; font-weight: bold">HTML Body:</p>
      <?php CreateNewTemplateSwitcherButton("listOfEditors[".$nid."]","htmlbody-".$nid,$nid); ?>
      <p>
        <input type="checkbox" <?php if ($arguments['htmlenable']) { echo 'checked="checked"'; } ?> onchange="toggleHtmlBody(<?php echo $nid ?>,this.checked)" name="htmlenable-<?php echo $nid ?>" id="htmlenable-<?php echo $nid ?>" value="1">
        Enable HTML Body</p>
      <div id="htmlformitems-<?php echo $nid ?>">
        <p>
          <input type="checkbox" name="attachimages-<?php echo $nid ?>"  <?php if ($arguments['attachimages']) { echo 'checked="checked"'; } ?>  value="1">
          Images in the email are attached with the email instead of being given a URL in the source code</p>
          <div style="float:right; padding-bottom:20px;"><a class="button-primary" target="_blank"  href="http://www.krusible.com/newsletter-design/">Get a custom Email Newsletter template</a>
          </div>
<br/><br/><br/>
          <div style="clear:both"></div> 
        <div id="editor-<?php echo $nid ?>">
          <textarea id="htmlbody-<?php echo $nid ?>" name="htmlbody-<?php echo $nid ?>" rows="20" cols="80"><?php echo ($arguments['htmlenable'])?$arguments['htmlbody']:""; ?></textarea>
        </div>
        <input type="button" onclick="createEditor(<?php echo $nid ?>)" value="Enable WYSIWYG" name="enable"/>
        <input type="button" value="Disable WYSIWYG" onclick="removeEditor(<?php echo $nid; ?>)" name="enable"/>
      </div>
    </div>
    <script>
toggleStatus(<?php echo $nid ?>,document.getElementById("toggleForm-<?php echo $nid ?>").checked);

toggleHtmlBody(<?php echo $nid ?>,document.getElementById("htmlenable-<?php echo $nid ?>").checked);

toggleCustomization(<?php echo $nid ?>,document.getElementById("disablecustomization-<?php echo $nid ?>").checked);

</script>
    <p style="font-size: 17px; font-weight: bold">Shortcodes</p>
    <div style="font-size: 14px;"> Use the following codes in the Text or HTML body to replace it with the appropriate
      
      text.
      <p></p>
      <ol>
        <li>Post related shortcodes:<br>
          <ol>
            <li>[!post_url!]  -  Replace with the URL of this post when it is published</li>
            <li>[!delivery_date!]  - Date when this email was delivered</li>
            <li>[!post_date!] - Date under which you file this post for publishing.</li>
          </ol>
        </li>
        <li>Subscriber attributes and custom fields:
          <ol>
            <li>[!name!] - Name of the subscriber</li>
            <li>[!email!] - The email address of the subscriber.</li>
            <?php

        $query = "SELECT * from ".$wpdb->prefix."wpr_custom_fields where nid=$nid;";

        $results = $wpdb->get_results($query);

        foreach ($results as $custom_fields)

        {

            ?>
            <li>[!<?php echo $custom_fields->name ?>!] - <?php echo $custom_fields->name ?></li>
            <?php

        }

        ?>
          </ol>
        </li>
      </ol>
    </div>
  </div>
  </p></fieldset>
</div>
<?php



}



function wpr_add_post_save($id)

{

	$nonce = $_POST['wpr-nonce'];

	$setting = $_POST['wpresponder_on'];

	if (wp_verify_nonce($nonce, 'wpr-skip-nonce'))

	{

		if ($setting=="on")

		{

			add_post_meta($id, "_wpresponder_on","on");

		}

		else

		{

			add_post_meta($id, "_wpresponder_on","off");

		}

	}



}

