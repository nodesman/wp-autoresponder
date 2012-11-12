<?php
/**
 * Created by JetBrains PhpStorm.
 * User: raj
 * Date: 11/3/12
 * Time: 5:23 PM
 * To change this template use File | Settings | File Templates.
 */

function _wpr_broadcast_compose() {

    wp_enqueue_script('jquery-ui');
    wp_enqueue_script('jquery-ui-tabs');

    wp_enqueue_style('jqueryui-style');

    _wpr_setview("broadcast_compose");
}