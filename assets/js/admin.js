jQuery(document).ready(function($) {

	var file_frame;

	// Upload media file
	jQuery.fn.sensei_addons_upload_media_file = function( button, preview_media ) {
		var button_id = button.attr('id');
		var field_id = button_id.replace( '_button', '' );
		var preview_id = button_id.replace( '_button', '_preview' );

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: jQuery( this ).data( 'uploader_title' ),
		  button: {
		    text: jQuery( this ).data( 'uploader_button_text' ),
		  },
		  multiple: false
		});

		// When a file is selected, run a callback.
		file_frame.on( 'select', function() {
		  attachment = file_frame.state().get('selection').first().toJSON();
		  jQuery("#"+field_id).val(attachment.url);
		  if( preview_media ) {
		  	jQuery("#"+preview_id).attr('src',attachment.url);
		  }
		});

		// Open the modal
		file_frame.open();
	}

	// Set click trigger for file upload
	jQuery('#sensei_media_attachments').on('click', '.upload_media_file_button', function(event){
		event.preventDefault();
		jQuery.fn.sensei_addons_upload_media_file( jQuery(this), false );
	});

	// Add new upload rows
	jQuery('#sensei_media_attachments_add_row').click(function(event) {

		// Get unique IDs for the upload fields
		var id1 = String(Math.random()).replace('0.','');
		var id2 = String(Math.random()).replace('0.','');
		var row_id = 'media_attachments_row_'+String(Math.random()).replace('0.','');

		// Generate HTML for upload fields
		var html = '<tr valign="top" id="' + row_id + '">';
		html += '<td><input type="button" id="sensei_media_attachments_' + id1 + '_button" class="button upload_media_file_button" value="' + sensei_media_attachments_localisation.upload_file + '" data-uploader_title="' + sensei_media_attachments_localisation.choose_file + '" data-uploader_button_text="' + sensei_media_attachments_localisation.add_file + '" /> <input name="sensei_media_attachments[]" type="text" id="sensei_media_attachments_' + id1 + '" value="" /></td>';
		html += '<td><input type="button" id="sensei_media_attachments_' + id2 + '_button" class="button upload_media_file_button" value="' + sensei_media_attachments_localisation.upload_file + '" data-uploader_title="' + sensei_media_attachments_localisation.choose_file + '" data-uploader_button_text="' + sensei_media_attachments_localisation.add_file + '" /> <input name="sensei_media_attachments[]" type="text" id="sensei_media_attachments_' + id2 + '" value="" /></td>';
		html += '</tr>';

		// Append HTML to upload fields
		jQuery('#sensei_media_attachments_new_row').before(html);
	});

});