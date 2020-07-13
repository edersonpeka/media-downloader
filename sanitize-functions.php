<?php

// Functions to sanitize user input
function sanitizeRDir( $d ){
    return is_readable( ABSPATH . $d ) ? $d : '' ;
}
function sanitizeWDir( $d ){
    return is_writeable( ABSPATH . $d ) ? $d : '' ;
}
if ( !function_exists( 'sanitizeArray' ) ) {
function sanitizeArray( $i, $a ){
    if ( is_array( $i ) ) {
        return array_intersect( $i, $a );
    } else {
        return in_array( $i, $a ) ? $i : '' ;
    }
}
}
function sanitizeMediaExtensions( $t ) {
    return sanitizeArray( $t, md_mediaAllExtensions() );
}
function sanitizeSortingField( $t ){
    global $mdsortingfields;
    return sanitizeArray( $t, array_keys( $mdsortingfields ) );
}
function sanitizeBeforeAfter( $t ){
    return sanitizeArray( $t, array( 'before', 'after' ) );
}
function sanitizeTagEncoding( $t ){
    global $mdencodings;
    return sanitizeArray( $t, $mdencodings );
}
function sanitizeBoolean( $b ){
    return $b == 1 ;
}
function sanitizeImageSize( $size ){
    $sizes = get_intermediate_image_sizes();
    array_unshift( $sizes, 'fallback' );
    return sanitizeArray( $size, $sizes );
}
function sanitizeHEXColor( $c ){
    return preg_match( '/^\s*#?[0-9A-F]{3,6}\s*$/i', $c, $m ) ? trim( str_replace( '#', '', $c ) ) : '';
}
function sanitizeMarkupTemplate( $t ){
    global $mdmarkuptemplates;
    return sanitizeArray( $t, array_keys( $mdmarkuptemplates ) );
}
function sanitizeURL( $t ) {
    return filter_var( $t, FILTER_VALIDATE_URL );
}
