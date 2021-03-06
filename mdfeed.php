<?php

if ( !class_exists( 'Parsedown' ) ) require_once( dirname( __FILE__ ) . '/inc/Parsedown.php' );
$Parsedown = new Parsedown();

// Backwards compatibility: redirecting old parameters
if ( array_key_exists( 'folder', $_GET ) && !array_key_exists( 'md_feed', $_GET ) ) :
    $r = explode( 'wp-content', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
    header( 'Location: ' . '//' . $r[0] . '?md_feed=' . $_GET['folder'] );
    exit();
endif;

function mdfeed_die() {
    header( 'Location: ' . get_bloginfo( 'rss2_url' ) );
    exit();
}

$folder = array_key_exists( 'md_feed', $_GET ) ? $_GET['md_feed'] : '';

if ( !$folder ) mdfeed_die();

$cont = listMedia( '[media:' . $folder . ']' );

if ( !trim( $cont ) ) mdfeed_die();

$allmatches = array();
foreach ( md_mediaExtensions() as $mext ) {
    $ret = array();
    preg_match_all( '/href=[\\\'"](.*)'.preg_quote('.'.$mext).'[\\\'"]/im', $cont, $matches );
    preg_match_all( '/href=[\\\'"].*\?md_getfile\&f\=(.*)[\\\'"]/im', $cont, $newmatches );
    // It makes no sense, "there can be only one", but just in case...
    if ( count( $matches ) && count( $matches[1] ) ) $ret = array_unique( array_merge( $matches[1], $newmatches[1] ) );

    $markuptemplate = get_option( 'markuptemplate' );
    $adj = array();
    $tablehead = '';
    // For each MP3 URL...
    foreach ( $ret as $r ) {
        $mfile = str_replace( get_option( 'siteurl' ) . '/', '', urldecode( $r ) );
        $finfo = mediadownloaderFileInfo( $mfile, $mext );
        // Loading all possible tags
        $ftags = array();
        foreach ( array( 'id3v2', 'quicktime', 'ogg', 'asf', 'flac', 'real', 'riff', 'ape', 'id3v1', 'comments' ) as $poss ) {
            if ( array_key_exists( $poss, $finfo['tags'] ) ) {
                $ftags = array_merge( $finfo['tags'][$poss], $ftags );
                if ( array_key_exists( 'comments', $finfo['tags'][$poss] ) ) {
                    $ftags = array_merge( $finfo['tags'][$poss]['comments'], $ftags );
                }
            }
        }
        $ftags['bitrate'] = array( floatval( $finfo['audio']['bitrate'] ) / 1000 . 'kbps' );
        $ftags['filesize'] = array( byte_convert( $finfo['filesize'] ) );
        $ftags['filedaterss'] = array( date( DATE_RSS, filemtime( $finfo['filepath'] . '/' . $finfo['filename'] ) ) );
        if ( array_key_exists( 'id3v2', $finfo ) && array_key_exists( 'comments', $finfo['id3v2'] ) ) {
            $fcomm = $finfo['id3v2']['comments'];
            if ( array_key_exists( 'recording_dates', $fcomm ) && is_array( $fcomm['recording_dates'] ) ) $ftags['filedaterss'] = array( date( DATE_RSS, strtotime( $fcomm['recording_dates'][0] ) ) );
            if ( array_key_exists( 'original_release_year', $fcomm ) && is_array( $fcomm['original_release_year'] ) ) $ftags['filedaterss'] = array( date( DATE_RSS, strtotime( $fcomm['original_release_year'][0] . '-01-01' ) ) );
            if ( array_key_exists( 'original_release_time', $fcomm ) && is_array( $fcomm['original_release_time'] ) ) $ftags['filedaterss'] = array( date( DATE_RSS, strtotime( $fcomm['original_release_time'][0] ) ) );
            if ( array_key_exists( 'release_time', $fcomm ) && is_array( $fcomm['release_time'] ) ) $ftags['filedaterss'] = array( date( DATE_RSS, strtotime( $fcomm['release_time'][0] ) ) );
        }
        $ftags['filedate'] = array( date_i18n( get_option('date_format'), filemtime( $finfo['filepath'] . '/' . $finfo['filename'] ) ) );
        $ftags['directory'] = array( $hlevel );
        $ftags['file'] = array( $ifile );
        $ftags['sample_rate'] = array( hertz_convert( intval( '0' . $finfo['audio']['sample_rate'] ) ) );
        unset( $finfo );
        $adj[$r] = $ftags;
    }
    $allmatches[$mext] = $adj;
}

if ( !count( $allmatches ) ) mdfeed_die();

$t = '';
foreach ( $allmatches as $mext => $matches ) {
    foreach ( $matches as $m => $ftags ) {
        $t .= '<item>' . "\n";
        $t .= '<title>' . $ftags['title'][0] . '</title>' . "\n";
        $t .= '<link>' . ( $m . '.' . $mext ) . '</link>' . "\n";
        $t .= '<description><![CDATA[' . $Parsedown->text( $ftags['comment'][0] ) . ']]></description>' . "\n";
        $t .= '<pubDate>' . $ftags['filedaterss'][0] . '</pubDate>' . "\n";
        $t .= '<guid>' . ( $m . '.' . $mext ) . '</guid>' . "\n";
        $t .= '<enclosure url="' . ( $m . '.' . $mext ) . '" length="' . mediadownloaderFileSize( $m, $mext ) . '" type="audio/mpeg" />' . "\n";
        $t .= '</item>' . "\n";
    }
}

header( 'Content-type: application/rss+xml' );
echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?>' . "\n";

?>
<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
    <?php do_action('rss2_ns'); ?> >

<channel>
    <title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
    <atom:link href="<?php echo md_self_link(); ?>" rel="self" type="application/rss+xml" />
    <link><?php echo md_self_link(); ?></link>
    <description><?php bloginfo( 'description' ); ?></description>
    <lastBuildDate><?php echo date( DATE_RSS ); ?></lastBuildDate>
    <language><?php bloginfo_rss( 'language' ); ?></language>
    <sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
    <sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
    <?php do_action('rss2_head'); ?>
    <?php echo str_replace( "\n", "\n    ", $t ) . "\n"; ?>
</channel>

</rss>

