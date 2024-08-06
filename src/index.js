// WordPress dependencies.
import { registerBlockVariation } from '@wordpress/blocks';

// Local dependencies.
import './mobile-view-switcher';

// Data inlined from PHP.
const { pluginName, classNames } = GETDAVERNB;

const { mobile: mobileClassName, desktop: desktopClassName } = classNames;

registerBlockVariation( 'core/navigation', {
	name: `${ pluginName }-desktop`,
	title: 'Desktop Navigation',
	description: 'Navigation block preconfigured for larger viewports.',
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
	name: `${ pluginName }-mobile`,
	title: 'Mobile Navigation',
	description: 'Navigation block preconfigured for smaller viewports.',
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
