<?php

namespace MetForm\Core\Integrations\Onboard\Classes;

defined( 'ABSPATH' ) || exit;

class Ajax {
	
	private $utils;

	public function __construct() {
		add_action( 'wp_ajax_mf_admin_action', [ $this, 'metform_admin_action' ] );
		$this->utils = Utils::instance();
	}

	public function metform_admin_action() {
		// Check for nonce security
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['user_data'] ) ) {
			$this->utils->save_option( 'user_data', empty( $_POST['user_data'] ) ? [] : $_POST['user_data'] );
		}

		if ( isset( $_POST['settings'] ) ) {
			$this->utils->save_settings( empty( $_POST['settings'] ) ? [] : $_POST['settings'] );
		}


		do_action( 'metform/admin/after_save' );

		exit;
	}

	public function return_json( $data ) {
		if ( is_array( $data ) || is_object( $data ) ) {
			return json_encode( $data );
		} else {
			return $data;
		}
	}

}