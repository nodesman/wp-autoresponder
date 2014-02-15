<?php

/*
Plugin Name: Javelin
Plugin URI: http://www.nodesman.com
Description: Gather subscribers in newsletters, follow up with automated e-mails, provide subscription to all posts in your blog or individual categories.
Version: 5.3.12
Author: Raj Sekharan
Author URI: http://www.nodesman.com/
*/

$plugindir = plugin_dir_path( __FILE__ );;

define("WPR_DIR", plugin_dir_path( __FILE__ ));

$controllerDir = WPR_DIR . "/controllers";
$modelsDir = WPR_DIR."/models";
$helpersDir = WPR_DIR."/helpers";

define("WPR_VERSION", "5.3.12");
define("WPR_PLUGIN_DIR", "$plugindir");

include_once "$controllerDir/newsletters.php";
include_once "$controllerDir/custom_fields.php";
include_once "$controllerDir/importexport.php";
include_once "$controllerDir/background_procs.php";
include_once "$controllerDir/settings.php";
include_once "$controllerDir/new-broadcast.php";
include_once "$controllerDir/queue_management.php";
include_once "$controllerDir/autoresponder.php";


include_once "$modelsDir/subscriber.php";
include_once "$modelsDir/newsletter.php";
include_once "$modelsDir/autoresponder_message.php";
include_once "$modelsDir/autoresponder.php";

include_once WPR_DIR . '/home.php';
include_once WPR_DIR . '/blog_series.php';
include_once WPR_DIR . '/forms.php';
include_once WPR_DIR . '/newmail.php';
include_once WPR_DIR . '/subscribers.php';
include_once WPR_DIR . '/htmltemplates.lib.php';
include_once WPR_DIR . '/wpr_deactivate.php';
include_once WPR_DIR . '/all_mailouts.php';
include_once WPR_DIR . '/actions.php';
include_once WPR_DIR . '/blogseries.lib.php';
include_once WPR_DIR . '/lib.php';

include_once WPR_DIR . '/conf/meta.php';
include_once WPR_DIR . '/conf/config.php';
include_once WPR_DIR . '/conf/global_constants.php';

include_once WPR_DIR . '/lib/swift_required.php';
include_once WPR_DIR . '/lib/admin_notifications.php';
include_once WPR_DIR . '/lib/global.php';
include_once WPR_DIR . '/lib/custom_fields.php';
include_once WPR_DIR . '/lib/database_integrity_checker.php';
include_once WPR_DIR . '/lib/framework.php';
include_once WPR_DIR . '/lib/database_integrity_checker.php';
include_once WPR_DIR . '/lib/mail_functions.php';
include_once WPR_DIR . '/other/cron.php';
include_once WPR_DIR . '/other/firstrun.php';
include_once WPR_DIR . '/other/queue_management.php';
include_once WPR_DIR . '/other/notifications_and_tutorials.php';
include_once WPR_DIR . '/other/background.php';
include_once WPR_DIR . '/other/install.php';
include_once WPR_DIR . '/other/blog_crons.php';
include_once WPR_DIR . '/other/maintain.php';
include_once WPR_DIR . '/widget.php';

//processes
include_once WPR_DIR . '/processes/background_process.php';
include_once WPR_DIR . '/processes/autoresponder_process.php';
include_once WPR_DIR . '/processes/broadcast_processor.php';


include_once WPR_DIR . '/conf/routes.php';
include_once WPR_DIR . '/conf/events.php';
include_once WPR_DIR . '/conf/files.php';
include_once WPR_DIR . '/conf/config.php';

include_once WPR_DIR . "/helpers/routing.php";
include_once WPR_DIR . "/helpers/paging.php";

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


