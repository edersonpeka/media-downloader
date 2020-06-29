( function( wp ) {
	/**
	 * Registers a new block provided a unique name and an object defining its behavior.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/#registering-a-block
	 */
	var registerBlockType = wp.blocks.registerBlockType;
	var withInstanceId = wp.compose.withInstanceId;
	var InspectorControls = wp.editor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var PanelRow = wp.components.PanelRow;
	var TextControl = wp.components.TextControl;
	var ToggleControl = wp.components.ToggleControl;

	/**
	 * Returns a new element of given type. Element is an abstraction layer atop React.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/packages/packages-element/
	 */
	var el = wp.element.createElement;
	/**
	 * Retrieves the translation of text.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/packages/packages-i18n/
	 */
	var __ = wp.i18n.__;

	/**
	 * Every block starts by registering a new block type definition.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/#registering-a-block
	 */
	registerBlockType( 'media-downloader/mediadownloader', {
		/**
		 * This is the display title for your block, which can be translated with `i18n` functions.
		 * The block inserter will show this name.
		 */
		title: __( 'Media Downloader', 'media-downloader' ),
		description: __( 'Lists MP3 files from a folder.', 'media-downloader' ),
		icon: 'playlist-audio',
		keywords: [ 'playlist', 'audio', 'mp3' ],

		/**
		 * Blocks are grouped into categories to help users browse and discover them.
		 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
		 */
		category: 'widgets',

		/**
		 * Optional block extended support features.
		 */
		supports: {
			// Removes support for an HTML mode.
			html: false,
			customClassName: false,
		},
		example: {
			attributes: {
				folder: 'Classical/Bach',
				example: true,
			},
		},

		attributes: {
            folder: { type: 'string' },
			showplaylist: { type: 'boolean', default: true },
			showcover: { type: 'boolean', default: true },
			calculateprefix: { type: 'boolean', default: false },
			reversefiles: { type: 'boolean', default: false },
			removeextension: { type: 'boolean', default: false },
			downloadtext: { type: 'string' },
			playtext: { type: 'string' },
			stoptext: { type: 'string' },
			//
			example: { type: 'boolean', default: false },
		},
		
		/**
		 * The edit function describes the structure of your block in the context of the editor.
		 * This represents what the editor will render when the block is used.
		 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#edit
		 *
		 * @param {Object} [props] Properties passed from the editor.
		 * @return {Element}       Element to render.
		 */
		edit: withInstanceId( function( props ) {
			if ( props.attributes.example ) {
				return el(
					'div',
					{
						className: 'wp-block-media-downloader-mediadownloader wp-block-media-downloader-example'
					},
					el( 'table', {},
						el( 'tr', {},
							el( 'th', {}, 'Track Title' ),
							el( 'th', {}, '.' ),
							el( 'th', {}, '.' )
						),
						el( 'tr', {},
							el( 'td', {}, 'Au clair de la lune.mp3' ),
							el( 'td', {}, el(
								'span',
								{ className: 'dashicons dashicons-controls-play' }
							) ),
							el( 'td', {}, el(
								'span',
								{ className: 'dashicons dashicons-download' }
							) )
						),
						el( 'tr', {},
							el( 'td', {}, 'Fear of the dark.mp3' ),
							el( 'td', {}, el(
								'span',
								{ className: 'dashicons dashicons-controls-play' }
							) ),
							el( 'td', {}, el(
								'span',
								{ className: 'dashicons dashicons-download' }
							) )
						),
					)
				);
			}
			return [
                el( InspectorControls, {},
					el(	PanelBody, {},
						el(	PanelRow, {}, el( ToggleControl, {
							label: __( 'Show media playlist and player', 'media-downloader' ),
							checked: props.attributes.showplaylist,
							onChange: function( val ) {
								props.setAttributes( { showplaylist: val } );
							}
						} ) ),
						el(	PanelRow, {}, el( ToggleControl, {
							label: __( 'Show cover (if a folder.jpg file is found)', 'media-downloader' ),
							checked: props.attributes.showcover,
							onChange: function( val ) {
								props.setAttributes( { showcover: val } );
							}
						} ) ),
						el(	PanelRow, {}, el( ToggleControl, {
							label: __( 'Try to guess and remove a common "prefix" to all the files of the same folder', 'media-downloader' ),
							checked: props.attributes.calculateprefix,
							onChange: function( val ) {
								props.setAttributes( { calculateprefix: val } );
							}
						} ) ),
						el(	PanelRow, {}, el( ToggleControl, {
							label: __( 'Reverse order', 'media-downloader' ),
							checked: props.attributes.reversefiles,
							onChange: function( val ) {
								props.setAttributes( { reversefiles: val } );
							}
						} ) ),
						el(	PanelRow, {}, el( ToggleControl, {
							label: __( 'Remove ".mp3" from download URL', 'media-downloader' ),
							checked: props.attributes.removeextension,
							onChange: function( val ) {
								props.setAttributes( { removeextension: val } );
							}
						} ) ),
						el(	PanelRow, {}, el( TextControl, {
							label: __( 'Download button\'s text', 'media-downloader' ),
							value: props.attributes.downloadtext,
							onChange: function( val ) {
								props.setAttributes( { downloadtext: val } );
							}
						} ) ),
						el(	PanelRow, {}, el( TextControl, {
							label: __( 'Play button\'s text', 'media-downloader' ),
							value: props.attributes.playtext,
							onChange: function( val ) {
								props.setAttributes( { playtext: val } );
							}
						} ) ),
						el(	PanelRow, {}, el( TextControl, {
							label: __( 'Stop button\'s text', 'media-downloader' ),
							value: props.attributes.stoptext,
							onChange: function( val ) {
								props.setAttributes( { stoptext: val } );
							}
						} ) ),
					),
                ),
				el(
					'div',
					{
						className: props.className,
					},
					el(
						'label',
						{
							className: 'components-placeholder__label',
							for: 'media_downloader_block_' + props.instanceId,
						},
						el(
							'span',
							{
								className: 'dashicons dashicons-media-audio',
							},
						),
						__( 'Media Folder', 'media-downloader' ),
					),
					el(
						'input',
						{
							onChange: function ( _ev ) {
								props.setAttributes( { folder: _ev.currentTarget.value } );
							},
							value: props.attributes.folder,
							className: 'block-editor-plain-text input-control',
							id: 'media_downloader_block_' + props.instanceId,
						}
					)
				)
			];
		} ),

		/**
		 * The save function defines the way in which the different attributes should be combined
		 * into the final markup, which is then serialized by Gutenberg into `post_content`.
		 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#save
		 *
		 * @return {Element}       Element to render.
		 */
		save: function( props ) {
			var atts = props.attributes;
			var shatts = [ 'mediadownloader' ];
			Object.keys( atts ).sort().forEach( function( _key ) {
				if ( 'example' != _key ) {
					var _val = btoa( atts[ _key ].toString() );
					shatts.push( _key + '="base64:' + _val + '"' );
				}
			} );
			var shcode = '[' + shatts.join( ' ' ) + ']';
			return el(
				'p',
				{},
				shcode
			);
		}
	} );
} )(
	window.wp
);
