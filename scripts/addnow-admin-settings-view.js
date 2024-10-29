jQuery(document).ready(function(){
	jQuery( '#addnow-placement-accordion-wrap' ).accordion({
		collapsible: true,
		heightStyle : 'content'
	});

	process_visibility_tables();

	function process_visibility_tables() {

		var placement_labels_input = '';

		jQuery('#addnow-placement-accordion-wrap table.addnow-settings-placement-list').each(function() {
			var table_object = this;
			var selected_labels_table = process_visibility_table_checkboxes( table_object );
			var h3_object = jQuery(table_object).parent().prev('h3');
			jQuery('.addnow-placement-selections', h3_object).html(selected_labels_table);

			if (selected_labels_table != '') {
				if (placement_labels_input != '') placement_labels_input+='; ';
				placement_labels_input += selected_labels_table;
			}
		});

		jQuery('input#addnow-form-widget-placement-labels').val(placement_labels_input);

	}

	function process_visibility_table_checkboxes( table_object ) {
		var selected_labels_table = '';

		jQuery('tbody tr', table_object).each(function() {
			var tr_object = this;
			var selected_labels_tr = '';
			jQuery('input[type=checkbox]:checked', tr_object).each(function() {
				var checkbox_object = jQuery(this);
				var label_text 	= 	jQuery(this).next('label').text();

				if (selected_labels_tr != '') {
					selected_labels_tr += ', ';
				}
				selected_labels_tr += label_text;
			});

			if (selected_labels_tr != '') {
				var tr_label = jQuery('th label', tr_object).html();
				if (selected_labels_table != '') {
					selected_labels_table += '; ';
				}
				selected_labels_table = selected_labels_table+tr_label+': '+selected_labels_tr;
			}
		});

		return selected_labels_table;
	}


	jQuery('table.addnow-settings-placement-list input[type=checkbox]').change(function(event) {
		process_visibility_tables();
	});
});