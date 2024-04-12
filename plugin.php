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


init();


function init() {
    add_action( 'init', __NAMESPACE__ . '\register_assets' );
    add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
    add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_front_of_site_assets' );
}

/**
 * Register the assets.
 */
function register_assets() {
    $asset_file = plugin_dir_path( __FILE__ ) . '/build/index.asset.php';

    if ( file_exists( $asset_file ) ) {
        $assets = include $asset_file;

        // register the script
        wp_register_script(
            'getdave-responsive-nav-block-variations-script',
            plugins_url( 'build/index.js', __FILE__ ),
            $assets['dependencies'],
            $assets['version']
        );

        wp_register_style(
            'getdave-responsive-nav-block-variations-style',
            plugins_url( 'build/style-index.css', __FILE__ ),
            array(),
            $assets['version']
        );
    }
}

/**
 * Enqueue the front of site assets.
 */
function enqueue_front_of_site_assets() {
    wp_enqueue_style(
        'getdave-responsive-nav-block-variations-style',
    );
}

/**
 * Enqueue the editor assets.
 */
function enqueue_block_editor_assets() {
    wp_enqueue_script(
        'getdave-responsive-nav-block-variations-script',
    );

    wp_enqueue_style(
        'getdave-responsive-nav-block-variations-style',
    );
}