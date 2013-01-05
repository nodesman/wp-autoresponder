<div class="wrap">
    <h2>Import/Export Subscribers</h2>

    <h3>Export Subscribers</h3>
    <table class="widefat" style="width: 500px;">
        <thead>
            <tr>
                <th>Newsletter</th>
                <th>Download</th>
            </tr>
        </thead>
        <?php
        foreach ($newslettersList as $newsletter)
        {
        ?>
        <tr>
            <td><?php echo $newsletter->name ?></td>
            <td>
            <form action="admin.php?page=_wpr/importexport" method="post">
            <input type="hidden" name="wpr_form" value="wpr_subscriber_export" />
            <input type="hidden" name="newsletter" value="<?php echo $newsletter->id ?>" />
            <input type="submit" value="Download" class="button-primary">
            </form></td>
            
        </tr><?php
        }
        ?>
        </thead>
    </table>
</div>


<h3>Import Subscribers</h3>

Import a subscriber to your newsletter. Import from any service or program - Feedburner, Aweber, Feedblitz, etc. You can import your subscribers if you have the subscriber list in a CSV format. Select the newsletter below to import subscribers:

<p></p>
<?php
if (count($newslettersList))
{
	?>
<form action="admin.php?page=_wpr/importexport&subact=step1" method="post">

Select a newsletter: <select name="newsletter">
<?php
foreach ($newslettersList as $newsletter)
{
?>
<option <?php if ($_SESSION['wpr_import_newsletter']== $newsletter->id) echo 'selected="selected"'; ?> value="<?php echo $newsletter->id ?>"><?php echo $newsletter->name ?></option>
<?php	
}
?>
</select>
<p></p><p></p>
<?php do_action("_wpr_import_form"); ?>
<input type="hidden" name="wpr_form" value="wpr_import_first" />
<input class="button-primary" type="submit" value="Next &raquo;" />
</form>
<?php
}
else
{
	?>
    <strong>No newsletters created. Create a newsletter before importing subscribers</strong>
    <?php
}?>
