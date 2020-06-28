<div id="icon-options-general" class="icon32"><br /></div>

<h2><?php _e( 'Media Downloader:', 'media-downloader' ) ;?></h2>

<h3 class="nav-tab-wrapper">

<?php
// Tabs array
$mdtabs = array(
    'general' => __( 'General Options', 'media-downloader' ),
    'markup-options' => __( 'Markup Options', 'media-downloader' ),
    'more-options' => __( 'More Options', 'media-downloader' ),
    'tag-editor' => __( 'Tag Editor', 'media-downloader' ),
);
// If no tab is set as active, we set the first
$anyTab = false;
foreach ( $mdtabs as $tabSlug => $tabText ) if ( isset( $_GET[$tabSlug] ) ) $anyTab = true;
$_tabs = array_keys($mdtabs);
if ( !$anyTab ) $_GET[array_shift($_tabs)] = true;

// Building tab's markup
foreach ( $mdtabs as $tabSlug => $tabText ) :
?>
    <a href="?page=mediadownloader-options&amp;<?php echo $tabSlug; ?>" class="nav-tab<?php if ( isset( $_GET[$tabSlug] ) ) { ?> nav-tab-active<?php }; ?>"><?php echo $tabText; ?></a>
<?php endforeach; ?>

</h3>
