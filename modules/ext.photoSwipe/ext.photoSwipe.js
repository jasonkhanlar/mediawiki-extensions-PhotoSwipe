$(document).ready(function() {
  if ($('head > script[type="module"]').length === 0) {
    $('head').append(`<link rel='stylesheet' href='https://cdn.jsdelivr.net/gh/dimsemenov/PhotoSwipe@master/dist/photoswipe.css'/>`);
    let html = `
	  <script type='module'>
	    document.querySelectorAll('table.gallery img').forEach((e,i) => {
	      if (e.parentElement.tagName !== 'A') {
	        document.querySelectorAll('img')[i].outerHTML = \`<a class='img' href='\${e.src}' data-my-size='\${e.naturalWidth}x\${e.naturalHeight}'>\${e.outerHTML}</a>\`;
	      }
	    });
	    import PhotoSwipeLightbox from 'https:\/\/cdn.jsdelivr.net/gh/dimsemenov/PhotoSwipe@master/dist/photoswipe-lightbox.esm.min.js';
	    import PhotoSwipeDeepZoom from 'https:\/\/cdn.jsdelivr.net/gh/dimsemenov/photoswipe-deep-zoom-plugin@main/photoswipe-deep-zoom-plugin.esm.min.js';
	    const lightbox = new PhotoSwipeLightbox({
	      gallery: '.gallery',
	      children: 'a\.img',
	      thumbSelector: 'a',
	      pswpModule: () => import('https\:\/\/cdn.jsdelivr.net/gh/dimsemenov/PhotoSwipe@master/dist/photoswipe.esm.min.js'),
	      allowPanToNext: false, // prevent swiping to the next slide when image is zoomed
	      allowMouseDrag: true, // display dragging cursor at max zoom level
	      wheelToZoom: true, // enable wheel-based zoom
	      zoom: false // disable default zoom button
	    });
	    lightbox.addFilter('domItemData', (itemData, element, linkEl) => {
	      if (linkEl) {
	        const sizeAttr = linkEl.dataset.mySize;
	        if (sizeAttr) {
	          itemData.src = linkEl.href;
	          itemData.w = Number(sizeAttr.split('x')[0]);
	          itemData.h = Number(sizeAttr.split('x')[1]);
	          itemData.msrc = linkEl.dataset.thumbSrc;
	          itemData.thumbCropped = true;
	        }
	      }
	      return itemData;
	    });
	    const deepZoomPlugin = new PhotoSwipeDeepZoom(lightbox, { tileSize: 256 });
	    lightbox.init();
        console.log('lightbox', lightbox);
	  </script>
    `;
    $('head').append(html);
  }
});