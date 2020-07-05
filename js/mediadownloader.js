var mediadownloaderPluginURL = '';
function initMediaDownloader() {
    var mediadownloaderPlayColumnText = 'Play';
    var mediadownloaderDownloadTitleText = 'Download:';
    var mediadownloaderPlayTitleText = 'Play:';
    var mediadownloaderStopTitleText = 'Play:';
    if ( typeof(mdStringTable) != 'undefined' ) {
        mediadownloaderPluginURL = mdStringTable.pluginURL;
        mediadownloaderPlayColumnText = mdStringTable.playColumnText;
        mediadownloaderDownloadTitleText = mdStringTable.downloadTitleText;
        mediadownloaderPlayTitleText = mdStringTable.playTitleText;
        mediadownloaderStopTitleText = mdStringTable.stopTitleText;
    } else {
        mediadownloaderPluginURL = '/wp-content/plugins/media-downloader/';
        var scripts = jQuery('script[src*="js/mediadownloader.js"]');
        if( scripts.length ) mediadownloaderPluginURL = scripts[0].src.split('js/mediadownloader.js')[0];
    }
    jQuery("table.mediaTable.embedPlayer th.mediaDownload").each( function () {
        var thcont='<th class="mediaPlay">' + mediadownloaderPlayColumnText + '</th>';
        if ( jQuery(this).parents('table.mediaTable').hasClass('embedposafter') ) {
            jQuery(this).after(thcont);
        } else {
            jQuery(this).before(thcont);
        }
    } );
    jQuery('table.mediaTable.embedPlayer td.mediaDownload a').each( function () {
        var link = jQuery(this).attr('href');
        var title = jQuery(this).attr('title').replace(mediadownloaderDownloadTitleText, mediadownloaderPlayTitleText);
        var text = jQuery(this).html().replace(mediadownloaderDownloadTitleText, mediadownloaderPlayTitleText);
        var relattr = jQuery(this).html().replace(mediadownloaderDownloadTitleText, mediadownloaderStopTitleText);
        var arrrel = jQuery(this).attr('rel');
        if ( arrrel ) {
            try {
                arrrel = JSON.parse( arrrel );
                if ( 'mediaDownloaderPlayText' in arrrel ) {
                    text = arrrel.mediaDownloaderPlayText;
                }
                if ( 'mediaDownloaderStopText' in arrrel ) {
                    relattr = arrrel.mediaDownloaderStopText;
                }
                if ( 'mediaDownloaderTitleText' in arrrel ) {
                    title = arrrel.mediaDownloaderTitleText;
                }
            } catch( e ) {
                console.log( 'Error parsing JSON', e );
            }
        }
        var tdcont = '<td class="mediaPlay"><a href="'+link+'" title="'+title+'" rel="' + escape(relattr) + '">'+text+'</a></td>';
        if ( jQuery(this).parents('table.mediaTable').hasClass('embedposafter') ) {
            jQuery(this).parent().after(tdcont);
        } else {
            jQuery(this).parent().before(tdcont);
        }
    } );
    jQuery('table.mediaTable.embedPlayer td.mediaPlay a').click( function () {
        var link = jQuery(this).attr('href');
        var linkText = jQuery(this).html();
        var playText = jQuery(this).data('playtext');
        if ( !playText ) {
            playText = linkText;
            jQuery(this).data('playtext', playText);
        }
        var linkRel = unescape(jQuery(this).attr('rel'));
        var stopText = jQuery(this).data('stoptext');
        if ( !stopText ) {
            stopText = linkRel;
            jQuery(this).data('stoptext', stopText);
        }
        mediaplayerStop();
        var linkPlaying = jQuery(this).hasClass('mediaStop');
        if( !linkPlaying ){
            var title = jQuery(this).attr('title').replace(mediadownloaderPlayTitleText, '');
            mediaplayerPlay( link, title, jQuery(this) );
            jQuery(this).addClass('mediaStop').parents('td.mediaPlay').addClass('mediaPlaying');
        }
        jQuery(this).attr('rel', linkText).html(linkRel);
        return false;
    } );
}

jQuery(document).ready(function($) {
    initMediaDownloader();
});

