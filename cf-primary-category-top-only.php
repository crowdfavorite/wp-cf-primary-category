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
?>
<script type="text/javascript">
function setPrimaryCatOptions() {
	var primary_cat = jQuery('#cf_meta__cf_primary_category');
	jQuery('#category-all > ul > li > label > input[name="post_category[]"]').each(function() {
// if not checked, remove from select list
		if (!jQuery(this).attr('checked')) {
			primary_cat.find('option[value=' + jQuery(this).val() + ']').remove();
		}
	});
}
jQuery(function($) {
	var primary_cat = $('#cf_meta__cf_primary_category');
// handle removal of selected options	
	$('#category-all > ul > li > label > input[name="post_category[]"]').click(function() {
		if ($(this).attr('checked') && primary_cat.find('option[value=' + $(this).val() + ']').size() < 1) {
			primary_cat.append('<option value="' + $(this).val() + '">' + $(this).parent().text() + '</option>');
		}
		setPrimaryCatOptions();
	});
	setPrimaryCatOptions();
});
</script>
<?php
}

if (is_admin() && in_array(basename($_SERVER['SCRIPT_FILENAME'], '.php'), array('post', 'post-new'))) {
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