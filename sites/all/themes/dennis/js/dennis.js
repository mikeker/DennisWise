/**
 * @file
 * dennis.js
 *
 * Provides site-wide JS for DennisWise.com.
 */
var Dennis = Dennis || { photos: {} };
(function ($) {
  Drupal.behaviors.dennis = {
    attach: function(context) {
      $('.photos-listing .view-content li').once(function() {
        if ('undefined' == typeof Dennis.photos.list) {
          Dennis.photos.list = [];
          Dennis.photos.curr = 0;
        }
        Dennis.photos.list.push($(this).text().trim());
      });
      Dennis.photos.preload();
      Dennis.photos.init();
      Dennis.photos.show();
    }
  };

  ///////////////////////////////////
  // Defines slideshow/image tools //
  ///////////////////////////////////

  /**
   * Sets up various HTML elements for navigating the slideshow.
   */
  Dennis.photos.init = function() {};

  /**
   * Set the current image as the background of the "fluid" layout region.
   */
  Dennis.photos.show = function() {
    $('.dennis-two-col .fluid-column')
      .css('background-image', 'url(' + this.list[this.curr] + ')');
  };

  /**
   * Increments the current index to the next photo (with looping).
   */
  Dennis.photos.next = function() {};

  /**
   * Decrements the current index to the previous photo (with looping).
   */
  Dennis.photos.prev = function() {};

  /**
   * Replaces the main image area with thumbnails.
   */
  Dennis.photos.showThumbs = function() {};

  /**
   * Lazy-loads images in this slideshow.
   */
  Dennis.photos.preload = function() {
    if ('undefined' != typeof this.preloaded) {
      // Already did this...
      return;
    }

    // Keep image objects in scope after we've exited this function.
    this.preloaded = [];

    // Note: we want to load from the begining to the end so we don't use the
    // ever-so-slightly-more optimized decrement loop. Besides, this is only
    // a dozen or two items...
    for (var i = 0; i < this.list.length; i++) {
      this.preloaded[i] = new Image();
      if (0 == i) {
        // Remove the "Loading..." text when we've pulled in the first image.
        this.preloaded[i].onload = function() {
          $('.photos-loading').fadeOut(350);
        };
      }
      this.preloaded[i].src = this.list[i];
    };
  };

}) (jQuery);
