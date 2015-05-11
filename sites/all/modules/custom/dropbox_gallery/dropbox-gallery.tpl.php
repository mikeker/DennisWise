<?php
/**
 * Available variables:
 *
 * $images array()
 *   Keys of the array are the derivative names. The values are arrays of URLs
 *   of the images in the same order as other derivative arrays.
 *
 * $derivatives array()
 *   Array of derivative names, which are also keys to $images
 *
 * $gallery_name string
 *   The name of the gallery.
 */
?>
<h1><?php print $gallery_name; ?></h1>
<ul>
  <?php foreach($images['thumbnails'] as $index => $url) : ?>
     <li><a href="<?php $images['full'][$index]?>"><img src="<?php print $url; ?>" /></a></li>
  <?php endforeach; ?>
</ul>