class Javelin
{
    private static $instance;
    private function __construct() {

        add_action('admin_init',array($this, 'admin_init'));
        add_action('init', array($this, 'init'),1);
        add_action('plugins_loaded','_wpr_nag');
        add_action('admin_menu', 'wpr_admin_menu');
        add_action('admin_head', array(&$this, 'admin_head'));
        add_action('widgets_init','wpr_widgets_init');
        register_activation_hook(__FILE__,"wpresponder_install");
        register_deactivation_hook(__FILE__,"wpresponder_deactivate");
        add_filter('cron_schedules','wpr_cronschedules');
    }

    public function install()
    {
        $this->manageCapabilities();
        $this->manageDatabaseStructure();
        $this->updateLastPostDate();
        $this->loadEmailTemplates();
        $this->setNextCronScheduleTime();
        $this->initializeOptions();
        $this->updateNotificationEmail();
        wpr_enable_tutorial();
        wpr_enable_updates();
        _wpr_schedule_crons_initial();
    }

    function admin_head() {
        ?>
        <script>
            var WPRConfig = {
                ckeditor_baseHref: '<?php echo plugins_url("ckeditor/",__FILE__) ?>'
            };
        </script>
        <?php
    }

    function init()
    {
        _wpr_load_plugin_textdomain();
        _wpr_add_required_blogseries_variables();

        if (_wpr_whether_optin_post_request())
            _wpr_optin();
        if (_wpr_whether_verify_subscription_request())
            _wpr_render_verify_email_address_page();
        if (_wpr_whether_confirm_subscription_request())
            _wpr_render_confirm_subscription();
        if (_wpr_whether_html_broadcast_view_frame_request())
            _wpr_render_broadcast_view_frame();
        if (Routing::is_template_html_request()) {
            Routing::render_template_html();
        }

        if (Routing::is_admin_popup())
            Routing::render_admin_screen_popup();
        if (Routing::whether_file_request())
            Routing::serve_file();


        if ($this->whetherBroadcastCompositionScreen()) {
            $this->enqueueAdminScripts();
        }

        if (_wpr_whether_confirmed_subscription_request())
            _wpr_render_confirmed_subscription_page();
        if (Routing::is_subscription_management_page_request())
            _wpr_render_subscription_management_page();

        _wpr_attach_cron_actions_to_functions();
        _wpr_ensure_single_instance_of_cron_is_registered(); //TODO: Get rid of this and make something more appropriate
        _wpr_attach_to_non_wpresponder_email_delivery_filter();

        do_action("_wpr_init");
    }

    public function whetherBroadcastCompositionScreen()
    {
        return (isset($_GET['page']) && (('wpresponder/newmail.php' == $_GET['page'] || ('wpresponder/allmailouts.php' == $_GET['page'] && isset($_GET['action']) && 'edit' == $_GET['action'] ))));
    }

