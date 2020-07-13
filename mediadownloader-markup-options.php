<?php

global $mdmarkupsettings, $mdtags, $mdmarkuptemplates, $mdencodings, $mdtags, $mdsortingfields;
$mdoptions = array();
foreach( $mdmarkupsettings as $mdmarkupsetting => $mdsanitizefunction ) $mdoptions[$mdmarkupsetting] = get_option( $mdmarkupsetting );

?>


<div class="wrap">

<?php include( dirname( __FILE__ ) . '/mediadownloader-options-header.php' ); ?>

<form method="post" action="options.php">
<?php settings_fields( 'md_markup_options' ); ?>

<fieldset>

<h2><?php _e( 'Media list options', 'media-downloader' ); ?></h2>

<h4><?php _e( 'General tag info template', 'media-downloader' ); ?></h4>

<p>
<?php
$markuptemplate = $mdoptions['markuptemplate'];
if ( !sanitizeMarkupTemplate( $markuptemplate ) ) $markuptemplate = array_shift( array_keys( $mdmarkuptemplates ) );
?>
<?php foreach ( $mdmarkuptemplates as $key => $value ) : ?>
    <label for="md_markuptemplate_<?php echo $key; ?>"><input type="radio" name="markuptemplate" id="md_markuptemplate_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php if ( $key == $markuptemplate ) : ?>checked="checked"<?php endif; ?> /> <?php echo __( $value, 'media-downloader' ); ?></label> <br />
<?php endforeach; ?>
</p>

<p>
<h4><?php _e( 'Media files:', 'media-downloader' ) ;?></h4>
<?php foreach ( md_mediaAllExtensions() as $mext ) :
    $checked = in_array( $mext, md_mediaExtensions() ) ? ' checked="checked"' : '';
    ?>
    <label for="md_mediaextension_<?php echo $mext; ?>" style="min-width:82px;display:inline-block;"><input type="checkbox" id="md_mediaextension_<?php echo $mext; ?>" name="mediaextensions[]" value="<?php echo $mext; ?>"<?php echo $checked; ?> /> <?php echo '.' . $mext; ?></label>
<?php endforeach; ?>
</p>

<h4><label for="md_downloadtext"><?php _e( 'Download Text:', 'media-downloader' ); ?></label></h4>
<p>
<input type="text" name="downloadtext" id="md_downloadtext" value="<?php echo esc_attr( $mdoptions['downloadtext'] ) ;?>" size="75" /><br />
<small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code>Download: [title]</code></i></small><br />
<small><?php _e( 'Wildcards:', 'media-downloader' ); ?> <code>[<?php echo implode( ']</code>, <code>[', $mdtags ) ;?>]</code></small>
</p>

<p>
<input type="checkbox" name="removeextension" id="md_removeextension" value="1" <?php if ( $mdoptions['removeextension'] ) echo ' checked="checked" ' ;?> />
<label for="md_removeextension">
<?php _e( 'Remove ".mp3" from download URL', 'media-downloader' ) ;?>
<br />
<small><?php _e( '(checking it may cause some server overloading)', 'media-downloader' ) ;?></small>
</label>
</p>

<h4><label for="md_showtags"><?php _e( 'Comma-separated MP3 info to show for each file on the list:', 'media-downloader' ) ;?></label></h4>
<p>
<input type="text" id="md_showtags" name="showtags" size="75" value="<?php echo $mdoptions['showtags'] ;?>" />
<br />
<small><?php _e( 'Default:', 'media-downloader' ) ;?> <code><?php echo $mdtags[0] ;?></code>.</small><br />
<small><?php _e( 'Possible values:', 'media-downloader' ) ;?> <code><?php echo implode( '</code>, <code>', $mdtags ) ;?></code>.</small>
</p>

<h4><?php _e( 'Replace ID3 tags column headers', 'media-downloader' ); ?></h4>

<p>
<label for="md_replaceheaders"><?php _e( 'Replaces default columns headers (ie: "title", "comment") with custom values:', 'media-downloader' ) ;?></label> <br />
<textarea name="replaceheaders" id="md_replaceheaders" cols="75" rows="7"><?php echo $mdoptions['replaceheaders'] ;?></textarea> <br />
<small><?php _e( 'Syntax example: <br /><code>comment:Description</code><br /><code>title:Episode</code>', 'media-downloader' ); ?></small>
</p>

