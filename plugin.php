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

add_action('init', __NAMESPACE__ . '\register_block_styles');

/**
 * Register block styles.
 */
function register_block_styles() {
    register_block_style(
        'core/navigation',
        array(
            'name'         => 'getdave-navigation-mobile',
            'label'        => __( 'Mobile', 'getdavernbv' ),
            'style_handle' => 'index-style',
        )
    );

    register_block_style(
        'core/navigation',
        array(
            'name'         => 'getdave-navigation-desktop',
            'label'        => __( 'Desktop', 'getdavernbv' ),
            'style_handle' => 'index-style',
        )
    );
}

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );

/**
 * Enqueue the editor assets.
 */
function enqueue_block_editor_assets() {
    $asset_file = plugin_dir_path( __FILE__ ) . '/build/index.asset.php';

    if ( file_exists( $asset_file ) ) {
        $assets = include $asset_file;
        wp_enqueue_script(
            'index',
            plugin_dir_url( __FILE__ ) . 'build/index.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );

        wp_enqueue_style(
            'index-style',
            plugin_dir_url( __FILE__ ) . 'build/style-index.css',
            array(),
            $assets['version']
        );
    }
}