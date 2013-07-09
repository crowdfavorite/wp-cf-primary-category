<script type="text/javascript">
	jQuery(function($) {
		var pc = {};
		pc.first_run = true;
		pc.primary_cat = null; // becomes a jQuery object at init
		pc.taxonomies = [cfPrimaryCats];

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
				$('#' + taxonomy + 'checklist input[type=checkbox]').on('change', function(){
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