<p>
<input type="checkbox" name="calculateprefix" id="md_calculateprefix" value="1" <?php if ( $mdoptions['calculateprefix'] ) echo ' checked="checked" ' ;?> />
<label for="md_calculateprefix">
<?php _e( 'Try to guess and remove a common "prefix" to all the files of the same folder', 'media-downloader' ) ;?>
<br />
<small><?php _e( '(though a very helpful "magic" sometimes, this feature behaves in a unpredictably wild way)', 'media-downloader' ) ;?></small>
</label>
</p>

<p>
<label for="md_sortfiles"><?php _e( 'Sort by:', 'media-downloader' );?></label>
<select name="sortfiles" id="md_sortfiles">
<?php foreach ( $mdsortingfields as $mdsortingfield => $mdsanitizefunction ) { ?>
    <option value="<?php echo $mdsortingfield ;?>" <?php if ( $mdsortingfield == $mdoptions['sortfiles'] ) echo 'selected="selected"' ;?> ><?php echo $mdsortingfield ;?></option>
<?php } ;?>
</select>

<input type="checkbox" name="reversefiles" id="md_reversefiles" value="1" <?php if ( $mdoptions['reversefiles'] ) echo ' checked="checked" ' ;?> />
<label for="md_reversefiles"><?php _e( 'Reverse order', 'media-downloader' );?></label>
</p>

<p>
<label for="md_tagencoding"><?php _e( 'MP3 tag encoding:', 'media-downloader' );?></label>
<select name="tagencoding" id="md_tagencoding">
<?php foreach ( $mdencodings as $mdencoding ) { ?>
    <option value="<?php echo $mdencoding ;?>" <?php if ( $mdencoding == $mdoptions['tagencoding'] ) echo 'selected="selected"' ;?> ><?php echo $mdencoding ;?></option>
<?php } ;?>
</select>
</p>

<p>
<label for="md_filenameencoding"><?php _e( 'File name encoding:', 'media-downloader' );?></label>
<select name="filenameencoding" id="md_filenameencoding">
<?php foreach ( $mdencodings as $mdencoding ) { ?>
    <option value="<?php echo $mdencoding ;?>" <?php if ( $mdencoding == $mdoptions['filenameencoding'] ) echo 'selected="selected"' ;?> ><?php echo $mdencoding ;?></option>
<?php } ;?>
</select>
</p>

</fieldset>

<hr />

<fieldset>

<h2><?php _e( 'Audio player options', 'media-downloader' ); ?></h2>

<p>
<input type="checkbox" name="embedplayer" id="md_embedplayer" value="1" <?php if ( $mdoptions['embedplayer'] ) echo ' checked="checked" ' ;?> />
<label for="md_embedplayer"><?php _e( 'Embed player', 'media-downloader' ) ;?></label>
</p>

<p>
<label for="md_embedwhere"><?php _e( 'Embed player\'s button position:', 'media-downloader' );?></label>
<select name="embedwhere" id="md_embedwhere">
    <option value="before" <?php if ( 'before' == $mdoptions['embedwhere'] ) echo 'selected="selected"' ;?> ><?php _e( 'Before download link', 'media-downloader' );?></option>
    <option value="after" <?php if ( 'after' == $mdoptions['embedwhere'] ) echo 'selected="selected"' ;?> ><?php _e( 'After download link', 'media-downloader' );?></option>
</select>
</p>

<h4><label for="md_playtext"><?php _e( 'Play Text:', 'media-downloader' ) ;?></label></h4>
<p>
<input type="text" name="playtext" id="md_playtext" value="<?php echo esc_attr( $mdoptions['playtext'] ) ;?>" size="75" /><br />
<small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code>Play: [title]</code></i></small><br />
<small><?php _e( 'Wildcards:', 'media-downloader' ); ?> <code>[<?php echo implode( ']</code>, <code>[', $mdtags ) ;?>]</code></small>
</p>

<h4><label for="md_stoptext"><?php _e( 'Stop Text:', 'media-downloader' ) ;?></label></h4>
<p>
<input type="text" name="stoptext" id="md_stoptext" value="<?php echo esc_attr( $mdoptions['stoptext'] ) ;?>" size="75" /><br />
<small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code>Stop: [title]</code></i></small><br />
<small><?php _e( 'Wildcards:', 'media-downloader' ); ?> <code>[<?php echo implode( ']</code>, <code>[', $mdtags ) ;?>]</code></small>
</p>

<p>
<input type="checkbox" name="autoplaylist" id="md_autoplaylist" value="1" <?php if ( $mdoptions['autoplaylist'] ) echo ' checked="checked" ' ;?> />
<label for="md_autoplaylist"><?php _e( 'Autoplay next file', 'media-downloader' );?></label>
</p>

