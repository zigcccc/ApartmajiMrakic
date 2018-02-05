<div id="apartment-info" class="flex-container">
  <?php while(have_rows('basic_info')): the_row(); $capacity = get_sub_field('capacity'); $size = get_sub_field('size'); $location = get_sub_field('apartment_location'); ?>
    <div class="apartment-basic-info">
      <h3 class="details-subtitle"><?php echo pll__('Osnovne informacije') ?></h3>
      <div id="apartment-capacity" class="item">
        <p class="item-text"><?php echo $capacity; ?></p>
      </div>
      <div id="apartment-size" class="item">
        <p class="item-text"><?php echo $size; ?></p>
      </div>
      <div id="apartment-location" class="item">
        <p class="item-text"><?php echo $location; ?></p>
      </div>
    </div>
  <?php endwhile; ?>
  <div class="apartment-extra-info">
  <h3 class="details-subtitle"><?php echo pll__('Dodatna oprema'); ?></h3>
    <?php while(have_rows('extras')): the_row(); $equipment = get_sub_field('equipment'); ?>
      <p class="item-text"><?php echo $equipment; ?></p>
    <?php endwhile; ?>
  </div>
</div>