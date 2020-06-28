<?php

global $mdsettings, $mdencodings, $mdtags, $mdsortingfields;
$mdoptions = array();
foreach( $mdsettings as $mdsetting => $mdsanitizefunction ) $mdoptions[$mdsetting] = get_option( $mdsetting );

?>


<div class="wrap">

<?php include( dirname( __FILE__ ) . '/mediadownloader-options-header.php' ); ?>

<form method="post" action="options.php">
<?php settings_fields( 'md_options' ); ?>

<fieldset id="mdf_mp3folder">
<h3><label for="md_mp3folder"><?php _e( 'MP3 Folder:', 'media-downloader' ) ;?></label></h3>
<p>
<label for="md_mp3folder"><code><?php echo ABSPATH ;?></code></label>
<input type="text" name="mp3folder" id="md_mp3folder" value="<?php echo $mdoptions['mp3folder'] ;?>" size="20" /> <small><?php echo sprintf( __( '(must be <a href="%s">readable</a>)', 'media-downloader' ), 'http://codex.wordpress.org/Changing_File_Permissions' ) ;?></small>

<?php if( '' != trim( $mdoptions['mp3folder'] ) ){
    $dirok = is_readable( ABSPATH . '/' . $mdoptions['mp3folder'] ) ;?>
    <br /><small style="color:#999;background-color:#<?php echo $dirok ? 'DFD' : 'FDD' ;?>"><?php echo $dirok ? __( 'Folder successfully read.', 'media-downloader' ) : __( 'Could not read folder.', 'media-downloader' ) ;?></small>
<?php };?>

</p>


<p>
<h4><?php _e( 'Media files:', 'media-downloader' ) ;?></h4>
<?php foreach ( md_mediaAllExtensions() as $mext ) :
    $checked = in_array( $mext, md_mediaExtensions() ) ? ' checked="checked"' : '';
    ?>
    <label for="md_mediaextension_<?php echo $mext; ?>" style="min-width:82px;display:inline-block;"><input type="checkbox" id="md_mediaextension_<?php echo $mext; ?>" name="mediaextensions[]" value="<?php echo $mext; ?>"<?php echo $checked; ?> /> <?php echo '.' . $mext; ?></label>
<?php endforeach; ?>
</p>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
</p>
</fieldset>

<hr />

<fieldset id="mdf_customcss">
<h3><label for="md_showtags"><?php _e( 'Show MP3 Info:', 'media-downloader' ) ;?></label></h3>
<p>
<label for="md_showtags"><?php _e( 'Comma-separated MP3 info to show for each file on the list:', 'media-downloader' ) ;?></label><br />
<input type="text" id="md_showtags" name="showtags" size="75" value="<?php echo $mdoptions['showtags'] ;?>" />
<br />
<small><?php _e( 'Possible values:', 'media-downloader' ) ;?> <code><?php echo implode( '</code>, <code>', $mdtags ) ;?></code>.</small>
<br />
<small><?php _e( 'Default:', 'media-downloader' ) ;?> <code><?php echo $mdtags[0] ;?></code>.</small>
</p>

<h3><label for="md_customcss"><?php _e( 'Custom CSS:', 'media-downloader' ) ;?></label></h3>
<p>
<textarea id="md_customcss" name="customcss" cols="75" rows="7"><?php echo $mdoptions['customcss'] ;?></textarea>
</p>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
</p>
</fieldset>

<hr />

<fieldset id="mdf_advanced">
<h3><?php _e( 'Advanced:',  'media-downloader' ) ;?></h3>
<p>
<input type="checkbox" name="removeextension" id="md_removeextension" value="1" <?php if ( $mdoptions['removeextension'] ) echo ' checked="checked" ' ;?> />
<label for="md_removeextension">
<?php _e( 'Remove ".mp3" from download URL', 'media-downloader' ) ;?>
<br />
<small><?php _e( '(checking it may cause some server overloading)', 'media-downloader' ) ;?></small>
</label>
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

<p>
<label for="md_packageextensions"><?php _e( 'Comma-separated extensions for compacted files:', 'media-downloader' ) ;?></label><br />
<input type="text" id="md_packageextensions" name="packageextensions" size="75" value="<?php echo $mdoptions['packageextensions'] ;?>" /><br />
<small><?php _e( 'Example:', 'media-downloader' ); ?> <code>zip</code>, <code>rar</code>, <code>tgz</code></small>
</p>

