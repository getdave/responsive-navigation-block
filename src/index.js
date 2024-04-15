// WordPress dependencies.
import { registerBlockVariation } from "@wordpress/blocks";

// Local dependencies.
import "./mobile-view-switcher";
import "./style.scss";

// Data inlined from PHP.
const { mobile: mobileClassName, desktop: desktopClassName } =
	getdaveResponsiveNavBlockVariations.classNames;

registerBlockVariation("core/navigation", {
	name: "getdave-navigation-desktop",
	title: "Desktop Navigation",
	description: "Navigation block preconfigured for larger viewports.",
	attributes: {
		overlayMenu: "never",
		className: desktopClassName,
	},
	isActive(blockAttributes) {
		return (
			blockAttributes.className?.includes(desktopClassName) &&
			blockAttributes.overlayMenu === "never"
		);
	},
});

registerBlockVariation("core/navigation", {
	name: "getdave-navigation-mobile",
	title: "Mobile Navigation",
	description: "Navigation block preconfigured for smaller viewports.",
	attributes: {
		overlayMenu: "always",
		className: mobileClassName,
	},
	isActive(blockAttributes) {
		return (
			blockAttributes.className?.includes(mobileClassName) &&
			blockAttributes.overlayMenu === "always"
		);
	},
});
