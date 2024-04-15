# Responsive Navigation Block

Stable tag: 1.0.0
Tested up to: 6.5
License: GPL v2 or later
Tags: navigation, menus, responsive, blocks
Contributors: get_dave

![](.wordpress-org/banner-1544x500.png)

Control your navigation menus based on screen size.

[![](https://img.shields.io/badge/ethical-open%20source-4baaaa.svg?style=flat-square)](#ethical-open-source)
[![](https://img.shields.io/wordpress/plugin/installs/responsive-navigation-block?style=flat-square)](https://wordpress.org/plugins/responsive-navigation-block/)

## Description

This Plugin allows you to show different navigation menus based on the screen size using the WordPress Navigation block. Useful for displaying different menus on mobile or for more control of styling on smaller screens.

### Features

-   Style your menu differently depending on screen size.
-   Use a different menu for each screen size.
-   Customize the "breakpoint" (where you switch between mobile and desktop).
-   Automatically switch editor to "mobile" preview when editing the mobile navigation.

### Usage

-   Install and Activate the plugin - two new block variations will be registered for "Mobile" and "Desktop".
-   Go to the Editor and remove any existing Navigation block.
-   Add the "Desktop Navigation" block - style and configure the menu for "desktop" as required.
-   Add the "Mobile Navigation" block - style and configure the menu for "mobile" as required.

### Support

Please see FAQs. If you still have an issue please:

-   check [Github for existing Issue reports](https://github.com/getdave/responsive-navigation-block/issues).
-   (if none) then file a new Issue on Github

### Privacy Statement

Responsive Navigation does _not_:

-   use cookies.
-   send data to any third party.
-   include any third party resources.

## Frequently Asked Questions

### Adjusting breakpoint

By default, the "breakpoint" at which the mobile navigation will switch to show the desktop navigation is `782px`. This aligns with the default configuration of the built in Wordpress Navigation block. To change this you can:

-   Go to the WP Admin Dashboard.
-   Go to `Settings -> Responsive Navigation`.
-   Configure the breakpoint value and the required unit. Save.
-   The breakpoint will be adjusted to match your new configuration.

### Styling of my mobile menu doesn't work

Due to complications with the way the default WordPress Navigation block works you are advised to use the following settings to control the styling of your mobile navigation:

-   Mobile overlay
    -   background color - `Styles -> Color -> Submenu & overlay background`.
    -   text color - `Styles -> Color -> Submenu & overlay text`.
-   Mobile menu toggle button ("hamburger"):
    -   icon color - `Styles -> Color -> Text`.
    -   background color - `Styles -> Color -> Text`.

Styles for Desktop Navigation can be applied using the standard controls.
