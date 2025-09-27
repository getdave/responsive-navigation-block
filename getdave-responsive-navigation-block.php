<?php
/**
 * Plugin Name:       Responsive Navigation Block
 * Description:       Allows you to show different navigation menus based on the screen size using the Navigation block.
 * Requires at least: 6.5
 * Version:           1.0.10
 * Author:            Dave Smith
 * Author URI:        https://aheadcreative.co.uk
 * Plugin URI:        https://github.com/getdave/responsive-navigation-block
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       getdave-responsive-navigation-block
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
define( 'GDRNB_PLUGIN_SLUG', 'getdave-responsive-navigation-block' );
define( 'GDRNB_PLUGIN_SLUG_SHORT', 'getdavernb' );
define( 'GDRNB_DEFAULT_BREAKPOINT', 782 );
define( 'GDRNB_DEFAULT_UNIT', 'px' );
define( 'GDRNB_MOBILE_NAV_CLASS', GDRNB_PLUGIN_SLUG . '-is-mobile' );
define( 'GDRNB_DESKTOP_NAV_CLASS', GDRNB_PLUGIN_SLUG . '-is-desktop' );

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
	delete_option( GDRNB_PLUGIN_SLUG . '_responsive_nav_breakpoint' );
	delete_option( GDRNB_PLUGIN_SLUG . '_responsive_nav_unit' );
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
			'mobile'  => GDRNB_MOBILE_NAV_CLASS,
			'desktop' => GDRNB_DESKTOP_NAV_CLASS,
		),
		'pluginName' => GDRNB_PLUGIN_SLUG,
	);

	wp_add_inline_script(
		'getdavernb-script',
		'const ' . strtoupper( GDRNB_PLUGIN_SLUG_SHORT ) . ' = ' . wp_json_encode( $inline_variables ) . ';',
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

	// "Late" sanitization for the breakpoint and unit.
	// These are pre-validated in the settings page `sanitize_callback`
	// but we'll sanitize them here to ensure they're safe to use in CSS.
	$breakpoint = absint( $breakpoint );
	$unit       = sanitize_text_field( $unit );

	return '
        @media (min-width: ' . esc_attr( $breakpoint ) . esc_attr( $unit ) . ') {
            .wp-block-navigation.' . esc_attr( GDRNB_MOBILE_NAV_CLASS ) . ' {
                display: none;
            }
        }

        @media (max-width: calc(' . esc_attr( $breakpoint ) . esc_attr( $unit ) . ' - 1px)) {
            .wp-block-navigation.' . esc_attr( GDRNB_DESKTOP_NAV_CLASS ) . ' {
                display: none;
            }
        }
    ';
}

function enqueue_block_assets() {

	$breakpoint = get_option( GDRNB_PLUGIN_SLUG . '_responsive_nav_breakpoint', GDRNB_DEFAULT_BREAKPOINT );
	$unit       = get_option( GDRNB_PLUGIN_SLUG . '_responsive_nav_unit', GDRNB_DEFAULT_UNIT );
	$css        = generate_block_breakpoints_css( $breakpoint, $unit );

	// Create a fake stylesheet to allow for inlining the CSS rules.
	wp_register_style( GDRNB_PLUGIN_SLUG . '-style', false );
	wp_enqueue_style( GDRNB_PLUGIN_SLUG . '-style' );
	wp_add_inline_style( GDRNB_PLUGIN_SLUG . '-style', $css );
}


function add_settings_page() {
	add_options_page(
		__( 'Responsive Navigation Block Settings', 'getdave-responsive-navigation-block' ), // Page title
		__( 'Responsive Navigation Block', 'getdave-responsive-navigation-block' ), // Menu title
		'manage_options', // Capability
		GDRNB_PLUGIN_SLUG . '_responsive_nav', // Menu slug
		__NAMESPACE__ . '\settings_page_callback' // Callback function
	);
}

function settings_page_callback() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( GDRNB_PLUGIN_SLUG . '_responsive_nav' ); // Use your custom settings group
			do_settings_sections( GDRNB_PLUGIN_SLUG . '_responsive_nav' );
			submit_button( __( 'Save Settings', 'getdave-responsive-navigation-block' ) );
			?>
		</form>
	</div>
	<?php
}

function register_settings() {
	register_setting(
		GDRNB_PLUGIN_SLUG . '_responsive_nav', // Custom settings group
		GDRNB_PLUGIN_SLUG . '_responsive_nav_breakpoint',
		array(
			'type'              => 'integer',
			'description'       => __( 'The breakpoint value at which the navigation will switch.', 'getdave-responsive-navigation-block' ),
			'sanitize_callback' => 'absint',
			'default'           => GDRNB_DEFAULT_BREAKPOINT,
		)
	);

	register_setting(
		GDRNB_PLUGIN_SLUG . '_responsive_nav', // Custom settings group
		GDRNB_PLUGIN_SLUG . '_responsive_nav_unit',
		array(
			'type'              => 'string',
			'description'       => __( 'The unit used for the breakpoint.', 'getdave-responsive-navigation-block' ),
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => GDRNB_DEFAULT_UNIT,
		)
	);

	add_settings_section(
		GDRNB_PLUGIN_SLUG . '_responsive_nav_settings_section',
		__( 'Responsive Navigation Settings', 'getdave-responsive-navigation-block' ),
		__NAMESPACE__ . '\settings_section_callback',
		GDRNB_PLUGIN_SLUG . '_responsive_nav'
	);

	add_settings_field(
		GDRNB_PLUGIN_SLUG . '_responsive_nav_breakpoint',
		__( 'Breakpoint', 'getdave-responsive-navigation-block' ),
		__NAMESPACE__ . '\settings_field_callback',
		GDRNB_PLUGIN_SLUG . '_responsive_nav',
		GDRNB_PLUGIN_SLUG . '_responsive_nav_settings_section'
	);

	add_settings_field(
		GDRNB_PLUGIN_SLUG . '_responsive_nav_unit',
		__( 'Breakpoint Unit', 'getdave-responsive-navigation-block' ),
		__NAMESPACE__ . '\settings_field_unit_callback',
		GDRNB_PLUGIN_SLUG . '_responsive_nav',
		GDRNB_PLUGIN_SLUG . '_responsive_nav_settings_section'
	);
}


function settings_section_callback() {
	echo '<p>' . esc_html__( 'Set the breakpoint and unit at which the special Navigation block variations "Desktop Navigation" and "Mobile Navigation" will switch.', 'getdave-responsive-navigation-block' ) . '</p>';
	echo '<p>' . esc_html__( '⚠️ Please note: setting this value will have no effect on the standard Navigation block.', 'getdave-responsive-navigation-block' ) . '</p>';
}

function settings_field_callback() {
	$breakpoint = get_option( GDRNB_PLUGIN_SLUG . '_responsive_nav_breakpoint', GDRNB_DEFAULT_BREAKPOINT );
	echo '<input type="number" name="' . esc_attr( GDRNB_PLUGIN_SLUG ) . '_responsive_nav_breakpoint" value="' . esc_attr( $breakpoint ) . '" min="0">';
}

function settings_field_unit_callback() {
	$unit = get_option( GDRNB_PLUGIN_SLUG . '_responsive_nav_unit', GDRNB_DEFAULT_UNIT );
	?>
	<select id="<?php echo esc_attr( GDRNB_PLUGIN_SLUG . '_responsive_nav_unit' ); ?>" name="<?php echo esc_attr( GDRNB_PLUGIN_SLUG . '_responsive_nav_unit' ); ?>">
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
