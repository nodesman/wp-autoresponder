<div class="wrap">
    <h2>Import Subscribers: Upload CSV Files</h2>
<p></p>
Browse your computer and select the CSV file to be imported.<p><p>
</p>
</p>
    <form action="admin.php?page=_wpr/importexport&subact=step4" method="post" enctype="multipart/form-data">

        <div id="uploads">
            <div>
                <input type="file" name="csv">
            </div>
        </div>
The maximum size of uploads allowed by your server is: <?php echo ini_get(upload_max_filesize); ?>B
<p></p><p></p>
<input type="hidden" name="wpr_form" value="wpr_import_upload">
<a href="admin.php?page=_wpr/importexport&subact=step3" class="button-primary"> &laquo; Previous:  Blog Subscription</a><input type="submit" value="Next: Identifiy Columns &raquo;" class="button-primary">
    </form>


    

</div>