<?php
/*
Plugin Name: Media Downloader
Plugin URI: https://ederson.peka.nom.br
Description: Media Downloader plugin lists MP3 files from a folder through the [mediadownloader] shortcode.
Version: 0.4.7
Author: Ederson Peka
Author URI: https://profiles.wordpress.org/edersonpeka/
Text Domain: media-downloader
*/

if ( !function_exists( 'iconv' ) ) {
    add_action( 'admin_notices', function () {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'Media Downloader plugin requires <a href="https://www.php.net/manual/en/ref.iconv.php" target="_blank">iconv</a> PHP extension.', 'media-downloader' ); ?></p>
        </div>
        <?php
    } );
    return;
}

// Pre-2.6 compatibility ( From: http://codex.wordpress.org/Determining_Plugin_and_Content_Directories )
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

require_once( dirname( __FILE__ ) . '/inc/multibyte-functions.php' );
require_once( dirname( __FILE__ ) . '/inc/useful-functions.php' );
require_once( dirname( __FILE__ ) . '/inc/sanitize-functions.php' );
include_once( dirname( __FILE__ ) . '/inc/_deprecated-functions.php' );
require_once( dirname( __FILE__ ) . '/blocks/mediadownloader.php' );

// MarkDown, used for text formatting
if ( !class_exists( 'Parsedown' ) ) require_once( dirname( __FILE__ ) . '/inc/Parsedown.php' );

if ( !class_exists( 'media_downloader' ) ) :

class media_downloader {

