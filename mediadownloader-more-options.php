<?php

global $mdembedplayerdefaultcolors;
$mdmoreoptions = array();
foreach( $mdembedplayerdefaultcolors as $mdcolor => $mddefault ) $mdmoreoptions[$mdcolor] = get_option( $mdcolor . '_embed_color' );

$vazio = true;
foreach( $mdembedplayerdefaultcolors as $mdcolor => $mddefault ) 
  if ( trim( $mdmoreoptions[$mdcolor] ) ) {
    $vazio = false;
    break;
  }

// As seen here: http://wpaudioplayer.com/standalone/
$colordescriptions = array( 
    'bg' => 'Background',
    'leftbg' => 'Speaker icon/Volume control background',
    'lefticon' => 'Speaker icon',
    'voltrack' => 'Volume track',
    'volslider' => 'Volume slider',
    'rightbg' => 'Play/Pause button background',
    'rightbghover' => 'Play/Pause button background (hover state)',
    'righticon' => 'Play/Pause icon',
    'righticonhover' => 'Play/Pause icon (hover state)',
    'loader' => 'Loading bar',
    'track' => 'Loading/Progress bar track backgrounds',
    'tracker' => 'Progress track',
    'border' => 'Progress bar border',
    'skip' => 'Previous/Next skip buttons',
    'text' => 'Text',
);

?>


<div class="wrap">

<?php include( dirname( __FILE__ ) . '/mediadownloader-options-header.php' ); ?>

<form method="post" action="options.php">
<?php settings_fields( 'md_more_options' ); ?>

<fieldset id="mdf_embedcolors">
<h3><?php _e( 'Old Embed Flash Player Colors:', 'media-downloader' ) ;?></h3>
<p class="description"><?php _e( 'Fallback for old browsers that don\'t support the <code>audio</code> tag.', 'media-downloader' ); ?></p>
<br />

<table cellpadding="3">
<thead>
<tr>
<th><?php _e( 'Property', 'media-downloader' ); ?></th>
<th><?php _e( 'Value', 'media-downloader' ); ?></th>
<th><?php _e( 'Default', 'media-downloader' ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $mdembedplayerdefaultcolors as $mdcolor => $mddefault ) : ?>
  <tr>
  <td>
    <label for="md_<?php echo esc_attr( $mdcolor ); ?>"><?php echo $colordescriptions[$mdcolor]; ?> <!--<em>("<?php echo $mdcolor; ?>")</em>--> </label>
  </td>
  <td>
    <input type="color" name="<?php echo esc_attr( $mdcolor ); ?>_embed_color" id="md_<?php echo esc_attr( $mdcolor ); ?>" value="<?php echo '#' . ( $mdmoreoptions[$mdcolor] ? $mdmoreoptions[$mdcolor] : $mddefault ); ?>" size="7" maxlength="7" />
  </td>
  <td>
    <code style="border: 2px solid #<?php echo $mddefault; ?>;"><?php echo $mddefault; ?></code>
  </td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>

<p class="submit">
<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
</p>
</fieldset>

</form>


</div>
