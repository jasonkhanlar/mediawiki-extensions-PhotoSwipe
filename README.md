# mediawiki-extensions-PhotoSwipe
This is a MediaWiki extension that provides a front-end JavaScript image gallery and lightbox using PhotoSwipe

## Installation

- Download and place the file(s) in a directory called <code>PhotoSwipe</code> in your <code>extensions/</code> folder.
- Add the following code at the bottom of your [LocalSettings.php](https://mediawiki.org/wiki/Special:MyLanguage/Manual:LocalSettings.php):

    <code>wfLoadExtension( 'PhotoSwipe' );</code>

- run <code>./bin/build.sh</code> or <code>npm run build-lib</code> to prepopulate the JS libraries.
- [Configure as required.](#configuration)
- ✅ Done – Navigate to [Special:Version](https://mediawiki.org/wiki/Special:Version) on your wiki to verify that the extension is successfully installed.


## Configuration

### <code>$wgPhotoSwipeConfig</code>

This defines values for each section of configuration. <code>$wgPhotoSwipeConfig</code></tvar> is an associative array of mixed values, with each sub-value having zero or more of the following parameters.

| parameter     | type              | default       | description |
| ------------- | ----------------- | ------------- | ---- |
| mode          | string            | 'recommended' | Adjusts the usage of PhotoSwipe library. Possible values: ['recommended'](https://photoswipe.com/getting-started/#initialization), ['withoutDynamicImport'](https://photoswipe.com/getting-started/#without-dynamic-import), ['withoutLightbox'](https://photoswipe.com/data-sources/#without-lightbox-module) |
| options       | object            | [library recommendation](https://photoswipe.com/getting-started/#initialization) | The <code>options</code> object passed into the <code>PhotoSwipeLightbox</code> instance. |
| addBeginning  | string            | ''            | Additional JavaScript to add in the beginning. |
|               | array of strings  | []            | An array of strings of JavaScript to add. |
| addEventables | string            | ''            | Additional JavaScript to add in the middle. |
|               | array of strings  | []            | An array of strings of JavaScript to add. |
| addEnd        | string            | ''            | Additional JavaScript to add in the end. |
|               | array of strings  | []            | An array of strings of JavaScript to add. |
| plugins       | array of strings  | []            | An array of strings of names of plugins to enable with default options. See <code>PhotoSwipeVendorList</code>. |
|               | object of options | {}            | An object of keys of plugins to enable with custom options. The values are the <code>options</code> object passed to the plugin library. |

For example, in your LocalSettings.php: (note: make sure to \ escape all $ in string values containing JavaScript)

#### all-in-one associative array

    $wgPhotoSwipeConfig = [
    	"mode" => "recommended",
    	"options" => [
    		"gallery" => "table.gallery",
    		"children" => "a.img",
    		"thumbSelector" => "a.img",
    		"pswpModule" => "() => require( 'js.photoswipe' )",
    		// Recommended PhotoSwipe options for this plugin
    		"allowPanToNext" => false, // prevent swiping to the next slide when image is zoomed
    		"allowMouseDrag" => true, // display dragging cursor at max zoom level
    		"wheelToZoom" => true, // enable wheel-based zoom
    		"zoom" => false // disable default zoom button
    	],
    	"addBeginning" => [
    		"document.querySelectorAll( 'table.gallery img' ).forEach( ( e, i ) => {
    			if ( e.parentElement.tagName !== 'A' ) {
    				document.querySelectorAll( 'img' )[ i ].outerHTML = `<a class='img' href='\${e.src}'; data-my-size='\${e.naturalWidth}x\${e.naturalHeight}'>\${e.outerHTML}</a>`;
    			}
    		} );"
    	],
    	"addEventables" => [
    		"const backEasing = {
    			in: 'cubic-bezier(0.6, -0.28, 0.7, 1)',
    			out: 'cubic-bezier(0.3, 0, 0.32, 1.275)',
    			inOut: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'
    		}",
    		"lightbox.on( 'firstUpdate', () => { lightbox.pswp.options.easing = backEasing.out; } );",
    		"lightbox.on( 'initialZoomInEnd', () => { lightbox.pswp.options.easing = backEasing.inOut; } );",
    		"lightbox.on( 'close', () => { lightbox.pswp.options.easing = backEasing.in; } );",
    		"lightbox.addFilter( 'domItemData', ( itemData, element, linkEl ) => {
    			if ( linkEl ) {
    				const sizeAttr = linkEl.dataset.mySize;
    				itemData.src = linkEl.href;
    				itemData.w = Number( sizeAttr.split( 'x' )[ 0 ] );
    				itemData.h = Number( sizeAttr.split( 'x' )[ 1 ] );
    				itemData.msrc = linkEl.dataset.thumbSrc;
    				itemData.thumbCropped = true;
    			}
    			return itemData;
    		} );"
    	],
    	"addEnd" => [],
    	"plugins" => [
    		"DeepZoomPlugin" => [
    			"enabled" => true,
    			"options" => [
    				"tileSize" => 256
    			]
    		],
    		"DynamicCaption" => [
    			"enabled" => true,
    			"options" => [
    				"captionContent" => ".pswp-caption-content",
    				"horizontalEdgeThreshold" => 20,
    				"mobileCaptionOverlapRatio" => 0.3,
    				"mobileLayoutBreakpoint" => 600,
    				"type" => "auto"
    			]
    		],
    		"VideoPlugin" => [
    			"enabled" => true,
    			"options" => []
    		]
    	]
    ];

