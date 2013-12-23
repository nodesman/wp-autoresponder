<?php
function wpr_dashboard()
{
	global $wpdb;
	$action = @$_GET['action'];
	switch ($action)
	{		
		case 'graph':
		$nid = (int) $_GET['nid'];
		show_graph($nid);
		break;
		default:
	?>
<style>
#throw {
	font-family:Tahoma, Geneva, sans-serif;
	font-size:17px;
	background-color: #dae5d8;
	color:#666;
	padding: 10px;
}
#throw td {
	padding: 10px;
	font-weight: bold;
}
#statstable {
	background-color: #dae5d8;
	margin-bottom:10px;
}
.statrow {
	background-color:#FFF;
}

#news {
	
}
</style>

<div class="wrap">
<table width="100%" >
  <tr>
    <td valign="top"><table width="100%">
        <tr>
          <td>
            <div style="display:block;">
            <h2>Subscriber Count</h2>
            <table cellpadding="10" id="statstable" width="100%">
                <br/>
              <tr id="throw">
                <td>Newsletter Name</td>
                <td>Confirmed Subscribers</td>
   				<td>Unconfirmed Subscribers</td>
   				<td>Unsubscribed Subscibers</td>
                
              </tr>
              <?php
			  
		$query = "select * from ".$wpdb->prefix."wpr_newsletters";
		$ns = $wpdb->get_results($query);
		foreach ($ns as $n)
		{
			?>
              <tr class="statrow">
                <td style="padding: 10px;"><?php echo $n->name ?></td>
                <td style="padding: 10px;"><?php
			   $nid = $n->id;
			   $query = "select count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid and active=1 and confirmed=1;";
			   $num = $wpdb->get_results($query);
			   $num = (int) $num[0]->num;
			   echo $num;
			   ?></td>
               <td style="padding: 10px;"><?php
			   $nid = $n->id;
			   $query = "select count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid and active=1 and confirmed=0;";
			   $num = $wpdb->get_results($query);
			   $num = (int) $num[0]->num;
			   echo $num;
			   ?></td>
               <td style="padding: 10px;"><?php
			   $nid = $n->id;
			   $query = sprintf("select count(*) num from {$wpdb->prefix}wpr_subscribers where nid=%d and active=0 and confirmed=1;",$nid);
			   $num = $wpdb->get_results($query);
			   $num = (int) $num[0]->num;
			   echo $num;
			   ?></td>
                
              </tr>
              <?php
		}
		
		?>
            </table>
            </div>
            <hr size="1" color="#CCCCCC"/>


<hr size="1" color="#CCCCCC"/>
<h2>Subscribe to the WP Responder Email Newsletter</h2>

Enter your name and email address below to subscribe to the  WP Responder newsletter. <br />
              <form action="http://nodesman.com/?wpr-optin=1" method="post">
  <span class="wpr-subform-hidden-fields">
  <input type="hidden" name="blogsubscription" value="none" />
    <input type="hidden" name="newsletter" value="6" />
      <input type="hidden" name="fid" value="2" />
      </span>
                  <table>
                      <tr>
                          <td><span class="wprsfl wprsfl-name">Name:</span></td>
                          <td><span class="wprsftf wpr-subform-textfield-name">
        <input type="text" name="name" /></td>
                      </tr>
                      <tr>
                          <td><span class="wprsfl wprsfl-email">E-Mail:</span></td>
                          <td><span class="wprsftf wpsftf-email">
        <input type="text" name="email" />
        </span>
                      </tr>
                      <tr>
                          <td colspan="2" align="center"><input type="submit" value="Subscribe" /></td>
                      </tr>
                      <tr>
                          <td colspan="2" align="center"></td>
                      </tr>
                  </table>
              </form>

</td>
  </tr>
</table>

        <h2>Report A Bug</h2>

Javelin is actively developed. You can see the development progress at the <a href="https://github.com/nodesman/javelin">GitHub repo</a>. While I take great deal of efforts to ensure that the plugin is bug free, a few tend to slip through. Please open a bug report at the official repo to see a fix for it in the next release. Opening a bug takes only a minute or two. <p> <a href="https://github.com/nodesman/javelin/issues/new" class="wpr-action-button">Click here to report a bug</a></p>

</div>

<?php
		break;
	}
}