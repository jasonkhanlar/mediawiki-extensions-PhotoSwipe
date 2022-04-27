<?php
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

/*
 * Title PhotoSwipe class
 * @ingroup Extensions
 * @author Jason Khanlar
 * @license GPL-3.0-or-later
 * @file
 */

if ( !defined( 'MEDIAWIKI' ) )
	die( 'This is a MediaWiki extension, and must be run from within MediaWiki.' );

//namespace PhotoSwipe;

use Ahc\Json\Comment;
use MediaWiki\MediaWikiServices;
use Respect\Validation\Validator as v;
use Seld\JsonLint\JsonParser;

class PhotoSwipe {
	/**
	 * @var config The PhotoSwipe extension.json configuration
	 */
	private static $config = [];

	/**
	 * Loads a config value for a given key from the main extension.json config
	 * Returns null if config key does not exist
	 * @link https://mediawiki.org/wiki/Manual:Configuration_for_developers
	 *
	 * @param string $key The name of the key
	 *
	 * @return mixed|null The value of the key
	 */
	protected function getConfigValue( string $key ) {
		if ( !self::$config ) {
			self::$config = MediaWikiServices::getInstance()->getConfigFactory()
				->makeConfig( 'PhotoSwipe' );
		}

		try {
			$value = self::$config->get( $key );
		} catch ( ConfigException $e ) {
			wfLogWarning( sprintf( 'Could not get config for "$wg%s". %s', $key, $e->getMessage() ) );
			$value = null;
		}

		return $value;
	}

	/**
	 * Returns true or false depending on if plugin is enabled
	 *
	 * @param string &$resource The configuration resource (extension, attribute, or content)
	 * @param string &$key The name of the key
	 * @param mixed &$value The value of the key
	 * @return boolean
	 */
	protected function isValidConfig( string &$resource, string &$key, &$value ) {
		/*
		 * MediaWiki evaluates objects in extension.json as associative arrays.
		 * Evaluate the same way for tag attributes and content
		 * See https://github.com/wikimedia/mediawiki/blob/master/includes/registration/ExtensionRegistry.php#L372
		 */

		$validKeys = array_map( 'strtolower', array(
			'mode',
			'options',
			'addBeginning',
			'addEventables',
			'addEnd',
			'plugins'
		) );
		if ( !v::in( $validKeys )->validate( $key ) ) {
			// https://mediawiki.org/wiki/Manual:Messages_API#Using_messages_in_PHP
			return wfMessage( 'photoswipe-invalid-config-key' )
				->params( $resource, htmlspecialchars( json_encode( $key ) ) )
				->parse();
		}

		unset( $validArrayValues, $validValueType );
		if ( $key === 'mode' ) {
			$validArrayValues = array_map( 'strtolower', array(
				'recommended', // https://photoswipe.com/getting-started/#initialization
				'withoutDynamicImport', // https://photoswipe.com/getting-started/#without-dynamic-import
				'withoutLightbox' // https://photoswipe.com/data-sources/#without-lightbox-module
			) );
		} else if ( $key === 'options' ) {
			$validValueType = 'isArray';
		} else if ( $key === 'addbeginning' ) {
			$validValueType = 'isStringorArrayofStrings';
		} else if ( $key === 'addeventables' ) {
			$validValueType = 'isStringorArrayofStrings';
		} else if ( $key === 'addend' ) {
			$validValueType = 'isStringorArrayofStrings';
		} else if ( $key === 'plugins' ) {
			$validValueType = 'isArray';
		}

		// Tag arguments are always strings. Deserialize JSON.
		// https://respect-validation.readthedocs.io/en/latest/rules/Json/
		if ( v::json()->validate( $value ) ) {
			$value = json_decode( $value, /* $assoc = */ true );
		}

		$valid = true;
		if ( $validArrayValues ) {
			// https://respect-validation.readthedocs.io/en/latest/rules/StringType/
			if ( !v::stringType()->validate( $value ) ) {
				$valid = false;
			}

			// https://respect-validation.readthedocs.io/en/latest/rules/In/
			if ( !v::in( $validArrayValues )->validate( strtolower( $value ) ) ) {
				$valid = false;
			}
		} else if ( $validValueType === 'isArray' ) {
			// https://respect-validation.readthedocs.io/en/latest/rules/ArrayType/
			if ( !v::arrayType()->validate( $value, ) ) {
				$valid = false;
			}
		} else if ( $validValueType === 'isString' ) {
			if ( !v::stringType()->validate( $value ) ) {
				$valid = false;
			}
		} else if ( $validValueType === 'isStringorArrayofStrings' ) {
			// https://respect-validation.readthedocs.io/en/latest/rules/AnyOf/
			if ( !v::anyOf( v::arrayType(), v::stringType() )->validate( $value ) ) {
				$valid = false;
			}

			if ( v::arrayType()->validate( $value, ) ) {
				foreach ( json_decode( $value, /* $assoc = */ true ) as $k => $v ) {
					if ( !v::stringType()->validate( $v ) ) {
						$valid = false;
					}
				}
			}
		}

		if ( !$valid ) {
			return wfMessage( 'photoswipe-invalid-config-keyvalue' )
				->params(
					$resource,
					htmlspecialchars( json_encode( $key ) ),
					htmlspecialchars( json_encode( $value ) )
				)
				->text();
		}
		return true;
	}

