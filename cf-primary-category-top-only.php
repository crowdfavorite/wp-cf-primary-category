<?php
/*
Plugin Name: CF Primary Category (Top Level Only)
Plugin URI: http://crowdfavorite.com 
Description: Limits CF Primary Category selection feature to top level categories only.
Version: 1.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// @deprecated, not updated to support custom taxonomies

//ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

function cfprimecatto_init() {
	remove_action('admin_head', 'cfprimecat_admin_js');
}
add_action('init', 'cfprimecatto_init');

function cfprimecatto_admin_js() {
	require 'js/primary-category-top-only.js';
}

if (in_array(basename($_SERVER['SCRIPT_FILENAME']), array('post.php', 'post-new.php'))) {
	add_action('admin_head', 'cfprimecatto_admin_js');
}

function cfprimecatto_edit_cfmeta_cats($cats) {
	$top_cats = array();
	foreach ($cats as $cat) {
		if ($cat->category_parent == 0) {
			$top_cats[] = $cat;
		}
	}
	return $top_cats;
}
add_filter('cfprimecat_edit_cfmeta_cats', 'cfprimecatto_edit_cfmeta_cats');

?>