<h3 class="details-subtitle"><?php echo pll__('O sobah'); ?></h3>
<div id="rooms-container" class="zig-zag-content">
  <?php while(have_rows('rooms')): the_row(); 
    $roomType = get_sub_field('room_type');
    $roomImage = get_sub_field('room_image');
    $roomFeatures = get_sub_field('room_features');
  ?>
    <div class="room-description">
      <div class="room-image-container">
        <img src="<?php echo $roomImage['url']; ?>" alt="<?php echo $roomImage['alt']; ?>" />
      </div>
      <div class="room-content-container">
        <h4 class="room-name"><?php echo $roomType; ?></h4>
        <?php if($roomFeatures): ?>
          <hr class="content-divider">
          <?php while(have_rows('room_features')): the_row(); ?>
            <p><?php echo get_sub_field('feature'); ?></p>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>
</div>