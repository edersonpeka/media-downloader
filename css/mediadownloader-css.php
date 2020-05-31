<?php

// Backwards compatibility: redirecting old parameters
if ( !array_key_exists( 'md_getcss', $_GET ) ) :
    $r = explode( 'wp-content', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
    header( 'Location: ' . '//' . $r[0] . '?md_getcss' );
    exit();
endif;

?>
