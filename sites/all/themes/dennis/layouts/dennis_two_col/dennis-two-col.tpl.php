<?php
/**
 * @file
 * Two column layout, first column fixed, the second column fluid.
 */
?>

<div class="panel-layout dennis-two-col<?php if (!empty($class)) { print ' ' . $class; } ?>"<?php if (!empty($css_id)) { print ' id="' . $css_id . '"'; } ?>>
  <section class="dennis-fixed-column">
    <?php print $content['fixed']; ?>
  </section>
  <section class="dennis-fluid-column">
    <?php print $content['fluid']; ?>
  </section>
</div>