<p>
<input type="checkbox" name="embedplayer" id="md_embedplayer" value="1" <?php if ( $mdoptions['embedplayer'] ) echo ' checked="checked" ' ;?> />
<label for="md_embedplayer"><?php _e( 'Embed player', 'media-downloader' ) ;?></label>
</p>

<p>
<input type="checkbox" name="autoplaylist" id="md_autoplaylist" value="1" <?php if ( $mdoptions['autoplaylist'] ) echo ' checked="checked" ' ;?> />
<label for="md_autoplaylist"><?php _e( 'Autoplay next file', 'media-downloader' );?></label>
</p>

<p>
<input type="checkbox" name="scriptinfooter" id="md_scriptinfooter" value="1" <?php if ( $mdoptions['scriptinfooter'] ) echo ' checked="checked" ' ;?> />
<label for="md_scriptinfooter"><?php _e( 'Place embed player\'s javascript at the bottom of the <code>&lt;body&gt;</code> in <abbr title="HyperText Markup Language">HTML</abbr> code', 'media-downloader' );?></label>
</p>

<p>
<label for="md_embedwhere"><?php _e( 'Embed player\'s button position:', 'media-downloader' );?></label>
<select name="embedwhere" id="md_embedwhere">
    <option value="before" <?php if ( 'before' == $mdoptions['embedwhere'] ) echo 'selected="selected"' ;?> ><?php _e( 'Before download link', 'media-downloader' );?></option>
    <option value="after" <?php if ( 'after' == $mdoptions['embedwhere'] ) echo 'selected="selected"' ;?> ><?php _e( 'After download link', 'media-downloader' );?></option>
</select>
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

<p>
<label for="md_cachedir"><?php _e( 'Cache dir:', 'media-downloader' );?> <code><?php echo ABSPATH ;?></code></label>
<input type="text" name="cachedir" id="md_cachedir" value="<?php echo $mdoptions['cachedir'] ;?>" size="20" /> <small><?php echo sprintf( __( '(must be <a href="%s">writable</a>)', 'media-downloader' ), 'http://codex.wordpress.org/Changing_File_Permissions' ) ;?></small>

<?php if( '' != trim( $mdoptions['cachedir'] ) ){
    $dirok = is_writeable( ABSPATH . '/' . $mdoptions['cachedir'] ) ;?>
    <br /><small style="color:#999;background-color:#<?php echo $dirok ? 'DFD' : 'FDD' ;?>"><?php _e( $dirok ? 'Folder successfully written on.' : 'Could not write on folder.', 'media-downloader' );?></small>
<?php };?>

</p>

<p>
<input type="checkbox" name="handlefeed" id="md_handlefeed" value="1" <?php if ( $mdoptions['handlefeed'] ) echo ' checked="checked" ' ;?> />
<label for="md_handlefeed">
<?php _e( 'Include MP3 files in wordpress feeds', 'media-downloader' );?>
</label>
</p>

<p>
<label for="md_overwritefeedlink"><?php _e( 'Overwrite feed link:', 'media-downloader' );?></label><br />
<input type="text" id="md_overwritefeedlink" name="overwritefeedlink" size="75" value="<?php echo $mdoptions['overwritefeedlink'] ;?>" />
</p>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' );?>" />
</p>
</fieldset>

</form>

<hr />

<h2><?php _e( 'Sample Usage', 'media-downloader' );?></h2>
<p><?php _e( 'Media Downloader plugin lists MP3 files from a folder through the [mediadownloader] shortcode.', 'media-downloader' );?></p>

<p><?php _e( 'An example may help... Say you have a folder called <em>"music"</em> under your root folder, and it has some subfolders, as: <em>"Beethoven",</em> <em>"Mozart",</em> <em>"Bach"</em> and <em>"Haendel".</em>', 'media-downloader' ) ;?></p>

<p><?php _e( 'First of all, you should configure Media Downloader by typing <em>"music"</em> in the <label for="md_mp3folder"><em>"MP3 Folder"</em> field.</label> That done, you can edit a post talking about Johann Sebastian Bach and insert anywhere on it the shortcode <code>[mediadownloader folder="Bach"]</code>, then Media Downloader will create a list of all files under the <em>"music/Bach"</em> directory.', 'media-downloader' ) ;?></p>

<p><?php _e( 'The [mediadownloader] shortcode also accepts the following parameters:', 'media-downloader' ); ?></p>

