<div id="rooms-container" class="zig-zag-content">
  <?php $n; while(have_rows('rooms')): the_row(); $n++;
    $roomType = get_sub_field('room_type');
    $roomImage = get_sub_field('room_image');
    $roomFeatures = get_sub_field('room_features');
    $even = $n % 2 == 0;
  ?>
    <div class="room-description<?php if($even) echo ' is-inverted'; ?>">
      <div class="room-image-container">
        <img src="<?php echo $roomImage['url']; ?>" alt="<?php echo $roomImage['alt']; ?>" />
        <div clasS="room-image-overlay">
          <h4 class="room-name"><?php echo $roomType; ?></h4>
        </div>
      </div>
      <!-- <hr class="content-divider"> -->
      <div class="room-content-container">
        <?php if($roomFeatures): ?>
          <?php while(have_rows('room_features')): the_row(); ?>
            <p><?php echo get_sub_field('feature'); ?></p>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>
</div>