import { registerPlugin } from '@wordpress/plugins';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { store as editorStore } from '@wordpress/editor';
import { store as blockEditorStore } from '@wordpress/block-editor';

// Data inlined from PHP.
const { mobile: mobileClassName } =
	getdaveResponsiveNavBlockVariations.classNames;

const MobileViewSwitcher = () => {
	const selectedNavigationBlock = useSelect( ( select ) => {
		const { getSelectedBlock, getBlock, getBlockParentsByBlockName } =
			select( blockEditorStore );
		const block = getSelectedBlock();

		if ( ! block ) {
			return false;
		}

		if (
			block?.name === 'core/navigation' &&
			block.attributes?.className?.includes( mobileClassName )
		) {
			return true;
		}

		const parentIds = getBlockParentsByBlockName(
			block.clientId,
			'core/navigation'
		);
		return parentIds.some( ( id ) => {
			const parentBlock = getBlock( id );
			return parentBlock?.attributes?.className?.includes(
				mobileClassName
			);
		} );
	}, [] );

	const { setDeviceType } = useDispatch( editorStore );

	useEffect( () => {
		if ( selectedNavigationBlock ) {
			// Switch the editor to mobile view
			setDeviceType( 'Mobile' );
		} else {
			// Switch the editor to desktop view
			setDeviceType( 'Desktop' );
		}
	}, [ selectedNavigationBlock, setDeviceType ] );

	return null;
};

registerPlugin( 'mobile-view-switcher', { render: MobileViewSwitcher } );
