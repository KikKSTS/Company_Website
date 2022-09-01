<?php
// Do not use namespace to keep this on global space to keep the singleton initialization working
if (!class_exists('Openexchange_api_settings')) {

/**
 *
 * This is the main class for creating dashbord addon page and all submenu items
 *
 * Do not call or initialize this class directly, instead use the function mentioned at the bottom of this file
 */
    class Openexchange_api_settings
    {

        /**
         * None of these variables should be accessable from the outside of the class
         */
        private static $instance;

        /**
         * initialize the class and create dashboard page only one time
         */
        public static function init()
        {

            if (empty(self::$instance)) {
                return self::$instance = new self;
            }
            return self::$instance;

        }

        /**
         * Initialize the dashboard with specific plugins as per plugin tag
         *
         */
        public function cool_init_hooks()
        {
            add_action('admin_notices', array($this, 'Openexchange_api_key_notice'));
            add_action('admin_menu', array($this, 'Openexchange_add_submenu'),100);
            add_action( 'admin_init', array( $this, 'Openexchange_settings' ) );
            // add_action('cmb2_admin_init', array($this, 'Openexchange_settings_callback'));
            //  add_action('wp_ajax_cool_dissmiss_notice', array($this, 'cool_dissmiss_notice'));
            add_action('admin_head', array($this, 'Openexchange_custom_javascript_for_cmb2'));

        }

        public function Openexchange_custom_javascript_for_cmb2()
        {
            wp_enqueue_script('jquery');

            $script = "
            <script>
            jQuery(document).ready(function($){

                var url = window.location.href;
                if (url.indexOf('?page=openexchange-api-settings') > 0) {
                $('[href=\"admin.php?page=openexchange-api-settings\"]').parent('li').addClass('current');
                }
                var data=$('#adminmenu #toplevel_page_cool-crypto-plugins ul li a[href=\"admin.php?page=openexchange-api-settings\"]')
                data.each(function(e){
                    if($(this).is(':empty')){
                        $(this).hide();
                    }
                });
                  $('#ccpw_dismiss_notice button.notice-dismiss').on('click', function (event) {
                        var notice_data = $('#ccpw_dismiss_notice');
                        $(notice_data).slideUp();

                    });

            });
            </script>
            ";

            echo $script;
        }

        // ajax callback for review notice
        public function cool_dissmiss_notice()
        {

            set_transient('ccpw_dismiss_notice', 'yes', 24 * HOUR_IN_SECONDS);
            echo json_encode(array("success" => "true"));
            exit;
        }

        /**
         * This function will initialize the main dashboard menu for all plugins
         */
        public function Openexchange_add_submenu()
        {

            add_submenu_page('cool-crypto-plugins', 'Open Exchange API', 'Open Exchange API', 'manage_options', 'openexchange-api-settings', array($this,'Openexchange_settings_callback'), 100);

        }

        public function Openexchange_settings()
        {
            register_setting(
                'Openexchange_option_group', // Option group
                'openexchange-api-settings' // Option name
            );

            
            add_settings_section(
                'Openexchange_section_id', // ID
                'Open Exchange Rates API', // Title
                array($this,'Openexchange_section_option'),
                'openexchange-api-settings' // Page
            );

            add_settings_field( 'Api_key', 'Enter API Key',array($this,'Api_key_fun'), 'openexchange-api-settings', 'Openexchange_section_id');
        }

        /**
         * This function will render and create the HTML display of dashboard page.
         * All the HTML can be located in other template files.
         * Avoid using any HTML here or use nominal HTML tags inside this function.
         */

        public function Openexchange_section_option(){
            echo '<div class="ccew_api_setting_section">ENTER OPEN EXCHANGE RATES API KEY</div>';
        } 

        public function Api_key_fun(){
            $api_option = get_option('openexchange-api-settings');
            $apikey = (!empty($api_option['openexchangerate_api'])) ? $api_option['openexchangerate_api'] : "";
	        echo '<input type="text" name="openexchange-api-settings[openexchangerate_api]" size="35" value='.esc_attr($apikey).' ><br><br>Click Here To <a href="https://openexchangerates.org/signup/free" target="blank">Get Openexchangerates Free API Key</a>';
        }

        public function Openexchange_settings_callback()
        {

            ?>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
            <?php settings_fields( 'Openexchange_option_group'); ?>
	        <?php do_settings_sections('openexchange-api-settings'); ?>
	        <?php submit_button(); ?>
            </form>

            <?php

            // /**
            //  * Registers options page menu item and form.
            //  */
            // $cool_options = new_cmb2_box(array(
            //     'id' => 'ccpw_settings_page',
            //     'title' => esc_html__('Open Exchange Rates API', 'celp1'),
            //     'object_types' => array('options-page'),

            //     /*
            //      * The following parameters are specific to the options-page box
            //      * Several of these parameters are passed along to add_menu_page()/add_submenu_page().
            //      */

            //     'option_key' => 'openexchange-api-settings', // The option key and admin menu page slug.
            //     // 'icon_url'        => 'dashicons-palmtree', // Menu icon. Only applicable if 'parent_slug' is left empty.
            //     'menu_title' => false, // Falls back to 'title' (above).
            //     'parent_slug' => 'cool-crypto-plugins', // Make options page a submenu item of the themes menu.
            //     'capability' => 'manage_options', // Cap required to view options-page.
            //     'position' => 44, // Menu position. Only applicable if 'parent_slug' is left empty.
            //     // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
            //     // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
            //     // 'save_button'     => esc_html__( 'Save Settings', 'celp1' ), // The text for the options-page save button. Defaults to 'Save'.
            // ));

            // /*
            //  * Options fields ids only need
            //  * to be unique within this box.
            //  * Prefix is not needed.
            //  */
            // $cool_options->add_field(array(
            //     'name' => __('Enter Open Exchange Rates API Key', 'ccpw1'),
            //     'id' => 'ccpw_openexchangerate_api_title',
            //     'type' => 'title',

            // ));

            // $cool_options->add_field(array(
            //     'name' => __('Enter API Key', 'ccpw1'),
            //     'desc' => __('Click Here To <a href="https://openexchangerates.org/signup/free" target="blank">Get Openexchangerates Free API Key</a>', 'ccpw1'),
            //     'id' => 'openexchangerate_api',
            //     'type' => 'text',

            // ));

        }

        /*
        |----------------------------------------------------------------
        |   Admin notice for API Key registration admin users only
        |----------------------------------------------------------------
         */
        public function Openexchange_api_key_notice()
        {
            $api_option = get_option("openexchange-api-settings");
            $api = (!empty($api_option['openexchangerate_api'])) ? $api_option['openexchangerate_api'] : "";
            if (!current_user_can('delete_posts') || !empty($api)) {
                return;
            }
            $current_user = wp_get_current_user();
            $user_name = $current_user->display_name;
            $ajax_url = admin_url('admin-ajax.php');

            ?>
			<div  class="license-warning notice notice-error is-dismissible" data-ajaxurl="<?php echo esc_url($ajax_url); ?>" data-ajax-callback="cool_dissmiss_notice"  id="ccpw_dismiss_notice">
				<p>Hi, <strong><?php echo ucwords($user_name); ?></strong>! Please <strong><a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=openexchange-api-settings')); ?>">enter</a></strong> Openexchangerates.org free API key for crypto to fiat price conversions.</p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
		<?php
}

    }

    /**
     *
     * initialize the main dashboard class with all required parameters
     */

    $Openexchange = Openexchange_api_settings::init();
    $Openexchange->cool_init_hooks();

}
