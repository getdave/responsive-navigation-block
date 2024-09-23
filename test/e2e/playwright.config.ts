/**
 * External dependencies
 */
import os from 'os';
import { fileURLToPath } from 'url';
import { defineConfig, devices } from '@playwright/test';

/**
 * WordPress dependencies
 */
const baseConfig = require( '@wordpress/scripts/config/playwright.config' );

const config = defineConfig( {
	...baseConfig,
	use: {
		...baseConfig.use,
		baseURL: 'http://localhost:4013',
	},
	webServer: {
		...baseConfig.webServer,
		port: 4013,
	},
} );

export default config;
