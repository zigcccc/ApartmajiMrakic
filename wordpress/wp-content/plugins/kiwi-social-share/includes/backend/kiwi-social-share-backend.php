<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="sl-kiwi">
    <div class="kiwi-notification-popup">
		<?php echo esc_html__( 'Seems like you made some changes, don\'t forget to save them!', 'kiwi-social-share' ) ?>
        <input type="button" value="Save Changes" class="button button-primary">
    </div>
    <div class="overlay active">
        <div class="overlay-content">
            <h4><?php echo esc_html__( 'Loading', 'kiwi-social-share' ); ?></h4>
            <img src="<?php echo esc_url( Kiwi_Social_Share::instance()->assets_url . '/img/loader.svg' ) ?>"
                 alt="spinner"/>
        </div>
    </div>
    <form method="post" action="options.php" enctype="multipart/form-data">
		<?php
		$kiwi_networks = Kiwi_Social_Share_Helper::get_social_network_identities();
		$networks      = Kiwi_Social_Share_Helper::get_checked_networks();
		$post_types    = Kiwi_Social_Share_Helper::get_custom_post_types();
		$colors        = Kiwi_Social_Share_Helper::get_network_colors();
		$hash          = '';


		if ( ! empty( $_COOKIE ) && ! empty( $_COOKIE['tab'] ) ) {
			$hash = $_COOKIE['tab'];
		}

		/**
		 * Load Kiwi social lists
		 */
		require_once dirname( __FILE__ ) . '/parts/kiwi-networks-ordering.php';

		/**
		 * Load epsilon tabs view
		 */
		require_once dirname( __FILE__ ) . '/parts/epsilon-tabs.php';

		settings_fields( $this->parent->_token . '_settings' );
		do_settings_sections( $this->parent->_token . '_settings' );

		?>

        <div class="sl-kiwi-content">
            <!-- Start Tabs -->
			<?php
			/**
			 * Load epsilon tabs content
			 */
			require_once dirname( __FILE__ ) . '/parts/kiwi-tab-networks.php';
			require_once dirname( __FILE__ ) . '/parts/kiwi-tab-article-bar.php';
			require_once dirname( __FILE__ ) . '/parts/kiwi-tab-floating-bar.php';
			require_once dirname( __FILE__ ) . '/parts/kiwi-tab-advanced.php';
			require_once dirname( __FILE__ ) . '/parts/kiwi-tab-social-identity.php';
			?>
            <!-- End Tabs -->
        </div>
		<?php submit_button( __( 'Save changes', 'kiwi-social-share', 'primary', 'submitter', true, array( 'id' => 'submitter' ) ) ); ?>
    </form>
	<?php
	/* start-lite-version */
	require_once dirname( __FILE__ ) . '/parts/kiwi-modal-popup.php';
	/* end-lite-version */
	?>
</div>