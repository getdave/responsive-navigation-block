<?php
/**
 * Plugin Name:       Responsive Nav Block Variations
 * Description:       Your description here.
 * Requires at least: 6.5
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Dave Smith
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       getdave-responsive-nav-block-variations
 *
 * @package getdave
 */

namespace GetDave\ResponsiveNavBlockVariations;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin name constant
define( 'PLUGIN_NAME', 'getdave-responsive-nav-block-variations' );

/**
 * Initialize the plugin.
 */
function init() {
	add_action( 'init', __NAMESPACE__ . '\register_assets' );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
}

/**
 * Register the assets.
 */
function register_assets() {
	$asset_file = plugin_dir_path( __FILE__ ) . '/build/index.asset.php';

	if ( file_exists( $asset_file ) ) {
		$assets = include $asset_file;

		wp_register_script(
			PLUGIN_NAME . '-script',
			plugins_url( 'build/index.js', __FILE__ ),
			$assets['dependencies'],
			$assets['version']
		);

		wp_enqueue_block_style(
			'core/navigation',
			array(
				'handle' => PLUGIN_NAME . '-style',
				'src'    => plugins_url( 'build/style-index.css', __FILE__ ),
				// Allow Themes to opt into inlining the style.
				// See https://developer.wordpress.org/reference/functions/wp_enqueue_block_style/#parameters.
				'path'   => plugin_dir_path( __FILE__ ) . 'build/style-index.css',
				$assets['version'],
			)
		);
	}
}

/**
 * Enqueue the editor assets.
 */
function enqueue_block_editor_assets() {
	wp_enqueue_script(
		PLUGIN_NAME . '-script',
	);

	// Inline variables for access in JavaScript.
	$inline_variables = array(
		'classNames' => array(
			'mobile'  => PLUGIN_NAME . '-mobile',
			'desktop' => PLUGIN_NAME . '-desktop',
		),
	);

	wp_localize_script(
		PLUGIN_NAME . '-script',
		'getdaveResponsiveNavBlockVariations',
		$inline_variables
	);
}

init();
