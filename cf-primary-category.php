<?php
/*
Plugin Name: CF Primary Category
Plugin URI: http://crowdfavorite.com 
Description: Allows the selection of a primary category, and chooses that category in Carrington 
Version: 1.2
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

//ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

/**
 * Output admin-head JS
 * First checks for URL $_GET['post_type'] for new posts
 * and falls back to the queries $post object to get
 * the active post type.
 *
 * Only acts upon hierarchical taxonomies that are active for the current post-type
 * 
 * @return void
 */
function cfprimecat_admin_js() {
	if (!empty($_GET['post-type'])) {
		$post_type = esc_attr($_GET['post-type']);
	}
	else {
		global $post;
		$post_type = $post->post_type;
	}
	
	// bail if we're not enabled on this post-type
	if (!in_array($post_type, cfprimecat_get_post_types())) {
		return;
	}
	
	// find our hierarchical taxonomy types that are active for this post-type
	$taxonomies = array();
	foreach (get_object_taxonomies($post_type, 'objects') as $key => $obj) {
		if ($obj->hierarchical) {
			$taxonomies[$key] = $obj;
		}
	}
	$taxonomy_js_array = "'".implode("', '", array_keys($taxonomies))."'";
	
	echo <<<PRIMARYCATJS
<script type="text/javascript">
	jQuery(function($) {
		var pc = {};
		pc.first_run = true;
		pc.primary_cat = null; // becomes a jQuery object at init
		pc.taxonomies = [{$taxonomy_js_array}];
		
		// add an option to the primary_category select list
		// if list is empty, make added option the selected value
		pc.setPrimaryCatOption = function(val, text) {
			pc.primary_cat.append($('<option></option>').attr('value', val).text(text));
			if (pc.primary_cat.val().length == 0 && !pc.first_run) {
				pc.primary_cat.val(val);
			}
		};
		
		// remove an option from the primary_category select list
		pc.unSetPrimaryCatOption = function(val) {
			pc.primary_cat.find('option[value=' + val + ']').remove();
		};
		
		// check if an option already exists in the select list
		pc.catOptionExists = function(val) {
			return (pc.primary_cat.find('option[value=' + val + ']').size() > 0);
		}
		
		// assign click handlers and handle initial population of the select list
		pc.init = function() {
			pc.primary_cat = $('#cf_meta__cf_primary_category');
			
			// attach listeners
			$.each(pc.taxonomies, function(i, taxonomy){
				$('.list\\\:' + taxonomy + ' input[type=checkbox]').change(function(){
					var _this = $(this);
					var _value = _this.closest('li').attr('id');
					if (_this.is(':checked') && !pc.catOptionExists(_value)) {
						// fuggin categories are not done like all other hierarchical taxonmies... 
						if (_this.attr('id').match('in-category')) {
							var _tax = ['foo', 'category'];
						}
						else {
							var _tax = _this.attr('name').match(/\[(.*)\]\[/);
						}
						
						pc.setPrimaryCatOption(_value, _this.parent().text() + ' (' + _tax[1] + ')');
					}
					else if(!_this.is(':checked')) {
						pc.unSetPrimaryCatOption(_value);
					}
				}).trigger('change');
			});
			
			pc.first_run = false;
		};
		
		pc.init();
	});
</script>
PRIMARYCATJS;
}
add_action('admin_head', 'cfprimecat_admin_js');

/**
 * CF Post Meta config modification
 * Populate the default options list
 * Only value we need is the currently selected item (if selected)
 *
 * @param array $config 
 * @return array
 */
function cfprimecat_edit_cfmeta($config) {	
	global $post;
	$primary_cat = cfprimecat_get_primary_category($post->ID);

	$cat_options = array('' => '--------');
	$default_value = '';
	
	if (!empty($primary_cat)) {
		$key = $primary_cat->taxonomy.'-'.$primary_cat->term_id;
		$cat_options[$key] = $primary_cat->name.' ('.$primary_cat->taxonomy.')';
		$default_value = $key;
	}
		
	$config[] = array(
		'title' => 'Primary Category',
		'description' => '',
		'id' => 'cf_primary_category',
		'type' => cfprimecat_get_post_types(),
		'context' => 'side',
		'items' => array(
			array(
				'type' => 'select',	
				'name' => '_cf_primary_category',
				'label' => 'Category',
				'default_value' => $default_value,
				'options' => $cat_options
			),
		)
	);
	return $config;
}
add_filter('cf_meta_config', 'cfprimecat_edit_cfmeta');

/**
 * Return the primary category info for the post
 * Postmeta is stored as {taxonomy-name}-{term_id}
 * 
 * Function returns a taxonomy term object on success, false on failure
 *
 * @param int $post_id 
 * @return mixed
 */
function cfprimecat_get_primary_category($post_id) {
	$key = get_post_meta($post_id, '_cf_primary_category', true);
	$term = get_term(substr(strrchr($key, '-'), 1), substr($key, 0, strrpos($key, '-')));
	return (!empty($term) && !is_wp_error($term) ? $term : false);
}

function cfprimecat_cfct_choose_content_template($filename, $type) {
	global $post;
	$term = cfprimecat_get_primary_category($post->ID);
	if (!empty($term)) {
		if ($term->taxonomy == 'category') {
			$primary_cat_file = 'cat-'.$term->slug.'.php';
		}
		else {
			$primary_cat_file = 'tax-'.$term->taxonomy.'-'.$term->slug.'.php';
		}
		$files = cfct_files(CFCT_PATH.$type);
		if (in_array($primary_cat_file, $files)) {
			$filename = $primary_cat_file;
		}
	}
	return $filename;
}
add_filter('cfct_choose_content_template', 'cfprimecat_cfct_choose_content_template', 10, 2);
add_filter('cfct_choose_general_template', 'cfprimecat_cfct_choose_content_template', 10, 2);

/**
 * Add to the array of post-types that primary category selection will be avaible on
 *
 * @return array - post_types
 */
function cfprimecat_get_post_types() {
	return apply_filters('cfprimecat_active_post_types', array('post'));
}

// example of ensuring primary category is used in URL

/*
function cfprimecat_post_link($url, $post) {
	if ($prime_cat = cfprimecat_get_primary_category($post->ID)) {
		$home = trailingslashit(get_bloginfo('home'));
		$homeless = str_replace($home, '', $url);
		$delim = strpos($homeless, '/');
		$end = substr($homeless, $delim);
		$trimmed = substr($homeless, 0, $delim);
		if ($prime_cat->taxonomy == 'category') {
			$url = $home.$prime_cat->slug.$end;
		}
		else {
			// @todo needs verification
			$url = $home.$prime_cat->taxonomy.$delim.$prime_cat->slug.$end;
		}
	}
	return $url;
}
add_filter('post_link', 'cfprimecat_post_link', 10, 2);
*/

?>