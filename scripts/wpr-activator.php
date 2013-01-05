<?php 
include __DIR__."/wp-config.php";
include __DIR__."/wp-admin/includes/plugin.php";
echo "About to install ".__DIR__."/wp-content/plugins/wp-responder-email-autoresponder-and-newsletter-plugin/wpresponder.php";
if (is_file(__DIR__."/wp-content/plugins/wp-responder-email-autoresponder-and-newsletter-plugin/wpresponder.php")) {
   echo "Plugin file found"; 
   var_dump(activate_plugin(__DIR__."/wp-content/plugins/wp-responder-email-autoresponder-and-newsletter-plugin/wpresponder.php"));

    print_r(get_option('active_plugins'));
    mysql_connect("127.0.0.1", "root", "");
    mysql_select_db("myapp_test");
    $res = mysql_query("SHOW TABLES;");
    while ($table = mysql_fetch_object($res)) {
      print_r($table);
   }
}
else {
   echo "File not found";
   exit(1);
}
