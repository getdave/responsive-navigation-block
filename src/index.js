// WordPress dependencies.
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// Local dependencies.
import './mobile-view-switcher';

// Data inlined from PHP.
const { pluginSlug, classNames } = GETDAVERNB;

const { mobile: mobileClassName, desktop: desktopClassName } = classNames;

registerBlockVariation( 'core/navigation', {
	name: `${ pluginSlug }-desktop`,
	title: __( 'Desktop Navigation', 'getdave-responsive-navigation-block' ),
	description: __(
		'Navigation block preconfigured for larger viewports.',
		'getdave-responsive-navigation-block'
	),
	scope: [ 'block', 'inserter', 'transform' ],
	attributes: {
		overlayMenu: 'never',
		className: desktopClassName,
	},
	isActive( blockAttributes ) {
		return (
			blockAttributes.className?.includes( desktopClassName ) &&
			blockAttributes.overlayMenu === 'never'
		);
	},
} );

registerBlockVariation( 'core/navigation', {
	name: `${ pluginSlug }-mobile`,
	title: __( 'Mobile Navigation', 'getdave-responsive-navigation-block' ),
	description: __(
		'Navigation block preconfigured for smaller viewports.',
		'getdave-responsive-navigation-block'
	),
	scope: [ 'block', 'inserter', 'transform' ],
	attributes: {
		overlayMenu: 'always',
		className: mobileClassName,
	},
	isActive( blockAttributes ) {
		return (
			blockAttributes.className?.includes( mobileClassName ) &&
			blockAttributes.overlayMenu === 'always'
		);
	},
} );
