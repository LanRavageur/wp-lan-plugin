<?php
get_header();

wp_enqueue_style('lanorg-form');

?>
<div id="primary">
<div id="content" role="main">
<?php echo the_content(); ?>
</div>
</div>
<?php get_footer(); ?>