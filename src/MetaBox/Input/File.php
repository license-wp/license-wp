<?php

namespace Never5\LicenseWP\MetaBox\Input;

class File extends Input {

	/**
	 * View method
	 *
	 * @todo Future move views to view fields
	 */
	public function view() {

		global $thepostid;

		// local field
		$field = $this->get_field();

		if ( empty( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $this->get_key(), true );
		}

		if ( ! isset( $field['placeholder'] ) ) {
			$field['placeholder'] = '';
		}

		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $this->get_key() ); ?>"><?php echo esc_html( $field['label'] ); ?>:</label>
			<input type="text" class="file_url" name="<?php echo esc_attr( $this->get_key() ); ?>"
			       id="<?php echo esc_attr( $this->get_key() ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
			       value="<?php echo esc_attr( $field['value'] ); ?>"/>
			<button class="button upload_image_button"
			        data-uploader_button_text="<?php _e( 'Use file', 'wp-plugin-licencing' ); ?>"><?php _e( 'Upload', 'wp-plugin-licencing' ); ?></button> <?php if ( ! empty( $field['description'] ) ) : ?>
				<span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<script type="text/javascript">
			// Uploading files
			var file_frame;
			var file_target_input;

			jQuery( '.upload_image_button' ).live( 'click', function ( event ) {

				event.preventDefault();

				file_target_input = jQuery( this ).closest( '.form-field' ).find( '.file_url' );

				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					file_frame.open();
					return;
				}

				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media( {
					title: jQuery( this ).data( 'uploader_title' ),
					button: {
						text: jQuery( this ).data( 'uploader_button_text' ),
					},
					multiple: false  // Set to true to allow multiple files to be selected
				} );

				// When an image is selected, run a callback.
				file_frame.on( 'select', function () {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get( 'selection' ).first().toJSON();

					jQuery( file_target_input ).val( attachment.url );
				} );

				// Finally, open the modal
				file_frame.open();
			} );
		</script>
		<?php

	}

}