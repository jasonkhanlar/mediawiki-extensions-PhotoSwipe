#!/bin/bash
outdir=modules/ext.photoSwipe/lib
# NPM package for `photoswipe-video-plugin` does not exist yet. Source directly from GitHub
for url in \
  https://raw.githubusercontent.com/dimsemenov/PhotoSwipe/master/dist/photoswipe.css \
  https://raw.githubusercontent.com/dimsemenov/PhotoSwipe/master/dist/photoswipe.esm.js \
  https://raw.githubusercontent.com/dimsemenov/PhotoSwipe/master/dist/photoswipe.esm.js.map \
  https://raw.githubusercontent.com/dimsemenov/PhotoSwipe/master/dist/photoswipe.esm.min.js \
  https://raw.githubusercontent.com/dimsemenov/PhotoSwipe/master/dist/photoswipe-lightbox.esm.js \
  https://raw.githubusercontent.com/dimsemenov/PhotoSwipe/master/dist/photoswipe-lightbox.esm.js.map \
  https://raw.githubusercontent.com/dimsemenov/PhotoSwipe/master/dist/photoswipe-lightbox.esm.min.js \
  https://raw.githubusercontent.com/dimsemenov/photoswipe-deep-zoom-plugin/main/photoswipe-deep-zoom-plugin.esm.js \
  https://raw.githubusercontent.com/dimsemenov/photoswipe-deep-zoom-plugin/main/photoswipe-deep-zoom-plugin.esm.min.js \
  https://raw.githubusercontent.com/dimsemenov/photoswipe-dynamic-caption-plugin/main/photoswipe-dynamic-caption-plugin.css \
  https://raw.githubusercontent.com/dimsemenov/photoswipe-dynamic-caption-plugin/main/photoswipe-dynamic-caption-plugin.esm.js \
  https://raw.githubusercontent.com/dimsemenov/photoswipe-video-plugin/main/dist/photoswipe-video-plugin.esm.js \
  https://raw.githubusercontent.com/dimsemenov/photoswipe-video-plugin/main/dist/photoswipe-video-plugin.esm.min.js \
;do \
  outfile=$(echo $url|sed "s|^.*/\([^/]*\)$|\1|"); \
  curl -o $outdir/$outfile "$url"; \
  outfile=$(echo $outfile|sed "s|\.esm\.|.cjs.|"); \
  if [[ $url =~ \.js$ ]];then \
    curl "$url" | esbuild --target=es2015 --format=cjs --bundle --outfile=$outdir/$outfile; \
  fi \
done
