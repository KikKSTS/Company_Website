<?php
/**
 * Plugin Name:Cryptocurrency Widgets For Elementor
 * Description:Cryptocurrency Widgets For Elementor WordPress plugin displays current prices of crypto coins - bitcoin, ethereum, ripple etc.
 * Author:Cool Plugins
 * Author URI:https://coolplugins.net/
 * Plugin URI:https://cryptowidget.coolplugins.net/
 * Version: 1.4.1
 * License: GPL2
 * Text Domain:ccew
 * Domain Path: languages
 *
 * Elementor tested up to: 3.7.2
 * */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CCEW_VERSION', '1.4.1' );
define( 'CCEW_FILE', __FILE__ );
define( 'CCEW_DIR', plugin_dir_path( CCEW_FILE ) );
define( 'CCEW_URL', plugin_dir_url( CCEW_FILE ) );

 /**
 * Class Crypto_Currency_Elementor_Widget
 */
final class Crypto_Currency_Elementor_Widget {

	/**
	 * Plugin instance.
	 *
	 * @var Crypto_Currency_Elementor_Widget
	 * @access private
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return Crypto_Currency_Elementor_Widget
	 * @static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access private
	 */

	private function __construct() {
		// register activation deactivation hooks
		register_activation_hook( CCEW_FILE, array( $this, 'ccew_activate' ) );
		register_deactivation_hook( CCEW_FILE, array( $this, 'ccew_deactivate' ) );

		// Required file included
		$this->ccew_file_include();

		// Insert Data
		add_action( 'init', array( $this, 'ccew_data_insert' ) );

		// ajax call for coin data
		add_action( 'wp_ajax_ccew_getData', 'ccew_getData' );
		add_action( 'wp_ajax_nopriv_ccew_getData', 'ccew_getData' );

		// Load the plugin after Elementor  are loaded.
		add_action( 'plugins_loaded', array( $this, 'ccew_plugins_loaded' ) );
		add_action( 'init', array( $this, 'ccew_verify_plugin_version' ) );

	}

	public function ccew_plugins_loaded() {
		// Notice if the Elementor is not active
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'ccew_fail_to_load' ) );
			return;
		}

	}
	function ccew_fail_to_load() {

		if ( ! is_plugin_active( 'elementor/elementor.php' ) ) : ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php echo '<a href="https://wordpress.org/plugins/elementor/"  target="_blank" >' . esc_html__( 'Elementor Page Builder', 'ccew' ) . '</a>' . wp_kses_post( __( ' must be installed and activated for "<strong>Cryptocurrency Elementor Widgets</strong>" to work', 'ccew' ) ); ?></p>
			</div>
			<?php
		endif;

	}

	public function ccew_file_include() {
		if ( is_admin() ) {
			// require_once(CCEW_DIR . '/admin/cmb2/init.php');
			require_once CCEW_DIR . '/admin/addon-dashboard-page/addon-dashboard-page.php';
			cool_plugins_crypto_addon_settings_page( 'crypto', 'cool-crypto-plugins', 'Cryptocurrency plugins by CoolPlugins.net', 'Crypto Plugins', 'dashicons-chart-area' );
			require_once CCEW_DIR . '/admin/openexchange-api-settings.php';
		}

		require CCEW_DIR . 'includes/ccew-elementor-register.php';
		require CCEW_DIR . 'includes/ccew-widget-functions.php';
		require CCEW_DIR . 'includes/ccew-ajaxhandler.php';
		require CCEW_DIR . 'includes/ccew-crypto-elementor-db.php';

		// Review File
		require_once CCEW_DIR . '/admin/ccew-review-notice.php';
		new ccew_review_notice();

		// if( is_admin() === true ){
		require_once CCEW_DIR . 'admin/feedback/admin-feedback-form.php';
		// }
	}

	public function ccew_data_insert() {
		if ( get_option( 'ccew_data_save' ) == 'false' ) {
			ccew_widget_insert_data();
			update_option( 'ccew_data_save', 'true' );
		}

	}

	/*
	|--------------------------------------------------------------------------
	|  Check if plugin is just updated from older version to new
	|--------------------------------------------------------------------------
	*/
	public function ccew_verify_plugin_version() {

		$CCEW_VERSION = get_option( 'CCEW_FREE_VERSION' );
		if ( ! isset( $CCEW_VERSION ) || version_compare( $CCEW_VERSION, CCEW_VERSION, '<' ) ) {
			$conversions = get_transient( 'cmc_usd_conversions' );
			if ( ! empty( $conversions ) ) {
				update_option( 'cmc_usd_conversions', $conversions );
			}
			update_option( 'CCPW_FREE_VERSION', CCEW_VERSION );
		}
	}   // end of cmc_plugin_version_verify()

	public function ccew_activate() {
		$DB = new ccew_database();
		$DB->create_table();
		update_option( 'ccew-v', CCEW_VERSION );
		update_option( 'ccew_activation_time', gmdate( 'Y-m-d h:i:s' ) );
		update_option( 'ccew_data_save', 'false' );
		update_option( 'ccew-alreadyRated', 'no' );

	}

	public function ccew_deactivate() {
		$db = new ccew_database();
		$db->drop_table();
		delete_transient( 'ccew_data' );
		delete_option( 'ccew_data_save' );
	}

	public function ccew_on_widgets_registered() {
		$this->ccew_widget_includes();
	}

}
function Crypto_Currency_Elementor_Widget() {
	return Crypto_Currency_Elementor_Widget::get_instance();
}

Crypto_Currency_Elementor_Widget();
