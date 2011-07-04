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
<div style="display:block;">
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
			   $query = "select count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid and active=0 and confirmed=0;";
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
<div align="center"><div style="clear:both;display:block"><a href="http://www.krusible.com"><img src="http://www.wpresponder.com/dashboard.png" /></a></div></div>

<hr size="1" color="#CCCCCC"/>
<h2>Subscribe to the WP Responder Email Newsletter</h2>

Enter your name and email address below to subscribe to the  WP Responder newsletter. <br />
<table>
  <tr>
   <td style="padding-left:40px; border: 1px solid #ccc; background-color:#FFF; padding:20px;">
   <form action="http://www.wpresponder.com/?wpr-optin=1" method="post">
  <span class="wpr-subform-hidden-fields">
  <input type="hidden" name="blogsubscription" value="all" />
    <input type="hidden" name="newsletter" value="1" />
      <input type="hidden" name="fid" value="1" />
      </span>
  <table>
    <tr>
      <td><span class="wprsfl wprsfl-name">Name:</span></td>
      <td><span class="wprsftf wpr-subform-textfield-name"><input type="text" name="name" /></td>
    </tr>
    <tr>
      <td><span class="wprsfl wprsfl-email">E-Mail Address:</span></td>
      <td><span class="wprsftf wpsftf-email"><input type="text" name="email" /></span>
    </tr>
        <tr>
      <td colspan="2" align="center"><input type="submit" value="Subscribe" class="button-primary" /></td>
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
<div id="reportbug"><form method="post" id="reportform" action="http://www.expeditionpost.com/wpr/sb.php">
                <table width="100%">
                  <tr>
                    <td>Name:</td>
                    <td><label>
                        <input type="text" size="90" name="name" id="name" />
                      </label></td>
                  </tr>
                  <tr>
                    <td>E-Mail Address:</td>
                    <td><label>
                        <input type="text" size="90" name="email" id="email" />
                      </label></td>
                  </tr>
                  <tr>
                    <td>Description:</td>
                    <td><label>
                        <textarea name="desc" id="desc" cols="60" rows="6"></textarea>
                      </label></td>
                  </tr>
                  <tr>
                    <td>Steps To Replicate:</td>
                    <td><label>
                        <textarea name="stepstoreplicate" id="stepstoreplicate" cols="60" rows="5"></textarea>
                      </label></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td><label>
                        <input name="button" class="button-primary" type="submit" id="button" onclick="MM_validateForm('name','','R','email','','RisEmail','title','','R','desc','','R','stepstoreplicate','','R');return document.MM_returnValue" value="Submit Bug" />
                      </label></td>
                  </tr>
                </table>
              </form>
            </div></td>
          <td></td>
        </tr>
      </table></td>
  </tr>
</table>
</div>
</a>
<?php
		break;
	}
}

