<?php


function wpr_mail_form($parameters=array(),$mode="new",$error)
{

    global $wpdb;
    ?>
<style>
    .wrap label {
        font-family: Arial;
        font-size: 15px;
        font-weight: bold;
    }
</style>
<script>


    function Field(id,name,label)

    {

        this.id = id;

        this.name = name;

        this.label= label;

    }



    var ListOfFields = new Array();

        <?php

        $query = "select id,name from ".$wpdb->prefix."wpr_newsletters";

        $newsletters = $wpdb->get_results($query);

        foreach ($newsletters as $newsletter)

        {

            $query = "select * from ".$wpdb->prefix."wpr_custom_fields where nid=".$newsletter->id;

            $fields = $wpdb->get_results($query);

            ?>

        ListOfFields['<?php echo $newsletter->id ?>'] = new Array();

            <?php
            foreach ($fields as $field)
            {
                ?>
            ListOfFields['<?php echo $newsletter->id ?>'].push(new Field("<?php echo $field->id ?>","<?php echo $field->name ?>","<?php echo $field->label ?>"));
                <?php
            }
        }
        ?>
    function loadCustomFields(id)
    {

        var fieldList = ListOfFields[id];
        var container = document.getElementById('custom_fields');
        container.innerHTML=''
        var listItem  = document.createElement("ol");
        var item1 = document.createElement("li");
        newItem = document.createElement("li");
        newItem.innerHTML = "Enter <strong>[!name!]</strong> to substitute for Name.";
        listItem.appendChild(newItem);
        newItem = document.createElement("li");
        newItem.innerHTML = "Enter <strong>[!email!]</strong> to substitute for E-Mail Address.";

        listItem.appendChild(newItem);

        var newItem;
        if (fieldList.length >0)
        {
            for (field in fieldList)

            {

                newItem = document.createElement("li");

                newItem.innerHTML = "Enter <strong>[!"+fieldList[field].name+"!]</strong> to substitute for "+fieldList[field].label+".";

                listItem.appendChild(newItem);

            }
        }

        container.appendChild(listItem);

    }

    /*

    *  Functions for the preview email function.

     */


    function wpr_GetNewsletter()

    {

        return document.mailForm.newsletter.value;

    }



    function wpr_GetSubject()

    {

        return document.mailForm.subject.value;

    }



    function wpr_GetHtmlBody()

    {

        return editor.getData();

    }



    function wpr_GetTextBody()

    {

        return document.mailForm.body.value;

    }



    function wpr_GetWhetherHtmlEnabled()

    {

        return (document.mailForm.htmlenabled.checked)?1:0;

    }







    function showPreviewForm()
    {
        var nid = wpr_GetNewsletter();
        if (!window.open('<?php echo get_bloginfo("wpurl") ?>/?wpr-admin-action=preview_email&nid='+nid,'previewWindow','width=500,height=500'))
            alert("Please disable your pop up blocker to see the preview email form.");
    }

    function previewEmail()
    {
        if (validateFieldValues())
            showPreviewForm();

    }


</script>
<div style="clear:both"></div>
<blockquote>
<div class="wrap">
    <h2><?php echo ($parameters->formtitle)?$parameters->formtitle:"New Mail"; ?></h2>
    <?php if ($error) { ?>
    <div class="updated fade" style="background-color: rgb(255,241,204);">
        <div style="color:red; font-weight:bold; display:inline"> Error: </div>
        <?php echo $error ?></div>
    <?php } ?>
    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" name="mailForm" method="post">
        <table width="800" cellpadding="20" border="0" cellspacing="10">
            <?php



            if (!isset($_GET['aid']))

            {

                $query = "SELECT id,name from ".$wpdb->prefix."wpr_newsletters";
                $newsletters = $wpdb->get_results($query);

                ?>
                <tr>
                    <td width="200"><label for="thenewsletter">Select A Newsletter:</label>
                        <br>
                        <small>Select the newsletter that receives this email broadcast.</small></td>
                    <td width="474"><select style="width: 520px;" name="newsletter" id="thenewsletter" onchange="var davalue=this.options[this.selectedIndex].value; loadCustomFields(davalue); newsletterChanged(davalue) ">
                        <?php

                        foreach ($newsletters as $newsletter )

                        {

                            ?>
                            <option value="<?php echo $newsletter->id ?>" <?php if ($parameters->nid == $newsletter->id) { echo 'selected="selected"'; } ?> ><?php echo $newsletter->name ?></option>
                            <?php

                        }

                        ?>
                    </select></td>
                </tr>
                <?php

            }

            else

            {

                $responder = _wpr_autoresponder_get($_GET['aid']);
                $newsletter = _wpr_newsletter_get($responder->nid);

                ?>
                <tr>
                    <td width="200">Select A Newsletter:
                        <p><small>Select the newsletter that receives this email.</td>
                    <td width="474"><?php echo $newsletter->name ?></td>
                </tr>
                <input type="hidden" name="newsletter" id="thenewsletter" value="<?php echo $newsletter->id ?>" />
                <?php

            }

            ?>
            <tr>
                <td><label for="subject">Subject</label>
                    <br>
                    <small>Enter the subject of the email that your subscribers will receive</small></td>
                <td><input name="subject" value="<?php echo $parameters->subject ?>" type="text" id="subject" size="70" /></td>
            </tr>
            <tr>
                <td colspan="2"><label for="textbody">Text Body </label>
                    <br />
                    <small>Enter the email to be shown to subscribers who read your email in a mail client that doesn't support HTML email. </small>
                    <div style="float:right"><a href="http://www.krusible.com/"><img src="http://www.wpresponder.com/mailpage.png" /></a></div>
                    <textarea name="body" id="textbody" cols="55" rows="20" wrap="hard"><?php echo $parameters->textbody ?></textarea>
                    <br />
                    Hard breaks are inserted at the end of each line.
                    </p>
                    <h2>Custom Fields:</h2>
                    <div id="custom_fields">
                        <?php

                        if (isset($_GET['nid']))

                        {

                            $fields = _wpr_newsletter_all_custom_fields_get($nid);

                            if (count($fields))
                            {

                                ?>
                                Use the following placeholders to be substituted in the newsletter.
                                <ul>
                                    <?php

                                    foreach ($fields as $field)

                                    {

                                        ?>
                                        <li>&lt;!<?php echo $field->name ?>!&gt; for <?php echo $field->label ?></li>
                                        <?php

                                    }

                                    ?>
                                </ul>
                                <?php

                            }

                        }



                        ?>
                    </div></td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" name="htmlenabled" id="htmlenabled" onchange="changeHTMLBodyFieldsAvailability(this.checked,'htmlbodyfields');" <?php
                    if ($parameters->htmlenabled==1 )
                    {
                        echo 'checked="checked"';
                    } ?> />
                    <label for="htmlenable">Enable HTML Body</label>
                    <br />
                    <div id="htmlbodyfields"> <small>Check/uncheck this checkbox to enable or disable the HTML body of the email. When disabled only the text body will be sent.</small><br/>
                        <br/>
                        <?php CreateNewTemplateSwitcherButton("editor","htmlbody"); ?>
                        <label for="htmlbody">Enter the HTML Body Of The Email:</label>
                        <br>
                        <small>When HTML is enabled, most of your subscribers will see only the content in this body when they open the email.  If you don't enter a HTML body the email will be sent as text email.
                            <div id="htmlwrapper">
                                <textarea name="htmlbody" id="htmlbody" rows="20" cols="90"><?php echo htmlspecialchars($parameters->htmlbody) ?></textarea>
                            </div>

                            <input type="button" value="Disable WYSIWYG Editor" onclick="toggleHTML();this.value=(editorExists)?'Disable WYSIWYG Editor':'Enable WYSIWYG Editor';">
                    </div>
                    <br/>

</div>
</td>

</tr>

    <?php

    if ($mode == "new")

    {

        $theminute = date("i",$parameters->time);

        $thehour = date("H",$parameters->time);

        if ($parameters->time)

            $date = date("m/d/Y",$parameters->time);

        ?>
    <tr>
    <td>Send At: </td>
    <td><?php if (empty($parameters->time)) { ?>
    <input name="whentosend" <?php if ($parameters->whentosend == "now") { echo "checked=\"checked\""; } ?> type="radio" id="sendnow" value="now" checked="checked" />
    <label for="sendnow"> Immediately </label>
    (Now)<br />
    <input type="radio" <?php if ($parameters->whentosend == "date") { echo "checked=\"checked\""; } ?> name="whentosend" id="sendattime" value="date" />
          <label for="sendattime">
            <?php } ?>
        On
        <input type="text" name="date" id="date" value="<?php echo $date ?>">
    </label>
    at
    <select name="hour" id="hour" onfocus="document.getElementById('sendattime').checked=true;">
        <?php

        for ($i=0; $i<24; $i++)

        {

            $hour = sprintf("%'02d",$i);

            ?>
            <option><?php echo $hour; ?></option>
            <?php

        }
        ?>
    </select>
    :
    <select name="minute" id="minute">
        <option value="0">00</option>
        <option value="30">30</option>
    </select>
    Hrs<br/>
    Date format: mm/dd/yyyy
    <div style="background-color: #fefefe; padding: 5px; border: 1px solid #ccc;"> The e-mail will delivered as per your local time. Your timezone has been detected to be:<br />
        <strong>
            <input type="hidden" name="timezoneoffset" id="timezonefield" value="0" />
            <script>
                var thedate =  new Date();
                var theoffset = thedate.getTimezoneOffset();
                var theoffset = theoffset *60;
                var theDirection = (theoffset<0)?1:0;
                theoffset = (theoffset<0)?-theoffset:theoffset;
                var theHours = Math.floor(theoffset/3600);
                var theMinutes = ((theoffset%3600)/3600)*60;
                var theSymbol = (theDirection==1)?"+":"-";
                document.write("GMT "+theSymbol+" "+theHours+" hours and "+theMinutes+" minutes");
                document.getElementById('timezonefield').value=(-theoffset);
                var theBroadcastTime = <?php

                    if (empty($parameters->time))
                    {
                        echo time()+3600;
                    }
                    else
                    {
                        echo $parameters->time;
                    }  ?>;

                theBroadcastTime *= 1000;

                function setTheTime()
                {
                    var theDate = new Date();
                    if (theBroadcastTime ==0)
                        return 	false;
                    theDate.setTime(theBroadcastTime);
                    var month = (theDate.getMonth()+1).toString();
                    //add a zero if the month is a single digit.
                    if (month.length==1)
                    {
                        month = "0"+month;
                    }

                    var date = theDate.getDate().toString();
                    date = (date.length==1)?"0"+date:date;
                    var year = theDate.getFullYear();
                    theFullDate = month+"/"+date+"/"+year;

                    document.getElementById('date').value = theFullDate;
                    whichHour = theDate.getHours().toString();
                    if (whichHour.length==1)
                    {
                        whichHour = "0"+whichHour;
                    }
                    hourField = document.getElementById('hour')
                    var size = hourField.options.length;
                    for (var curr=0; curr<size;curr++)
                    {
                        if (hourField.options[curr].value==whichHour)
                        {
                            hourField.selectedIndex=curr;
                            break;
                        }
                    }

                    whichMinute = theDate.getMinutes();
                    whichMinute = (whichMinute/3600==0)?0:30;
                    minuteField = document.getElementById('minute')
                    var size = minuteField.options.length;
                    for (var curr=0; curr<size;curr++)
                    {
                        if (minuteField.options[curr].value==whichMinute)
                        {
                            minuteField.selectedIndex=curr;
                            break;
                        }
                    }
                }

                setTheTime();


                function setActualTime()
                {
                    var theFullDate = document.getElementById('date').value;
                    var theDateParts = theFullDate.split("/");
                    var themonth = theDateParts[0];
                    var thedate = theDateParts[1];
                    var theyear = theDateParts[2];

                    var theHour = document.getElementById('hour').options[document.getElementById('hour').selectedIndex].value;

                    var theMinute = document.getElementById('minute').options[document.getElementById('minute').selectedIndex].value;

                    themonth = themonth.replace(/^0/,'');
                    thedate = thedate.replace(/^0/,'');
                    theHour = theHour.replace(/^0/,'');

                    month= parseInt(themonth);
                    thedate = parseInt(thedate);
                    var goDate = new Date();
                    thedate = parseInt(thedate);
                    goDate.setDate(thedate);
                    goDate.setYear(theyear);

                    month=parseInt(themonth);
                    month -=1;
                    goDate.setMonth(month);
                    theMinute = parseInt(theMinute);

                    goDate.setMinutes(theMinute);
                    theHour = parseInt(theHour);

                    goDate.setHours(theHour);
                    goDate.setSeconds(0);
                    var utcStamp = goDate.getTime();
                    var utcStamp = utcStamp/1000;
//	utcStamp += theoffset;
                    utcStamp = Math.floor(utcStamp);
                    document.getElementById('actualTime').value= utcStamp.toString();
                }


                function getCurrentUTCTime()
                {
                    var theDate = new Date();
                    var theTime = theDate.getTime();
                    theTime = Math.floor(theTime/1000);
                    return (theTime);
                }


                function validateTheForm()
                {
                    //if the broadcast is scheduled to go out at a later time.
                    if (document.getElementById('sendattime').checked==true)
                    {
                        currentTime = getCurrentUTCTime();
                        scheduledTime = parseInt(document.getElementById('actualTime').value);
                        if (currentTime > scheduledTime)
                        {
                            alert('You cannot schedule a mailout to go out in the past. Please select a time in the future.');
                            return false;
                        }
                    }

                    if (!validateFieldValues())
                    {
                        return false;
                    }

                    return true;
                }

                window.setInterval('setActualTime()',1);
            </script>
        </strong>
        <input type="hidden" name="actualTime" value="0" id="actualTime" />
        <br />
    </div>
    </div>
    <script>

        jQuery(document).ready(function()
        {

            //jQuery("#date").datepicker({ minDate: 0});

        });



        function changeHTMLBodyFieldsAvailability(field,nameOfTheDivToHide)
        {
            if (!field)
            {
                document.getElementById(nameOfTheDivToHide).style.display = "none";
            }
            else
            {
                document.getElementById(nameOfTheDivToHide).style.display="inline";
            }

        }

        var editorExists=false;

        var editor;
        function toggleHTML()
        {
            if (editorExists)
            {



                var html = editor.getData();

                editor.destroy();

                editorExists=false;

                var textElement = document.createElement("textarea");



            }

            else

            {

                var element = document.getElementById("htmlbody");

                editor = CKEDITOR.replace("htmlbody",{
                    skin: 'moono',

                    toolbar :
                            [



                                ['Source','Maximize','ShowBlocks','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                                '/',
                                ['Bold', 'Italic','Underline','Strike', '-', 'NumberedList', 'BulletedList','-','Subscript','Superscript','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', '-','Outdent','Indent','Blockquote','-', 'Link','Image','SpellChecker'],['Link','Unlink','Anchor',],

                                '/',

                                ['Format','Font','FontSize','TextColor','BGColor','-','-','Table','CreateDiv'],


                            ]



                });

                editorExists=true;

            }

        }

        function setVisibilityOfHTMLFields()
        {
            changeHTMLBodyFieldsAvailability(document.getElementById('htmlenabled').checked,'htmlbodyfields');
        }

        setVisibilityOfHTMLFields();
        toggleHTML();

    </script></td>
    </tr>
        <?php

    }

    else

    {

        ?>
    <tr>
        <td>Send On<br />
            <small>0 for immediately after subscribing</small></td>
        <td><label for="select2"></label>
            <label for="textfield2"></label>
            <input name="sequence" type="text" id="textfield2" size="4" maxlength="3" value="<?php echo (int) $parameters->sequence ?>" />
            <label for="radio3"> Days </label>
            <script>

                jQuery(document).ready(function()

                {

                    //jQuery("#date").datepicker({ minDate: 0});

                });



                function changeHTMLBodyFieldsAvailability(field,nameOfTheDivToHide)

                {

                    if (!field)
                    {
                        document.getElementById(nameOfTheDivToHide).style.display = "none";
                    }
                    else
                    {
                        document.getElementById(nameOfTheDivToHide).style.display="inline";
                    }

                }


                var editorExists=false;

                var editor;
                function toggleHTML()
                {
                    if (editorExists)
                    {



                        var html = editor.getData();

                        editor.destroy();

                        editorExists=false;

                        var textElement = document.createElement("textarea");



                    }

                    else

                    {

                        var element = document.getElementById("htmlbody");

                        editor = CKEDITOR.replace("htmlbody",{

                            toolbar :

                                    [



                                        ['Source','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link','Image'],

                                        '/',

                                        ['Styles', 'Format','Font','FontSize','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Table'],



                                    ]



                        });

                        editorExists=true;

                    }

                }




                function setVisibilityOfHTMLFields()
                {
                    changeHTMLBodyFieldsAvailability(document.getElementById('htmlenabled').checked,'htmlbodyfields');
                }


                setVisibilityOfHTMLFields();
                toggleHTML();


            </script></td>
    </tr>
        <?php
        printTheJavascript();

    }

    ?>
    <?php if ($mode == "new")

{

    ?>
<tr>
    <td colspan="3"><br />
        <input type="hidden" name="mid" value="<?php echo $parameters->id ?>"  />
        <br /></td>
</tr>
    <?php

}
    ?>
<tr>
    <td colspan="2"><label for="button"></label>
        <input type="submit" class="button-primary" onclick="return validateTheForm();" name="button" id="button" value="<?php echo ($parameters->buttontext)?$parameters->buttontext:"Send Message";?>"/>
        <input type="button" name="PreviewEmailButton" onclick="wpr_GetHtmlBody();previewEmail()" value="Preview This Email" class="button-primary"></td>
</tr>
<script>
    function validateFieldValues()
    {

        var errors = new Array();
        //the subject must be mentioned
        subject = trim(document.getElementById('subject').value);
        count=0;
        if (subject.length== 0)
        {
            errors[count++] = "- Subject is empty. A Subject is mandatory for a broadcast";
        }

        //the text body must be mentioned
        textbody = trim(document.getElementById('textbody').value);
        if (textbody.length==0)
        {
            errors[count++] = "- Text body field is empty. A text body is mandatory for a broadcast.";

        }

        htmlbody = trim(editor.getData());
        if (document.getElementById('htmlenabled').checked==true && htmlbody.length==0)
        {
            errors[count++] = "- HTML body is enabled but the HTML Body has not filled out.";
        }

        if (errors.length > 0)
        {
            var message = "Some errors were found in the form: \n\n"+ errors.join("\n");
            alert(message);
            return false;
        }
        //if the html body is emabled, the html bdoy should have some text.
        return true;

    }
    function trim(stringToTrim) {
        stringToTrim = stringToTrim.toString();
        return stringToTrim.replace(/^\s+|\s+$/g,"");
    }
    function ltrim(stringToTrim) {
        return stringToTrim.replace(/^\s+/,"");
    }
    function rtrim(stringToTrim) {
        return stringToTrim.replace(/\s+$/,"");
    }

    function getCurrentNewsletter()

    {

        newsletter = document.getElementById('thenewsletter');

        var nid = newsletter.options[newsletter.selectedIndex].value;

        return nid;

    }

    jQuery(document).ready(
            function () {
                var ele = document.getElementById('thenewsletter')

                if (ele.tagName == "SELECT")

                {

                    var id = ele.options[document.getElementById('thenewsletter').selectedIndex].value

                }

                else

                {

                    var id = ele.value;

                }

                loadCustomFields(id)

            }
    );


</script>
</table>
</form>
</blockquote>
<?php

}

function printTheJavascript()
{
    ?>
<script>

    function changeHTMLBodyFieldsAvailability(field,nameOfTheDivToHide)

    {

        if (!field)
        {
            document.getElementById(nameOfTheDivToHide).style.display = "none";
        }
        else
        {
            document.getElementById(nameOfTheDivToHide).style.display="inline";
        }

    }


    var editorExists=false;

    var editor;
    function toggleHTML()
    {
        if (editorExists)
        {



            var html = editor.getData();

            editor.destroy();

            editorExists=false;

            var textElement = document.createElement("textarea");



        }

        else

        {

            var element = document.getElementById("htmlbody");

            editor = CKEDITOR.replace("htmlbody",{

                toolbar :

                        [



                            ['Source','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link','Image'],

                            '/',

                            ['Styles', 'Format','Font','FontSize','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Table'],



                        ]



            });

            editorExists=true;

        }

    }

    function setVisibilityOfHTMLFields()
    {
        changeHTMLBodyFieldsAvailability(document.getElementById('htmlenabled').checked,'htmlbodyfields');
        document.getElementById("htmlwrapper").style.display = (document.getElementById('htmlenabled').checked)?"block":"none";
    }


    setVisibilityOfHTMLFields();
    toggleHTML();


</script>
<?php
}





