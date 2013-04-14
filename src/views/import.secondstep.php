<div class="wrap"><?php

?>
<h2>Select Followup Subscription</h2>

    As soon as importing the subscribers in the CSV file, the subscribers can be followed up with some follow-up content - <a href="admin.php?page=wpresponder/autoresponder.php">autoresponders</a> or <a href="admin.php?page=wpresponder/blogseries.php">post series</a>.
    <p></p>
    <?php
    if (count($autoresponderList) || count($postseriesList))
    {
        ?>

  
    <form action="admin.php?page=_wpr/importexport&subact=step2" method="post">  Select follow-up subscription:
    <select name="followup">
        <option value="none" <?php if ($_SESSION['wpr_import_followup']=="none" || empty($_SESSION['wpr_import_followup'])) {
            echo 'selected="selected"' ;
            }
            ?> >None</option>
            <?php
        if (count($autoresponderList))
        {
            ?>
        <optgroup label="Autoresponder:">
            <?php
            foreach ($autoresponderList as $autoresponder)
            {
              ?><option <?php if ($_SESSION['wpr_import_followup'] == "autoresponder_".$autoresponder->getId()) echo 'selected="selected"'; ?> value="autoresponder_<?php echo $autoresponder->getId() ?>"><?php echo $autoresponder->getName() ?></option>
              <?php
            }
            ?>
        </optgroup>
        <?php
        }
        ?>

        <?php
        if (!empty($postseriesList) && count($postseriesList)>0)
        {
            
            ?><optgroup label="Post Series:">
                <?php
                foreach ($postseriesList as $postseries)
                {
                    ?><option <?php if ($_SESSION['wpr_import_followup'] == "postseries_".$postseries->id) echo 'selected="selected"'; ?>  value="postseries_<?php echo $postseries->id ?>"><?php echo $postseries->name ?></option>
                <?php
                
                }
                ?>
            </optgroup>
        <?php
        }
        ?>
    </select>
        <input type="hidden" name="wpr_form" value="wpr_import_followup">
        <div style="clear:both"></div>
        <p><p>
            
        </p>
        <a href="admin.php?page=_wpr/importexport" class="button-primary" style="float:left;">&laquo; Prev: Newsletter</a>
        <input style="float:right" type="submit" value="Next: Blog Subscription &raquo;" class="button-primary">
        <input type="hidden" name="wpr_form" value="wpr_import_followup"/>
    </form>
    <?php
    }
    else
    {
    ?>
    <strong>No autoresponders or post series have been created. Create them before
    proceeding or click next to skip configuring follow-up subscription.</strong>
    <p><p></p></p>
<a href="admin.php?page=_wpr/importexport" class="button-primary" style="float:left;">&laquo; Prev: Newsletter</a> <a style="float:right" href="admin.php?_wpr/importexport&subact=step3" class="button-primary">Next: Blog Subscription &raquo;</a>
    <?php
    }
    ?>
    

</div>