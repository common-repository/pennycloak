<?php
/**
 * Plugin Name: PennyCloak
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Makes your wordpress site work with PennyCloak, cloaked redirecting
 * Version: 1.0
 * Author: PennyCloak
 * Author URI: http://www.pennycloak.com/
 * License: GPL2
 */

/*  Copyright 2013  PennyCloak.com  (email : support@pennycloak.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*
Create the settings page that stores the unique ID using WP options.
*/
add_action('admin_menu', 'plugin_admin_add_page');
function plugin_admin_add_page() {
	add_options_page('PennyCloak Page', 'PennyCloak', 'manage_options', 'pennycloak_setup', 'pc_options_page');
}

add_action('admin_init', 'plugin_admin_init');
function plugin_admin_init(){
	register_setting( 'pc_options', 'pc_options', 'pc_options_validate' );
	add_settings_section('pc_main', '', 'pc_main_text', 'pennycloak_setup');
	add_settings_field('pc_unique_id', 'Unique ID', 'pc_setting_string', 'pennycloak_setup', 'pc_main');
}

function pc_options_validate($input) {
	$options = get_option('pc_options');
	$newinput['pc_unique_id'] = $input['pc_unique_id'];
	if(!preg_match('/^[a-z0-9]{16}$/', $newinput['pc_unique_id'])) {
		$newinput['pc_unique_id'] = 'Invalid ID';
	}
	else {
		$newinput['pc_unique_id'] = $input['pc_unique_id'];
	}
	
	
	return $newinput;
}

function pc_setting_string() {
	$options = get_option('pc_options');
	echo "<input id='pc_unique_id' name='pc_options[pc_unique_id]' size='16' maxlength='16' type='text' value='{$options['pc_unique_id']}' />";
}

function pc_main_text() {
	echo '';
}


function pc_options_page() {

	echo "<div>";
	echo "<h2>PennyCloak Settings</h2>";
	echo "Activate Your Website to Work With PennyCloak";
	echo "<form action='options.php' method='post'>";
	settings_fields('pc_options');
	do_settings_sections('pennycloak_setup');
 
	echo "<input name='Submit' type='submit' value='Save Changes' />";
	echo "</form></div>";
	echo "<br /><br />For each individual page you want to activate...";

}


/*
Add the checkbox to the edit page and manage the status of each page using meta tags
*/
function register_post_assets(){
    add_meta_box('pc-active', 'PennyCloak', 'add_pennycloak_meta_box', 'post', 'advanced', 'high');
	add_meta_box('pc-active', 'PennyCloak', 'add_pennycloak_meta_box', 'page', 'advanced', 'high');
}
add_action('admin_init', 'register_post_assets', 1);

function add_pennycloak_meta_box($post){
    $pcactivated = get_post_meta($post->ID, '_pc-active', true);
	$postType = get_post_type( get_the_ID() );
	if ($postType == "post") {
		$pt = "Post";
	}
	else {
		$pt = "Page";
	}
    echo "<label for='_pc-active'>Cloak this ". $pt ."?&nbsp;&nbsp;"."</label>";
	if ($pcactivated == "on"){
		$checkit = "checked";
	}
	else {
		$checkit = "";
	}
	echo "<input type='checkbox' name='_pc-active' id='pc-active' " . $checkit . " />";
}

function save_pc_active_meta($post_id){
	update_post_meta( $post_id, '_pc-active', esc_attr($_REQUEST['_pc-active']) ) ; 
}
add_action('save_post', 'save_pc_active_meta');



/*
Add the Pennycloak code to the header if this page is activated
*/
function addPennyCloakCode(){
	global $wp_query;
	$options = get_option('pc_options');
	$postid = $wp_query->post->ID;
	$pcactivated = get_post_meta($postid, '_pc-active', true);
	if ($pcactivated == "on"){
		echo "<script type='text/javascript'>";
		echo "var uniqueID = '" . $options['pc_unique_id'] ."';";
		echo "</script>";
		echo "<script type='text/javascript' src='http://pennycloak.appspot.com/js/pc.js'></script>\n";
	}
	else {
		echo "";
	}
}
add_action('wp_head', 'addPennyCloakCode');



?>