    function admin_init()
    {
        _wpr_initialize_admin_pages();
        if (_wpr_whether_wpresponder_admin_page())
            Routing::run_controller();
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new Javelin();
        }
        return self::$instance;
    }

    private function updateLastPostDate()
    {
        delete_option("wpr_last_post_date");
        $args = array('orderby' => 'date', 'order' => 'DESC', 'numberposts' => 1, 'post_type' => 'post');
        $posts = get_posts($args);
        if (count($posts) > 0) {
            $post = $posts[0];
            $last_post_date = $post->post_date_gmt;
        } else {
            $last_post_date = date("Y-m-d H:i:s", time());
        }
        add_option("wpr_last_post_date", $last_post_date);
    }

    private function loadEmailTemplates()
    {
        //the confirm email, confirmation email and confirmed subject templates.
        $confirm_subject = file_get_contents(WPR_DIR . "/templates/confirm_subject.txt");
        $confirm_body = file_get_contents(WPR_DIR . '/templates/confirm_body.txt');
        $confirmed_subject = file_get_contents(WPR_DIR . "/templates/confirmed_subject.txt");
        $confirmed_body = file_get_contents(WPR_DIR . "/templates/confirmed_body.txt");

        update_option("wpr_confirm_subject", $confirm_subject);
        update_option("wpr_confirm_body", $confirm_body);
        update_option("wpr_confirmed_subject", $confirmed_subject);
        update_option("wpr_confirmed_body", $confirmed_body);
    }

    private function setNextCronScheduleTime()
    {
        update_option("wpr_next_cron", time() + 300);
    }

    private function manageCapabilities()
    {
        $role = get_role('administrator');
        $role->add_cap('manage_newsletters');
    }

    private function manageDatabaseStructure()
    {
        $dbInitializer = DatabaseChecker::getInstance();
        $dbInitializer->init();
    }

    private function initializeOptions()
    {
        $options = $GLOBALS['initial_wpr_options'];
        foreach ($options as $option_name => $option_value) {
            $current_value = get_option($option_name);
            if (empty($current_value)) {
                add_option($option_name, $option_value);
            }
        }
    }

    private function updateNotificationEmail()
    {
        $notificationEmail = get_option('wpr_notification_custom_email');
        if (empty($notificationEmail)) {
            add_option('wpr_notification_custom_email', 'admin_email');
        }
    }

    private function enqueueAdminScripts()
    {
        wp_enqueue_style("wpresponder-tabber", get_bloginfo("wpurl") . "/?wpr-file=tabber.css");
        wp_enqueue_style("wpr-jquery-ui");
        wp_enqueue_script("wpresponder-tabber");
        wp_enqueue_script("wpresponder-scripts");
        wp_enqueue_script("wpresponder-ckeditor");
        wp_enqueue_script("jquery");
    }
}

register_activation_hook(__FILE__, array(Javelin::getInstance(), 'install'));

function no_address_error()
{
    ?><div class="error fade"><p><strong>You must set your address in the  <a href="<?php echo admin_url( 'admin.php?page=_wpr/settings' ) ?>"> newsletter settings page</a>.</strong></p></div><?php
}

function _wpr_enqueue_admin_scripts_and_styles()
{
    $url = (isset($_GET['page']))?$_GET['page']:'';
    $querystring  = $_SERVER['QUERY_STRING'];

    if (isset($_GET['page']) && preg_match("@^_wpr/@", $_GET['page'])) {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script("wpresponder-scripts");
        wp_enqueue_script('post');
    }
    $whetherBroadcastEditPage = preg_match("@wpresponder/allmailouts.php&action=edit@", $querystring);


    if (preg_match("@newmail\.php@", $url) || preg_match("@autoresponder\.php@", $url) || $whetherBroadcastEditPage == true) {
          wp_enqueue_script("wpresponder-ckeditor");
        wp_enqueue_script("jquery");
    }

    wp_enqueue_style("wpr-jquery-ui");

    add_action("admin_head", "_wpr_admin_enqueue_less");
}

function _wpr_admin_enqueue_less()
{
?>
<link rel="stylesheet/less" type="text/css" href="<?php echo get_bloginfo('wpurl') ?>/?wpr-file=admin-ui.less"/>
<script type="text/javascript" src="<?php echo get_bloginfo('wpurl') ?>/?wpr-file=less.js"></script>
<?php
 }

function _wpr_load_plugin_textdomain()
{
    $domain = 'wpr_autoresponder';
    $locale = apply_filters('plugin_locale', get_locale(), $domain);
    $plugindir = dirname(plugin_basename(__FILE__));
    load_textdomain($domain, WP_LANG_DIR.'/'.$plugindir.'/'.$domain.'-'.$locale.'.mo');
    load_plugin_textdomain($domain, FALSE, $plugindir.'/languages/');
}

function _wpr_do_first_run_initializations()
{
    _wpr_firstrunv526();
    add_option("_wpr_firstrunv526", "done");
}

function _wpr_whether_first_run()
{
    return get_option("_wpr_firstrun") != "done";
}


function _wpr_initialize_admin_pages()
{
    _wpr_register_wpresponder_scripts();
    _wpr_register_wpresponder_styles();
    _wpr_enqueue_admin_scripts_and_styles();
}

function _wpr_attach_to_non_wpresponder_email_delivery_filter()
{
    add_filter("wp_mail", "_wpr_non_wpr_email_sent");
}

