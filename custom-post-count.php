<?php
/*
 * Plugin Name: Advance user post CRUD
 * Plugin URI: -
 * Description: Advance user post CRUD lets you easily manage post.
 * Version: 1.1
 * Author: KrishaWeb PVT LTD
 * Author URI: http://www.krishaweb.com 
 * License: GPL2
*/
?>
<?php
define( 'ADVANCE_USER_POST_CRUD', '1.1' );
//Activation
function adv_user_crud_active() {
	}
register_activation_hook(__FILE__,'adv_user_crud_active');
//Deactivation
function adv_user_crud_deactivates() {
}
register_deactivation_hook( __FILE__,'adv_user_crud_deactivates');
require_once('adv_user_widget.php');
require_once('functions.php');