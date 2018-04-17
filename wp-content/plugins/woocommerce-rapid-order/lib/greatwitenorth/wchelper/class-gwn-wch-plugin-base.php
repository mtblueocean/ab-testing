<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class GWN_WCH_Plugin_Base {

	public $override_templates = false;

	protected $file_path;

	protected $base_path;
	
	public function __construct() {
		// Enable template overriding for the plugin.
		if ( $this->override_templates ) {
			$this->enable_override_templates();
		}
	}

	public function enable_override_templates() {
		add_filter( 'woocommerce_locate_template', array( $this, 'override_templates' ), 10, 3 );
		add_filter( 'wc_get_template_part', array( $this, 'override_template_part' ), 10, 3 );
	}

	public function disable_override_templates() {
		remove_filter( 'woocommerce_locate_template', array( $this, 'override_templates' ) );
		remove_filter( 'wc_get_template_part', array( $this, 'override_template_part' ) );
	}

	public function template_loader( $template ) {
		// Theme template overrides this plugin's
		if ( strpos( $template, WC()->plugin_path() ) !== 0 ) {
			return $template;
		}

		$file = basename( $template );
		if ( file_exists( $this->get_template_path( $file ) ) ) {
			$template = $this->get_template_path( $file );
		}

		return $template;
	}

	public function get_template_path( $relativePath = '' ) {
		return $this->base_path( '/templates/' . WC()->template_path() . $relativePath );
	}

	protected function base_path( $relativePath = '' ) {
		$rc = new \ReflectionClass( get_class( $this ) );

		return dirname( $rc->getFileName() ) . $relativePath;
	}

	public function override_templates( $template, $template_name, $template_path ) {

		global $woocommerce;

		$_template = $template;

		if ( ! $template_path ) {
			$template_path = $woocommerce->template_url;
		}

		$plugin_path = $this->get_template_path();

		// Look within plugin - this is priority
		if ( file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}

		if ( ! $template ) {
			$template = locate_template(
				array(
					$template_path . $template_name,
					$template_name,
				)
			);
		}

		if ( ! $template ) {
			$template = $_template;
		}

		return $template;
	}

	public function override_template_part( $template, $slug, $name ) {
		// Look in plugin/woocommerce/slug-name.php or plugin/woocommerce/slug.php
		if ( $name ) {
			$path = $this->get_template_path( "{$slug}-{$name}.php" );
		} else {
			$path = $this->get_template_path( "{$slug}.php" );
		}

		return file_exists( $path ) ? $path : $template;
	}

	protected function get_plugin_url( $relativePath = '' ) {
		return untrailingslashit( plugins_url( $relativePath, $this->base_path_file() ) );
	}

	protected function base_path_file() {
		$rc = new \ReflectionClass( get_class( $this ) );

		return $rc->getFileName();
	}
}