//TODO: Refactor the contents of manage.php
function _wpr_render_subscription_management_page()
{
    include "manage.php";
    exit;
}

function _wpr_render_confirmed_subscription_page()
{
    include "confirmed.php";
    exit;
}

function _wpr_whether_confirmed_subscription_request()
{
    return isset($_GET['wpr-confirm']) && $_GET['wpr-confirm'] == 2;
}


function _wpr_register_wpresponder_scripts()
{

    $url = get_bloginfo("wpurl");
    wp_register_script("jqueryui-full", "$url/?wpr-file=jqui.js");
    wp_register_script("angularjs", "$url/?wpr-file=angular.js");
    wp_register_script("wpresponder-tabber", "$url/?wpr-file=tabber.js");
    wp_register_script("wpresponder-ckeditor", plugin_dir_url(__FILE__) ."ckeditor/ckeditor.js");
    wp_register_script("wpresponder-scripts", get_bloginfo('wpurl').'/?wpr-file=script.js');
}

function _wpr_register_wpresponder_styles() {

    wp_register_style('wpr-jquery-ui', plugin_dir_url(__FILE__).'/jqueryui.css');
}

function _wpr_whether_wpresponder_admin_page()
{
    return (is_admin() && Routing::isWPRAdminPage() && !Routing::whetherLegacyURL($_GET['page'])) || (whetherActionsPage()) ;
}

function whetherActionsPage()
{
    if (!isset($_GET['page']))
        return false;
    return 'wpresponder/actions.php' == $_GET['page'];
}

function _wpr_render_broadcast_view_frame()
{
    $vb = intval($_GET['wpr-vb']);
    if (isset($_GET['wpr-vb']) && $vb > 0) {
        require "broadcast_html_frame.php";
        exit;
    }
}

function _wpr_whether_html_broadcast_view_frame_request()
{
    return isset($_GET['wpr-vb']);
}

function _wpr_whether_confirm_subscription_request()
{
    return isset($_GET['wpr-confirm']) && $_GET['wpr-confirm'] != 2;
}

function _wpr_render_confirm_subscription()
{
    include "confirm.php";
    exit;
}

function _wpr_whether_verify_subscription_request()
{
    return isset($_GET['wpr-optin']) && $_GET['wpr-optin'] == 2;
}

function _wpr_render_verify_email_address_page()
{
    require "verify.php";
    exit;
}

function _wpr_optin()
{
    require "optin.php";
    exit;
}

function _wpr_whether_optin_post_request()
{
    return isset($_GET['wpr-optin']) && $_GET['wpr-optin'] == 1;
}

function _wpr_add_required_blogseries_variables()
{
    $activationDate = get_option("_wpr_NEWAGE_activation");
    if (empty($activationDate) || !$activationDate) {
        $timeNow = time();
        update_option("_wpr_NEWAGE_activation", $timeNow);
    }
}

function _wpr_ensure_single_instance_of_cron_is_registered()
{
    /*
     * The following code ensures that the WP Responder's crons are always scheduled no matter what
     * Sometimes the crons go missing from cron's registry. Only the great zeus knows why that happens.
     * The following code ensures that the crons are always scheduled immediately after they go missing.
     * It also unenqueues duplicate crons that get enqueued when the plugin is deactivated and then reactivated.
     */

    $last_run_esic = intval(_wpr_option_get("_wpr_ensure_single_instances_of_crons_last_run"));
    $timeSinceLast = time() - $last_run_esic;
    if ($timeSinceLast > WPR_ENSURE_SINGLE_INSTANCE_CHECK_PERIODICITY) {
        do_action("_wpr_ensure_single_instances_of_crons");
        $currentTime = time();
        _wpr_option_set("_wpr_ensure_single_instances_of_crons_last_run", $currentTime);
    }
}

function wpr_widgets_init()
{
    return register_widget("WP_Subscription_Form_Widget");
}
