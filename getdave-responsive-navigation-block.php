<?php
/**
 * Plugin Name:       Responsive Navigation Block - control your menus based on screen size.
 * Description:       Allows you to show different navigation menus based on the screen size using the Navigation block.
 * Requires at least: 6.5
 * Version:           1.0.0-beta.1
 * Author:            Dave Smith
 * Author URI:        https://aheadcreative.co.uk
 * Plugin URI:        https://github.com/getdave/responsive-navigation-block
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       getdavernb
 *
 * @package getdave
 */

namespace GETDAVERNB;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Constants.
 */
define( 'PLUGIN_SLUG', 'getdavernb' );
define( 'DEFAULT_BREAKPOINT', 782 );
define( 'DEFAULT_UNIT', 'px' );
define( 'MOBILE_NAV_CLASS', PLUGIN_SLUG . '-is-mobile' );
define( 'DESKTOP_NAV_CLASS', PLUGIN_SLUG . '-is-desktop' );

/**
 * Initialize the plugin.
 */
function init() {
	add_action( 'init', __NAMESPACE__ . '\register_assets' );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
	add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_block_assets' );
	add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );
	add_action( 'admin_menu', __NAMESPACE__ . '\add_settings_page' );
}



function uninstall_plugin() {
	delete_option( PLUGIN_SLUG . '_responsive_nav_breakpoint' );
	delete_option( PLUGIN_SLUG . '_responsive_nav_unit' );
}

/**
 * Register the assets.
 */
function register_assets() {
	$asset_file = plugin_dir_path( __FILE__ ) . '/build/index.asset.php';

	if ( file_exists( $asset_file ) ) {
		$assets = include $asset_file;

		wp_register_script(
			'getdavernb-script',
			plugins_url( 'build/index.js', __FILE__ ),
			$assets['dependencies'],
			$assets['version'],
			true
		);
	}
}

/**
 * Enqueue the editor assets.
 */
function enqueue_block_editor_assets() {

	wp_enqueue_script(
		'getdavernb-script',
	);

	// Inline variables for access in JavaScript.
	$inline_variables = array(
		'classNames' => array(
			'mobile'  => MOBILE_NAV_CLASS,
			'desktop' => DESKTOP_NAV_CLASS,
		),
		'pluginName' => PLUGIN_SLUG,
	);

	wp_add_inline_script(
		'getdavernb-script',
		'const ' . strtoupper( PLUGIN_SLUG ) . ' = ' . json_encode( $inline_variables ) . ';',
		'before'
	);

}

/**
 * Dynamically generate the CSS for the block breakpoints
 * using the defined breakpoint.
 *
 * @return string
 */
function generate_block_breakpoints_css( $breakpoint, $unit ) {
	return '
        @media (min-width: ' . $breakpoint . $unit . ') {
            .wp-block-navigation.' . MOBILE_NAV_CLASS . ' {
                display: none;
            }
        }

        @media (max-width: calc(' . $breakpoint . $unit . ' - 1px)) {
            .wp-block-navigation.' . DESKTOP_NAV_CLASS . ' {
                display: none;
            }
        }
    ';
}

function enqueue_block_assets() {

	$breakpoint = get_option( PLUGIN_SLUG . '_responsive_nav_breakpoint', DEFAULT_BREAKPOINT );
	$unit       = get_option( PLUGIN_SLUG . '_responsive_nav_unit', DEFAULT_UNIT );
	$css        = generate_block_breakpoints_css( $breakpoint, $unit );

	// Create a fake stylesheet to allow for inlining the CSS rules.
	wp_register_style( PLUGIN_SLUG . '-style', false );
	wp_enqueue_style( PLUGIN_SLUG . '-style' );
	wp_add_inline_style( PLUGIN_SLUG . '-style', $css );
}

function add_settings_page() {
	add_options_page(
		__( 'Responsive Navigation Block Settings', 'getdavernb' ), // Page title
		__( 'Responsive Navigation Block', 'getdavernb' ), // Menu title
		'manage_options', // Capability
		PLUGIN_SLUG . '_responsive_nav', // Menu slug
		__NAMESPACE__ . '\settings_page_callback' // Callback function
	);
}

function settings_page_callback() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'reading' );
			do_settings_sections( PLUGIN_SLUG . '_responsive_nav' );
			submit_button( __( 'Save Settings', 'getdavernb' ) );
			?>
		</form>
	</div>
	<?php
}

function register_settings() {
	register_setting(
		'reading',
		PLUGIN_SLUG . '_responsive_nav_breakpoint',
		array(
			'type'              => 'integer',
			'description'       => __( 'The breakpoint at which the navigation will switch to mobile view', 'getdavernb' ),
			'sanitize_callback' => 'absint',
			'default'           => DEFAULT_BREAKPOINT,
		)
	);

	register_setting(
		'reading',
		PLUGIN_SLUG . '_responsive_nav_unit',
		array(
			'type'              => 'string',
			'description'       => __( 'The unit of the navigation breakpoint', 'getdavernb' ),
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => DEFAULT_UNIT,
		)
	);

	add_settings_section(
		PLUGIN_SLUG . '_responsive_nav_settings_section',
		__( 'Responsive Navigation Settings', 'getdavernb' ),
		__NAMESPACE__ . '\settings_section_callback',
		PLUGIN_SLUG . '_responsive_nav'
	);

	add_settings_field(
		PLUGIN_SLUG . '_responsive_nav_breakpoint',
		__( 'Breakpoint', 'getdavernb' ),
		__NAMESPACE__ . '\settings_field_callback',
		PLUGIN_SLUG . '_responsive_nav',
		PLUGIN_SLUG . '_responsive_nav_settings_section'
	);

	add_settings_field(
		PLUGIN_SLUG . '_responsive_nav_unit',
		__( 'Breakpoint Unit', 'getdavernb' ),
		__NAMESPACE__ . '\settings_field_unit_callback',
		PLUGIN_SLUG . '_responsive_nav',
		PLUGIN_SLUG . '_responsive_nav_settings_section'
	);
}

function settings_section_callback() {
	echo '<p>' . esc_html__( 'Set the breakpoint and unit at which the special Navigation block variations "Desktop Navigation" and "Mobile Navigation" will switch.', 'getdavernb' ) . '</p>';
	echo '<p>' . esc_html__( '⚠️ Please note: setting this value will have no effect on the standard Navigation block.', 'getdavernb' ) . '</p>';
}

function settings_field_callback() {
	$breakpoint = get_option( PLUGIN_SLUG . '_responsive_nav_breakpoint', DEFAULT_BREAKPOINT );
	echo '<input type="number" name="' . esc_attr( PLUGIN_SLUG ) . '_responsive_nav_breakpoint" value="' . esc_attr( $breakpoint ) . '" min="0">';
}

function settings_field_unit_callback() {
	$unit = get_option( PLUGIN_SLUG . '_responsive_nav_unit', DEFAULT_UNIT );
	?>
	<select id="<?php echo esc_attr( PLUGIN_SLUG . '_responsive_nav_unit' ); ?>" name="<?php echo esc_attr( PLUGIN_SLUG . '_responsive_nav_unit' ); ?>">
		<option value="px" <?php selected( $unit, 'px' ); ?>>px</option>
		<option value="em" <?php selected( $unit, 'em' ); ?>>em</option>
		<option value="rem" <?php selected( $unit, 'rem' ); ?>>rem</option>
		<option value="vw" <?php selected( $unit, 'vw' ); ?>>vw</option>
	</select>
	<?php
}

// Handle uninstallation.
register_uninstall_hook( __FILE__, 'uninstall_plugin' );

// Handle initialization.
init();
