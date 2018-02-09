<?php $feature_posts = get_terms('cpo_service_category', 'order=ASC&orderby=name'); ?>
<?php if(sizeof($feature_posts) > 0): $feature_count = 0; ?>
<div id="menu-services" class="menu-services">
	<?php foreach($feature_posts as $current_feature): ?>
	<?php $feature_count++; ?>
	<a href="<?php echo get_term_link($current_feature, 'cpo_service_category'); ?>" rel="bookmark" class="menu-item">
		<div class="menu-title">
			<?php echo $current_feature->name; ?>
		</div>
	</a>
	<?php endforeach; ?>
</div>
<?php endif; ?>