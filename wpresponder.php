<?php
/*
Plugin Name: WP Autoresponder
Plugin URI: http://www.wpresponder.com
Description: Gather subscribers in newsletters, follow up with automated e-mails, provide subscription to all posts in your blog or individual categories.
Version: 5.2.8
Author: Raj Sekharan
Author URI: http://www.krusible.com/
*/


//protect from multiple copies of the plugin. this can happen sometimes.

if (!defined("WPR_DEFS"))
{
    define("WPR_DEFS",1);
    $plugindir =  str_replace(basename(__FILE__),"",__FILE__);
    $plugindir = str_replace('\\','/',$plugindir);
    $plugindir = rtrim($plugindir,"/");
    $controllerDir = "$plugindir/controllers";
    $modelsDir = "$plugindir/models";
    $helpersDir = "$plugindir/helpers";

    define("WPR_VERSION","5.2.8");
    define("WPR_PLUGIN_DIR","$plugindir");

    $GLOBALS['WPR_PLUGIN_DIR'] = $plugindir;
    include_once "home.php" ;	
    include_once "autoresponder.php";
    include_once "blog_series.php";
    include_once "forms.php";
    include_once "newmail.php";
    include_once "customizeblogemail.php";
    include_once "subscribers.php";
    include_once "wpr_deactivate.php";
    include_once "all_mailouts.php";
    include_once "actions.php";
    include_once "blogseries.lib.php";
    include_once "lib.php";
    require_once "$plugindir/meta.php";
    require_once "$plugindir/lib/swift_required.php";
    require_once "$plugindir/lib/admin_notifications.php";
    require_once "$plugindir/lib/global.php";
    require_once "$plugindir/lib/custom_fields.php";
    require_once "$plugindir/lib/database_integrity_checker.php";
    require_once "$plugindir/lib/framework.php";
    require_once "$plugindir/lib/database_integrity_checker.php";    
    require_once "$plugindir/lib/mail_functions.php";
    require_once "$plugindir/other/cron.php";
    require_once "$plugindir/other/firstrun.php";
    require_once "$plugindir/other/queue_management.php";
    require_once "$plugindir/other/notifications_and_tutorials.php";
    require_once "$plugindir/other/background.php";
    require_once "$plugindir/other/install.php";
    require_once "$plugindir/other/maintain.php";
    require_once "$plugindir/other/blog_crons.php";
    include_once "widget.php";
    require_once "$controllerDir/newsletters.php";
    require_once "$controllerDir/custom_fields.php";
    require_once "$controllerDir/importexport.php";
    require_once "$controllerDir/background_procs.php";
    require_once "$controllerDir/settings.php";
    require_once "$controllerDir/new-broadcast.php";
    require_once "$controllerDir/queue_management.php";
    require_once "$modelsDir/subscriber.php";
    require_once "$modelsDir/newsletter.php";
    require_once "$helpersDir/routing.php";

    $GLOBALS['db_checker'] = new DatabaseChecker();
    $GLOBALS['wpr_globals'] = array();

	function _wpr_nag()
	{
		$address = get_option("wpr_address");		
		if (!$address && current_user_can("manage_newsletters"))  
		{
			add_action("admin_notices","no_address_error");	
		}
		
		
		add_action("admin_notices","_wpr_admin_notices_show");
		
	}
	
	add_action("plugins_loaded","_wpr_nag");
        add_action("admin_init","_wpr_admin_init");
	
	function no_address_error()
	{
            ?><div class="error fade"><p><strong>You must set your address in the  <a href="<?php echo admin_url( 'admin.php?page=_wpr/settings' ) ?>"> newsletter settings page</a>. It is a mandatory requirement for conformance with CAN-SPAM act guidelines (in USA).</strong></p></div><?php
	}
	
	function _wpr_no_newsletters($message)
	{
		
		global $wpdb;
	
		$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters"; 
	
		$countOfNewsletters = $wpdb->get_results($query);
	
		$count = count($countOfNewsletters);
	
		unset($countOfNewsletters);
	
		if ($count ==0)
		{
	
			?>
<div class="wrap">
  <h2>No Newsletters Created Yet</h2>

<?php echo $message ?>, you must first create a newsletter. <br />
<br/>
<a href="admin.php?page=_wpr/newsletter&act=add" class="button">Create Newsletter</a>
</div>
<?php
	
			return true;
	
		}
	
		else
	
			return false;
		
	}
	
	
	function wpr_enqueue_post_page_scripts()
	{
		if (isset($_GET['post_type']) && $_GET['post_type'] == "page")
		{
			return;		
		}
		
		$directory = str_replace("wpresponder.php","",__FILE__);
		$containingdirectory = basename($directory);
		wp_enqueue_style("wpresponder-tabber","/".PLUGINDIR."/".$containingdirectory."/tabber.css");
		wp_enqueue_script("wpresponder-tabber");
		wp_enqueue_script("wpresponder-addedit");		
		 wp_enqueue_script("wpresponder-ckeditor");
		wp_enqueue_script("jquery");
	}
	
	
	function wpr_enqueue_admin_scripts()
	{
		$directory = str_replace("wpresponder.php","",__FILE__);
		$containingdirectory = basename($directory);
		$home_url = get_bloginfo("url");
		if (current_user_can('manage_newsletters') && isset($_GET['page']) && ereg("_wpr/.*",$_GET['page']))
		{
			wp_enqueue_script('post');

			wp_enqueue_script('editor');
			wp_enqueue_script('word-count');
			wp_enqueue_script('wpresponder-uis',"$home_url/?wpr-file=jqui.js");
			wp_enqueue_style("wpresponder-ui-style","$home_url/?wpr-file=jqui.css");		
			add_thickbox();
			wp_enqueue_script('media-upload');
			wp_enqueue_script('quicktags');
			add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 50 );
			wp_enqueue_script('jquery');
			wp_enqueue_script('jqueryui-full');			
		}
		$url = $_GET['page'];
		if (preg_match("@newmail\.php@",$url) || preg_match("@autoresponder\.php@",$url)|| preg_match("@allmailouts\.php\&action=edit@",$url))
		{
			wp_enqueue_script("wpresponder-ckeditor");
			wp_enqueue_script("jquery");
		}

	}	
        
        
        
        function _wpr_admin_init()
        {
            
            //first run?
            $first_run = get_option("_wpr_firstrunv526");
            if ($first_run != "done")
            {
                    _wpr_firstrunv526();
                    add_option("_wpr_firstrunv526","done");
            }
        }
        
	
	function wpresponder_init_method() 
	{
		//load the scripts only for the administrator.
		global $current_user;
		global $db_checker;
                
                $activationDate = get_option("_wpr_NEWAGE_activation");
                if (empty($activationDate) || !$activationDate)
                {
                    $timeNow = time();
                    update_option("_wpr_NEWAGE_activation",$timeNow);
                    /*
                     * Because of the lack of tracking that was done in previous versions
                     * of the blog category subscriptions, this version will deliver
                     * blog posts to blog category subscribers ONLY after this date 
                     * This was done to prevent triggering a full delivery of all 
                     * blog posts in all categories to the respective category subscribers
                     * on upgrade to this version.
                     * I came up with the lousy name. Was a good idea at the time. 
                     */
                }

		if (isset($_GET['wpr-optin']) && $_GET['wpr-optin'] == 1)
		{
			require "optin.php";			
			exit;
		}
		
		if (isset($_GET['wpr-optin']) && $_GET['wpr-optin'] == 2)
		{
			require "verify.php";	
			exit;
		}
                
                
		
		//a subscriber is trying to confirm their subscription. 
		if (isset($_GET['wpr-confirm']) && $_GET['wpr-confirm']!=2)
		{
			include "confirm.php";			
			exit;
		}
		
		if (isset($_GET['wpr-vb']))
 		{
		    $vb = intval($_GET['wpr-vb']);
		    if (isset($_GET['wpr-vb']) && $vb > 0)
		    {
		       require "broadcast_html_frame.php";
		       exit;
		    }
                }
   		
        
		
		require WPR_PLUGIN_DIR."/proxy.php";
		

		
		do_action("_wpr_init");
                
		$admin_page_definitions = $GLOBALS['admin_pages_definitions'];
		foreach ($admin_page_definitions as $item)
		{
			if (isset($item['legacy']) && $item['legacy']===0)
			{
				$slug = str_replace("_wpr/","",$item['menu_slug']);
				$actionName = "_wpr_".$slug."_handle";
				$handler = "_wpr_".$slug."_handler";
				add_action($actionName,$handler);
			}
		}
		_wpr_attach_cron_actions_to_functions();
	
		add_action('admin_menu', 'wpr_admin_menu');
                
		/*
		 * This is needed until all the pages are migrated to the
		 * MVC format. 
		 */

		if (isset($_GET['page']) && ( ereg("^wpresponder/.*",$_GET['page']) || ereg("^_wpr/.*",$_GET['page'])))
		{
			_wpr_handle_post();
	 		_wpr_run_controller();
		}
		//a visitor is trying to subscribe.
		$directory = str_replace(basename(__FILE__),"",__FILE__);
		$containingdirectory = basename($directory);
		$url = get_bloginfo("url");
		wp_register_script( "jqueryui-full","$url/?wpr-file=jqui.js" );
		wp_register_script( "wpresponder-tabber", "$url/?wpr-file=tabber.js");
		wp_register_script( "wpresponder-ckeditor", "/".PLUGINDIR."/".$containingdirectory."/ckeditor/ckeditor.js");
		wp_register_script( "wpresponder-addedit", "/".PLUGINDIR."/".$containingdirectory."/script.js");
                
                
                /*
                 * The following code ensures that the WP Responder's crons are always scheduled no matter what
                 * Sometimes the crons go missing from cron's registry. Only the great zeus knows why that happens. 
                 * The following code ensures that the crons are always scheduled immediately after they go missing. 
                 * It also unenqueues duplicate crons that get enqueued when the plugin is deactivated and then reactivated.
                 */
                
                //run the single instances every day once:
                $last_run_esic = intval(_wpr_option_get("_wpr_ensure_single_instances_of_crons_last_run"));
                $timeSinceLast = time() - $last_run_esic;
                if ($timeSinceLast > WPR_ENSURE_SINGLE_INSTANCE_CHECK_PERIODICITY)
                {
                    do_action("_wpr_ensure_single_instances_of_crons");
                    $currentTime= time();
                    _wpr_option_set("_wpr_ensure_single_instances_of_crons_last_run", $currentTime );
                }
		
		if (isset($_GET['wpr-confirm']) && $_GET['wpr-confirm']==2)
		{
			include "confirmed.php";
			exit;
		}
		
		if (isset($_GET['wpr-manage']))
		{
			include "manage.php";
			exit;
		}
		
		if (isset($_GET['wpr-admin-action']) )
		{
			switch ($_GET['wpr-admin-action'])
			{
				case 'preview_email':
					include "preview_email.php";
					exit;
				break;
				case 'view_recipients':
					include("view_recipients.php");
					exit;
				break;
				case 'filter':
					include("filter.php");
					exit;
				break;
				case 'delete_mailout':
				
				include "delmailout.php";
				exit;
				
				break;
				case '':
				
				break;
				
                        }
		}
		
		if (isset($_GET['wpr-template']))
		{
			include "templateproxy.php";
			exit;
		}
		
		 add_action('admin_init','wpr_enqueue_admin_scripts');
		 add_action('admin_menu', 'wpresponder_meta_box_add');
		 //count all non-WP Autoresponder emails so that the hourly limit can be suitably adjusted
		 add_filter("wp_mail","_wpr_non_wpr_email_sent");
		 

		 add_action('edit_post', "wpr_edit_post_save");
		 add_action('load-post-new.php','wpr_enqueue_post_page_scripts');
		 add_action('admin_action_edit','wpr_enqueue_post_page_scripts');
		 add_action('publish_post', "wpr_add_post_save");	
	}    
	
	add_action('widgets_init','wpr_widgets_init');
	add_action('init', "wpresponder_init_method",1);
	register_activation_hook(__FILE__,"wpresponder_install");
	register_deactivation_hook(__FILE__,"wpresponder_deactivate");
	$url = $_SERVER['REQUEST_URI'];	
	
	function wpr_admin_menu()
	{
		add_menu_page('Newsletters','Newsletters','manage_newsletters',__FILE__);
		add_submenu_page(__FILE__,'Dashboard','Dashboard','manage_newsletters',__FILE__,"wpr_dashboard");
		$admin_pages_definitions = $GLOBALS['admin_pages_definitions'];
		$admin_pages_definitions = apply_filters("_wpr_menu_definition",$admin_pages_definitions);
		foreach ($admin_pages_definitions as $definition)
		{
			add_submenu_page(__FILE__,$definition['page_title'],$definition['menu_title'],$definition['capability'],$definition['menu_slug'],$definition['callback']);
		}
	}
	
	function wp_credits()
	{
		echo base64_decode("PGJyIC8+DQo8YnIgLz4NCjxkaXYgc3R5bGU9ImJvcmRlcjogMXB4IHNvbGlkICMwMDAwMDA7IHRleHQtYWxpZ246Y2VudGVyOyBiYWNrZ3JvdW5kLWNvbG9yOiNGQUU2QjQ7IHBhZGRpbmc6IDEwcHg7IG1hcmdpbi1sZWZ0OmF1dG87IG1hcmdpbi1yaWdodDphdXRvOyB3aWR0aDo1MDBweDsiPlBvd2VyZWQgYnkgPGEgaHJlZj0iaHR0cDovL3d3dy53cHJlc3BvbmRlci5jb20vIj5XUCBSZXNwb25kZXI8L2E+PC9kaXY+");	
	}

	function wpr_widgets_init()
	{
		return register_widget("WP_Subscription_Form_Widget");
	}

    add_filter('cron_schedules','wpr_cronschedules');
	
	
}
