(function($){
  Drupal.behaviors.dropbox_gallery = {
    attach: function(context) {
      var images = [];
      $('.dropbox-gallery').once('dropbox_gallery', function() {
        $(this).find('figure > a').each(function() {
          var $this = $(this);
          var size = $this.data('size').split('x');
          images.push({
            src: $this.attr('href'),
            w: size[0],
            h: size[1]
          });
        });
      });

      // Initiate PhotoSwipe gallery.
      // @TODO:
      var options = [];

      var gallery = new PhotoSwipe($('.pswp')[0], PhotoSwipeUI_Default, images, options);
      gallery.init();
    }
  }
})(jQuery);
