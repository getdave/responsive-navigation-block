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
define( 'BREAKPOINT', 782 );


/**
 * Initialize the plugin.
 */
function init() {
	add_action( 'init', __NAMESPACE__ . '\register_assets' );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
	add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_block_assets' );
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
		'getdave-responsive-nav-block-variations-script',
		'getdaveResponsiveNavBlockVariations',
		$inline_variables
	);

}

/**
 * Dynamically generate the CSS for the block breakpoints
 * using the defined breakpoint.
 *
 * @return string
 */
function generate_block_breakpoints_css( $breakpoint ) {
	return '
        @media (min-width: ' . $breakpoint . 'px) {
            .wp-block-navigation.getdave-responsive-nav-block-variations-mobile {
                display: none;
            }
        }

        @media (max-width: ' . ( $breakpoint - 1 ) . 'px) {
            .wp-block-navigation.getdave-responsive-nav-block-variations-desktop {
                display: none;
            }
        }
    ';
}

function enqueue_block_assets() {

    $breakpoint = BREAKPOINT;
	$css = generate_block_breakpoints_css( $breakpoint );

	// Create a fake stylesheet to allow for inlining the CSS rules.
	wp_register_style( PLUGIN_NAME . '-style', false );
	wp_enqueue_style( PLUGIN_NAME . '-style' );
	wp_add_inline_style( PLUGIN_NAME . '-style', $css );
}

init();