	/**
	 * Register the <photoswipe> tag with the Parser.
	 * @link https://mediawiki.org/wiki/Manual:Tag_extensions
	 * @link https://mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 * @link https://doc.wikimedia.org/mediawiki-core/master/php/interfaceMediaWiki_1_1Hook_1_1ParserFirstCallInitHook.html
	 *
	 * @param Parser &$parser
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'photoswipe', [ self::class, 'renderTagPhotoSwipe' ] );
	}

	/**
	 * Callback for onParserFirstCallInit
	 *
	 * @param string|null $input User-supplied input (null for self-closing tag)
	 * @param array &$args Tag arguments, if any
	 * @param Parser &$parser
	 * @param PPFrame &$frame
	 * @return string HTML
	 */
	public static function renderTagPhotoSwipe( &$input, array &$args, Parser &$parser, PPFrame &$frame ) {
		$extension = self::getConfigValue( 'PhotoSwipeConfig' );
		$errors = array();

		$jsConfigVars = array( 'wgPhotoSwipeConfig' => array() );

		// Strip single and multi-line comments, strip trailing commas, enable multiline strings
		if ( $input ) {
			$input = ( new Comment )->strip( $input );
		}

		foreach ( array( 'validate', 'parse' ) as $process ) {
			foreach ( array( 'extension', 'attribute', 'content' ) as $resource ) {
				if ( $resource === 'extension' ) {
					$configsrc = 'extension';
				} else if ( $resource === 'attribute' ) {
					$configsrc = 'args';
				} else if ( $resource === 'content' ) {
					$configsrc = 'input';
				}

				// 'content' resource initializes as string. Deserialize if valid JSON.
				if ( v::stringType()->validate( $$configsrc ) ) {
					$$configsrc = str_replace( "\n", "", $$configsrc );
					$$configsrc = str_replace( "\t", "", $$configsrc );
					if ( v::json()->validate( $$configsrc ) ) {
						$$configsrc = json_decode( $$configsrc, /* $assoc = */ true );
					}
				}

				// First check for errors (extension config and page config)
				if ( $process === 'validate' ) {
					// Check if 'content' resource JSON deserialization failed.
					if ( v::allOf( v::stringType(), v::not( v::json() ) )->validate( $$configsrc ) ) {
						$jsonparser = new JsonParser();
						$jsonerror = $jsonparser->lint( $$configsrc )->getMessage();
						$jsonerror = trim( str_replace( 'Parse error on line 1:', '', $jsonerror ) );
						$jsonerror = '<blockquote><code>'
							. str_replace( "\n", '<br>', $jsonerror )
							. '</code></blockquote>';

						array_push(
							$errors,
							'* ' . wfMessage( 'photoswipe-invalid-config-json' )
								->params( $resource, $jsonerror )->parse()
						);
					}

					else if ( v::arrayType()->validate( $$configsrc ) && count( $$configsrc ) > 0 ) {
						if ( v::arrayType()->validate( $$configsrc ) ) {
							foreach ( $$configsrc as $key => &$value ) {
								$isValid = self::isValidConfig( $resource, strtolower( $key ), $value );
								if ( $isValid !== true ) {
									array_push( $errors, '* ' . $isValid );
								}
							}
						}
					}
				} else if ( $process === 'parse' ) {
					if ( v::arrayType()->validate( $$configsrc ) && count( $$configsrc ) > 0 ) {
						//$ccKeyNames = array_map( 'strtolower', array(
						$ccKeyNames = array(
							'mode',
							'options',
							'addBeginning',
							'addEventables',
							'addEnd',
							'plugins'
						);
						foreach ( $$configsrc as $key => &$value ) {
							// Force camelCase
							$ccKeyName = $ccKeyNames[ array_search(
								strtolower( $key ),
								array_map( 'strtolower', $ccKeyNames )
							) ];
							/*
							$GLOBALS[ 'wgPhotoSwipeConfig' ][ $ccKeyName ] = $value;
							if (
								v::in( array_map( 'strtolower', $ccKeyNames ) )
									->validate( strtolower( $key ) )
								&& v::not( v::in( $ccKeyNames ) )
									->validate( $key )
							) {
								if ( array_key_exists( $key, $GLOBALS[ 'wgPhotoSwipeConfig' ] ) ) {
									unset( $GLOBALS[ 'wgPhotoSwipeConfig' ][ $key ] );
								}
							}
							*/
							// Instead use a nonglobal variable for combining configurations
							// (extension, tag arguments, tag content)
							$jsConfigVars[ 'wgPhotoSwipeConfig' ][ $ccKeyName ] = $value;
						}
					}
				}
			}

			// Return all errors before processing
			if ( $process === 'validate' ) {
				if ( count( $errors ) > 0 ) {
					return wfMessage( 'photoswipe-invalid-config-all' )
						->plaintextParams( implode( "\n", $errors ) )
						->text();
				}
			}
		}

		$out = $parser->getOutput();
		foreach ( $jsConfigVars[ 'wgPhotoSwipeConfig' ] as $key => &$value ) {
			if ( strtolower( $key ) === 'mode' ) {
				if ( $value !== 'withoutlightbox' ) {
					// https://photoswipe.com/data-sources/#without-lightbox-module
					$out->addModules( 'js.photoswipe-lightbox' );
				}
				// This doesn't work well server-side, handle client-side
				//if ( $value === 'withoutdynamicimport' ) {
				$out->addModules( 'js.photoswipe' );
				//}
			//} else if ( strtolower( $key ) === 'options' ) {
			//} else if ( strtolower( $key ) === 'addbeginning' ) {
			//} else if ( strtolower( $key ) === 'addeventables' ) {
			//} else if ( strtolower( $key ) === 'addend' ) {
			} else if ( strtolower( $key ) === 'plugins' ) {
				foreach ( $value as $k => &$v ) {
					if ( v::stringType()->validate( $v ) ) {
						if ( strtolower( $v ) === strtolower( 'DeepZoomPlugin' ) ) {
							$out->addModules( 'js.photoswipe-deep-zoom-plugin' );
							// dependency, ensure this module is loaded even if misconfigured
							$out->addModules( 'js.photoswipe-lightbox' );
						} else if ( strtolower( $v ) === strtolower( 'DynamicCaption' ) ) {
							$out->addModules( 'js.photoswipe-dynamic-caption-plugin' );
							// dependency, ensure this module is loaded even if misconfigured
							$out->addModules( 'js.photoswipe-lightbox' );
						} else if ( strtolower( $v ) === strtolower( 'VideoPlugin' ) ) {
							$out->addModules( 'js.photoswipe-video-plugin' );
							// dependency, ensure this module is loaded even if misconfigured
							$out->addModules( 'js.photoswipe-lightbox' );
						}
					} else if ( v::arrayType()->validate( $v ) ) {
						// Enable by default if 'enabled' key isn't specified
						if ( v::anyOf(
							v::key( 'enabled', v::equals( true ) ),
							v::not( v::key( 'enabled' ) ) )->validate( $v
						) ) {
							if ( strtolower( $k ) === strtolower( 'DeepZoomPlugin' ) ) {
								$out->addModules( 'js.photoswipe-deep-zoom-plugin' );
								// dependency, ensure this module is loaded even if misconfigured
								$out->addModules( 'js.photoswipe-lightbox' );
							} else if ( strtolower( $k ) === strtolower( 'DynamicCaption' ) ) {
								$out->addModules( 'js.photoswipe-dynamic-caption-plugin' );
								// dependency, ensure this module is loaded even if misconfigured
								$out->addModules( 'js.photoswipe-lightbox' );
							} else if ( strtolower( $k ) === strtolower( 'VideoPlugin' ) ) {
								$out->addModules( 'js.photoswipe-video-plugin' );
								// dependency, ensure this module is loaded even if misconfigured
								$out->addModules( 'js.photoswipe-lightbox' );
							}
						}
					}
				}
			}
		}

		global $wgOut; // OutputPage
		$jsConfigVars[ 'wgPhotoSwipeConfig' ][ 'nonce' ] = $wgOut->getCSPNonce();
		$out->addJsConfigVars( $jsConfigVars );
		$out->addModules( 'ext.photoSwipe' );
		return '';
	}
}