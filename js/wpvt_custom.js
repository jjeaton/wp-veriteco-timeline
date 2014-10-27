jQuery(document).ready(function($){
	jQuery(function() {
		jQuery( ".datepicker" ).datepicker({ dateFormat: 'yy,m,d' });
	});

	/**
	 * Add a new timeline name, without refresh page.
	 * @author Ewerton Luiz <ewerton@cancaonova.com>
	 * @version 1.1.1 [23/10/2014 10:04:00]
	 * @copyright 2014 Desenvolvimento Canção Nova
	 * @param event event prevent refresh page
	 * @var array data pass the values to jquery post 
	 */
	jQuery("button[name=add_timeline_name]").click(function (event) {
		//prevent refresh page after submit form.
		event.preventDefault();

		var data = {
			action: 'add_new_timeline_name',
			wpvt_new_timeline_name: jQuery('#wpvt_new_timeline_name').val(),
		};

		jQuery.post(ajaxurl, data, function($) {
			if(jQuery('#wpvt_new_timeline_name').val() != "") {
				jQuery('#wpvt_timeline_name').append(jQuery('<option>', {
					'value': jQuery('#wpvt_new_timeline_name').val(),
					'text': jQuery('#wpvt_new_timeline_name').val(),
					'id': (parseInt(jQuery('#wpvt_timeline_name').children().length)+1),
					'selected': 'selected'
				}));
			} else {
				alert("Could not add a new timeline name, because the Add New field is empty!")
			}
		});
	});

	/**
	 * Adds "id" attribute to the default plugin shortcode with the selected option "value" from the stored timelines
	 * @author Ewerton Luiz <ewerton@cancaonova.com>
	 * @version 1.2.1 [23/10/2014 11:12:30]
	 * @copyright 2014 Desenvolvimento Canção Nova
	 * @param event event prevent refresh page
	 * @var string option get the id attribute of the this selected element
	 * @var array data pass the values to jquery post
	 * @var string shortcode create the shortcode to insert into post content
	 */
	jQuery("button[name=wpvt_insert_timeline]").click(function (event) {
		//prevent refresh page after submit form.
		event.preventDefault();

		//Close the thickbox after submit form.
		window.parent.tb_remove();

		var option = "";
		$( "#wpvt_select_timeline option:selected" ).each(function() {
			option = $( this ).attr('id');
		});

		var data = {
			action: 'wpvt_insert_timeline_shortcode_ajax',
			select_owner: option
		};

		jQuery.post(ajaxurl, data, function($) {
			if (jQuery("#content").length) {
				var shortcode = '[WPVT id='+ data.select_owner +']';

				//Add the shortcode into the post content for Text and Visual modes
				jQuery('#content').val(jQuery('#content').val() + shortcode);
				tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);
			}
		});
	});
});