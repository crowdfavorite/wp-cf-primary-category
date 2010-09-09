<?php
/*
Plugin Name: CF Primary Category
Plugin URI: http://crowdfavorite.com 
Description: Allows the selection of a primary category, and chooses that category in Carrington 
Version: 1.1
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

//ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

function cfprimecat_request_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfprimecat_admin_js':
				cfprimecat_admin_js();				
				break;
		}
	}
}
add_action('init', 'cfprimecat_request_handler');

function cfprimecat_admin_js() {
	header('Content-type: text/javascript');
?>
function setPrimaryCatOptions() {
	var primary_cat = jQuery('#cf_meta__cf_primary_category');
	jQuery('input[name="post_category[]"]').each(function() {
// if not checked, remove from select list
		if (!jQuery(this).attr('checked')) {
			primary_cat.find('option[value=' + jQuery(this).val() + ']').remove();
		}
	});
}
jQuery(document).ready(function($) {
	var primary_cat = $('#cf_meta__cf_primary_category');
// handle removal of selected options	
	$('input[name="post_category[]"]').click(function() {
		if ($(this).attr('checked') && primary_cat.find('option[value=' + $(this).val() + ']').size() < 1) {
			primary_cat.append('<option value="' + $(this).val() + '">' + $(this).parent().text() + '</option>');
		}
		setPrimaryCatOptions();
	});
	setPrimaryCatOptions();
});
<?php
	die();
}

if (is_admin()) {
	wp_enqueue_script('jquery');
	wp_enqueue_script('cfprimecat_admin_js', trailingslashit(get_bloginfo('url')).'?cf_action=cfprimecat_admin_js', array('jquery'));
}

function cfprimecat_edit_cfmeta($config) {
	$cat_options = array('' => '-----');
	$cats = get_categories('hide_empty=0');
	foreach ($cats as $cat) {
		$cat_options[$cat->term_id] = $cat->name;
	}
	$config[] = array(
		'title' => 'Primary Category',
		'description' => '',
		'id' => 'cf_primary_category',
		'type' => 'post',
		'items' => array(
			array(
				'type' => 'select',
				'name' => '_cf_primary_category',
				'label' => 'Primary Category',
				'default_value' => '',
				'options' => $cat_options
			),
		)
	);
	return $config;
}
add_filter('cf_meta_config', 'cfprimecat_edit_cfmeta');

function cfprimecat_get_primary_category($post_id) {
	return get_post_meta($post_id, '_cf_primary_category', true);	
}

function cfprimecat_get_primary_category_slug($post_id) {
	return cfct_cat_id_to_slug(cfprimecat_get_primary_category($post_id));	
}

function cfprimecat_cfct_choose_content_template($filename, $type) {
	global $post;
	$cat_id = cfprimecat_get_primary_category($post->ID);
	if (intval($cat_id)) {
		$primary_cat_file = 'cat-'.cfct_cat_id_to_slug($cat_id).'.php';
		$files = cfct_files(CFCT_PATH.$type);
		if (in_array($primary_cat_file, $files)) {
			$filename = $primary_cat_file;
		}
	}
	return $filename;
}
add_filter('cfct_choose_content_template', 'cfprimecat_cfct_choose_content_template', 10, 2);
add_filter('cfct_choose_general_template', 'cfprimecat_cfct_choose_content_template', 10, 2);

// example of ensuring primary category is used in URL

/*
function cfprimecat_post_link($url, $post) {
	if ($prime_cat = cfprimecat_get_primary_category($post->ID)) {
		$home = trailingslashit(get_bloginfo('home'));
		$homeless = str_replace($home, '', $url);
		$delim = strpos($homeless, '/');
		$end = substr($homeless, $delim);
		$trimmed = substr($homeless, 0, $delim);
		$url = $home.cfprimecat_get_primary_category_slug($post->ID).$end;
	}
	return $url;
}
add_filter('post_link', 'cfprimecat_post_link', 10, 2);
*/

?>