<?php

function wpr_blogseries()
{
	$action = $_GET['action'];
	
	switch ($action)
	{

		case 'create':
		    _wpr_blog_series_create();
		
		break;
		case 'delete':
			
		_wpr_blog_series_delete();
		break;
		default:
		_wpr_blog_series_list();
	}
	
}

function _wpr_blog_series_delete()
{
	global $wpdb;
	$id = $_GET['bid'];
	if ($_GET['confirm'] == "true")
	{
		$bid = $_GET['bid'];
		$query = "DELETE FROM ".$wpdb->prefix."wpr_blog_series WHERE id=".$bid;
		$wpdb->query($query);
		$query = "DELETE FROM ".$wpdb->prefix."wpr_followup_subscriptions where type='blogseries' and eid='$bid'";
		$wpdb->query($query);
		?>
        <script>window.location='admin.php?page=wpresponder/blogseries.php';</script><?php
	}
	else
	{
		?>
        <div class="wrap"><h2>Delete Post Series</h2></div>
        <div style="background-color:#FFFF80; border: 1px solid #A4A400; padding:10px;"> Are you sure you want to delete this post series?</div>
<br />
        This will not delete any blog posts or categories. It will stop sending the blog posts to users who have opted-in to receive this post series by e-mail.<br/>
<br />
        <a href="<?php echo $_SERVER['REQUEST_URI'] ?>&confirm=true" class="button">Yes</a>  <a href="admin.php?page=wpresponder/blogseries.php" class="button">Cancel</a>
        <?php
	}
}

function _wpr_blog_series_list()
{
  global $wpdb;
  ?><div class="wrap">
  <h2>Manage Post Series</h2>
  <p>With post series you can use your blog posts as follow up autoresponder emails. To do so:
  <ol><li>Create a new category using the Posts > Categories link (say 'Email Marketing Series').
      <li>Either edit the previous posts that are part of the post series and mark them under this category or if you are yet to add the posts, add the new posts and mark the new posts under this category while publishing.</li>
      <li>Create a new Post series using the "Create" button below. Select the category from step 2.</li>
      <li>That's it. Now create a new subscription form. Select the follow up series to the post series you just selected. Each subscriber subscribing using that form will receive the posts in the post series in the order they were published.</li>
  </ol>
</div>
<form action="<? echo $_SERVER['PHP_SELF'] ?>">
  <table class="widefat">
    <tr>
    <thead>
    <th>Name</th>
      <th>Category</th>
      <th>Actions</th>
      </thead>
      <?php
	  $query = "SELECT * FROM ".$wpdb->prefix."wpr_blog_series";
	  $bseries = $wpdb->get_results($query);
	  foreach ($bseries as $series)
	  {
		  ?>
    <tr>
      <td><?php echo $series->name ?></td>
      <td><?php $taxonomy = get_category($series->catid);
			 echo $taxonomy->name ;
			 ?></td>
      <td><input type="button" value="Delete" onclick="window.location='admin.php?page=wpresponder/blogseries.php&action=delete&bid=<?php echo $series->id ?>'" class="button" /></td>
    </tr>
    <?php
	  }
	  ?>
    </tr>
  </table>

</form>
<input type="button" value="Create" onclick="window.location='admin.php?page=wpresponder/blogseries.php&action=create';" style="margin: 10px;clear:both;" class="button" />
<?php
}

function _wpr_blog_series_create()
{

	global $wpdb;
	if (isset($_POST['name']))
	{
		$name  = $_POST['name'];
		$cid = $_POST['category'];
		$freq = $_POST['frequency'];
		if ($name)
		{
			$query = "INSERT INTO ".$wpdb->prefix."wpr_blog_series (name, catid,frequency) VALUES ('$name','$cid','$freq');";
			$wpdb->query($query);
			?>
<script>window.location='admin.php?page=wpresponder/blogseries.php'</script>
<?php
exit;
		}
		else
		{
			$error = "Name Field Is Required";
		}
		
	}
	
	?>
<div align="center" style="color:red; font-weight:bold"><?php echo $error ?></div>
<div class="wrap">

  <h2>Create Post Series</h2>
</div>


<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
  <table>
    <tr>
      <td>Name:</td>
      <td><input type="text" name="name"></td>
    </tr>
    <tr>
      <td><div id="taxonomy">Category*</div></td>
      <td><select id="taxonomylist" name="category">
          <?php  
	  
	$args = array(
    'type'                     => 'post',
    'child_of'                 => 0,
    'orderby'                  => 'name',
    'order'                    => 'ASC',
    'hide_empty'               => false,
    'include_last_update_time' => false,
    'hierarchical'             => 0,
    'pad_counts'               => false );
	$categories = get_categories($args);
	  foreach ($categories as $category)
	  {
		  if ($category->term_id ==0)
		  {
			  continue;
		  }
		  ?>
          <option value="<?php echo $category->term_id ?>"><?php echo  $category->cat_name ?></option>
          <?php
	  }
	  
	  ?>
        </select>
    </tr>
    <tr>
       <td>Time between posts :</td>
       <td>Send a post every <input type="text" name="frequency" value="1" size="2" /> days.</td>
    <tr>
      <td><input type="submit" class="button-primary" value="Create" /></td>
    </tr>
  </table>
</form>
<?php
}



