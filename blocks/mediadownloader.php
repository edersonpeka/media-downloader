<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package media-downloader
 */

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function mediadownloader_block_init() {
	// Skip block registration if Gutenberg is not enabled/merged.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$dir = dirname( __FILE__ );

	$index_js = 'mediadownloader/index.js';
	wp_register_script(
		'mediadownloader-block-editor',
		plugins_url( $index_js, __FILE__ ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
		),
		filemtime( "$dir/$index_js" )
	);
	wp_localize_script( 'mediadownloader-block-editor', 'mediadownloaderOptions', array(
        'mp3folderurl' => home_url( '/' ) . rtrim( get_option('mp3folder'), '/' ) . '/',
	) );
	wp_set_script_translations( 'mediadownloader-block-editor', 'media-downloader', $dir . '/../languages' );

	$editor_css = 'mediadownloader/editor.css';
	wp_register_style(
		'mediadownloader-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	register_block_type( 'media-downloader/mediadownloader', array(
		'editor_script'   => 'mediadownloader-block-editor',
		'editor_style'    => 'mediadownloader-block-editor',
		'render_callback' => 'mediadownloader_block_render',
	) );
}
add_action( 'init', 'mediadownloader_block_init' );

function mediadownloader_block_render( $atts, $content ) {
	return buildMediaTable( $atts['folder'], $atts );
}
