<?php

namespace Never5\LicenseWP\Admin\MetaBox\Input;

class Textarea extends Input {

	/**
	 * View method
	 *
	 * @todo Future move views to view fields
	 */
	public function view() {
		global $thepostid;

		// local field var
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
			<textarea name="<?php echo esc_attr( $this->get_key() ); ?>" id="<?php echo esc_attr( $this->get_key() ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" cols="25" rows="3"><?php echo esc_html( $field['value'] ); ?></textarea>
			<?php if ( ! empty( $field['description'] ) ) : ?>
				<span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php

	}

}