function mediaplayerStr( url, title, tdcolspan ) {
    var artist = '';
    if ( title.indexOf( '-' ) > -1 ) {
        var stitle = title.split( '-' );
        artist = stitle[0].replace( '[_]', '-' );
        title = stitle.slice(1,stitle.length).join('-');
    }
    var strColors = '';
    var mdBgColor = 'FFF';
    if ( typeof(mdEmbedColors) != 'undefined' ) {
        for ( i in mdEmbedColors ) strColors += i + '=' + mdEmbedColors[i] + '&amp;';
        mdBgColor = mdEmbedColors.bg;
    }
    if ( typeof(tdcolspan) == 'undefined' ) tdcolspan = 3;
    var strMarkupFlash = '<object type="application/x-shockwave-flash" name="audioplayer_1" style="outline: none" data="'+mediadownloaderPluginURL+'js/audio-player.swf?ver=2.0.4.1" width="100%" height="25" id="audioplayer_1">' + '<param name="bgcolor" value="#' + mdBgColor + '">' + '<param name="movie" value="'+mediadownloaderPluginURL+'js/audio-player.swf?ver=2.0.4.1">' + '<param name="menu" value="false">' + '<param name="flashvars" value="animation=yes&amp;encode=no&amp;initialvolume=80&amp;remaining=no&amp;noinfo=no&amp;buffer=5&amp;' + 'checkpolicy=no&amp;rtl=no&amp;' + strColors + 'autostart=yes&amp;soundFile=' + escape(url) + '&amp;titles=' + title + '&amp;artists=' + artist + '&amp;playerID=audioplayer_1"><a href="' + url + '">' + title + '</a></object>';
    var strMarkupHTML5 = '<audio controls="controls" preload="auto" style="width:100%; background-color: #' + mdBgColor + ';" name="browserplayer_1" id="browserplayer_1"><source src="' + url + '" type="audio/mp3" /><a href="' + url + '">' + title + '</a></audio>';
    var a = document.createElement( 'audio' );
    var strMarkup = !!(a.canPlayType) ? strMarkupHTML5 : strMarkupFlash;
    return '<tr class="mediaPlayer"><td colspan="'+tdcolspan+'" align="center">' + strMarkup + '</td></tr>';
}
    
var mediaplayerPlayingURL = '';
function mediaplayerPlay( url, title, clicked ) {
    if( url != mediaplayerPlayingURL ) {
        mediaplayerStop();
        if ( typeof clicked == 'undefined' ) {
            clicked = jQuery('a[href="'+url+'"]').first();
        }
        var linktr = clicked.parents('tr').first();
        var tdcolspan = 0;
        linktr.children('td').each( function () {
            var currentcolspan = parseInt( '0' + jQuery(this).attr('colspan'), 10 );
            if ( !currentcolspan ) currentcolspan = 1;
            tdcolspan += currentcolspan;
        } );
        linktr.after( mediaplayerStr( url, title, tdcolspan ) );
        var o_browser_player = document.getElementById( 'browserplayer_1' );
        if ( o_browser_player ) {
            if ( linktr.parents( 'table' ).hasClass( 'autoPlayList' ) ) {
                o_browser_player.addEventListener( 'ended', function () {
                    var o_next_tr = jQuery( this ).parents( 'tr.mediaPlayer' ).next( 'tr.mdTags' );
                    mediaplayerStop();
                    if ( o_next_tr.length ) {
                        jQuery( 'td.mediaPlay a', o_next_tr ).first().trigger( 'click' );
                    }
                }, false );
            }
            o_browser_player.play();
        }
        mediaplayerPlayingURL = url;
    }
}

function mediaplayerStop() {
    if ( document.getElementById( 'browserplayer_1' ) ) {
        document.getElementById( 'browserplayer_1' ).pause();
    }
    jQuery('tr.mediaPlayer').find('object').remove().end().find('audio').remove().end().remove();
    jQuery('a.mediaStop').removeClass('mediaStop').each( function () {
        jQuery(this).html( jQuery(this).data('playtext') ).attr( 'rel', jQuery(this).data('stoptext') );
    } );
    jQuery('td.mediaPlaying').removeClass('mediaPlaying');

    mediaplayerPlayingURL = '';
}