<dl class="md_sample_atts">

<dt>showtags</dt>
<dd>
<p><?php _e( 'Comma-separated MP3 info to show for each file on the list', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code><?php echo implode( '</code>, <code>', $mdtags ) ;?></code></small></p>
</dd>

<dt>showplaylist</dt>
<dd>
<p><?php _e( 'Show media playlist and player', 'media-downloader' );?></p>
<small><?php _e( 'Possible values:', 'media-downloader' );?> <code>true</code>, <code>false</code></small>
</dd>

<dt>showpackages</dt>
<dd>
<p><?php _e( 'Show links for compacted files', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code>true</code> <?php _e( 'or comma-separated extensions', 'media-downloader' ); ?></small></p>
</dd>


<dt>packagetitle</dt>
<dd>
<p><?php _e( 'Compacted files list title', 'media-downloader' );?></p>
<p><small><?php _e( 'Example:', 'media-downloader' );?> <code><?php _e( 'Compacted Files', 'media-downloader' ); ?></code></small></p>
</dd>

<dt>packagetexts</dt>
<dd>
<p><?php _e( 'Compacted files link texts', 'media-downloader' );?></p>
<p><small><?php _e( 'Example:', 'media-downloader' );?> <code><?php _e( 'zip: Download ZIP; rar: Download RAR;', 'media-downloader' ); ?></code></small></p>
</dd>

<dt>showcover</dt>
<dd>
<p><?php _e( 'Show cover (if a <code>folder.jpg</code> file is found)', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code>true</code>, <code>false</code></small></p>
</dd>

<dt>showfeatured</dt>
<dd>
<p><?php _e( 'Show post thumbnail', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code><?php echo implode( '</code>, <code>', array_merge( array( 'false', 'fallback' ), get_intermediate_image_sizes() ) ) ;?></code></small></p>
<p><small><?php printf( __( '(If set to %s, the post thumbnail will be shown only if there is no "cover" image)', 'media-downloader' ), '<code>fallback</code>' ); ?></small></p>
</dd>

<dt>embedwhere</dt>
<dd>
<p><?php _e( 'Embed player\'s button position', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code>before</code>, <code>after</code></small></p>
</dd>

<dt>calculateprefix</dt>
<dd>
<p><?php _e( 'Try to guess and remove a common "prefix" to all the files of the same folder', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code>true</code>, <code>false</code></small></p>
<p><small><?php _e( '(though a very helpful "magic" sometimes, this feature behaves in a unpredictably wild way)', 'media-downloader' ) ;?></small></p>
</dd>

<dt>mp3folder</dt>
<dd>
<p><?php _e( 'MP3 Folder.', 'media-downloader' );?></p>
<p><small><?php echo sprintf( __( '(must be <a href="%s">readable</a>)', 'media-downloader' ), 'http://codex.wordpress.org/Changing_File_Permissions' ) ;?></small></p>
</dd>

<dt>tagencoding</dt>
<dd>
<p><?php _e( 'MP3 tag encoding', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code><?php echo implode( '</code>, <code>', $mdencodings ); ?></code></small></p>
</dd>

<dt>filenameencoding</dt>
<dd>
<p><?php _e( 'File name encoding', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code><?php echo implode( '</code>, <code>', $mdencodings ); ?></code></small></p>
</dd>

<dt>sortfiles</dt>
<dd>
<p><?php _e( 'Sort files by', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code><?php echo implode( '</code>, <code>', array_keys( $mdsortingfields ) ); ?></code></small></p>
</dd>

<dt>reversefiles</dt>
<dd>
<p><?php _e( 'Reverse order', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code>true</code>, <code>false</code></small></p>
</dd>

<dt>removeextension</dt>
<dd>
<p><?php _e( 'Remove ".mp3" from download URL', 'media-downloader' );?></p>
<p><small><?php _e( 'Possible values:', 'media-downloader' );?> <code>true</code>, <code>false</code></small></p>
</dd>

<dt>downloadtext</dt>
<dd>
<p><?php _e( 'Download button\'s text', 'media-downloader' );?></p>
</dd>

<dt>playtext</dt>
<dd>
<p><?php _e( 'Play button\'s text', 'media-downloader' );?></p>
</dd>

<dt>stoptext</dt>
<dd>
<p><?php _e( 'Stop button\'s text', 'media-downloader' );?></p>
</dd>

</dl>

</div>
