import "./style.scss";

import { registerBlockVariation } from "@wordpress/blocks";

registerBlockVariation("core/navigation", {
  name: "getdave-navigation-desktop",
  title: "Desktop Navigation",
  description: "Navigation block preconfigured for larger viewports.",
  attributes: {
    overlayMenu: "never",
    className: "is-style-getdave-navigation-desktop",
  },
  isActive: function (blockAttributes, variationAttributes) {
    return (
      blockAttributes.className?.includes(
        "is-style-getdave-navigation-desktop"
      ) && blockAttributes.overlayMenu === "never"
    );
  },
});

registerBlockVariation("core/navigation", {
  name: "getdave-navigation-mobile",
  title: "Mobile Navigation",
  description: "Navigation block preconfigured for smaller viewports.",
  attributes: {
    overlayMenu: "always",
    className: "is-style-getdave-navigation-mobile",
  },
  isActive: function (blockAttributes, variationAttributes) {
    return (
      blockAttributes.className?.includes(
        "is-style-getdave-navigation-mobile"
      ) && blockAttributes.overlayMenu === "always"
    );
  },
});