#### individual key values of associative array

    $wgPhotoSwipeConfig["mode"] = "recommended";
    $wgPhotoSwipeConfig["options"]["gallery"] = "table.gallery";
    $wgPhotoSwipeConfig["options"]["children"] = "a.img";
    $wgPhotoSwipeConfig["options"]["thumbSelector"] = "a.img";
    $wgPhotoSwipeConfig["options"]["pswpModule"] = "() => require( 'js.photoswipe' )";
    // Recommended PhotoSwipe options for this plugin
    $wgPhotoSwipeConfig["options"]["allowPanToNext"] = false; // prevent swiping to the next slide when image is zoomed
    $wgPhotoSwipeConfig["options"]["allowMouseDrag"] = true; // display dragging cursor at max zoom level
    $wgPhotoSwipeConfig["options"]["wheelToZoom"] = true; // enable wheel-based zoom
    $wgPhotoSwipeConfig["options"]["zoom"] = false; // disable default zoom button
    $wgPhotoSwipeConfig["addBeginning"] = [
    	"document.querySelectorAll( 'table.gallery img' ).forEach( ( e, i ) => {
    		if ( e.parentElement.tagName !== 'A' ) {
    			document.querySelectorAll( 'img' )[ i ].outerHTML = `<a class='img' href='\${e.src}'; data-my-size='\${e.naturalWidth}x\${e.naturalHeight}'>\${e.outerHTML}</a>`;
    		}
    	} );"
    ];
    $wgPhotoSwipeConfig["addEventables"] = [
    	"const backEasing = {
    		in: 'cubic-bezier(0.6, -0.28, 0.7, 1)',
    		out: 'cubic-bezier(0.3, 0, 0.32, 1.275)',
    		inOut: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'
    	}",
    	"lightbox.on( 'firstUpdate', () => { lightbox.pswp.options.easing = backEasing.out; } );",
    	"lightbox.on( 'initialZoomInEnd', () => { lightbox.pswp.options.easing = backEasing.inOut; } );",
    	"lightbox.on( 'close', () => { lightbox.pswp.options.easing = backEasing.in; } );",
    	"lightbox.addFilter( 'domItemData', ( itemData, element, linkEl ) => {
    		if ( linkEl ) {
    			const sizeAttr = linkEl.dataset.mySize;
    			itemData.src = linkEl.href;
    			itemData.w = Number( sizeAttr.split( 'x' )[ 0 ] );
    			itemData.h = Number( sizeAttr.split( 'x' )[ 1 ] );
    			itemData.msrc = linkEl.dataset.thumbSrc;
    			itemData.thumbCropped = true;
    		}
    		return itemData;
    	} );"
    ];
    $wgPhotoSwipeConfig["addEnd"] = [];
    $wgPhotoSwipeConfig["plugins"]["DeepZoomPlugin"]["enabled"] = true;
    $wgPhotoSwipeConfig["plugins"]["DeepZoomPlugin"]["options"]["tileSize"] = 256;
    $wgPhotoSwipeConfig["plugins"]["DynamicCaption"]["enabled"] = true;
    $wgPhotoSwipeConfig["plugins"]["DynamicCaption"]["options"]["captionContent"] = ".pswp-caption-content";
    $wgPhotoSwipeConfig["plugins"]["DynamicCaption"]["options"]["horizontalEdgeThreshold"] = 20;
    $wgPhotoSwipeConfig["plugins"]["DynamicCaption"]["options"]["mobileCaptionOverlapRatio"] = 0.3;
    $wgPhotoSwipeConfig["plugins"]["DynamicCaption"]["options"]["mobileLayoutBreakpoint"] = 600;
    $wgPhotoSwipeConfig["plugins"]["DynamicCaption"]["options"]["type"] = "auto";
    $wgPhotoSwipeConfig["plugins"]["VideoPlugin"]["enabled"] = true;
    $wgPhotoSwipeConfig["plugins"]["VideoPlugin"]["options"] = [];

