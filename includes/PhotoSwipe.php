<?php
/*
 * Title PhotoSwipe class
 * @ingroup Extensions
 * @author Jason Khanlar
 * @license GPL-3.0-or-later
 * @file
 */

if ( !defined( 'MEDIAWIKI' ) )
	die( 'This is a MediaWiki extension, and must be run from within MediaWiki.' );

use MediaWiki\MediaWikiServices;

class PhotoSwipe {
	/**
	 * Uses the Resource Loader to add the javacript and css files.
	 * 
	 * @param Page $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public static function useWay (&$out, $skin, $config) {
		$paths = self::getPaths( $config );

		//if ($config[ 'method' ] === 'recommended') {
		// https://photoswipe.com/getting-started/#initialization
		// JSON cannot contain functions, recreate function
		if (array_key_exists( 'pswpModule', $config[ 'options' ] )) {
			$config[ 'options' ][ 'pswpModule' ] = "%pswpModule%";
		}
		$options = json_encode($config['options']);
		if (strpos( $options, "\"pswpModule\":\"%pswpModule%\"" ) !== false) {
			if ($config[ 'method' ] === 'withoutdynamicimport') {
				$options = str_replace( "\"%pswpModule%\"", "PhotoSwipe", $options );
			} else {
				$options = str_replace( "\"%pswpModule%\"", "() => import('" . $paths[ 'PhotoSwipe' ] . "photoswipe.esm.min.js')", $options );
			}
		}

		$script = "<script type='module'>\n";
		if ($config[ 'method' ] !== 'withoutlightbox') {
			// https://photoswipe.com/data-sources/#without-lightbox-module
			$script .= "import PhotoSwipeLightbox from '${paths[ 'PhotoSwipe' ]}/photoswipe-lightbox.esm.min.js';\n";
		}
		if ($config[ 'method' ] === 'withoutdynamicimport') {
			// https://photoswipe.com/getting-started/#without-dynamic-import
			$script .= "import PhotoSwipe from '${paths[ 'PhotoSwipe' ]}/photoswipe.esm.min.js';\n";
		}
		if (in_array( 'DeepZoomPlugin', $config[ 'plugins' ] ) || array_key_exists( 'DeepZoomPlugin', $config[ 'plugins' ] )) {
			// https://github.com/dimsemenov/photoswipe-deep-zoom-plugin
			$script .= "import PhotoSwipeDeepZoom from '${paths[ 'PhotoSwipeDeepZoomPlugin' ]}/photoswipe-deep-zoom-plugin.esm.min.js';\n";
		}
		if (in_array( 'DynamicCaption', $config[ 'plugins' ] ) || array_key_exists( 'DynamicCaption', $config[ 'plugins' ] )) {
			// https://github.com/dimsemenov/photoswipe-dynamic-caption-plugin
			$script .= "import PhotoSwipeDynamicCaption from '${paths[ 'PhotoSwipeDynamicCaption' ]}/photoswipe-dynamic-caption-plugin.esm.js';\n";
		}
		if (in_array( 'VideoPlugin', $config[ 'plugins' ] ) || array_key_exists( 'VideoPlugin', $config[ 'plugins' ] )) {
			// https://github.com/dimsemenov/photoswipe-video-plugin
			$script .= "import PhotoSwipeVideoPlugin from '${paths[ 'PhotoSwipeVideoPlugin' ]}/photoswipe-video-plugin.esm.min.js';\n";
		}

		if (array_key_exists( 'other_beginning', $config )) {
			if (gettype( $config[ 'other_beginning' ] ) === 'array') {
				$config[ 'other_beginning' ] = implode( "\n", $config[ 'other_beginning' ] );
			}
			$script .= $config[ 'other_beginning' ] . "\n";
		}

		if ($config[ 'method' ] !== 'withoutlightbox') {
			$script .= "const lightbox = new PhotoSwipeLightbox( $options );\n";
		} else {
			// https://photoswipe.com/data-sources/#without-lightbox-module
			// Use $config[ 'other_eventables' ] or $config[ 'other_end' ]
		}

		// Eventables - Add custom defined JS
		// https://photoswipe.com/opening-or-closing-transition/#transition-duration-and-easing
		// https://photoswipe.com/opening-or-closing-transition/#hiding-elements-that-overlap-thumbnails
		// https://photoswipe.com/adding-ui-elements/
		if (array_key_exists( 'other_eventables', $config )) {
			if (gettype( $config[ 'other_eventables' ] ) === 'array') {
				$config[ 'other_eventables' ] = implode( "\n", $config[ 'other_eventables' ] );
			}
			$script .= $config[ 'other_eventables' ] . "\n";
		}

		if (in_array( 'DeepZoomPlugin', $config[ 'plugins' ] ) || array_key_exists( 'DeepZoomPlugin', $config[ 'plugins' ] )) {
			if (gettype( $config[ 'plugins' ][ 'DeepZoomPlugin' ] ) == 'array' && gettype( $config[ 'plugins' ][ 'DeepZoomPlugin' ][ 'options' ] ) == 'array') {
				$options = json_encode($config[ 'plugins' ][ 'DeepZoomPlugin' ][ 'options' ]);
				$script .= "const deepZoomPlugin = new PhotoSwipeDeepZoom(lightbox, $options);\n";
			} else {
				$script .= "const deepZoomPlugin = new PhotoSwipeDeepZoom(lightbox, {});\n";
			}
		}

		if (in_array( 'DynamicCaption', $config[ 'plugins' ] ) || array_key_exists( 'DynamicCaption', $config[ 'plugins' ] )) {
			if (gettype( $config[ 'plugins' ][ 'DynamicCaption' ] ) == 'array' && gettype( $config[ 'plugins' ][ 'DynamicCaption' ][ 'options' ] ) == 'array') {
				$options = json_encode($config[ 'plugins' ][ 'DynamicCaption' ][ 'options' ]);
				$script .= "const captionPlugin = new PhotoSwipeDynamicCaption(lightbox, $options);\n";
			} else {
				$script .= "const captionPlugin = new PhotoSwipeDynamicCaption(lightbox, {});\n";
			}
		}

		if (in_array( 'VideoPlugin', $config[ 'plugins' ] ) || array_key_exists( 'VideoPlugin', $config[ 'plugins' ] )) {
			if (gettype( $config[ 'plugins' ][ 'VideoPlugin' ] ) == 'array' && gettype( $config[ 'plugins' ][ 'VideoPlugin' ][ 'options' ] ) == 'array') {
				$options = json_encode($config[ 'plugins' ][ 'VideoPlugin' ][ 'options' ]);
				$script .= "const videoPlugin = new PhotoSwipeVideoPlugin(lightbox, $options);\n";
			} else {
				$script .= "const videoPlugin = new PhotoSwipeVideoPlugin(lightbox, {});\n";
			}
		}

		$script .= "lightbox.init();\n";

		if (array_key_exists( 'other_end', $config )) {
			if (gettype( $config[ 'other_end' ] ) === 'array') {
				$config[ 'other_end' ] = implode( "\n", $config[ 'other_end' ] );
			}
			$script .= $config[ 'other_end' ] . "\n";
		}

		$script .= "</script>\n";

		$out->addScript( $script );
		$out->addStyle( $paths[ 'PhotoSwipe' ] . 'photoswipe.css' );
		if (in_array( 'DynamicCaption', $config[ 'plugins' ] ) || array_key_exists( 'DynamicCaption', $config[ 'plugins' ] )) {
			$out->addStyle( $paths[ 'PhotoSwipeDynamicCaption' ] . 'photoswipe-dynamic-caption-plugin.css' );
		}
		//}
	}

	public static function AddResources (&$out, $skin) {
		$out->addModules( 'js.photoswipe' );
		$out->addModules( 'js.photoswipe-lightbox' );
		$out->addModules( 'js.photoswipe-deep-zoom-plugin' );
		$out->addModules( 'js.photoswipe-dynamic-caption-plugin' );
		$out->addModules( 'js.photoswipe-video-plugin' );
		$out->addModules( 'ext.photoSwipe' );
/*
		$config = self::getConfigValue( 'PhotoSwipeConfig' );
		$paths = self::getPaths( $config );
		//$out->addModules( 'ext.photoSwipe' );
		//$out->addScriptFile( $paths[ 'PhotoSwipe' ] . 'photoswipe.esm.min.js' );
		//$out->addScriptFile( $paths[ 'PhotoSwipe' ] . 'photoswipe-lightbox.esm.min.js' );
		self::useWay( $out, $skin, $config );
		$out->addScript( '<script src="' . $paths[ 'PhotoSwipe' ] . 'photoswipe.esm.min.js' . '" type="module">' );
		$out->addScript( '<script src="' . $paths[ 'PhotoSwipe' ] . 'photoswipe-lightbox.esm.min.js' . '" type="module">' );
		$out->addStyle( $paths[ 'PhotoSwipe' ] . 'photoswipe.css' );
        */
	}

	/**
	 * Adds parser hooks.
	 * 
	 * @global HighlideGallery $hg
	 * @param Parser $parser
	 * @return boolean
	 */
	public static function AddHooks (&$parser) {
	}

	/**
	* The WikiSEO Config object
	*
	* @var Config
	*/
	private static $config;

	/**
	* Loads a config value for a given key from the main config
	* Returns null if config key does not exist
	*
	* @param string $key The config key
	*
	* @return mixed|null
	*/
	protected function getConfigValue( string $key ) {
		if ( !self::$config ) {
			self::$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'PhotoSwipe' );
		}

		try {
			$value = self::$config->get( $key );
		} catch ( ConfigException $e ) {
			wfLogWarning( sprintf( 'Could not get config for "$wg%s". %s', $key, $e->getMessage() ) );
			$value = null;
		}

		return $value;
	}

	protected function getPaths( $config ) {
		// Warning: Currently no checks if vendor provides resources
		$vendorList = self::getConfigValue( 'PhotoSwipeVendorList' );
		$paths = array();
		$paths[ 'PhotoSwipe' ] = $vendorList[ $config[ 'vendor' ] ][ $config[ 'version' ] ][ 'PhotoSwipe' ];
		$paths[ 'PhotoSwipeDeepZoomPlugin' ] = $vendorList[ $config[ 'vendor' ] ][ $config[ 'version' ]][ 'PhotoSwipeDeepZoomPlugin' ];
		$paths[ 'PhotoSwipeDynamicCaption' ] = $vendorList[ $config[ 'vendor' ] ][ $config[ 'version' ]][ 'PhotoSwipeDynamicCaption' ];
		$paths[ 'PhotoSwipeVideoPlugin' ] = $vendorList[ $config[ 'vendor' ] ][ $config[ 'version' ]][ 'PhotoSwipeVideoPlugin' ];
		return $paths;
	}
}