<?php 
  /**
   * Template Name: Apartment Page
   * 
   * @package WordPress
   * @subpackage ApartmajiMrakic
   * @since Apartmaji Mrakič 1.0.0
   */
  get_header();
?>

<div id="main" class="main">
<div class="container">

<!-- PORTFOLIO CONTENT -->
<section id="content" class="content">
  <?php do_action('cpotheme_before_content'); ?>
  <?php if(have_posts()) while(have_posts()): the_post(); ?>
  <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="page-content">
      <?php the_content(); ?>
    </div>
    <?php cpotheme_post_pagination(); ?>
    <div class="clear"></div>
  </div>
  <?php comments_template('', true); ?>
  <?php endwhile; ?>			
  <?php do_action('cpotheme_after_content'); ?>
</section>

<!-- PORTFOLIO DETAILS -->
<section id="portfolio-details" class="portfolio-details content">
  <?php if(have_rows('basic_info') && have_rows('extras')){
    get_template_part('template-parts/element', 'portfolio-details');
  } ?>
</section>

<!-- ROOMS DETAILS -->
<section id="portfolio-rooms-details">
  <?php 
    if(have_rows('rooms')){
      get_template_part('template-parts/element', 'portfolio-rooms');
    }
  ?>
</section>

<?php if(get_field('show_book_apartment')): ?>
  <section id="portfolio-book-apartment">
    <div class="container">
      <h2><?php echo get_field('book_apartment_title'); ?></h2>
      <h3><?php echo get_field('book_apartment_subtitle'); ?></h3>
      <a target="_blank" class="ctsc-button ctsc-button-normal section-cta book-cta" href="<?php echo get_field('book_apartment_cta')['povezava']; ?>"><?php echo get_field('book_apartment_cta')['tekst']; ?></a>
    </div>
  </section>
<?php endif; ?>


<!-- PORTFOLIO IMAGES -->
<?php if(get_field('galerija_apartmaja') != null): ?>
  <section id="portfolio-gallery">			
    <?php echo get_field('galerija_apartmaja'); ?>
  </section>
<?php endif; ?>

<?php if (have_rows('360_posnetek_apartmaja')): while (have_rows('360_posnetek_apartmaja')): the_row(); ?>
  <section id="portfolio-360-image">
    
      <h3><strong><?php echo get_sub_field('naslov_sekcije'); ?><strong></h3>
      <?php
        $image_360 = get_sub_field('posnetek');
        $widget = do_shortcode( '[vrview img="' . $image_360 . '" width="100%" height="400px" ]');
        echo apply_filters('the_content', $widget);
      ?>
    
  </section>
  <?php endwhile; ?>
<?php endif; ?>

<?php //cpotheme_post_media(get_the_ID(), get_post_meta(get_the_ID(), 'portfolio_layout', true)); ?>
<?php get_sidebar(); ?>
<div class="clear"></div>
</div>
<?php get_template_part('template-parts/element', 'portfolio-related'); ?>

  
</div>




<?php 
  get_footer();
?>