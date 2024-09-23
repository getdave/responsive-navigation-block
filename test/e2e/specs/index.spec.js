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

		// We need pages to be published so the Link Control can return pages
		await requestUtils.createPage( {
			title: 'Cat',
			status: 'publish',
		} );
		await requestUtils.createPage( {
			title: 'Dog',
			status: 'publish',
		} );
		await requestUtils.createPage( {
			title: 'Walrus',
			status: 'publish',
		} );
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

	test( 'should be able to use the block insertion UI to insert "Desktop Navigation" and "Mobile Navigation" variations of the Navigation block', async ( {
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

	test.only( 'should show and hide variations at configured screen sizes', async ( {
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

		// confirm only the "Desktop Navigation" block is visible
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

		// Confirm mobile nav not visible.
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
				// toggle button
				.getByRole( 'button', {
					name: 'Open menu',
				} )
		).toBeVisible( {
			timeout: 10000, // wait for the network request to complete
		} );

		await expect( desktopNavigationBlock ).not.toBeVisible();

		await pageUtils.setBrowserViewport( 'large' );

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
} );