    // Init
    public static function init() {
        // Internationalization
        load_plugin_textdomain( 'media-downloader', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        // Hooking into admin's screens
        add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
        // If query string 'md_getfile' parameter is set, we stream the file and quit
        if ( array_key_exists( 'md_getfile', $_GET ) ) :
            include( dirname( __FILE__ ) . '/getfile.php' );
            exit();
        // If query string 'md_getcss' parameter is set, we stream the custom CSS option value
        elseif ( array_key_exists( 'md_getcss', $_GET ) ) :
            header( 'Content-Type: text/css' );
            echo get_option( 'customcss' );
            exit();
        // If query string 'md_feed' parameter is set, we stream the RSS feed and quit
        elseif ( array_key_exists( 'md_feed', $_GET ) ) :
            include( dirname( __FILE__ ) . '/mdfeed.php' );
            exit();
        endif;

        add_shortcode( 'mediadownloader', array( __CLASS__, 'shortcode' ) );
        if ( array_key_exists( 'mediadownloader', $_GET ) ) {
            add_action( 'rest_api_init', array( __CLASS__, 'register_rest_field' ) );
        }
    }

    public static function admin_init() {
        // Create "settings" link for this plugin on plugins list
        add_filter( 'plugin_action_links', array( __CLASS__, 'settings_link' ), 10, 2 );
    }

    // Add Settings link to plugins - code from GD Star Ratings
    // (as seen in http://www.whypad.com/posts/wordpress-add-settings-link-to-plugins-page/785/ )
    public static function settings_link( $links, $file ) {
        $this_plugin = plugin_basename(__FILE__);
        if ( $file == $this_plugin ) {
            $settings_link = '<a href="' . admin_url( 'admin.php?page=mediadownloader-options' ) . '">' . __( 'Settings', 'media-downloader' ) . '</a>';
            array_unshift( $links, $settings_link );
        }
        return $links;
    }

    public static function shortcode( $atts ) {
        $ret = '';
        if ( array_key_exists( 'folder', $atts ) && $atts['folder'] ) {
            $ret = buildMediaTable( $atts['folder'], $atts );
        }
	    return $ret;
    }

    public static function register_rest_field() {
        register_rest_field( array( 'post', 'page' ),
            'mediadownloader',
            array(
                'get_callback'      => array( __CLASS__, 'rest_field' ),
                'update_callback'   => null,
                'schema'            => null,
            )
        );
    }

    public static function rest_field( $post ) {
        $folders = array();
        $mdir = '/' . get_option( 'mp3folder' );        
        $mpath = ABSPATH . trim( $mdir, '/' ) . '/';

        // MP3 folder URL
        if ( function_exists( 'switch_to_blog' ) ) switch_to_blog(1);
        $murl = get_option( 'siteurl' ) . $mdir;
        if ( function_exists( 'restore_current_blog' ) ) restore_current_blog();
        // MP3 folder relative URL
        $mrelative = preg_replace( '/^https?\:/m', '', $murl );
        $mrelative = preg_replace( '/^\/\//', '', $mrelative );
        $mrelative = explode( '/', $mrelative );
        array_shift( $mrelative );
        $mrelative = '/' . implode( '/', $mrelative );

        $ret = array(
            'relativepath' => '/' . trim( $mrelative, '/' ) . '/',
            'mediasets' => array(),
        );

        $cont = $post['content']['raw'];
        $cont = preg_replace( '|\[media\:(.*?)\]|ms', '[mediadownloader folder="$1"]', $cont );
        $sh_regexp = get_shortcode_regex( array( 'mediadownloader' ) );
        $blocks = parse_blocks( $cont );
        foreach ( $blocks as $block ) :
            if ( 'media-downloader/mediadownloader' == $block['blockName'] ) :
                $folders[] = $block['attrs'];
            else :
                $block_cont = implode( PHP_EOL, $block['innerContent'] );
                if ( preg_match_all( "/$sh_regexp/", $block_cont, $sh_matches ) ) :
                    $attrs = shortcode_parse_atts( implode( ' ', $sh_matches[3] ) );
                    $folders[] = $attrs;
                endif;
            endif;
        endforeach;
        foreach ( $folders as $folderinfo ) :
            $_path = $mpath;
            if ( array_key_exists( 'mp3folder', $folderinfo ) ) $_path = ABSPATH . trim( $folderinfo['mp3folder'], '/' ) . '/';
            $scan = buildMediaTable( $folderinfo['folder'], $folderinfo, true );
            $scan['folder'] = $folderinfo['folder'];
            
            $files = $scan['files'];
            $tags = $scan['tags'];
            unset($scan['tags']);
            $scan['files'] = array();
            foreach ( $files as $file ) :
                $filetags = array();
                foreach ( $tags as $tag => $tagvalues ) :
                    if ( array_key_exists( $file, $tagvalues ) ) :
                        $filetags[ $tag ] = $tagvalues[ $file ];
                    endif;
                endforeach;
                $scan['files'][] = array(
                    'file' => $file,
                    'tags' => $filetags,
                );
            endforeach;

            $ret['mediasets'][] = $scan;
        endforeach;
        return $ret;
    }

    public static function scandir( $ipath ) {
        $cover = '';
        $iall = $ifiles = array();
        // Populating arrays with respective files
        if ( is_dir( $ipath ) ) {
            if ( is_readable( $ipath ) ) {
                $idir = dir( $ipath );
                while ( false !== ( $ifile = $idir->read() ) ) if ( !is_dir( $ifile ) ) {
                    $arrfile = explode( '.', $ifile );
                    if ( count( $arrfile ) > 1 ) {
                        $fext = array_pop( $arrfile );
                    } else {
                        $fext = '.none';
                    }
                    if ( in_array( $fext, md_mediaExtensions() ) ) {
                        $ifiles[] = $ifile;
                    } else {
                        if ( !array_key_exists( $fext, $iall ) ) $iall[$fext] = array();
                        $iall[$fext][] = $ifile;
                    }
                    if ( mb_strtolower( preg_replace( '/\.jpeg/m', '.jpg', $ifile ) ) == 'folder.jpg' ) $cover = $ifile;
                }
            } else {
                /* translators: %1$s will be replaced by the unreadable path */
                $errors[] = sprintf( __( 'Could not read: %1$s', 'media-downloader' ), $ipath );
            }
        } elseif ( file_exists( $ipath ) && is_readable( $ipath ) ) {
            $apath = explode( '/', $ipath );
            $ifile = array_pop( $apath );
            $ipath = implode( '/', $apath );
            $arrfile = explode( '.', $ifile );
            if ( count( $arrfile ) > 1 ) {
                $fext = array_pop( $arrfile );
            } else {
                $fext = '.none';
            }
            if ( in_array( $fext, md_mediaExtensions() ) ) {
                $ifiles[] = $ifile;
            } else {
                if ( !array_key_exists( $fext, $iall ) ) $iall[$fext] = array();
                $iall[$fext][] = $ifile;
            }
        }
        return array( 'dir' => $ipath, 'cover' => $cover, 'other' => $iall, 'media' => $ifiles );
    }
}

// Initialize
add_action( 'init', array( 'media_downloader', 'init' ) );

endif;

// Possible encodings
$mdencodings = array( 'UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'cp866', 'cp1251', 'cp1252', 'KOI8-R', 'BIG5', 'GB2312', 'BIG5-HKSCS', 'Shift_JIS', 'EUC-JP' );
$md_comp_encs = array();
foreach ( $mdencodings as $mdenc ) {
    if ( 'ISO-8859-1' != $mdenc ) {
        $md_comp_encs[] = 'ISO-8859-1 + ' . $mdenc;
    }
}
$mdencodings = array_merge( $mdencodings, $md_comp_encs );
// Possible fields by which file list should be sorted,
// and respective sorting functions
$mdsortingfields = array(
    'none' => null,
    'file name' => 'orderByFileName',
    'title' => 'orderByTitle',
    'file date' => 'orderByFileDate',
    'recording dates' => 'orderByRecordingDates',
    'year' => 'orderByYear',
    'track number' => 'orderByTrackNumber',
    'album' => 'orderByAlbum',
    'artist' => 'orderByArtist',
    'file size' => 'orderByFileSize',
    'sample rate' => 'orderBySampleRate',
);
// Settings and respective sanitize functions
$mdsettings = array(
    'mp3folder' => 'sanitizeRDir',
    'cachedir' => 'sanitizeWDir',
);
// Possible ID3 tags
$mdtags = array( 'title', 'artist', 'album', 'year', 'recording_dates', 'genre', 'comment', 'track_number', 'bitrate', 'filesize', 'filedate', 'directory', 'file', 'sample_rate', 'playtime_string' );

// Markup settings and respective sanitize functions
$mdmarkupsettings = array(
    'mediaextensions' => 'sanitizeMediaExtensions',
    'sortfiles' => 'sanitizeSortingField',
    'reversefiles' => 'sanitizeBoolean',
    'showtags' => null,
    'removeextension' => 'sanitizeBoolean',
    'showcover' => 'sanitizeBoolean',
    'showfeatured' => 'sanitizeImageSize',
    'packageextensions' => null,
    'embedplayer' => 'sanitizeBoolean',
    'autoplaylist' => 'sanitizeBoolean',
    'embedwhere' => 'sanitizeBeforeAfter',
    'tagencoding' => 'sanitizeTagEncoding',
    'filenameencoding' => 'sanitizeTagEncoding',
    'calculateprefix' => 'sanitizeBoolean',
    'covermarkup' => null,
    'packagetitle' => null,
    'packagetexts' => null,
    'downloadtext' => null,
    'playtext' => null,
    'stoptext' => null,
    'replaceheaders' => null,
    'markuptemplate' => 'sanitizeMarkupTemplate',
);
// Possible markup templates
$mdmarkuptemplates = array(
    'definition-list' => __( '<strong>"DL" mode:</strong> One table cell containing a definition list (one definition term for each tag)', 'media-downloader' ),
    'table-cells' => __( '<strong>"TR" mode:</strong> One table cell for each tag', 'media-downloader' ),
);

// More settings and respective sanitize functions
$mdmoresettings = array(
    'customcss' => null,
    'handlefeed' => 'sanitizeBoolean',
    'overwritefeedlink' => 'sanitizeURL',
);

function get_replaceheaders() {
    $replaceheaders = array();
    $arrreplaceheaders = explode( "\n", trim( get_option( 'replaceheaders' ) ) );
    foreach ( $arrreplaceheaders as $line ) {
        $arrline = explode( ':', trim( $line ) );
        if ( count( $arrline ) >= 2 ) $replaceheaders[ mb_strtolower( trim( array_shift( $arrline ) ) ) ] = implode( ':', $arrline );
    }
    return $replaceheaders;
}

function md_mediaAllExtensions() {
    return array( 'mp3', 'mp2', 'mp1', 'ogg', 'wma', 'm4a', 'aac', 'ac3', 'flac', 'ra', 'rm', 'wav', 'aiff', 'cda', 'mid', 'avi', 'webm', 'asf', 'wmv', 'mpg', 'avi', 'qt', 'mov', 'ogv', 'mp4', '3gp' );
}
function md_mediaExtensions() {
    $ret = get_option( 'mediaextensions' );
    if ( ! ( is_array( $ret ) && count( $ret ) ) ) $ret = array( 'mp3' );
    return $ret;
}

function md_packageExtensions() {
    $ret = explode( ',', get_option( 'packageextensions' ) );
    foreach ( $ret as &$r ) $r = preg_replace( '/\./m', '', $r );
    return array_filter( $ret );
}

function md_btoa( $str ) {
    if ( mb_strpos( $str, 'base64:' ) === 0 ) {
        $str = base64_decode( mb_substr( $str, 6 ) );
    }
    return $str;    
}

function buildMediaTable( $folder, $atts = false, $onlyjson = false ) {
    global $mdtags, $tagvalues, $mdsortingfields, $mdmarkuptemplates;
    $errors = array();
    
    if ( !is_array( $atts ) ) $atts = array();

    $folder = md_btoa( $folder );
    $atts = array_map( 'md_btoa', $atts );
    
    $forcePrefix = array_key_exists( 'calculateprefix', $atts ) && ( $atts['calculateprefix'] == 'true' );

    // MP3 folder
    $mdir = '/' . get_option( 'mp3folder' );
    if ( array_key_exists( 'mp3folder', $atts ) )
        $mdir = '/' . $atts['mp3folder'];
    
    // MP3 folder URL
    if ( function_exists( 'switch_to_blog' ) ) switch_to_blog(1);
    $murl = get_option( 'siteurl' ) . $mdir;
    if ( function_exists( 'restore_current_blog' ) ) restore_current_blog();
    // MP3 folder relative URL
    $mrelative = preg_replace( '/^https?\:/m', '', $murl );
    $mrelative = preg_replace( '/^\/\//', '', $mrelative );
    $mrelative = explode( '/', $mrelative );
    array_shift( $mrelative );
    $mrelative = '/' . implode( '/', $mrelative );

    $mpath = ABSPATH . mb_substr( $mdir, 1 );

    // Should we show the mp3 file list?
    $mshowplaylist = true;
    if ( array_key_exists( 'showplaylist', $atts ) ) {
        $mshowplaylist = ( $atts['showplaylist'] != 'false' );
    }

    // Should we show the packages' links?
    $mshowpackages = true;
    $packageextensions = md_packageExtensions();
    if ( array_key_exists( 'showpackages', $atts ) ) {
        if ( $atts['showpackages'] == 'false' ) {
            $mshowpackages = false;
            $packageextensions = array();
        } elseif ( $atts['showpackages'] != 'true' ) {
            $packageextensions = array_filter( explode( ',', $atts['showpackages'] ) );
        }
    }

    // Should we show the 'cover' file ('folder.jpg')?
    $mshowcover = get_option( 'showcover' );
    if ( array_key_exists( 'showcover', $atts ) ) {
        $mshowcover = ( $atts['showcover'] != 'false' );
    }

    $mshowfeatured = get_option( 'showfeatured' );
    if ( array_key_exists( 'showfeatured', $atts ) ) {
        $mshowfeatured = $atts['showfeatured'];
    }
    if ( !sanitizeImageSize( $mshowfeatured ) ) {
        $mshowfeatured = false;
    }

    // Player position (before or after download link)
    $membedwhere = get_option( 'embedwhere' );
    if ( array_key_exists( 'embedwhere', $atts ) ) {
        $membedwhere = ( $atts['embedwhere'] == 'before' ) ? 'before' : 'after';
    }

    // Should we re-encode the tags?
    $mdoencode = get_option( 'tagencoding' );
    if ( array_key_exists( 'tagencoding', $atts ) ) {
        $mdoencode = $atts['tagencoding'];
    }
    if ( !$mdoencode ) {
        $mdoencode = 'UTF-8';
    }
    $_a = explode( ' + ', $mdoencode );
    $mdoencode = array_pop( $_a );

    // Should we re-encode the file names?
    $mdofnencode = get_option( 'filenameencoding' );
    if ( array_key_exists( 'filenameencoding', $atts ) ) {
        $mdofnencode = $atts['filenameencoding'];
    }
    if ( !$mdofnencode ) {
        $mdofnencode = 'UTF-8';
    }
    $_a = explode( ' + ', $mdofnencode );
    $mdofnencode = array_pop( $_a );

    // How should we sort the files?
    $msort = get_option( 'sortfiles' );
    if ( array_key_exists( 'sortfiles', $atts ) ) {
        $msort = $atts['sortfiles'];
    }
    // "Backward compatibilaziness": it used to be a boolean value
    if ( isset( $msort ) && !array_key_exists( $msort . '', $mdsortingfields ) ) {
        $msort = 'title';
    }

    // Should the sorting be reversed?
    $mreverse = ( get_option( 'reversefiles' ) == true );
    if ( array_key_exists( 'reversefiles', $atts ) ) {
        $mreverse = ( $atts['reversefiles'] == 'true' );
    }

    // Which tags to show?
    $option_showtags = get_option( 'showtags' );
    if ( array_key_exists( 'showtags', $atts ) ) {
        $option_showtags = $atts['showtags'];
    }
    $option_showtags = preg_replace( '/comments/m', 'comment', $option_showtags );
    $mshowtags = array_intersect( array_map( 'trim', explode( ',', $option_showtags ) ), $mdtags );
    // If none, shows the first tag (title)
    if ( !count($mshowtags) ) {
        $mshowtags = array( $mdtags[0] );
    }

    // Markup options
    $covermarkup = get_option( 'covermarkup' );
    if ( array_key_exists( 'covermarkup', $atts ) ) {
        $covermarkup = $atts['covermarkup'];
    }

    $downloadtext = get_option( 'downloadtext' );
    if ( array_key_exists( 'downloadtext', $atts ) ) {
        $downloadtext = $atts['downloadtext'];
    }

    $playtext = get_option( 'playtext' );
    if ( array_key_exists( 'playtext', $atts ) ) {
        $playtext = $atts['playtext'];
    }

    $stoptext = get_option( 'stoptext' );
    if ( array_key_exists( 'stoptext', $atts ) ) {
        $stoptext = $atts['stoptext'];
    }

    $replaceheaders = get_replaceheaders();
    $markuptemplate = get_option( 'markuptemplate' );
    if ( array_key_exists( 'markuptemplate', $atts ) ) {
        $markuptemplate = $atts['markuptemplate'];
    }
    if ( !sanitizeMarkupTemplate( $markuptemplate ) ) {
        $markuptemplate = array_shift( array_keys( $mdmarkuptemplates ) ); // Default: first option
    }

    // Should the ".mp3" file extension be removed from download links?
    $removeextension = ( get_option( 'removeextension' ) == true );
    if ( array_key_exists( 'removeextension', $atts ) ) {
        $removeextension = ( $atts['removeextension'] == 'true' );
    }

    $packagetitle = get_option( 'packagetitle' );
    if ( array_key_exists( 'packagetitle', $atts ) ) {
        $packagetitle = $atts['packagetitle'];
    }
    $packagetexts = get_option( 'packagetexts' );
    if ( !is_array( $packagetexts ) ) $packagetexts = array();
    if ( array_key_exists( 'packagetexts', $atts ) ) {
        $_packagetexts = explode( ';', $atts['packagetexts'] );
        foreach ( $_packagetexts as $_packagetext ) :
            $_pieces = array_map( 'trim', array_filter( explode( ':', $_packagetext ) ) );
            if ( count( $_pieces ) > 1 ) :
                $packagetexts[ array_shift( $_pieces ) ] = implode( ':', $_pieces );
            endif;
        endforeach;
    }

    // Initializing variables
    $cover = '';
    $ihtml = '';
    $iall = array();
    $ifiles = array();
    $ititles = array();
    $ipath = $mpath . '/' . $folder;
    $folderalone = $folder;
    $scan = media_downloader::scandir( $ipath );
    if ( $ipath != $scan['dir'] ) {
        $folderalone = implode( '/', array_slice( explode( '/', $folder ), 0, -1 ) );
        $ipath = $scan[ 'dir' ];
    }
    $cover = $scan[ 'cover' ];
    $iall = $scan[ 'other' ];
    $ifiles = $scan[ 'media' ];

    // Encoding folder name
    $pfolder = array_filter( explode( '/', $folderalone ) );
    foreach( $pfolder as &$p ) $p = rawurlencode( $p );
    unset( $p );

    $ufolder = implode( '/', $pfolder );

    $countextra = 0;
    foreach ( $packageextensions as $pext ) {
        if ( array_key_exists( $pext, $iall ) ) {
            if ( is_countable( $iall[$pext] ) ) {
                $countextra += count( $iall[$pext] );
            }
        } else {
            $iall[ $pext ] = array();
        }
    }
    if ( 'fallback' == $mshowfeatured ) {
        if ( $mshowcover && $cover ) {
            $mshowfeatured = false;
        } else {
            $mshowfeatured = 'large';
        }
    }
    if ( ( $mshowcover && $cover ) || ( $mshowfeatured && has_post_thumbnail() ) || $countextra ) {
        $ihtml .= '<div class="md_albumInfo">';

        if ( $mshowcover && $cover ) {
            $coversrc = network_home_url($mdir) . '/' . ( $ufolder ? $ufolder . '/' : '' ) . $cover;
            $icovermarkup = $covermarkup ? $covermarkup : '<img class="md_coverImage" src="[coverimage]" alt="' . __( 'Album Cover', 'media-downloader' ) . '" />';
            $ihtml .= str_replace( '[coverimage]', $coversrc, $icovermarkup );
        }

        if ( $mshowfeatured && has_post_thumbnail() ) {
            $coversrc = get_the_post_thumbnail_url( null, $mshowfeatured );
            $icovermarkup = $covermarkup ? $covermarkup : '<img class="md_coverImage md_postThumbnail" src="[coverimage]" alt="' . __( 'Album Cover', 'media-downloader' ) . '" />';
            $ihtml .= str_replace( '[coverimage]', $coversrc, $icovermarkup );
        }

        if ( $mshowpackages && $countextra ) {
            $ihtml .= '<div class="md_wholebook">';
            if ( $packagetitle ) $ihtml .= '<h3 class="md_wholebook_title">' . $packagetitle . '</h3>';
            $afolder = explode( '/', $folderalone );
            for ( $a=0; $a<count($afolder); $a++ ) $afolder[$a] = rawurlencode( $afolder[$a] );
            $cfolder = implode( '/', $afolder );
            $ihtml .= '<ul class="md_wholebook_list">';
            foreach ( $packageextensions as $pext ) {
                $cpf = 0; if ( count( $iall[$pext] ) ) foreach( $iall[$pext] as $pf ) {
                    $cpf++;
                    /* translators: %1$s will be replaced by the file extension */
                    $ptext = sprintf( __( 'Download %1$s', 'media-downloader' ), mb_strtoupper( $pext ) );
                    if ( array_key_exists( $pext, $packagetexts ) && $packagetexts[$pext] ) {
                        $ptext = preg_replace( '/\[filename\]/m', $pf, $packagetexts[$pext] );
                    }
                    $ihtml .= '<li class="d' . mb_strtoupper(mb_substr($pext,0,1)) . mb_substr($pext,1) . '"><a href="'.$mrelative.($mrelative!='/'?'/':'').($cfolder).'/'.rawurlencode( $pf ).'" title="' . esc_attr( $pf ) . '" download="' . esc_attr( $pf ) . '">'.$ptext.(count($iall[$pext])>1?' ('.$cpf.')':'').'</a></li>' ;
                }
            }
            $ihtml .= '</ul>';
            $ihtml .= '</div>';
        }

        $ihtml .= '</div>';
    }

    // Any MP3 file?
    if ( $mshowplaylist && count( $ifiles ) ) {
        $Parsedown = new Parsedown();
        // Calculating file "prefixes"
        $prefix = calculatePrefix( $ifiles, $forcePrefix );
        $hlevel = explode( '/', $folder ); $hlevel = array_pop( $hlevel );

        // Initializing array of tag values
        $tagvalues = array();
        foreach ( $mshowtags as $mshowtag ) $tagvalues[$mshowtag] = array();
        $alltags = array();
        foreach ( $ifiles as $ifile ) {
            $ifile = explode( '.', $ifile );
            $iext = array_pop( $ifile );
            $ifile = implode( '.', $ifile );
            // Getting ID3 info
            $blank = array(
                'tags' => array(),
                'audio' => array(),
                'filesize' => '',
                'filepath' => '',
                'filename' => '',
                'playtime_string' => '',
            );
            $finfo = mediadownloaderFileInfo( $mrelative.'/'.$folderalone.'/'.$ifile, $iext );
            $finfo = array_merge( $blank, $finfo );
            if ( !array_key_exists( 'bitrate', $finfo['audio'] ) ) {
                $finfo['audio']['bitrate'] = '';
            }
            if ( !array_key_exists( 'sample_rate', $finfo['audio'] ) ) {
                $finfo['audio']['sample_rate'] = '';
            }
            // Loading all possible tags
            $ftags = array();
            foreach ( array( 'id3v2', 'quicktime', 'ogg', 'asf', 'flac', 'real', 'riff', 'ape', 'id3v1', 'comments' ) as $poss ) {
                if ( is_array( $finfo['tags'] ) && array_key_exists( $poss, $finfo['tags'] ) ) {
                    $ftags = array_merge( $finfo['tags'][$poss], $ftags );
                    if ( array_key_exists( 'comments', $finfo['tags'][$poss] ) ) {
                        $ftags = array_merge( $finfo['tags'][$poss]['comments'], $ftags );
                    }
                }
            }
            $ftags['bitrate'] = array( floatval( $finfo['audio']['bitrate'] ) / 1000 . 'kbps' );
            $ftags['filesize'] = array( byte_convert( $finfo['filesize'] ) );
            $ftags['filedate'] = array( date_i18n( get_option('date_format'), filemtime( $finfo['filepath'] . '/' . $finfo['filename'] ) ) );
            $ftags['directory'] = array( $hlevel );
            $ftags['file'] = array( $ifile );
            $ftags['sample_rate'] = array( hertz_convert( intval( '0' . $finfo['audio']['sample_rate'] ) ) );
            $ftags['playtime_string'] = array( $finfo['playtime_string'] );
            unset( $finfo );
            $alltags[$ifile] = $ftags;
            // Populating array of tag values with all tags
            foreach ( $mdtags as $mshowtag ) {
                $_v = array_key_exists( $mshowtag, $ftags ) ? $ftags[$mshowtag][0] : '';
                if ( 'comment' == $mshowtag ) {
                    if ( array_key_exists( 'text', $ftags ) && is_array( $ftags['text'] ) && trim( strip_tags( $ftags['text'][0] ) ) ) {
                        $tagvalues[$mshowtag][$ifile.'.'.$iext] = $ftags['text'][0];
                    } else {
                        $tagvalues[$mshowtag][$ifile.'.'.$iext] = $Parsedown->text( $_v );
                    }
                } else {
                    $tagvalues[$mshowtag][$ifile.'.'.$iext] = $_v;
                }
            }
            unset( $ftags );
        }
        // Calculating tag "prefixes"
        $tagprefixes = array();
        foreach ( $mshowtags as $mshowtag )
            if ( 'file' == $mshowtag || 'title' == $mshowtag )
                $tagprefixes[$mshowtag] = calculatePrefix( $tagvalues[$mshowtag], $forcePrefix );
        // If set, sorting array
        if ( $msort != 'none' ) {
            sort( $ifiles );
            uasort( $ifiles, $mdsortingfields[$msort] );
        }
        // If set, reversing array
        if ( $mreverse ) $ifiles = array_reverse( $ifiles );

        if ( $onlyjson ) return array( 'files' => $ifiles, 'tags' => $tagvalues, 'prefixes' => $tagprefixes );

        $tablecellsmode_header = '';
        $tablecellsmode_firstfile = true;
        // Building markup for each file...
        foreach ( $ifiles as $ifile ) {
            $ifile = explode( '.', $ifile );
            $iext = array_pop( $ifile );
            $ifile = implode( '.', $ifile );
            $ititle = '';
            // Each tag list item
            foreach ( $mshowtags as $mshowtag ) {
                $_t = $tagvalues[$mshowtag];
                $ifile_and_ext = $ifile.'.'.$iext;
                $tagvalue = array_key_exists( $ifile_and_ext, $_t ) ? $_t[ $ifile_and_ext ] : '';
                if ( '' == $tagvalue ) {
                    $tagvalue = '&nbsp;';
                } else {
                    // Removing "prefix" of this tag
                    if ( array_key_exists( $mshowtag, $tagprefixes ) )
                        if ( '' != $tagprefixes[$mshowtag] )
                            $tagvalue = preg_replace( '/^' . preg_quote( $tagprefixes[$mshowtag], '/' ) . '/', '', $tagvalue );
                    // Cleaning...
                    $tagvalue = replaceUnderscores( $tagvalue );
                    // Encoding...
                    if ( 'file' == $mshowtag || 'directory' == $mshowtag ) {
                        if ( $mdofnencode != 'UTF-8' ) $tagvalue = iconv( $mdofnencode, 'UTF-8', $tagvalue );
                    } elseif ( 'recording_dates' == $mshowtag ) {
                        if ( $tagtime = strtotime( $tagvalue ) ) {
                            $tagvalue = date_i18n( get_option('date_format'), $tagtime );
                        } else {
                            $tagvalue = '';
                        }
                    } elseif ( $mdoencode != 'UTF-8' ) {
                        $tagvalue = iconv( $mdoencode, 'UTF-8', $tagvalue );
                    }
                }
                // Item markup
                $columnheader = ucwords( $mshowtag );
                if ( array_key_exists( $mshowtag, $replaceheaders ) ) {
                    $columnheader = $replaceheaders[ $mshowtag ];
                }
                if ( 'table-cells' == $markuptemplate ) {
                    // For "table cells" markup template,
                    // we store a "row with headers", so it
                    // just needs to run once
                    if ( $tablecellsmode_firstfile ) {
                        $tablecellsmode_header .= '<th class="mdTag'.$mshowtag.'">'.$columnheader.'</th>' ;
                    }
                    $ititle .= '<td class="mdTag'.$mshowtag.'">'.$tagvalue.'</td>' ;
                } elseif ( 'definition-list' == $markuptemplate )  {
                    $ititle .= '<dt class="mdTag'.$mshowtag.'">'.$columnheader.':</dt>' ;
                    $ititle .= '<dd class="mdTag'.$mshowtag.'">'.$tagvalue.'</dd>' ;
                }
            }
            // List markup (if any item)
            if ( '' != $ititle ) {
                if ( 'definition-list' == $markuptemplate ) {
                    $ititle = '<dl class="mdTags">' . $ititle . '</dl>' ;
                }
            }
            $ititles[$ifile] = $ititle ;
            // "Row with headers" is stored already,
            // so skip the task next iteration
            $tablecellsmode_firstfile = false;
        }

        // Building general markup
        $tableClass = array( 'mediaTable' );
        if ( TRUE == get_option( 'embedplayer' ) ) $tableClass[] = 'embedPlayer';
        if ( TRUE == get_option( 'autoplaylist' ) ) $tableClass[] = 'autoPlayList';
        $tableClass[] = 'embedpos' . $membedwhere ;
        $ihtml .= '<table class="' . implode( ' ', $tableClass ) . '">' . "\n";
        $ihtml .= "<thead>\n<tr>\n";
        if ( 'table-cells' == $markuptemplate ) {
            $ihtml .= $tablecellsmode_header;
        } elseif ( 'definition-list' == $markuptemplate ) {
            $ihtml .= "\n" . '<th class="mediaTitle">&nbsp;</th>' . "\n";
        }
        $downloadheader = __( 'Download', 'media-downloader' );
        if ( array_key_exists( 'download', $replaceheaders ) ) $downloadheader = $replaceheaders['download'];
        $ihtml .= '<th class="mediaDownload">'.$downloadheader.'</th>
</tr>
</thead>
<tbody>';


        // Each file...
        foreach ( $ifiles as $ifile ) {
            $ifile = explode( '.', $ifile );
            $iext = array_pop( $ifile );
            $ifile = implode( '.', $ifile );
            // File name
            $showifile = $ifile ;
            // Removing prefix
            if ( array_key_exists( 'file', $tagprefixes ) )
                $showifile = str_replace( $tagprefixes['file'], '', $showifile );
            // Cleaning
            $showifile = replaceUnderscores( $showifile );
            $alltags[$ifile]['file'][0] = $showifile;
            // Download text
            $idownloadtext = $downloadtext ? $downloadtext : 'Download: [file]';
            // Play, Stop, Title and Artist texts (for embed player)
            $iplaytext = $playtext ? $playtext : 'Play: [file]';
            $istoptext = $stoptext ? $stoptext : 'Stop: [file]';
            $ititletext = $showifile;
            $iartisttext = '';
            foreach ( $mdtags as $mdtag ) {
                if ( !array_key_exists( $mdtag, $alltags[$ifile] ) ) $alltags[$ifile][$mdtag] = array( '' );
                $tagvalue = $alltags[$ifile][$mdtag][0];
                if ( 'file' == $mdtag || 'directory' == $mdtag ) {
                    if ( $mdofnencode != 'UTF-8' ) $tagvalue = iconv( $mdofnencode, 'UTF-8', $tagvalue );
                } elseif ( $mdoencode != 'UTF-8' ) {
                    $tagvalue = iconv( $mdoencode, 'UTF-8', $tagvalue );
                }
                // Replacing wildcards
                $idownloadtext = str_replace( '[' . $mdtag . ']', $tagvalue, $idownloadtext );
                $iplaytext = str_replace( '[' . $mdtag . ']', $tagvalue, $iplaytext );
                $istoptext = str_replace( '[' . $mdtag . ']', $tagvalue, $istoptext );
                // If "title", populate "Title text"
                if ( 'title' == $mdtag ) $ititletext = $tagvalue;
                // If "artist", populate "Artist text"
                if ( 'artist' == $mdtag && $tagvalue ) $iartisttext = str_replace( '-', '[_]', $tagvalue ) . ' - ';
            }

            // Getting stored markup
            $ititle = $ititles[$ifile];

            // $ititle = str_replace( $prefix, '', $ititle ); // Causing weird behavior in some cases

            // Markup
            // 20100107 - I took it away: strtoupper( $hlevel )
            $ihtml .= '<tr class="mdTags">'."\n" ;
            if ( 'table-cells' == $markuptemplate ) {
                // a group of "td's"
                $ihtml .= $ititle . "\n";
            } elseif ( 'definition-list' == $markuptemplate ) {
                // one "td" with a "dl" inside
                $ihtml .= '<td class="mediaTitle">'.$ititle.'</td>'."\n" ;
            }
            // Play, Stop and Title (concatenated with Artist) texts
            // all packed in rel attribute, for embed player to read
            // and do its misterious magic
            $data = array();
            if ( $iplaytext ) $data['playtext'] = html_entity_decode( $iplaytext );
            if ( $istoptext ) $data['stoptext'] = html_entity_decode( $istoptext );
            $ititletext = $iartisttext . $ititletext;
            if ( $ititletext ) $data['titletext'] = html_entity_decode(  $ititletext );
            $data_str = '';
            foreach ( $data as $attr => $val ) {
                $data_str .= 'data-' . $attr . '="' . esc_attr( $val ) . '" ';
            }

            $href = implode( '/', array_filter( array(
                network_home_url($mdir),
                $ufolder,
                rawurlencode( $ifile ) . '.' . $iext
            ) ) );

            $ihtml .= '<td class="mediaDownload">';
            $ihtml .= '<a href="'.$href.'" title="' . esc_attr( $showifile ) . '" ' . $data_str . ' id="mdfile_' . sanitize_title( $ifile ) . '" download="' . esc_attr( $ifile . '.' . $iext ) . '">';
            $ihtml .= $idownloadtext;
            $ihtml .= '</a>';
            $ihtml .= '</td>'."\n" ;
            $ihtml .= '</tr>'."\n" ;
        }
        $ihtml .= '</tbody></table>'."\n" ;

    } elseif ( $onlyjson ) {
        return array( 'files' => array(), 'tags' => array(), 'prefixes' => array() );
    }

    if ( count( $errors ) ) {
        $errorHtml = '<div class="mediaDownloaderErrors">';
        foreach ( $errors as $error ) {
            $errorHtml .= '<p>';
            $errorHtml .= '<strong>' . __( 'Error:', 'media-downloader' ) . '</strong> ';
            $errorHtml .= $error;
            $errorHtml .= '</p>';
        }
        $errorHtml .= '</div>';
        $ihtml .= $errorHtml;
    }

    if ( $removeextension ) {
        $ihtml = preg_replace(
            '/href\=[\\\'\"](.*?)'.preg_quote('.mp3').'[\\\'\"]/im',
            "href=\"?md_getfile=$1\"",
            $ihtml
        );
    };

    return $ihtml;
}

// Searches post content for our smarttag and do all the magic
function listMedia( $t ){
    // Searching for our smarttags
    $t = preg_replace( '/<p>\[media:([^\]]*)\]<\/p>/i', '[media:$1]', $t );
    preg_match_all( '/\[media:([^\]]*)\]/i', $t, $matches );
    // Any?
    if ( count( $matches ) > 1 ) {
        // Each...
        foreach ( $matches[1] as $folder ) {
            // Removing paragraph
            $t = str_replace('<p>[media:'.$folder.']</p>', '[media:'.$folder.']', $t);
            $ihtml = buildMediaTable( $folder );
            // Finally, replacing our smart tag
            $t = str_replace( '[media:'.$folder.']', $ihtml, $t );
        }
    }
    return $t ;
}

// To sort file array by some tag
function orderByTag( $a, $b, $tag ) {
    if ( !is_array( $tag ) ) $tag = array( $tag );
    global $tagvalues;
    $ret = 0;
    foreach ( $tag as $t ) {
        if ( ( !$tagvalues ) || ( !array_key_exists( $t, $tagvalues ) ) ) break;
        $ret = strnatcmp( $tagvalues[$t][$a], $tagvalues[$t][$b] );
        if ( 0 != $ret ) break;
    }
    if ( 0 == $ret ) $ret = strnatcmp( $a, $b );
    return $ret;
}
function orderByTitle( $a, $b ) {
    return orderByTag( $a, $b, array( 'title', 'filedate' ) );
}
function orderByFileName( $a, $b ) {
    return orderByTag( $a, $b, 'file' );
}
function orderByFileDate( $a, $b ) {
    return orderByTag( $a, $b, 'filedate' );
}
function orderByRecordingDates( $a, $b ) {
    return orderByTag( $a, $b, 'recording_dates', 'year', 'filedate' );
}
function orderByYear( $a, $b ) {
    return orderByTag( $a, $b, array( 'year', 'track_number', 'filedate' ) );
}
function orderByTrackNumber( $a, $b ) {
    return orderByTag( $a, $b, 'track_number' );
}
function orderByAlbum( $a, $b ) {
    return orderByTag( $a, $b, array( 'album', 'track_number' ) );
}
function orderByArtist( $a, $b ) {
    return orderByTag( $a, $b, array( 'artist', 'album', 'track_number' ) );
}
function orderByFileSize( $a, $b ) {
    return orderByTag( $a, $b, 'filesize' );
}
function orderBySampleRate( $a, $b ) {
    return orderByTag( $a, $b, 'sample_rate' );
}

function md_plugin_dir() {
    return plugin_basename( __DIR__ );
}
function md_plugin_url() {
    return preg_replace( '/^https?\:/m', '', WP_PLUGIN_URL . '/' . md_plugin_dir() );
}

function mediadownloader( $t ) {
    if ( !is_feed() || !get_option( 'handlefeed' ) ) :
        $t = listMedia( $t );
    elseif ( is_feed() ) :
        $att_str = '<p><small>' . __( '(See attached files...)', 'media-downloader' ) . '</small></p>';
        $t = preg_replace( '/<p>\[media:([^\]]*)\]<\/p>/i', $att_str, $t );
    endif;

    /* -- CASE SPECIFIC: -- */
    $t = listarCategorias( $t );
    $t = listarCategoriasEx( $t );
    $t = listarIdiomas( $t );
    /* -- END CASE SPECIFIC; -- */
    return $t;
}


function mediadownloaderFileLength( $filename ) {
    // Initialize getID3 engine
    $getID3 = new getID3;
    // Analyze file and store returned data in $ThisFileInfo
    $ThisFileInfo = $getID3->analyze( $filename );
    // Optional: copies data from all subarrays of [tags] into [comment] so
    // metadata is all available in one location for all tag formats
    // metainformation is always available under [tags] even if this is not called
    getid3_lib::CopyTagsToComments( $ThisFileInfo );
}

// Get ID3 tags from file
function mediadownloaderFileInfo( $f, $ext ) {
    // File path
    if ( function_exists( 'switch_to_blog' ) ) switch_to_blog(1);
    $relURL = str_replace( 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['SERVER_NAME'], '', get_option( 'siteurl' ) );
    if ( function_exists( 'restore_current_blog' ) ) restore_current_blog();
    if ( $relURL ) if ( mb_stripos( $f, $relURL ) === 0 ) $f = mb_substr( $f, mb_strlen( $relURL ) );
    $f = ABSPATH . $f . '.' . $ext;
    $f = preg_replace( '|/+|ims', '/', $f );

    // Checking cache
    $return = false;
    $hash = md5( $f );
    $cachedir = trim( get_option( 'cachedir' ) );
    $cachefile = ABSPATH . '/' . $cachedir . '/md-' . $hash . '.cache' ;
    if ( $cachedir && is_readable( $cachefile )  && file_exists( $f ) && ( filemtime( $cachefile ) >= filemtime( $f ) ) ) {

        $return = unserialize( file_get_contents( $cachefile ) );
        if ( $return ) return $return;

    }
    if ( !$return ) {

        // include getID3() library (can be in a different directory if full path is specified)
        require_once( dirname( __FILE__ ) . '/getid3/getid3.php' );
        // Initialize getID3 engine
        $getID3 = new getID3;
        $mdoencode = get_option( 'tagencoding' );
        $mdoencode = explode( ' + ', $mdoencode );
        $mdoencode = array_shift( $mdoencode );
        if ( 'UTF-8' != $mdoencode ) $getID3->setOption( array( 'encoding' => $mdoencode ) );
        // Analyze file and store returned data in $ThisFileInfo
        if ( $ThisFileInfo = $getID3->analyze( $f ) ) {
            // Saving cache
            if ( $cachedir && is_writeable( ABSPATH . '/' . $cachedir ) ) file_put_contents( $cachefile, serialize( $ThisFileInfo ) );
        }
        return $ThisFileInfo;
    }
}
// File size
function mediadownloaderFileSize( $f, $ext ){
    if ( 0 === mb_stripos( $f, get_option( 'siteurl' ) ) ) $f = str_replace( get_option( 'siteurl' ), '', $f );
    $f = ABSPATH . mb_substr( $f, 1 ) . '.' . $ext;
    if ( !file_exists( $f ) ) $f = urldecode( $f );
    return file_exists( $f ) ? filesize( $f ) : 0;
}
// Extract MP3 links form post content
function mediadownloaderEnclosures( $adjacentmarkup = false ){
    $allmatches = array();
    global $post;
    $cont = listMedia( get_the_content( $post->ID ) );
    foreach ( md_mediaExtensions() as $mext ) {
        $ret = array();
        preg_match_all( '/href=[\\\'"](.*)'.preg_quote('.'.$mext).'[\\\'"]/im', $cont, $matches );
        preg_match_all( '/href=[\\\'"].*\?md_getfile\=(.*)[\\\'"]/im', $cont, $newmatches );
        // It makes no sense, "there can be only one", but just in case...
        if ( count( $matches ) && count( $matches[1] ) ) $ret = array_unique( array_merge( $matches[1], $newmatches[1] ) );

        // Should we get only the MP3 URL's?
        if ( !$adjacentmarkup ) {
            foreach ( $ret as $r ) if ( '/' == mb_substr( $r, 0, 1 ) ) $r = 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://' . $_SERVER['SERVER_NAME'] . $r;
            $allmatches[$mext] = $ret;

        // Or get all the markup around them?
        } else {
            $markuptemplate = get_option( 'markuptemplate' );
            $adj = array();
            $tablehead = '';
            // For each MP3 URL...
            foreach ( $ret as $r ) {
                $adj[$r] = $r;
                // Dirty magic to get the markup around it...
                $rarr = explode( $r . '.' . $mext, $cont );
                if ( count( $rarr ) > 1 ) {
                    $line = mb_substr( $rarr[0], mb_strripos( $rarr[0], '<tr class="mdTags">' ) );
                    $line .= mb_substr( $rarr[1], 0, mb_stripos( $rarr[1], '</tr>' ) ) .'</tr>';
                    if ( 'definition-list' == $markuptemplate ) {
                        $line = mb_substr( $line, mb_strripos( $line, '<dl class="mdTags">' ) );
                        $line = mb_substr( $line, 0, mb_stripos( $line, '</dl>' ) ) . '</dl>';
                        $adj[$r] = $line;
                    } elseif ( 'table-cells' == $markuptemplate ) {

                        if ( '' == $tablehead ) {
                            $safe_r = str_replace( array('/', '.', ':', '%', '-'), array('\\/', '\\.', '\\:', '\\%', '\\-'), $r );
                            preg_match_all( '/\<table([^\>]*)\>(.*?)'.$safe_r.'(.*?)\<\/table\>/ims', $cont, $adjtable );
                            if ( count( $adjtable ) && count( $adjtable[0] ) ) {
                                $ftable = $adjtable[0][0];
                                $ftable = mb_substr( $ftable, mb_strripos( $ftable, '<table' ) );
                                $tablehead = mb_substr( $ftable, 0, mb_stripos( $ftable, '</thead>' ) ) . '</thead>';
                            }
                        }

                        $adj[$r] = ($tablehead?$tablehead:'<table>') . '<tbody>' . $line . '</tbody></table>';
                    }
                }
            }
            $allmatches[$mext] = $adj;
        }
    }
    return $allmatches;
}
// Generate ATOM tags
function mediadownloaderAtom(){
    $t = '';
    $allmatches = mediadownloaderEnclosures();
    foreach ( $allmatches as $mext => $matches ) {
        foreach ( $matches as $m ) {
            //$t.='<link rel="enclosure" title="'.basename($m).'" length="'.mediadownloaderFileSize($m, $mext).'" href="'.WP_PLUGIN_URL.'/media-downloader/getfile.php?f='.urlencode($m).'" type="audio/mpeg" />';
            $t .= '<link rel="enclosure" title="' . basename( $m ) . '" length="' . mediadownloaderFileSize( $m, $mext ) . '" href="' . ( $m . '.' . $mext ) . '" type="audio/mpeg" />';
	    }
	}
    echo $t;
    //return $t;
}
// Generate RSS tags
function mediadownloaderRss(){
    global $post;
    $postdate = strtotime( $post->post_date_gmt );
    $t = '';
    $allmatches = mediadownloaderEnclosures( true );
    foreach ( $allmatches as $mext => $matches ) {
        foreach ( $matches as $m => $adjacentmarkup ) {
            $postdate -= 2;
            //$t.='<enclosure title="'.basename($m).'" url="'.WP_PLUGIN_URL.'/media-downloader/getfile.php?f='.urlencode($m).'" length="'.mediadownloaderFileSize($m, $mext).'" type="audio/mpeg" />';
            //$t .= '<enclosure title="' . basename( $m ) . '" url="' . ( $m . '.' . $mext ) . '" length="' . mediadownloaderFileSize( $m, $mext ) . '" type="audio/mpeg" />';
            $t .= '</item>';
            $t .= '<item>';
            /* translators: on this rss feed item, %1$s and %2$s will be replaced by file name and post title, respectively */
            $t .= '<title>' . sprintf( __( 'Attached file: %1$s - %2$s', 'media-downloader' ), urldecode( basename( $m ) ), get_the_title($post->ID) ) . '</title>';
            $t .= '<link>' . get_permalink($post->ID) . '#mdfile_' . sanitize_title( basename( urldecode( $m ) ) ) . '</link>';
            $t .= '<description><![CDATA[' . $adjacentmarkup . ']]></description>';
            $t .= '<pubDate>' . date( DATE_RSS, $postdate ) . '</pubDate>';
            $t .= '<guid>' . get_permalink($post->ID) . '#mdfile_' . sanitize_title( basename( urldecode( $m ) ) ) . '</guid>';
            $t .= '<enclosure url="' . ( $m . '.' . $mext ) . '" length="' . mediadownloaderFileSize( $m, $mext ) . '" type="audio/mpeg" />';
	    }
	}
    echo $t;
    //return $t;
}

add_filter( 'the_content', 'mediadownloader' );

if ( get_option( 'handlefeed' ) ) :
    add_action( 'atom_entry', 'mediadownloaderAtom' );
    //add_action( 'rss_item', 'mediadownloaderRss' );
    add_action( 'rss2_item', 'mediadownloaderRss' );
    // Lowering cache lifetime to 4 hours
    add_filter( 'wp_feed_cache_transient_lifetime', function( $a ) {
        $newvalue = 4*3600;
        if ( $a < $newvalue ) $a = $newvalue;
        return $a;
    } );
endif;

// Uses bcmath extension, if available, to generate a smaller hash than md5's one.
function mediaDownloaderStrHash( $customcss ) {
    $converted = md5( $customcss );
    if ( extension_loaded( 'bcmath' ) ) {
        $n = base_convert( $converted, 16, 10 );
        $codeset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
        $base = strlen( $codeset );
        $converted = '';
        while ( $n > 0 ) {
            $bcmod = bcmod( $n, $base );
            $converted = substr( $codeset, $bcmod, 1 ) . $converted;
            $n = bcdiv( $n, $base );
        }
    }
    return $converted;
}

function mediaDownloaderModificationTime( $file ) {
    $filepath = dirname(__FILE__) . $file;
    if ( file_exists( $filepath ) ) {
        return date( 'YmdHis', filemtime( $filepath ) );
    }
    return date( 'YmdHis' );
}

function mediaDownloaderEnqueueScripts() {
    // If any custom css, we enqueue our php that throws this css
    $customcss = trim( get_option( 'customcss' ) );
    if ( ( '' != $customcss ) && ( !is_admin() ) ) {
        wp_register_style( 'mediadownloaderCss', home_url() . '?md_getcss', array(), mediaDownloaderStrHash( $customcss ) );
        wp_enqueue_style( 'mediadownloaderCss' );
    }

    // Enqueuing our javascript
    wp_enqueue_script( 'mediadownloaderJs', md_plugin_url() . '/js/mediadownloader.js', array('jquery'), mediaDownloaderModificationTime( '/js/mediadownloader.js' ), true );

    // Passing options to our javascript
    add_action( 'get_header', 'mediaDownloaderLocalizeScript' );
}

// Passing options to our javascript
function mediaDownloaderLocalizeScript() {
    $replaceheaders = get_replaceheaders();
    $playheader = __( 'Play', 'media-downloader' );
    if ( array_key_exists( 'play', $replaceheaders ) ) $playheader = $replaceheaders['play'];
    wp_localize_script( 'mediadownloaderJs', 'mdStringTable', array(
        'pluginURL' => md_plugin_url() . '/',
        'playColumnText' => $playheader,
        'downloadTitleText' => __( 'Download:', 'media-downloader' ),
        'playTitleText' => __( 'Play:', 'media-downloader' ),
        'stopTitleText' => __( 'Stop:', 'media-downloader' ),
    ) );
}

function mediaDownloaderInit() {
    mediaDownloaderEnqueueScripts();
    add_filter( 'set-screen-option', 'mediadownloader_adm_save_options', 10, 3 );
}
add_action( 'init', 'mediaDownloaderInit' );

add_action( 'admin_init', 'md_admin_init' );

function md_admin_init() {
    wp_register_style(
        'md-admin-css',
        md_plugin_url() . '/css/admin.css',
        array(),
        mediaDownloaderModificationTime( '/css/admin.css' )
    );
    wp_register_script(
        'md-admin-script',
        md_plugin_url() . '/js/admin.js',
        array( 'jquery' ),
        mediaDownloaderModificationTime( '/js/admin.js' )
    );
}
function md_admin_styles() {
    wp_enqueue_style( 'md-admin-css' );
}
function md_admin_scripts() {
    wp_enqueue_script( 'md-admin-script', false, array( 'jquery' ) );
}

// Our options screens...
add_action( 'admin_menu', 'mediadownloader_menu' );

function mediadownloader_menu() {
    $oppage = add_menu_page( 'Media Downloader Options', 'Media Downloader', 'manage_options', 'mediadownloader-options', 'mediadownloader_options', 'dashicons-playlist-audio' );
    add_action( 'admin_print_styles-' . $oppage, 'md_admin_styles' );
    add_action( 'admin_print_scripts-' . $oppage, 'md_admin_scripts');
    if ( array_key_exists( 'tag-editor', $_GET ) ) add_action( "load-$oppage", 'mediadownloader_adm_add_options' );
}


function mediadownloader_adm_add_options() {
    $option = 'per_page';
    $args = array(
        /* translators: Placeholders receive min and max 'per page' values (10 and 100) */
        'label' => sprintf( __( 'items (min: %1$d - max: %2$d)', 'media-downloader' ), 10, 100 ),
        'default' => 50,
        'option' => 'mediadownloader_adm_items_per_page'
    );
    add_screen_option( $option, $args );
}
function mediadownloader_adm_save_options( $status, $option, $value ) {
    if ( 'mediadownloader_adm_items_per_page' == $option ) {
        if ( $value >= 10 && $value <= 100 ) {
            return $value;
        }
    }
    return false;
}

function mediadownloader_options() {
    // Basically, user input forms...
    if ( isset( $_GET['markup-options'] ) ) {
        require_once( dirname( __FILE__ ) . '/mediadownloader-markup-options.php' );
    } elseif ( isset( $_GET['more-options'] ) ) {
        require_once( dirname( __FILE__ ) . '/mediadownloader-more-options.php' );
    } elseif ( isset( $_GET['tag-editor'] ) ) {
        require_once( dirname( __FILE__ ) . '/mediadownloader-tag-editor.php' );
    } else {
        require_once( dirname( __FILE__ ) . '/mediadownloader-options.php' );
    }
}

// Registering our settings...
add_action( 'admin_init', 'mediadownloader_settings' );

function mediadownloader_settings() {
    global $mdsettings;
    foreach ( $mdsettings as $mdsetting => $mdsanitizefunction ) {
        register_setting( 'md_options', $mdsetting, $mdsanitizefunction );
    }

    global $mdmarkupsettings;
    foreach ( $mdmarkupsettings as $mdmarkupsetting => $mdsanitizefunction ) {
        register_setting( 'md_markup_options', $mdmarkupsetting, $mdsanitizefunction );
    }

    global $mdmoresettings;
    foreach ( $mdmoresettings as $mdsetting => $mdsanitizefunction ) {
        register_setting( 'md_more_options', $mdsetting, $mdsanitizefunction );
    }
}

function md_self_link() {
	$host = @parse_url( home_url() );
	return esc_url( apply_filters( 'md_self_link', set_url_scheme( 'http://' . $host['host'] . stripslashes($_SERVER['REQUEST_URI']) ) ) );
}
function md_filter_feed_link( $link, $type = 'rss2' ) {
    $overwritefeedlink = ( 'rss2' == $type ) ? trim( get_option( 'overwritefeedlink' ) ) : false;
    return $overwritefeedlink ? $overwritefeedlink : $link;
}
add_filter( 'md_self_link', 'md_filter_feed_link' );
add_filter( 'feed_link', 'md_filter_feed_link' );

