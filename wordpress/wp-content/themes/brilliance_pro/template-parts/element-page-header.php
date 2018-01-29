<?php wp_reset_query(); ?>

<?php if(cpotheme_show_title()): ?>

<?php do_action('cpotheme_before_title'); ?>
<section id="pagetitle" class="pagetitle dark secondary-color-bg">
	<div class="container">
		<?php do_action('cpotheme_title'); ?>
	</div>
</section>
<?php do_action('cpotheme_after_title'); ?>

<?php endif; ?>
