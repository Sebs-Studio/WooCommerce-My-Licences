<?php
/*
 * Plugin Name:       WooCommerce My Licences
 * Plugin URI:        http://www.sebs-studio.com
 * Description:       Displays the end-users licence keys in a table. Simply place a shortcode on a new page and any software your customers have purchased will have access to the licence keys once logged in. Requires WooCommerce Software Add-On
 * Version:           1.0.0
 * Author:            Sebastien Dumont
 * Author URI:        http://www.sebastiendumont.com
 * Text Domain:       woocommerce-my-licences
 * Domain Path:       languages
 * Network:           false
 * GitHub Plugin URI: https://github.com/Sebs-Studio/WooCommerce-My-Licences
 *
 * WooCommerce My Licences is distributed under the terms of the
 * GNU General Public License as published by the Free Software Foundation,
 * either version 2 of the License, or any later version.
 *
 * WooCommerce My Licences is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WooCommerce My Licences.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @package WooCommerce_My_Licences
 * @author  Sebs Studio
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WooCommerce_My_Licences' ) ) {

/**
 * Main WooCommerce_My_Licences Class
 *
 * @since 1.0.0
 */
final class WooCommerce_My_Licences {

	/**
	 * The single instance of the class
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    object
	 */
	protected static $_instance = null;

	/**
	 * Slug
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $plugin_slug = 'woocommerce_my_licences';

	/**
	 * Text Domain
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $text_domain = 'woocommerce-my-licences';

	/**
	 * The Plugin Name.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $name = "WooCommerce My Licences";

	/**
	 * The Plugin Version.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $version = "1.0.0";

	/**
	 * The WordPress version the plugin requires minumum.
	 *
	 * @var string
	 */
	public $wp_version_min = "4.1";

	/**
	 * The WooCommerce version this extension requires minimum.
	 *
	 * @var string
	 */
	public $woo_version_min = "2.3";

	/**
	 * Manage Plugin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $manage_plugin = "manage_options";

	/**
	 * This determins if the seller is selling 
	 * software with variable licenses.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    boolean
	 */
	public $variation_licence_types = false;

	/**
	 * Main WooCommerce My Licences Instance
	 *
	 * Ensures only one instance of WooCommerce My Licences is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @access public static
	 * @see    WooCommerce_My_Licences()
	 * @return WooCommerce My Licences instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new WooCommerce_My_Licences;
		}

		return self::$_instance;
	} // END instance()

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-my-licences' ), $this->version );
	} // END __clone()

	/**
	 * Disable unserializing of the class
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-my-licences' ), $this->version );
	} // END __wakeup()

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// Define constants
		$this->define_constants();

		// Check plugin requirements
		$this->check_requirements();

		// Include required files
		$this->includes();

		// Hooks
		add_action( 'init', array( $this, 'init_woocommerce_my_licences' ), 0 );
		add_filter( 'woocommerce_locate_template',  array( $this, 'locate_template' ), 20, 3 );
		add_action( 'woocommerce_my_licences_init', array( 'WC_My_Licence_Shortcode', 'init' ) );
	} // END __construct()

	/**
	 * Define Constants
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_constants() {
		if ( ! defined( 'WOOCOMMERCE_MY_LICENCES' ) )                     define( 'WOOCOMMERCE_MY_LICENCES', $this->name );
		if ( ! defined( 'WOOCOMMERCE_MY_LICENCES_FILE' ) )                define( 'WOOCOMMERCE_MY_LICENCES_FILE', __FILE__ );
		if ( ! defined( 'WOOCOMMERCE_MY_LICENCES_VERSION' ) )             define( 'WOOCOMMERCE_MY_LICENCES_VERSION', $this->version );
		if ( ! defined( 'WOOCOMMERCE_MY_LICENCES_WP_VERSION_REQUIRE' ) )  define( 'WOOCOMMERCE_MY_LICENCES_WP_VERSION_REQUIRE', $this->wp_version_min );
		if ( ! defined( 'WOOCOMMERCE_MY_LICENCES_WOO_VERSION_REQUIRE' ) ) define( 'WOOCOMMERCE_MY_LICENCES_WOO_VERSION_REQUIRE', $this->woo_version_min );
		if ( ! defined( 'WOOCOMMERCE_MY_LICENCES_VARIABLES' ) )           define( 'WOOCOMMERCE_MY_LICENCES_VARIABLES', $this->variation_licence_types );
	} // END define_constants()

	/**
	 * Checks that the WordPress setup meets the plugin requirements.
	 *
	 * @access private
	 * @global string $wp_version
	 * @global string $woocommerce
	 * @return boolean
	 */
	private function check_requirements() {
		global $wp_version, $woocommerce;

		$woo_version_installed = get_option( 'woocommerce_version' );

		if( empty( $woo_version_installed ) ) { $woo_version_installed = WOOCOMMERCE_VERSION; }

		define( 'WOOCOMMERCE_MY_LICENCES_WOOVERSION', $woo_version_installed );

		if ( !version_compare( $wp_version, WOOCOMMERCE_MY_LICENCES_WP_VERSION_REQUIRE, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'display_req_notice' ) );
			return false;
		}

