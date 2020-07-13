<?php

// Friendly file size
if( !function_exists( 'byte_convert' ) ){
    function byte_convert( $bytes ){
        $symbol = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );

        $exp = 0;
        $converted_value = 0;
        if( $bytes > 0 )
        {
          $exp = floor( log($bytes)/log(1024) );
          $converted_value = ( $bytes/pow(1024,floor($exp)) );
        }

        return sprintf( '%.2f '.$symbol[$exp], $converted_value );
    }
}

// Friendly frequency size
if( !function_exists( 'hertz_convert' ) ){
    function hertz_convert( $hertz ){
        $symbol = array( 'Hz', 'kHz', 'MHz', 'GHz', 'THz', 'PHz', 'EHz', 'ZHz', 'YHz' );

        $exp = 0;
        $converted_value = 0;
        if( $hertz > 0 ) {
          $exp = floor( log( $hertz, 10 ) / 3 );
          $converted_value = ( $hertz / pow( 1000 , floor( $exp ) ) );
        }

        return sprintf( '%.2f '.$symbol[$exp], $converted_value );
    }
}

// Scans an array of strings searching for a common prefix in all items
if( !function_exists( 'calculatePrefix' ) ){
    function calculatePrefix( $arr, $force = false ){
        $prefix = '';
        if ( ( $force || get_option( 'calculateprefix' ) ) && count( $arr ) > 1 ) {
            $prefix = strip_tags( array_pop( $arr ) );
            foreach ( $arr as $i ) {
                for ( $c=1; $c<mb_strlen($i); $c++ ) {
                    if ( strncasecmp( $prefix, $i, $c ) != 0 ) break;
                }
                $prefix = mb_substr( $prefix, 0, $c-1 );
            }
        }
        return $prefix;
    }
}

if( !function_exists( 'replaceUnderscores' ) ){
    function replaceUnderscores( $t ) {
        if ( $t && false === mb_strpos(' ', $t) ) {
            //if ( false === mb_strpos('_', $t) ) $t = str_replace( '-', '_', $t );
            $t = preg_replace( '/_(_+)/im', ' - ', $t );
            $t = preg_replace( '/_/m', ' ', $t );
        }
        return $t ;
    }
}