</fieldset>

<hr />

<fieldset>

<h2><?php _e( 'Cover image options', 'media-downloader' ); ?></h2>

<p>
<input type="checkbox" name="showcover" id="md_showcover" value="1" <?php if ( $mdoptions['showcover'] ) echo ' checked="checked" ' ;?> />
<label for="md_showcover"><?php _e( 'Show cover (if a <code>folder.jpg</code> file is found)', 'media-downloader' ) ;?></label>
</p>

<p>
<label for="md_showfeatured"><?php _e( 'Show post thumbnail:', 'media-downloader' ) ;?></label>
<select name="showfeatured" id="md_showfeatured">
    <option><?php _e( 'Never', 'media-downloader' ) ;?></option>
    <option value="fallback" <?php if ( 'fallback' == $mdoptions['showfeatured'] ) echo 'selected="selected"' ;?> ><?php _e( 'If there is no "cover" image', 'media-downloader' ) ;?></option>
    <?php foreach ( get_intermediate_image_sizes() as $mdsize ) { ?>
        <option value="<?php echo $mdsize ;?>" <?php if ( $mdsize == $mdoptions['showfeatured'] ) echo 'selected="selected"' ;?> ><?php _e( 'Size:', 'media-downloader' ); ?> <?php echo $mdsize ;?></option>
    <?php } ;?>
</select>
</p>

<h4><label for="md_covermarkup"><?php _e( 'Cover image markup', 'media-downloader' ) ;?></label></h4>
<p>
<input type="text" name="covermarkup" id="md_covermarkup" value="<?php echo esc_attr( $mdoptions['covermarkup'] ) ;?>" size="75" /><br />
<small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code>&lt;img class="md_coverImage" src="[coverimage]" alt="<?php _e( 'Album Cover', 'media-downloader' ); ?>" /&gt;</code></i></small><br />
<small><?php _e( 'Wildcards:', 'media-downloader' ) ;?> <code>[coverimage]</code></small>
</p>

</fieldset>

<hr />

<fieldset>
<h2><?php _e( 'Compacted files options', 'media-downloader' ); ?></h2>

<h4><label for="md_packagetitle"><?php _e( 'File list title:', 'media-downloader' ); ?></label></h4>
<p>
<input type="text" name="packagetitle" id="md_packagetitle" value="<?php echo esc_attr( $mdoptions['packagetitle'] ) ;?>" size="75" /><br />
<small><?php _e( 'Example:', 'media-downloader' ); ?> <i><code><?php _e( 'Compacted Files', 'media-downloader' ); ?></code></i></small>
</p>

<h4><label for="md_packageextensions"><?php _e( 'Comma-separated extensions for compacted files:', 'media-downloader' ); ?></label></h4>
<p>
<input type="text" id="md_packageextensions" name="packageextensions" size="75" value="<?php echo $mdoptions['packageextensions'] ;?>" /><br />
<small><?php _e( 'Example:', 'media-downloader' ); ?> <code>zip</code>, <code>rar</code>, <code>tgz</code></small>
</p>

<?php
$pexts = md_packageExtensions();
if ( count( $pexts ) ) :
    ?>    
    <h4><?php _e( 'Links texts:', 'media-downloader' ); ?></h4>
    <?php foreach ( $pexts as $pext ) : $pext = trim( $pext ); ?>
        <?php
        $ptexts = $mdoptions['packagetexts'];
        $pext_text = array_key_exists( $pext, $ptexts ) ? $ptexts[$pext] : ''; ?>
        <p>
        <?php /* translators: %1$s will be replaced by the file extensions */ ?>
        <label for="md_packagetext_<?php echo $pext; ?>"><?php printf( __( '<code>%1$s</code> file link text:', 'media-downloader' ), $pext ) ;?></label> <br />
        <input type="text" name="packagetexts[<?php echo $pext; ?>]" id="md_packagetext_<?php echo $pext; ?>" value="<?php echo esc_attr( $pext_text ) ;?>" size="75" /><br />
        <small><?php _e( 'Default:', 'media-downloader' ); ?> <i><code><?php echo sprintf( __( 'Download %1$s', 'media-downloader' ), mb_strtoupper( $pext ) ); ?></code></i></small><br />
        <small><?php _e( 'Wildcards:', 'media-downloader' ) ;?> <code>[filename]</code></small>
        </p>
    <?php endforeach; ?>
<?php endif; ?>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
</p>
</fieldset>

</form>

