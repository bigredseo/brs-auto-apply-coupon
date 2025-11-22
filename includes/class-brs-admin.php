<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BRS_Admin_Settings {

    const OPTION_KEY = 'brs_auto_apply_coupon_settings';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ], 99 );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_settings_page() {

        $parent = 'woocommerce';

        if ( isset( $GLOBALS['menu'] ) ) {
            foreach ( $GLOBALS['menu'] as $item ) {
                if ( isset( $item[2] ) && $item[2] === 'woocommerce-marketing' ) {
                    $parent = 'woocommerce-marketing';
                    break;
                }
            }
        }

        add_submenu_page(
            $parent,
            'Auto Coupon Settings',
            'Auto Coupon Settings',
            'manage_woocommerce',
            'brs-auto-apply-coupon',
            [ $this, 'settings_page_html' ]
        );
    }

    public function register_settings() {
        register_setting( 'brs_auto_apply_coupon_settings_group', self::OPTION_KEY );

        add_settings_section(
            'brs_auto_apply_coupon_section',
            'Auto Apply Coupon Settings',
            function() {
                echo '<p>Configure when the coupon should be automatically applied.</p>';
            },
            'brs_auto_apply_coupon_settings'
        );

        add_settings_field(
            'coupon_code',
            'Coupon Code',
            [ $this, 'field_coupon_code' ],
            'brs_auto_apply_coupon_settings',
            'brs_auto_apply_coupon_section'
        );

        add_settings_field(
            'start_datetime',
            'Start Date/Time',
            [ $this, 'field_start_datetime' ],
            'brs_auto_apply_coupon_settings',
            'brs_auto_apply_coupon_section'
        );

        add_settings_field(
            'end_datetime',
            'End Date/Time',
            [ $this, 'field_end_datetime' ],
            'brs_auto_apply_coupon_settings',
            'brs_auto_apply_coupon_section'
        );

        add_settings_field(
            'notice_message',
            'Success Message',
            [ $this, 'field_notice_message' ],
            'brs_auto_apply_coupon_settings',
            'brs_auto_apply_coupon_section'
        );
    }

    private function get_settings() {
        return wp_parse_args( get_option( self::OPTION_KEY ), [
            'coupon_code'    => '',
            'start_datetime' => '',
            'end_datetime'   => '',
            'notice_message' => 'Black Friday discount applied!',
        ] );
    }

    public function field_coupon_code() {
        $s = $this->get_settings();
        echo '<input type="text" name="' . self::OPTION_KEY . '[coupon_code]" value="' . esc_attr($s['coupon_code']) . '" class="regular-text" />';
    }

    public function field_start_datetime() {
        $s = $this->get_settings();
        echo '<input type="datetime-local" name="' . self::OPTION_KEY . '[start_datetime]" value="' . esc_attr($s['start_datetime']) . '" />';
    }

    public function field_end_datetime() {
        $s = $this->get_settings();
        echo '<input type="datetime-local" name="' . self::OPTION_KEY . '[end_datetime]" value="' . esc_attr($s['end_datetime']) . '" />';
    }

    public function field_notice_message() {
        $s = $this->get_settings();
        echo '<input type="text" name="' . self::OPTION_KEY . '[notice_message]" value="' . esc_attr($s['notice_message']) . '" class="regular-text" />';
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>Auto Apply Coupon Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'brs_auto_apply_coupon_settings_group' );
                do_settings_sections( 'brs_auto_apply_coupon_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
