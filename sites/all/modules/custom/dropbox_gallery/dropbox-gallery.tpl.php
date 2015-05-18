<?php
/**
 * Available variables:
 *
 * $images array()
 *   Keys of the array are the derivative names. The values are arrays of URLs
 *   of the images in the same order as other derivative arrays.
 *
 * $meta array()
 *   Similar to $images, but with image metadata. At a minimum, this will
 *   contain an array with width and height keys.
 *
 * $derivatives array()
 *   Array of derivative names, which are also keys to $images
 *
 * $gallery_name string
 *   The name of the gallery.
 */
?>
<h1><?php print $gallery_name; ?></h1>
<div class="dropbox-gallery" itemscope itemtype="http://schema.org/ImageGallery">
  <?php foreach($images['thumbnails'] as $index => $url) : ?>
    <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
      <a href="<?php print $images['full'][$index]; ?>" itemprop="contentUrl" data-size="<?php print $meta['full'][$index]['width']; ?>x<?php print $meta['full'][$index]['height']; ?>">
        <img itemprop="thumbnail" src="<?php print $url; ?>" />
      </a>
    </figure>
    <figcaption itemprop="caption description"><?php // @TODO ?></figcaption>
  <?php endforeach; ?>
</div>

<?php // PhotoSwipe overlay. ?>
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="pswp__bg"></div>
  <div class="pswp__scroll-wrap">
    <div class="pswp__container">
      <div class="pswp__item"></div>
      <div class="pswp__item"></div>
      <div class="pswp__item"></div>
    </div>
    <div class="pswp__ui pswp__ui--hidden">
      <div class="pswp__top-bar">
        <div class="pswp__counter"></div>
        <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
        <button class="pswp__button pswp__button--share" title="Share"></button>
        <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
        <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
        <div class="pswp__preloader">
          <div class="pswp__preloader__icn">
            <div class="pswp__preloader__cut">
              <div class="pswp__preloader__donut"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
        <div class="pswp__share-tooltip"></div>
      </div>
      <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
      </button>
      <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
      </button>
      <div class="pswp__caption">
        <div class="pswp__caption__center"></div>
      </div>
    </div>
  </div>
</div>
