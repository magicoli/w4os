<?php
/**
 * Add fields to registration forms
 *
 * @since      2.6.2
 * @package    GuduleLapointe/w4os
 * @subpackage w4os/includes
 */

class W4OS_Tos extends W4OS_Loader {
	public $tos_page_id;
	public $tos_link;
	private $tos_error;

	public function __construct() {

		$this->actions = array();
		$this->filters = array(
			array(
				'hook'     => 'rwmb_meta_boxes',
				'callback' => 'register_settings_fields',
			),
		);

		$this->tos_page_id = W4OS::get_option('w4os_tos_page_id');
		if($this->tos_page_id) {
			$this->tos_link = '<a href="' . get_permalink($this->tos_page_id) . '">' . get_the_title( $this->tos_page_id ). '</a>';
			$this->tos_agreement = sprintf(
				/* translators: %s: title and link to a page created by the user (gender- and number-neutral phrasing recommended) */
				__( 'I agree to the terms on page %s.', 'w4os' ),
				$this->tos_link,
			);
			$this->tos_error = '<strong>' . __('Error', 'w4os') . '</strong>: ' . sprintf(
				/* translators: %s: title and link to a page created by the user (gender- and number-neutral phrasing recommended) */
				__( 'You must agree to the terms on page %s.', 'w4os' ),
				$this->tos_link,
			);

			$this->actions = array_merge($this->actions, array(
				array(
					'hook'     => 'register_form',
					'callback' => 'tos_checkbox',
				),

				array(
					'hook'     => 'woocommerce_register_form',
					'callback' => 'wc_tos_checkbox',
				),
			));

			$this->filters = array_merge($this->filters, array(
				array(
					'hook'     => 'registration_errors',
					'callback' => 'tos_checkbox_validation',
				),

				array(
					'hook'     => 'woocommerce_registration_errors',
					'callback' => 'wc_tos_checkbox_validation',
				),
			));
		}
	}


	function register_settings_fields( $meta_boxes ) {
		$prefix = 'w4os_';

		$meta_boxes[] = [
			'title'          => __( 'Registration', 'w4os' ),
			'id'             => 'registration',
			'settings_pages' => ['w4os_settings'],
			'class'          => 'w4os-settings',
			'fields'         => [
				[
					'name'        => __( 'Terms of Service page', 'w4os' ),
					'id'          => $prefix . 'tos_page_id',
					'type'        => 'post',
					'desc'        => '<ul><li>' . join('</li><li>', array(
						__( 'Select the page containing the terms of service to add a TOS consent checkbox on the user registration page.', 'w4os'),
						__( 'Leave blank to disable the checkbox or if it is handled by another plugin.', 'w4os'),
						__( 'Note: It is crucial to have a well-crafted and legally accurate TOS page for your website.', 'w4os')
						. ' ' . __( 'We recommend using a dedicated plugin or seeking professional services to help you write the text of the TOS page.', 'w4os' ),
					)) . '</li></ul>',
					'post_type'   => ['page'],
					'field_type'  => 'select_advanced',
					'add_new'     => true,
					'placeholder' => __( 'Select a TOS page', 'w4os' ),
				],
			],
		];

		return $meta_boxes;
	}

	function tos_checkbox() {
		echo '<p><label for="tos_confirm"><input type="checkbox" name="tos_confirm" id="tos_confirm" required> ' . $this->tos_agreement . '</label></p>';
	}

	function tos_checkbox_validation( $errors ) {
		if ( empty( $_POST['tos_confirm'] ) ) {
			$errors->add( 'tos_confirm_error', $this->tos_error );
		}
		return $errors;
	}

	// Add the checkbox to WooCommerce registration form
	function wc_tos_checkbox() {
		echo '<p class="form-row terms"><label for="tos_confirm" class="woocommerce-form__label woocommerce-form__label-for-checkbox"><input type="checkbox" class="woocommerce-form__input-checkbox" name="tos_confirm" id="tos_confirm" required> ' . $this->tos_agreement . '</label></p>';
	}

	// Validate the checkbox in WooCommerce registration
	function wc_tos_checkbox_validation( $errors ) {
		if ( empty( $_POST['tos_confirm'] ) ) {
			$errors->add( 'tos_confirm_error', $this->tos_error );
		}
		return $errors;
	}

}

$this->loaders[] = new W4OS_Tos();