## Usage

Note: Images and gallery content not included.

Use extension configuration by default

<code>&lt;photoswipe/&gt;</code>

Use argument configuration (overrides extension configuration)

    <photoswipe
      mode=recommended
      options="{
        &quot;gallery&quot;: &quot;table.gallery&quot;,
        &quot;children&quot;: &quot;a.img&quot;,
        &quot;thumbSelector&quot;: &quot;a.img&quot;,
        &quot;pswpModule&quot;: &quot;() =&gt; require(&apos;js.photoswipe&apos;)&quot;,
        &quot;allowPanToNext&quot;: false,
        &quot;allowMouseDrag&quot;: true,
        &quot;wheelToZoom&quot;: true,
        &quot;zoom&quot;: false
      }"
      addBeginning="document.querySelectorAll(&apos;table.gallery img&apos;).forEach((e,i) =&gt; {
        if (e.parentElement.tagName !== &apos;A&apos;) {
          document.querySelectorAll(&apos;img&apos;)[i].outerHTML = `&lt;a href=&quot;${e.src}&quot; data-my-size=&quot;${e.naturalWidth}x${e.naturalHeight}&quot;&gt;${e.outerHTML}&lt;/a&gt;`;
        }
      });"
      "addEventables": [
        "const backEasing = { in: &apos;cubic-bezier(0.6, -0.28, 0.7, 1)&apos;, out: &apos;cubic-bezier(0.3, 0, 0.32, 1.275)&apos;, inOut: &apos;cubic-bezier(0.68, -0.55, 0.265, 1.55)&apos; }",
        "lightbox.on( &apos;firstUpdate&apos;, () =&gt; { lightbox.pswp.options.easing = backEasing.out; } );",
        "lightbox.on( &apos;initialZoomInEnd&apos;, () =&gt; { lightbox.pswp.options.easing = backEasing.inOut; } );",
        "lightbox.on( &apos;close&apos;, () =&gt; { lightbox.pswp.options.easing = backEasing.in; } );",
        "lightbox.addFilter( &apos;domItemData&apos;, ( itemData, element, linkEl ) =&gt; { if ( linkEl ) { const sizeAttr = linkEl.dataset.mySize; itemData.src = linkEl.href; itemData.w = Number( sizeAttr.split( &apos;x&apos; )[ 0 ] ); itemData.h = Number( sizeAttr.split( &apos;x&apos; )[ 1 ] ); itemData.msrc = linkEl.dataset.thumbSrc; itemData.thumbCropped = true; } return itemData; } );"
      ],
      plugins="{
        &quot;DeepZoomPlugin&quot;: {
          &quot;enabled&quot;: true,
          &quot;options&quot;: {
            &quot;tileSize&quot;: 256
          }
        },
        &quot;DynamicCaption&quot;: {
          &quot;enabled&quot;: true,
          &quot;options&quot;: {
            &quot;captionContent&quot;: &quot;.pswp-caption-content&quot;,
            &quot;horizontalEdgeThreshold&quot;: 20,
            &quot;mobileCaptionOverlapRatio&quot;: 0.3,
            &quot;mobileLayoutBreakpoint&quot;: 600,
            &quot;type&quot;: &quot;auto&quot;
          }
        },
        &quot;VideoPlugin&quot;: {
          &quot;enabled&quot;: true,
          &quot;options&quot;: {}
        }
      }" />

