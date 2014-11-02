<?php
/**
 * Display template for an FTP Gallery.
 *
 * $gallery_items contains an array of either images or thumbnail images linked
 * to the larger image.
 */
?>
<div class="ftp_gallery_wrapper">
  <ul class="ftp_gallery">
    <?php
      foreach ($gallery_items as $gallery_item) {
        print "<li>$gallery_item</li>";
      }
    ?>
  </ul>
</div>
