<?php

global $mdmoresettings;
$mdmoreoptions = array();
foreach( $mdmoresettings as $mdsetting => $mdsanitizefunction ) $mdmoreoptions[$mdsetting] = get_option( $mdsetting );

?>

<div class="wrap">

<?php include( dirname( __FILE__ ) . '/mediadownloader-options-header.php' ); ?>

<form method="post" action="options.php">
    <?php settings_fields( 'md_more_options' ); ?>

    <fieldset>

        <h2><label for="md_customcss"><?php _e( 'Custom CSS', 'media-downloader' ) ;?></label></h2>

        <p>
        <textarea id="md_customcss" name="customcss" cols="75" rows="10"><?php echo $mdmoreoptions['customcss'] ;?></textarea>
        </p>

    </fieldset>

    <fieldset>

        <h2><?php _e( 'RSS Feeds', 'media-downloader' ) ;?></h2>

        <p>
        <input type="checkbox" name="handlefeed" id="md_handlefeed" value="1" <?php if ( $mdmoreoptions['handlefeed'] ) echo ' checked="checked" ' ;?> />
        <label for="md_handlefeed">
        <?php _e( 'Include MP3 files in wordpress feeds', 'media-downloader' );?>
        </label>
        </p>

        <p>
        <label for="md_overwritefeedlink"><?php _e( 'Overwrite feed link:', 'media-downloader' );?></label><br />
        <input type="text" id="md_overwritefeedlink" name="overwritefeedlink" size="75" value="<?php echo $mdmoreoptions['overwritefeedlink'] ;?>" />
        </p>

        <p class="submit">
        <input type="submit" class="button button-primary" value="<?php _e( 'Update Options', 'media-downloader' ) ;?>" />
        </p>

    </fieldset>

</form>

</div>