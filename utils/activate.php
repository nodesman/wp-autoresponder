<?php
error_reporting(E_ALL);
include "wp-config.php";
include "wp-admin/includes/plugin.php";
$dir = getcwd();

$plugin_file = $dir."/wp-content/plugins/wp-responder/wpresponder.php";


function run_activate_plugin( $plugin ) {
    $current = get_option( 'active_plugins' );
    $plugin = plugin_basename( trim( $plugin ) );
    

    if ( !in_array( $plugin, $current ) ) {
        $current[] = $plugin;
        sort( $current );
        do_action( 'activate_plugin', trim( $plugin ) );
        update_option( 'active_plugins', $current );
        do_action( 'activate_' . trim( $plugin ) );
        do_action( 'activated_plugin', trim( $plugin) );
    }

    return null;
}

run_activate_plugin($plugin_file);
include $plugin_file;

Javelin::getInstance()->install();