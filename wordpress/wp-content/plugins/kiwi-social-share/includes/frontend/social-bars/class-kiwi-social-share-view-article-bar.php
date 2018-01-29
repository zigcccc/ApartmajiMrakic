<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Kiwi_Social_Share_View_Article_Bar
 */
final class Kiwi_Social_Share_View_Article_Bar extends Kiwi_Social_Share_View implements Kiwi_Social_Share_Interface_Frontend {
	/**
	 * @var bool
	 */
	private $stop = false;
	/**
	 * @var string
	 */
	private $style;

	/**
	 * Kiwi_Social_Share_Article_Bar constructor.
	 */
	public function __construct() {
		parent::__construct();
		$enable = Kiwi_Social_Share_Helper::get_setting_value( 'share_buttons', false );

		if ( ! $enable ) {
			return;
		}

		if ( empty( $this->networks['article_bar'] ) ) {
			return;
		}

		$this->style = Kiwi_Social_Share_Helper::get_setting_value( 'article_bar_style', 'center' );
		switch ( $this->style ) {
			case 'fit':
				$this->style = 'kiwi-article-bar-fit';
				break;
			default:
				$this->style = 'kiwi-article-bar-center';
				break;
		}

		$this->position = Kiwi_Social_Share_Helper::get_setting_value( 'share_buttons_location', 'bottom' );

		// render on the front
		add_action( 'wp', array( $this, 'check_front_page' ) );
		add_action( 'the_content', array( $this, 'display_bar' ) );

	}

	/**
	 * We need to make sure we don't render the article bar on a static front page ( some templates load custom post
	 * types as sections )
	 */
	public function check_front_page() {
		$this->stop = is_front_page();
	}

	/**
	 * Output contents in frontend
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function display_bar( $content = '' ) {

		if ( ( ! is_single() && ! is_page() ) || $this->stop ) {
			return $content;
		}

		$kiwi = Kiwi_Social_Share::instance();
		wp_enqueue_style( $kiwi->_token . '-frontend' );
		wp_enqueue_script( $kiwi->_token . '-frontend' );

		$bar = $this->generate_frontend_bar();

		switch ( $this->position ) {
			case 'top':
				return $bar . $content;
				break;
			case 'both':
				return $bar . $content . $bar;
				break;
			default:
				return $content . $bar;
				break;
		}
	}

	/**
	 * Render the display bar
	 *
	 * Allow theme customization for the article bar using the following filters:
	 *  * kiwi_before_article_bar
	 *  * kiwi_before_first_article_item
	 *  * kiwi_after_last_article_item
	 *  * kiwi_after_article_bar
	 *  * kiwi_article_bar_list_custom_class
	 */
	public function generate_frontend_bar() {
		$output = '';
		$class  = implode( ' ', $this->container_class );
		$class  .= ' ' . $this->style;
		$class  .= ' ' . apply_filters( 'kiwi_article_bar_list_custom_class', '' );
		$output .= wp_kses_post( apply_filters( 'kiwi_before_article_bar', '' ) );

		$tracking = '';
		if ( $this->tracking ) {
			$tracking = ' data-tracking="true" data-tracking-container="article-bar" ';
		}

		$output .= '<ul class="kiwi-article-bar ' . esc_attr( $class ) . '"' . $tracking . '>';
		$output .= wp_kses_post( apply_filters( 'kiwi_before_first_article_bar_item', '' ) );

		foreach ( $this->networks['article_bar'] as $network ) {
			$class = $this->generate_class_name( $network );
			if ( ! class_exists( $class ) ) {
				continue;
			}
			$t = new $class();
			$t = $t->generate_output();
			if ( ! empty( $t ) ) {
				$output .= '<li>';
				/**
				 * Social buttons will implement this interface :
				 * Kiwi_Social_Share_Interface_Social::generate_output
				 */
				$output .= $t;
				$output .= '</li>';
			}
		}
		$output .= wp_kses_post( apply_filters( 'kiwi_after_last_article_bar_item', '' ) );
		$output .= '</ul>';

		$output .= wp_kses_post( apply_filters( 'kiwi_after_article_bar', '' ) );

		return $output;
	}


}