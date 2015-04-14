<?php
/**
 * WC_My_Licence_Shortcode class.
 *
 * @class    WC_My_Licence_Shortcode
 * @version  1.0.0
 * @package  WooCommerce My Licence/Classes
 * @category Class
 * @author   Sebs Studio
 */
class WC_My_Licence_Shortcode {

	/**
	* Constructor
	*
	* @since  1.0.0
	* @access public
	* @return void
	*/
	public function __construct() {
		//add_action( 'woocommerce_my_licences_init', array( $this, 'init' ) );
	}

	/**
	 * Init shortcode
	 */
	public static function init() {
		add_shortcode( apply_filters( "woocommerce_my_licences_shortcode_tag", 'woocommerce_my_licences' ), __CLASS__ . '::get_woocommerce_my_licences' );
	}

	/**
	 * Shortcode Wrapper
	 *
	 * @access public static
	 * @param  mixed $function
	 * @param  array $atts (default: array())
	 * @return string
	 */
	public static function shortcode_wrapper( $function, $atts = array(), $wrapper = array(
			'class'  => 'woocommerce licences',
			'before' => null,
			'after'  => null
		) ) {
		ob_start();

		$before = empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		$after  = empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}

	/**
	 * Get the My Licences shortcode content.
	 *
	 * @access public static
	 * @param  array $atts
	 * @return string
	 */
	public static function get_woocommerce_my_licences( $atts ) {
		//global $woocommerce;

		//return $woocommerce->WC_Shortcode()->shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
		return self::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
		//return self::output( $atts );
	}

	/**
	 * My Licenses Shortcode.
	 *
	 * @access public static
	 * @param  mixed $atts
	 * @return void
	 */
	public static function output( $atts ) {
		global $woocommerce;

		if ( ! is_user_logged_in() ) {
			wc_get_template( 'myaccount/form-login.php' );
		} else {
			$recent_orders = -1;

			get_currentuserinfo();

			wc_get_template(
				'myaccount/my-licences.php', array(
					'current_user'  => get_user_by( 'id', get_current_user_id() ),
					'recent_orders' => $recent_orders
				),
				'',
				WooCommerce_My_Licences()->template_path()
			);

		} // END if is_user_logged_in()
	}
}
?>
