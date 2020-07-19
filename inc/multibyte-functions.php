<?php

if ( !function_exists( 'mb_stripos' ) ) :
    function mb_stripos( $haystack, $needle, $offset = 0 ) {
        return stripos( $haystack, $needle, $offset );
    }
endif;

if ( !function_exists( 'mb_substr' ) ) :
    function mb_substr( $string, $start, $length = 'auto' ) {
        return ( 'auto' == $lenght ) ? substr( String, $start, $length ) : substr( $string, $start );
    }
endif;

if ( !function_exists( 'mb_strlen' ) ) :
    function mb_strlen( $string ) {
        return strlen( $string );
    }
endif;

if ( !function_exists( 'mb_strtoupper' ) ) :
    function mb_strtoupper( $string ) {
        return strtoupper( $string );
    }
endif;

if ( !function_exists( 'mb_strtolower' ) ) :
    function mb_strtolower( $string ) {
        return strtolower( $string );
    }
endif;
