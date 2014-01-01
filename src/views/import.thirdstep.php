<div class="wrap">
<h2>Import Subscribers: Blog Subscription</h2>

<p>Select whether and how these subscribers will be subscribed to the blog.</p>
<form action="admin.php?page=_wpr/importexport&subact=step3" method="post">
<select name="blogsubscription">
    <option  <?php if ($_SESSION['_wpr_import_blogsub'] == "none" || empty($_SESSION['_wpr_import_blogsub'])) echo 'selected="selected"'; ?>  value="none">None</option>
    <option <?php if ($_SESSION['_wpr_import_blogsub'] == "all") echo 'selected="selected"'; ?>  value="all">All Posts Published On This Blog</option>
    <?php if (count($categoryList)>0 && !empty($categoryList))
    {
        ?>
    <optgroup label="Subscribe to Category:">
        <?php foreach ($categoryList as $category)
        {
            ?>
        <option <?php if ($_SESSION['_wpr_import_blogsub'] == "category_".$category->term_id) echo 'selected="selected"'; ?> value="category_<?php echo $category->term_id ?>"><?php echo $category->name ?></option>
        <?php
        }
    ?>
    </optgroup>
<?php
    }
    ?>
</select>
    <input type="hidden" name="wpr_form" value="wpr_import_blogsub">
    <p></p>
    <a href="admin.php?page=_wpr/importexport&subact=step2" class="button-primary">&laquo; Previous: Follow Up</a> <input class="button-primary" type="submit" value="Next: Upload CSV File(s) &raquo;">
</form>
</div>






