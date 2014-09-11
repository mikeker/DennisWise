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
      Dennis.photos.init();
      $('.photos-listing .view-content li').once(function() {
        Dennis.photos.addPhoto($(this).text().trim());
      });
      Dennis.photos.preload();
      Dennis.photos.show();
    }
  };

  ///////////////////////////////////
  // Defines slideshow/image tools //
  ///////////////////////////////////

  /**
   * Initializes this object, sets up various HTML elements needed for the
   * slideshow.
   */
  Dennis.photos.init = function() {
    if ('undefined' != typeof this.init.completed) {
      // Already called init(). Just a warning, so don't throw an exception.
      console.log("WARNING: Dennis.photos.init() called more than once.");
      return;
    }

    // URLs of images in this slideshow.
    this.list = [];

    // Index of the current image.
    this.curr = 0;

    // Array of Image objects, in the same order as this.list, available after
    // the images have been preloaded into the browser cache.
    this.preloaded = {};

    // This the the HTML element that we're putting the images in
    this.$display = $('.dennis-two-col .fluid-column');

    // Add a "Loading..." message to the photo area.
    this.$loading = $('<div class="photos-loading">Loading...</div>').prependTo(this.$display);

    // Add forward/back click areas
    $('<div class="photos-forward"></div><div class="photos-backward"></div>').appendTo(this.$display);
    $('.photos-forward', this.$display).click(function() {
      this.next();
      this.show();
    });
    $('.photos-backward', this.$display).click(function() {
      this.prev();
      this.show();
    });

    this.init.completed = true;
  };

  /**
   * Adds an image to the slideshow
   */
  Dennis.photos.addPhoto = function(photoUrl) {
    this.list.push(photoUrl.trim());
  };

  /**
   * Set the current image as the background of the "fluid" layout region.
   */
  Dennis.photos.show = function() {
    this.$display.css('background-image', 'none');

    // Wait until the image has loaded.
    if (!this.preloaded[this.curr].loaded) {
      this.showLoadingMessage();
      var self = this;
      window.setTimeout(function() { self.show(); }, 100);
      return;
    }

    this.hideLoadingMessage();
    var size = this.getImageSize();
    this.$display.css({
      backgroundImage: 'url(' + this.list[this.curr] + ')',
      width: size.width,
      height: size.height,
    });
  };

  /**
   * Returns the width/height of the image specified by imageIndex after
   * "background-size: contain" has resized it.
   *
   * @param  {int} imageIndex
   *   Which image to check, defaults to this.curr
   *
   * @return {object}
   *   Object with width and height members
   */
  Dennis.photos.getImageSize = function(imageIndex) {
    if ('undefined' == typeof imageIndex) {
      imageIndex = this.curr;
    }

    var image = this.preloaded[imageIndex];
    var displayWidth = $('.dennis-two-col .fluid-column').width();
    var displayHeight = $('.dennis-two-col .fluid-column').height();

    return {
      width: Math.min(displayWidth, displayHeight * image.aspectRatio),
      height: Math.min(displayHeight, displayWidth / image.aspectRatio)
    };
  };

  /**
   * Shows and hides the "Loading..." message.
   */
  Dennis.photos.showLoadingMessage = function() {
    this.$loading.show(100);
  };
  Dennis.photos.hideLoadingMessage = function() {
    this.$loading.hide(350);
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
    if ('undefined' != typeof this.preload.completed) {
      // Already did this...
      console.log("WARNING: Dennis.photos.preload called more than once.");
      return;
    }

    // Closure to access "this" in the image.onload function.
    var self = this;

    // Note: we want to load from the begining to the end so we don't use the
    // ever-so-slightly-more optimized decrement loop. Besides, this is only
    // a dozen or two items...
    for (var i = 0; i < this.list.length; i++) {
      var image = new Image();

      // We want to hide the "Loading..." message when the initial image has
      // loaded. We can't use a closure since the onload function fires after
      // the loop has finished and we can't pass a parameter to the anonymous
      // function as it would call the function then, rather than after the
      // image has loaded. So, we tack on an attribute to the image object.
      // if (0 == i) {
      //   image.closeLoadingMessage = true;
      // }

      image.onload = function() {
        // if (this.closeLoadingMessage) {
        //   self.$loading.fadeOut(350);
        // }

        // Store aspect ratio for easy access later.
        this.aspectRatio = this.width / this.height;
        this.loaded = true;
      }

      // Load image from the server. Since it's not part of the browser DOM, it
      // doesn't show on the screen. But it IS cached in the browser cache for
      // quick display when it is used in the DOM or CSS.
      image.src = this.list[i];

      // Keep in image object in scope so that it continues loading after this
      // function has exited.
      this.preloaded[i] = image;
    }

    this.preloaded.completed = true;
  };

}) (jQuery);

