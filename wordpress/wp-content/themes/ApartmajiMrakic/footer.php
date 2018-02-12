			<?php do_action('cpotheme_after_main'); ?>
			
			<?php get_sidebar('footer'); ?>
			
			<?php do_action('cpotheme_before_footer'); ?>
			<footer id="footer" class="footer secondary-color-bg dark">
				<div class="container">
					<?php do_action('cpotheme_footer'); ?>
				</div>
			</footer>
			<div id="site-authors">Izdelava spletne strani: <a href="https://www.forward.si/" target="_blank">Forward - agencija za digitalni marketing</a></div>
			<?php do_action('cpotheme_after_footer'); ?>
			<div class="clear"></div>
		</div><!-- wrapper -->
		<?php do_action('cpotheme_after_wrapper'); ?>
	</div><!-- outer -->
	<?php if(get_field('embed_cubilis', 'option')): ?>
		<script type="text/javascript" src="https://reservations.cubilis.eu/Widget/RateboxScript/<?php echo get_field('cubilis_id', 'option');?>" async></script>
	<?php endif; ?>
	<?php wp_footer(); ?>
</body>
</html>
