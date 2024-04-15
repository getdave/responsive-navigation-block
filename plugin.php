<?php
/**
 * Plugin Name:       Responsive Navigation Block - control your menus based on screen size.
 * Description:       Allows you to show different navigation menus based on the screen size using the Navigation block.
 * Requires at least: 6.5
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Dave Smith
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       getdave-responsive-navigation-block
 *
 * @package getdave
 */

namespace GetDave\ResponsiveNavBlockVariations;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Constants.
 */
define( 'PLUGIN_NAME', 'getdave-responsive-navigation-block' );
define( 'DEFAULT_BREAKPOINT', 782 );
define( 'DEFAULT_UNIT', 'px' );
define( 'MOBILE_NAV_CLASS', PLUGIN_NAME . '-is-mobile' );
define( 'DESKTOP_NAV_CLASS', PLUGIN_NAME . '-is-desktop' );

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
	delete_option( PLUGIN_NAME . '_responsive_nav_breakpoint' );
	delete_option( PLUGIN_NAME . '_responsive_nav_unit' );
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
			'mobile'  => MOBILE_NAV_CLASS,
			'desktop' => DESKTOP_NAV_CLASS,
		),
	);

	wp_localize_script(
		'getdave-responsive-navigation-block-script',
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

	$breakpoint = get_option( PLUGIN_NAME . '_responsive_nav_breakpoint', DEFAULT_BREAKPOINT );
	$unit       = get_option( PLUGIN_NAME . '_responsive_nav_unit', DEFAULT_UNIT );
	$css        = generate_block_breakpoints_css( $breakpoint, $unit );

	// Create a fake stylesheet to allow for inlining the CSS rules.
	wp_register_style( PLUGIN_NAME . '-style', false );
	wp_enqueue_style( PLUGIN_NAME . '-style' );
	wp_add_inline_style( PLUGIN_NAME . '-style', $css );
}

function add_settings_page() {
	add_options_page(
		__( 'Responsive Navigation Settings', 'getdave-responsive-navigation-block' ), // Page title
		__( 'Responsive Navigation', 'getdave-responsive-navigation-block' ), // Menu title
		'manage_options', // Capability
		PLUGIN_NAME . '_responsive_nav', // Menu slug
		__NAMESPACE__ . '\settings_page_callback' // Callback function
	);
}

function settings_page_callback() {
	$breakpoint = get_option( PLUGIN_NAME . '_responsive_nav_breakpoint', DEFAULT_BREAKPOINT );
	$unit       = get_option( PLUGIN_NAME . '_responsive_nav_unit', DEFAULT_UNIT );
	$is_default = $breakpoint == DEFAULT_BREAKPOINT && $unit == DEFAULT_UNIT;
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'reading' );
			do_settings_sections( PLUGIN_NAME . '_responsive_nav' );
			submit_button( __( 'Save Settings', 'getdave-responsive-navigation-block' ) );
			?>
			<input type="button" name="reset" id="reset" class="button button-secondary" value="<?php _e( 'Reset', 'getdave-responsive-navigation-block' ); ?>" <?php disabled( $is_default ); ?> />
		</form>
	</div>
	<script type="text/javascript">
		document.getElementById('reset').addEventListener('click', function() {
			document.querySelector('input[name="<?php echo PLUGIN_NAME; ?>_responsive_nav_breakpoint"]').value = '<?php echo DEFAULT_BREAKPOINT; ?>';
			document.querySelector('select[name="<?php echo PLUGIN_NAME; ?>_responsive_nav_unit"]').value = '<?php echo DEFAULT_UNIT; ?>';
		});
	</script>
	<?php
}

function register_settings() {
	register_setting(
		'reading',
		PLUGIN_NAME . '_responsive_nav_breakpoint',
		array(
			'type'              => 'integer',
			'description'       => __( 'The breakpoint at which the navigation will switch to mobile view', 'getdave-responsive-navigation-block' ),
			'sanitize_callback' => 'absint',
			'default'           => DEFAULT_BREAKPOINT,
		)
	);

	register_setting(
		'reading',
		PLUGIN_NAME . '_responsive_nav_unit',
		array(
			'type'              => 'string',
			'description'       => __( 'The unit of the navigation breakpoint', 'getdave-responsive-navigation-block' ),
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => DEFAULT_UNIT,
		)
	);

	add_settings_section(
		PLUGIN_NAME . '_responsive_nav_settings_section',
		__( 'Responsive Navigation Settings', 'getdave-responsive-navigation-block' ),
		__NAMESPACE__ . '\settings_section_callback',
		PLUGIN_NAME . '_responsive_nav'
	);

	add_settings_field(
		PLUGIN_NAME . '_responsive_nav_breakpoint',
		__( 'Breakpoint', 'getdave-responsive-navigation-block' ),
		__NAMESPACE__ . '\settings_field_callback',
		PLUGIN_NAME . '_responsive_nav',
		PLUGIN_NAME . '_responsive_nav_settings_section'
	);

	add_settings_field(
		PLUGIN_NAME . '_responsive_nav_unit',
		__( 'Breakpoint Unit', 'getdave-responsive-navigation-block' ),
		__NAMESPACE__ . '\settings_field_unit_callback',
		PLUGIN_NAME . '_responsive_nav',
		PLUGIN_NAME . '_responsive_nav_settings_section'
	);
}

function settings_section_callback() {
	echo '<p>' . __( 'Set the breakpoint and unit at which the special Navigation block variations "Desktop Navigation" and "Mobile Navigation" will switch.', 'getdave-responsive-navigation-block' ) . '</p>';
	echo '<p>' . __( '<strong>⚠️ Please note</strong>: setting this value will have no effect on the <em>standard</em> Navigation block.', 'getdave-responsive-navigation-block' ) . '</p>';
}

function settings_field_callback() {
	$breakpoint = get_option( PLUGIN_NAME . '_responsive_nav_breakpoint', DEFAULT_BREAKPOINT );
	echo '<input type="number" name="' . PLUGIN_NAME . '_responsive_nav_breakpoint" value="' . esc_attr( $breakpoint ) . '" min="0">';
}

function settings_field_unit_callback() {
	$unit = get_option( PLUGIN_NAME . '_responsive_nav_unit', DEFAULT_UNIT );
	?>
	<select id="<?php echo PLUGIN_NAME . '_responsive_nav_unit'; ?>" name="<?php echo PLUGIN_NAME . '_responsive_nav_unit'; ?>">
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
