/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

// Note this will match a slugified version of the "Plugin Name" in the plugin header.
const PLUGIN_SLUG = 'responsive-navigation-block';

test.describe( 'Responsive Navigation block', () => {
	let desktopMenu;
	let mobileMenu;

	test.beforeAll( async ( { requestUtils } ) => {
		await requestUtils.activatePlugin( PLUGIN_SLUG );
	} );

	test.beforeEach( async ( { requestUtils } ) => {
		desktopMenu = await requestUtils.createNavigationMenu( {
			title: 'Desktop Menu',
			content:
				'<!-- wp:navigation-link {"label":"Desktop Menu Item","type":"custom","url":"http://www.wordpress.org/","kind":"custom"} /-->',
		} );

		mobileMenu = await requestUtils.createNavigationMenu( {
			title: 'Mobile Menu',
			content:
				'<!-- wp:navigation-link {"label":"Mobile Menu Item","type":"custom","url":"http://www.wordpress.org/","kind":"custom"} /-->',
		} );
	} );

	test.afterEach( async ( { requestUtils } ) => {
		await Promise.all( [
			requestUtils.deleteAllPosts(),
			requestUtils.deleteAllPages(),
			requestUtils.deleteAllMenus(),
		] );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllMenus();
		await requestUtils.deactivatePlugin( PLUGIN_SLUG );
	} );

	test( 'exposes "Desktop Navigation" and "Mobile Navigation" variations of the Navigation block', async ( {
		admin,
		page,
		editor,
	} ) => {
		await admin.createNewPost();

		await page
			.getByRole( 'button', { name: 'Toggle block inserter' } )
			.click();

		await page
			.getByRole( 'region', { name: 'Block Library' } )
			.getByRole( 'searchbox', {
				name: 'Search for blocks and patterns',
			} )
			.fill( 'Desktop Navigation' );

		const desktopNavigationVariation = page
			.getByRole( 'listbox', { name: 'Blocks' } )
			.getByRole( 'option', { name: 'Desktop Navigation' } );

		await expect( desktopNavigationVariation ).toBeVisible();

		await desktopNavigationVariation.click();

		await page
			.getByRole( 'region', { name: 'Block Library' } )
			.getByRole( 'searchbox', {
				name: 'Search for blocks and patterns',
			} )
			.fill( 'Mobile Navigation' );

		const mobileNavigationVariation = page
			.getByRole( 'listbox', { name: 'Blocks' } )
			.getByRole( 'option', { name: 'Mobile Navigation' } );

		await expect( mobileNavigationVariation ).toBeVisible();

		await mobileNavigationVariation.click();

		// Check the markup of the block is correct.
		await editor.publishPost();
		const content = await editor.getEditedPostContent();

		const desktopNavigationPattern =
			/<!-- wp:navigation \{"ref":\d+,"overlayMenu":"never","className":"getdave-responsive-navigation-block-is-desktop"\} \/-->/;
		const mobileNavigationPattern =
			/<!-- wp:navigation \{"ref":\d+,"overlayMenu":"always","className":"getdave-responsive-navigation-block-is-mobile"\} \/-->/;

		expect( content ).toMatch( desktopNavigationPattern );
		expect( content ).toMatch( mobileNavigationPattern );
	} );

	test( 'shows and hide variations at configured screen sizes', async ( {
		admin,
		pageUtils,
		page,
		editor,
	} ) => {
		await admin.createNewPost();

		await editor.insertBlock( {
			name: 'core/navigation',
			attributes: {
				ref: mobileMenu.id,
				overlayMenu: 'always',
				className: 'getdave-responsive-navigation-block-is-mobile',
			},
		} );

		await editor.insertBlock( {
			name: 'core/navigation',
			attributes: {
				ref: desktopMenu.id,
				overlayMenu: 'never',
				className: 'getdave-responsive-navigation-block-is-desktop',
			},
		} );

		const desktopNavigationBlock = editor.canvas
			.getByRole( 'document', {
				name: 'Block: Desktop Navigation',
			} )
			.getByRole( 'document', { name: 'Block: Custom Link' } )
			.first(); // ignore the "remove outline" duplicate if it exists.

		await expect( desktopNavigationBlock ).toBeVisible( {
			// wait for the network request to complete and the menu
			// to "settle" on the block.
			timeout: 10000,
		} );

		const mobileNavigationBlock = editor.canvas.getByRole( 'document', {
			name: 'Block: Mobile Navigation',
		} );

		await expect( mobileNavigationBlock ).not.toBeVisible();

		await pageUtils.setBrowserViewport( 'small' );

		await expect(
			editor.canvas
				.getByRole( 'document', {
					name: 'Block: Mobile Navigation',
				} )
				// "hamburger" toggle button
				.getByRole( 'button', {
					name: 'Open menu',
				} )
		).toBeVisible( {
			timeout: 10000, // wait for the network request to complete
		} );

		await expect( desktopNavigationBlock ).not.toBeVisible();

		await pageUtils.setBrowserViewport( 'large' );

		// Front of site.
		const postId = await editor.publishPost();

		await page.goto( `/?p=${ postId }` );

		const desktopNavigationBlockFront = page.getByRole( 'navigation', {
			name: 'Desktop',
		} );

		await expect( desktopNavigationBlockFront ).toBeVisible();

		const mobileNavigationBlockFront = page.getByRole( 'navigation', {
			name: 'Mobile',
		} );

		await expect( mobileNavigationBlockFront ).not.toBeVisible();

		await pageUtils.setBrowserViewport( 'small' );

		await expect( desktopNavigationBlockFront ).not.toBeVisible();

		await expect( mobileNavigationBlockFront ).toBeVisible();
	} );

	test( 'toggles editor device type (screen size emulation) when selecting the respective block variations', async ( {
		admin,
		page,
		editor,
	} ) => {
		await admin.createNewPost();

		await editor.insertBlock( {
			name: 'core/navigation',
			attributes: {
				ref: mobileMenu.id,
				overlayMenu: 'always',
				className: 'getdave-responsive-navigation-block-is-mobile',
			},
		} );

		await editor.insertBlock( {
			name: 'core/navigation',
			attributes: {
				ref: desktopMenu.id,
				overlayMenu: 'never',
				className: 'getdave-responsive-navigation-block-is-desktop',
			},
		} );

		// Toggle the "List View"
		await page
			.getByRole( 'region', { name: 'Editor top bar' } )
			.getByRole( 'button', { name: 'Document overview' } )
			.click();

		// Select the "Mobile Navigation" block variation.
		await page
			.getByRole( 'region', { name: 'Document Overview' } )
			.getByRole( 'tabpanel', { name: 'List View' } )
			.getByRole( 'link', { name: 'Mobile Navigation' } )
			.click();

		// Unfortunately it's not possible to determine the device type
		// via the UI in a perceivable way, so we'll have to rely on the
		// data store.
		expect(
			await page.evaluate( () =>
				wp.data.select( 'core/editor' ).getDeviceType()
			)
		).toBe( 'Mobile' );

		// Select the "Desktop Navigation" block variation.
		await page
			.getByRole( 'region', { name: 'Document Overview' } )
			.getByRole( 'tabpanel', { name: 'List View' } )
			.getByRole( 'link', { name: 'Desktop Navigation' } )
			.click();

		expect(
			await page.evaluate( () =>
				wp.data.select( 'core/editor' ).getDeviceType()
			)
		).toBe( 'Desktop' );
	} );
} );
