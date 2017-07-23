<?php


namespace Rocket\Footer\JS\Integration;


class GoogleAnalytics implements IntegrationInterface {

	public function init() {
		add_action( 'init', [ $this, 'init_action' ], 11 );
	}

	public function init_action() {
		if ( class_exists( 'Ga_Helper' ) ) {
			if ( ! is_admin() ) {
				remove_action( 'wp_footer', 'Ga_Frontend::insert_ga_script' );
				if ( \Ga_Helper::can_add_ga_code() || \Ga_Helper::is_all_feature_disabled() ) {
					add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );
				}
			}
		}
	}

	public function enqueue_script() {
		$web_property_id = \Ga_Frontend::get_web_property_id();
		if ( \Ga_Helper::should_load_ga_javascript( $web_property_id ) ) {
			$javascript = \Ga_View_Core::load( 'ga_code', array(
				'data' => array(
					\Ga_Admin::GA_WEB_PROPERTY_ID_OPTION_NAME => $web_property_id,
				),
			), true );
			$javascript = strip_tags( $javascript );
			wp_add_inline_script( 'jquery-core', $javascript );
		}
	}
}