# mediawiki-extensions-PhotoSwipe
This is a MediaWiki extension that provides a frontend JavaScript image gallery and lightbox using PhotoSwipe

## Installation

- Download and place the file(s) in a directory called <code>PhotoSwipe</code> in your <code>extensions/</code> folder.
- Add the following code at the bottom of your [LocalSettings.php](https://mediawiki.org/wiki/Special:MyLanguage/Manual:LocalSettings.php):

    wfLoadExtension( 'JsonConfig' );

- [Configure as required.](#configuration)
- ✅ Done – Navigate to [Special:Version](https://mediawiki.org/wiki/Special:Version) on your wiki to verify that the extension is successfully installed.


## Configuration

### <code>$wgPhotoSwipeConfig</code>

This variable defines profiles for each type of configuration pages. <code>$wgPhotoSwipeConfig</code></tvar> is an associative array of arrays, with each sub-array having zero or more of the following parameters.
By default, PhotoSwipeConfig uses the string key as the model ID that this profile represents, but in case you want to reuse the same model ID in more than one profile, you can override it with the <code>model</code> parameter.

| parameter        | type              | default       | description |
| ---------------- | ----------------- | ------------- | ---- |
| method           | string            | 'recommended' | Adjusts the usage of PhotoSwipe library. Possible values: ['recommended'](https://photoswipe.com/getting-started/#initialization), ['withoutdynamicimport'](https://photoswipe.com/getting-started/#without-dynamic-import), ['withoutlightbox'](https://photoswipe.com/data-sources/#without-lightbox-module) |
| options          | object            | [library recommendation](https://photoswipe.com/getting-started/#initialization) | The <code>options</code> object passed into the <code>PhotoSwipeLightbox</code> instance. |
| other_beginning  | string            | ''            | Additional JavaScript to add in the beginning. |
|                  | array of strings  | []            | An array of strings of JavaScript to add. |
| other_eventables | string            | ''            | Additional JavaScript to add in the middle. |
|                  | array of strings  | []            | An array of strings of JavaScript to add. |
| other_end        | string            | ''            | Additional JavaScript to add in the end. |
|                  | array of strings  | []            | An array of strings of JavaScript to add. |
| plugins          | array of strings  | []            | An array of strings of names of plugins to enable with default options. See <code>PhotoSwipeVendorList</code>. |
|                  | object of options | {}            | An object of keys of plugins to enable with custom options. The values are the <code>options</code> object passed to the plugin library. |
| vendor           | string            | 'local'       | Source of libraries to use: CDN 'cdnjs', 'jsdelivr', 'unpkg', or 'local'. See <code>PhotoSwipeVendorList</code>. |
| version          | string            | 'latest'      | Version of libraries to use: Specific version release (e.g. 'v5.2.2', or 'latest'. |

## Usage

## See also

### <code>PhotoSwipeVendorList</code>

List of vendors providing PhotoSwipe libraries.

- Content Delivery Networks:

 - 'cdnjs'
 
    - Only PhotoSwipe v5.2.2 is available from this source.
    - Additional plugin libraries are not available.

 - 'jsdelivr'
 
    - All libraries and plugins are available from this source.

 - 'unpkg'
 
    - Only PhotoSwipe, PhotoSwipeDeepZoomPlugin, and PhotoSwipeDynamicCaption are available from this source.
    - [PhotoSwipeVideoPlugin is not yet available](https://github.com/dimsemenov/photoswipe-video-plugin/issues/1#issuecomment-1102087166).

- 'Local'