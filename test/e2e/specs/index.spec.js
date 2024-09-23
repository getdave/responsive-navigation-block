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

	test.beforeEach( async ( { requestUtils } ) => {
		await requestUtils.deleteAllMenus();
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

	test( 'should show and hide variations at configured screen sizes', async ( {
		admin,
		pageUtils,
		page,
		editor,
	} ) => {
		// insert a navigation block with the desktop variation active
		// that means using the correct attributes (as above) to ensure the desktop variation is active
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

		// Check the block in the canvas.
		// await expect(
		// 	editor.canvas.locator(
		// 		`role=textbox[name="Navigation link text"i] >> text="Custom link"`
		// 	)
		// ).toBeVisible( { timeout: 10000 } ); // allow time for network request.

		// confirm only the "Desktop Navigation" block is visible
		// do not change the viewport
		const desktopNavigationBlock = editor.canvas
			.getByRole( 'document', {
				name: 'Block: Desktop Navigation',
			} )
			.getByRole( 'document', {
				name: 'Block: Custom Link',
			} );

		await expect( desktopNavigationBlock ).toBeVisible();

		// confirm only the "Mobile Navigation" block is NOT visible
		// do not change the viewport
		const mobileNavigationBlock = editor.canvas.getByRole( 'document', {
			name: 'Block: Mobile Navigation',
		} );

		await expect( mobileNavigationBlock ).not.toBeVisible();

		await pageUtils.setBrowserViewport( 'small' );

		// confirm only the "Mobile Navigation" block is visible
		const mobileNavigationBlockMobile = editor.canvas.getByRole(
			'document',
			{
				name: 'Block: Mobile Navigation',
			}
		);

		await expect( mobileNavigationBlockMobile ).toBeVisible();

		// confirm only the "Desktop Navigation" block is NOT visible
		const desktopNavigationBlockMobile = editor.canvas.getByRole(
			'document',
			{
				name: 'Block: Desktop Navigation',
			}
		);

		await expect( desktopNavigationBlockMobile ).not.toBeVisible();

		await pageUtils.setBrowserViewport( 'large' );

		const postId = await editor.publishPost();

		await page.goto( `/?p=${ postId }` );

		// confirm only the "Desktop Navigation" block is visible
		// do not change the viewport
		const desktopNavigationBlockFront = page.getByRole( 'navigation', {
			name: 'Desktop Navigation',
		} );

		await expect( desktopNavigationBlockFront ).toBeVisible();
	} );
} );
