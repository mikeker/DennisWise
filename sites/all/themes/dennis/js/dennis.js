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
      Dennis.photos.show();
    }
  };

  ///////////////////////////////////
  // Defines slideshow/image tools //
  ///////////////////////////////////

  /**
   * Set the current image as the background of the "fluid" layout region.
   */
  Dennis.photos.show = function() {};

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

}) (jQuery);
