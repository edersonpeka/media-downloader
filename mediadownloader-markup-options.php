<?php

global $mdmarkupsettings, $mdtags, $mdmarkuptemplates;
$mdoptions = array();
foreach( $mdmarkupsettings as $mdmarkupsetting => $mdsanitizefunction ) $mdoptions[$mdmarkupsetting] = get_option( $mdmarkupsetting );

?>


<div class="wrap">

<?php include( dirname( __FILE__ ) . '/mediadownloader-options-header.php' ); ?>

<form method="post" action="options.php">
<?php settings_fields( 'md_markup_options' ); ?>

<fieldset id="mdf_replaceheaders">

<h2><?php _e( 'General tag info template', 'media-downloader' ); ?></h2>

<p>
<?php
$markuptemplate = $mdoptions['markuptemplate'];
if ( !sanitizeMarkupTemplate( $markuptemplate ) ) $markuptemplate = array_shift( array_keys( $mdmarkuptemplates ) );
?>
<?php foreach ( $mdmarkuptemplates as $key => $value ) : ?>
    <label for="md_markuptemplate_<?php echo $key; ?>"><input type="radio" name="markuptemplate" id="md_markuptemplate_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php if ( $key == $markuptemplate ) : ?>checked="checked"<?php endif; ?> /> <?php _e( $value, 'media-downloader' ) ;?></label> <br />
<?php endforeach; ?>
</p>

<h2><?php _e( 'List options', 'media-downloader' ); ?></h2>

<h3><?php _e( 'Replace ID3 tags column headers', 'media-downloader' ); ?></h3>

<p>
<label for="md_replaceheaders"><?php _e( 'Replaces default columns headers (ie: "title", "comments") with custom values:', 'media-downloader' ) ;?></label> <br />
<textarea name="replaceheaders" id="md_replaceheaders" cols="75" rows="10"><?php echo $mdoptions['replaceheaders'] ;?></textarea> <br />
<small><?php _e( 'Syntax example: <br /><code>comments:Description</code><br /><code>title:Episode</code>', 'media-downloader' ); ?></small>
</p>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
</p>
</fieldset>

<hr />

<fieldset>

<h2><?php _e( 'Cover image markup', 'media-downloader' ); ?></h2>

<h4><label for="md_covermarkup"><?php _e( 'Wildcards:', 'media-downloader' ) ;?> <code>[coverimage]</code></label></h4>
<p>
<input type="text" name="covermarkup" id="md_covermarkup" value="<?php echo esc_attr( $mdoptions['covermarkup'] ) ;?>" size="75" /><br />
<small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code>&lt;img class="md_coverImage" src="[coverimage]" alt="<?php _e( 'Album Cover', 'media-downloader' ); ?>" /&gt;</code></i></small>
</p>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
</p>
</fieldset>

<hr />

<?php
$pexts = md_packageExtensions();
if ( count( $pexts ) ) :
    ?>
    <fieldset>
    <h2><?php _e( 'Links for compacted files', 'media-downloader' ); ?></h2>
    
    <h3><label for="md_packagetitle"><?php _e( 'File list title:', 'media-downloader' ); ?></label></h3>
    <p>
    <input type="text" name="packagetitle" id="md_packagetitle" value="<?php echo esc_attr( $mdoptions['packagetitle'] ) ;?>" size="75" /><br />
    <small><?php _e( 'Example:', 'media-downloader' ); ?> <i><code><?php _e( 'Compacted Files' ); ?></code></i></small>
    </p>
    
    <h3><?php _e( 'Links texts:', 'media-downloader' ); ?></h3>
    <?php foreach ( $pexts as $pext ) : $pext = trim( $pext ); ?>
        <?php
        $ptexts = $mdoptions['packagetexts'];
        $pext_text = array_key_exists( $pext, $ptexts ) ? $ptexts[$pext] : ''; ?>
        <p>
        <label for="md_packagetext_<?php echo $pext; ?>"><?php printf( __( '<code>%s</code> file link text:', 'media-downloader' ), $pext ) ;?></label> <br />
        <input type="text" name="packagetexts[<?php echo $pext; ?>]" id="md_packagetext_<?php echo $pext; ?>" value="<?php echo esc_attr( $pext_text ) ;?>" size="75" /><br />
        <small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code><?php echo sprintf( __( 'Download %', 'media-downloader' ), mb_strtoupper( $pext ) ); ?></code></i></small>
        </p>
    <?php endforeach; ?>
    <h4><?php _e( 'Wildcards:', 'media-downloader' ) ;?> <code>[filename]</code></h4>
    
    <p class="submit">
    <input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
    </p>
    </fieldset>

    <hr />
<?php endif; ?>

<fieldset id="mdf_downloadtext">

<h2><?php _e( 'Each item options', 'media-downloader' ); ?></h2>

<h4><?php _e( 'Wildcards:', 'media-downloader' ); ?> <code>[<?php echo implode( ']</code>, <code>[', $mdtags ) ;?>]</code>.</h4>

<p>
<label for="md_downloadtext"><?php _e( 'Download Text:', 'media-downloader' ) ;?></label> <br />
<input type="text" name="downloadtext" id="md_downloadtext" value="<?php echo esc_attr( $mdoptions['downloadtext'] ) ;?>" size="75" /><br />
<small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code>Download: [title]</code></i></small>
</p>

<p>
<label for="md_playtext"><?php _e( 'Play Text:', 'media-downloader' ) ;?></label> <br />
<input type="text" name="playtext" id="md_playtext" value="<?php echo esc_attr( $mdoptions['playtext'] ) ;?>" size="75" /><br />
<small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code>Play: [title]</code></i></small>
</p>

<p>
<label for="md_stoptext"><?php _e( 'Stop Text:', 'media-downloader' ) ;?></label> <br />
<input type="text" name="stoptext" id="md_stoptext" value="<?php echo esc_attr( $mdoptions['stoptext'] ) ;?>" size="75" /><br />
<small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code>Stop: [title]</code></i></small>
</p>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
</p>
</fieldset>

</form>

