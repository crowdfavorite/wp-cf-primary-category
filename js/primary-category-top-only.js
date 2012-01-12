<?php 
/* This is fairly brittle JS, it only allows for the category taxonomy */
?>
<script type="text/javascript">
	jQuery(function($) {
		var cfPrimaryCategoryDropdown = jQuery('#cf_meta__cf_primary_category');
		var cfPrimaryCategoryCheckboxes = jQuery('#category-all > ul > li > label > input[name="post_category[]"]');

		var setPrimaryCatOptions = function() {
			cfPrimaryCategoryCheckboxes.each(function() {
				var checkbox = jQuery(this);
				var option = cfPrimaryCategoryDropdown
					.find('option[value=category-' + checkbox.val() + ']');
				if (checkbox.is(':checked')) {
					// see if it's in the dropdown, if not then add it
					if (option.size() < 1) {
						addAnotherOption(
							checkbox.val(), 
							checkbox
								.parent()
								.text()
						);
					}
				}
				else {
					option.remove();
				}
			});
		};
		
		var addAnotherOption = function(val, text) {
			cfPrimaryCategoryDropdown.append('<option value="category-' + val + '">' + text + '</option>')
		};
		
		// Append the options
		setPrimaryCatOptions();

		// handle removal of selected options
		cfPrimaryCategoryCheckboxes.click(function() {
			setPrimaryCatOptions();
		});
	});
</script>