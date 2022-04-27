/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://gnu.org/licenses/>.
 */

// https://attacomsian.com/blog/javascript-check-variable-is-object
const isObject = ( obj ) => {
    return Object.prototype.toString.call( obj ) === '[object Object]';
};

const isEnabled = ( plugin ) => {
    if ( Array.isArray( config.plugins ) ) {
        if ( config.plugins.includes( plugin ) ) {
            return true;
        }
    } else if ( isObject( config.plugins ) ) {
        if ( plugin in config.plugins ) {
            if ( 'enabled' in config.plugins[plugin] ) {
                if ( config.plugins[plugin].enabled ) {
                    return true;
                }
            } else  {
                return true;
            }
        }
    }
    return false;
};

let config = mw.config.get( 'wgPhotoSwipeConfig' );
if ( config ) {
    console.log( 'Loading PhotoSwipe' , config );
    let dynamiccaption;
    let lightbox;
    let PhotoSwipe;
    let PhotoSwipeLightbox;
    let PhotoSwipeDeepZoomPlugin;
    let PhotoSwipeDynamicCaption;
    let PhotoSwipeVideoPlugin;
    let videoplugin;

    /*
     * mode: recommended -> https://photoswipe.com/getting-started/#initialization
     * mode: withoutdynamicimport -> https://photoswipe.com/getting-started/#without-dynamic-import
     * mode: withoutlightbox -> https://photoswipe.com/data-sources/#without-lightbox-module
     */
    if ( config.mode !== 'withoutlightbox' ) {
        PhotoSwipeLightbox = require( 'js.photoswipe-lightbox' );
    }
    if ( config.mode === 'withoutdynamicimport' ) {
        PhotoSwipe = require( 'js.photoswipe' );
    }
    if ( isEnabled( 'DeepZoomPlugin' ) ) {
        PhotoSwipeDeepZoomPlugin = require( 'js.photoswipe-deep-zoom-plugin' );
    }
    if ( isEnabled( 'DynamicCaption' ) ) {
        PhotoSwipeDynamicCaption = require( 'js.photoswipe-dynamic-caption-plugin' );
    }
    if ( isEnabled( 'VideoPlugin' ) ) {
        PhotoSwipeVideoPlugin = require( 'js.photoswipe-video-plugin' );
    }

    // https://en.wikipedia.org/wiki/Cross-site_scripting
    // https://mediawiki.org/wiki/Requests_for_comment/Content-Security-Policy
    // https://mediawiki.org/wiki/Manual:$wgCSPHeader
    // https://mediawiki.org/wiki/Manual:$wgCSPReportOnlyHeader
    if ( config.addBeginning ) {
        if ( typeof config.addBeginning === 'string' ) {
            jQuery.globalEval( config.addBeginning, { nonce: config.nonce } );
        } else if ( Array.isArray( config.addBeginning ) ) {
            config.addBeginning.forEach( ( str ) => {
                jQuery.globalEval( str, { nonce: config.nonce } );
            });
        }
    }

    // https://photoswipe.com/data-sources/#without-lightbox-module
    if ( config.mode !== 'withoutlightbox' ) {
        if ( typeof config.options.pswpModule === 'string' ) {
            // Prepare require variable to be globally accessible within jQuery.globalEval
            if ( !( 'require' in window ) ) window.require = require;
            jQuery.globalEval( `var pswpModule = ${config.options.pswpModule}`, { nonce: config.nonce } );
            config.options.pswpModule = pswpModule;
        }
        lightbox = new PhotoSwipeLightbox[ 'default' ]( config.options );
        // Prepare lightbox variable to be globally accessible within jQuery.globalEval
        if ( !( 'lightbox' in window ) ) window.lightbox = lightbox;
    }

    /*
     * Eventables: For executing lightbox events, filters, methods, and other relevant JS code.
     *
     * https://photoswipe.com/events/
     * https://photoswipe.com/filters/
     * https://photoswipe.com/methods/
     *
     * https://photoswipe.com/opening-or-closing-transition/#transition-duration-and-easing
     * https://photoswipe.com/opening-or-closing-transition/#hiding-elements-that-overlap-thumbnails
     * https://photoswipe.com/adding-ui-elements/#adding-a-button-to-the-toolbar
     * https://photoswipe.com/adding-ui-elements/#adding-html-indicator-to-the-toolbar
     * https://photoswipe.com/adding-ui-elements/#adding-download-button
     * https://photoswipe.com/adding-ui-elements/#adding-navigation-indicator-bullets
     * https://photoswipe.com/adding-ui-elements/#uiregisterelement-api
     * https://photoswipe.com/caption/
     * https://photoswipe.com/custom-content/#using-webp-image-format
     * https://photoswipe.com/custom-content/#google-maps-demo
     * https://photoswipe.com/data-sources/#custom-last-slide
     * https://photoswipe.com/data-sources/#dynamically-generated-data
     * https://photoswipe.com/data-sources/#custom-html-markup
     * https://photoswipe.com/data-sources/#separate-dom-and-data
     * https://photoswipe.com/native-fullscreen-on-open/
     */
    if ( config.addEventables ) {
        if ( typeof config.addEventables === 'string' ) {
            jQuery.globalEval( config.addEventables, { nonce: config.nonce } );
        } else if ( Array.isArray( config.addEventables ) ) {
            config.addEventables.forEach( ( str ) => {
                jQuery.globalEval( str, { nonce: config.nonce } );
            });
        }
    }

    if ( isEnabled( 'DeepZoomPlugin' ) ) {
        lightbox.deepzoomplugin = new PhotoSwipeDeepZoomPlugin[ 'default' ]( lightbox, config.plugins.DeepZoomPlugin.options );
    }
    if ( isEnabled( 'DynamicCaption' ) ) {
        lightbox.dynamiccaption = new PhotoSwipeDynamicCaption[ 'default' ]( lightbox, config.plugins.DynamicCaption.options );
    }
    if ( isEnabled( 'VideoPlugin' ) ) {
        lightbox.videoplugin = new PhotoSwipeVideoPlugin[ 'default' ]( lightbox, config.plugins.VideoPlugin.options );
    }

    lightbox.init();

    if ( config.addEnd ) {
        if ( typeof config.addEnd === 'string' ) {
            jQuery.globalEval( config.addEnd, { nonce: config.nonce } );
        } else if ( Array.isArray( config.addEnd ) ) {
            config.addEnd.forEach( ( str ) => {
                jQuery.globalEval( str, { nonce: config.nonce } );
            });
        }
    }
}