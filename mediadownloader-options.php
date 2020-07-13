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
<?php /* translators: %1$s will be replaced by the URL for wordpress documentation on file permissions */ ?>
<input type="text" name="mp3folder" id="md_mp3folder" value="<?php echo esc_attr( $mdoptions['mp3folder'] ); ?>" size="20" /> <small><?php echo sprintf( __( '(must be <a href="%1$s">readable</a>)', 'media-downloader' ), 'http://codex.wordpress.org/Changing_File_Permissions' ) ;?></small>

<?php if( '' != trim( $mdoptions['mp3folder'] ) ){
    $dirok = is_readable( ABSPATH . '/' . $mdoptions['mp3folder'] ) ;?>
    <br /><small style="color:#999;background-color:#<?php echo $dirok ? 'DFD' : 'FDD' ;?>"><?php echo $dirok ? __( 'Folder successfully read.', 'media-downloader' ) : __( 'Could not read folder.', 'media-downloader' ) ;?></small>
<?php };?>

</p>

<p>
<label for="md_cachedir"><?php _e( 'Cache dir:', 'media-downloader' );?> <code><?php echo ABSPATH ;?></code></label>
<?php /* translators: %1$s will be replaced by the URL for wordpress documentation on file permissions */ ?>
<input type="text" name="cachedir" id="md_cachedir" value="<?php echo $mdoptions['cachedir'] ;?>" size="20" /> <small><?php echo sprintf( __( '(must be <a href="%1$s">writable</a>)', 'media-downloader' ), 'http://codex.wordpress.org/Changing_File_Permissions' ) ;?></small>

<?php if( '' != trim( $mdoptions['cachedir'] ) ){
    $dirok = is_writeable( ABSPATH . '/' . $mdoptions['cachedir'] ) ;?>
    <br /><small style="color:#999;background-color:#<?php echo $dirok ? 'DFD' : 'FDD' ;?>"><?php echo $dirok ? __( 'Folder successfully written on.', 'media-downloader' ) : __( 'Could not write on folder.', 'media-downloader' );?></small>
<?php };?>

</p>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
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
<?php /* translators: %1$s will be replaced by the "fallback" keyword (used in a shortcode attribute) */ ?>
<p><small><?php printf( __( '(If set to %1$s, the post thumbnail will be shown only if there is no "cover" image)', 'media-downloader' ), '<code>fallback</code>' ); ?></small></p>
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
<?php /* translators: %1$s will be replaced by the URL for wordpress documentation on file permissions */ ?>
<p><small><?php echo sprintf( __( '(must be <a href="%1$s">readable</a>)', 'media-downloader' ), 'http://codex.wordpress.org/Changing_File_Permissions' ) ;?></small></p>
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