		// Detect if WooCommerce is active.
		if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_action( 'admin_notices', array( $this, 'display_req_woo_not_active_notice' ) );
			return false;
		} else {
			// Check that the WooCommerce version is upto date.
			if ( version_compare( WOOCOMMERCE_MY_LICENCES_WOOVERSION, WOOCOMMERCE_MY_LICENCES_WOO_VERSION_REQUIRE, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'display_req_woo_notice' ) );
				return false;
			}
		}

		// Detect if WooCommerce Software Add-on is installed
		if ( !in_array( 'woocommerce-software-add-on/woocommerce-software.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_action( 'admin_notices', array( $this, 'display_req_woo_software_notice' ) );
			return false;
		}

		return true;
	}

	/**
	 * Display the WordPress version requirement notice.
	 *
	 * @access static
	 */
	static function display_req_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __( 'Sorry, <strong>%s</strong> requires WordPress ' . WOOCOMMERCE_MY_LICENCES_WP_VERSION_REQUIRE . ' or higher. Please upgrade your WordPress setup', 'woocommerce_my_licences' ), WOOCOMMERCE_MY_LICENCES );
		echo '</p></div>';
	}

	/**
	 * Display the requirement notice.
	 *
	 * @access static
	 */
	static function display_req_woo_not_active_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __( 'Sorry, <strong>%s</strong> requires WooCommerce to be installed and activated first. Please <a href="%s">install WooCommerce</a>.', 'woocommerce_my_licences' ), WOOCOMMERCE_MY_LICENCES, admin_url( 'plugin-install.php?tab=search&type=term&s=WooCommerce' ) );
		echo '</p></div>';
	}

	/**
	 * Display the WooCommerce version requirement notice.
	 *
	 * @access static
	 */
	static function display_req_woo_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __( 'Sorry, <strong>%s</strong> requires WooCommerce ' . WOOCOMMERCE_MY_LICENCES_WOO_VERSION_REQUIRE . ' or higher. Please update WooCommerce for %s to work.', 'woocommerce_my_licences' ), WOOCOMMERCE_MY_LICENCES, WOOCOMMERCE_MY_LICENCES );
		echo '</p></div>';
	}

	/**
	 * Display the WooCommerce Software Add-on requirement notice.
	 *
	 * @access static
	 */
	static function display_req_woo_software_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __( 'Sorry, <strong>%s</strong> requires WooCommerce Software Add-on to be installed and activated first.', 'woocommerce_my_licences' ), WOOCOMMERCE_MY_LICENCES );
		echo '</p></div>';
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function includes() {
		//$woocommerce_path = str_replace( '-my-licences', '', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		//include_once( $woocommerce_path . '/includes/class-wc-shortcodes.php' );
		//$wc_shortcodes = new WC_Shortcodes();

		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			$this->frontend_includes();
		}
	} // END includes()

	//public function woocommerce_my_licences( $atts ) { echo 'Hi'; }

	/**
	 * Include required frontend files.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function frontend_includes() {
		include_once( 'includes/shortcode.php' );
	} // END frontend_includes()

	/**
	 * Runs when the plugin is initialized.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function init_woocommerce_my_licences() {
		// Set up localisation
		$this->load_plugin_textdomain();

		// Init action
		do_action( 'woocommerce_my_licences_init' );
	} // END init_woocommerce_my_licences()

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any
	 * following ones if the same translation is present.
	 *
	 * @since  1.0.0
	 * @access public
	 * @filter woocommerce_my_licences_languages_directory
	 * @filter plugin_locale
	 * @return void
	 */
	public function load_plugin_textdomain() {
		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( WOOCOMMERCE_MY_LICENCES_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'woocommerce_my_licences_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale',  get_locale(), 'woocommerce_my_licences' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'woocommerce_my_licences', $locale );

		// Setup paths to current locale file
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . 'woocommerce_my_licences' . '/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/plugin-name/ folder
			load_textdomain( 'woocommerce_my_licences', $mofile_global );
		} else if ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/plugin-name/languages/ folder
			load_textdomain( 'woocommerce_my_licences', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'woocommerce_my_licences', false, $lang_dir );
		}

	} // END load_plugin_textdomain()

	/**
	 * Locates the WooCommerce template files from our templates directory
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  string $template      Already found template
	 * @param  string $template_name Searchable template name
	 * @param  string $template_path Template path
	 * @return string                Search result for the template
	 */
	public function locate_template( $template, $template_name, $template_path ) {
		// Temp holder
		$_template = $template;

		/*if ( ! $template_path ) {
			$template_path = WC()->template_path();
		}*/

		// Set our base path
		$plugin_path = $this->template_path();

		// Look within passed path within the theme - this is priority
		$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
			)
		);

		// Get the template from this plugin, if it exists
		if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}

		// Use default template
		if ( ! $template ) {
			$template = $_template;
		}

		// Return what we found
		return $template;
	}

	/** Helper functions ******************************************************/

	/**
	 * Get the plugin url.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	} // END plugin_url()

	/**
	 * Get the plugin path.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	} // END plugin_path()

	/**
	 * Get the plugin template path.
	 *
	 * @since  1.0.0
	 * @access public
	 * @filter woocommerce_my_licences_template_path
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'woocommerce_my_licences_template_path', $this->plugin_path() . '/templates/' );
	} // END template_path()

} // END WooCommerce_My_Licences()

} // END class_exists('WooCommerce_My_Licences')

/**
 * Returns the instance of WooCommerce_My_Licences to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Plugin Name
 */
function WooCommerce_My_Licences() {
	return WooCommerce_My_Licences::instance();
}

// Global for backwards compatibility.
$GLOBALS['woocommerce_my_licences'] = WooCommerce_My_Licences();

?>