Use content configuration (overrides extension configuration and argument configuration)

Note: Comments and multi-line strings are permitted here

    <photoswipe>
    {
    	"mode": "recommended",
    	"options": {
    		"gallery": "table.gallery",
    		"children": "a.img",
    		"thumbSelector": "a.img",
    		"pswpModule": "() => require( 'js.photoswipe' )",
    		// Recommended PhotoSwipe options for this plugin
    		"allowPanToNext": false, // prevent swiping to the next slide when image is zoomed
    		"allowMouseDrag": true, // display dragging cursor at max zoom level
    		"wheelToZoom": true, // enable wheel-based zoom
    		"zoom": false // disable default zoom button
    	},
    	"addBeginning": [
    		"document.querySelectorAll( 'table.gallery img' ).forEach( ( e, i ) => {
    			if ( e.parentElement.tagName !== 'A' ) {
    				document.querySelectorAll( 'img' )[ i ].outerHTML = `<a class='img' href='${e.src}'; data-my-size='${e.naturalWidth}x${e.naturalHeight}'>${e.outerHTML}</a>`;
    			}
    		} );"
    	],
    	"addEventables": [
    		"const backEasing = {
    			in: 'cubic-bezier(0.6, -0.28, 0.7, 1)',
    			out: 'cubic-bezier(0.3, 0, 0.32, 1.275)',
    			inOut: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'
    		}",
    		"lightbox.on( 'firstUpdate', () => { lightbox.pswp.options.easing = backEasing.out; } );",
    		"lightbox.on( 'initialZoomInEnd', () => { lightbox.pswp.options.easing = backEasing.inOut; } );",
    		"lightbox.on( 'close', () => { lightbox.pswp.options.easing = backEasing.in; } );",
    		"lightbox.addFilter( 'domItemData', ( itemData, element, linkEl ) => {
    			if ( linkEl ) {
    				const sizeAttr = linkEl.dataset.mySize;
    				itemData.src = linkEl.href;
    				itemData.w = Number( sizeAttr.split( 'x' )[ 0 ] );
    				itemData.h = Number( sizeAttr.split( 'x' )[ 1 ] );
    				itemData.msrc = linkEl.dataset.thumbSrc;
    				itemData.thumbCropped = true;
    			}
    			return itemData;
    		} );"
    	],
    	"addEnd": [],
    	"plugins": {
    		"DeepZoomPlugin": {
    			"enabled": true,
    			"options": {
    				"tileSize": 256
    			}
    		},
    		"DynamicCaption": {
    			"enabled": true,
    			"options": {
    				"captionContent": ".pswp-caption-content",
    				"horizontalEdgeThreshold": 20,
    				"mobileCaptionOverlapRatio": 0.3,
    				"mobileLayoutBreakpoint": 600,
    				"type": "auto"
    			}
    		},
    		"VideoPlugin": {
    			"enabled": true,
    			"options": {}
    		}
    	}
    }
    </photoswipe>

## See also

https://photoswipe.com/getting-started/
