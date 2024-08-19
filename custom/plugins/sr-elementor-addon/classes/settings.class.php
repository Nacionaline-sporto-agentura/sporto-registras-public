<?php

class BEA_Settings
{
    public $bea_slug;
    public $hook;

    public function __construct()
    {
        $this->bea_slug = 'sr-admin';
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    public function admin_menu(){
        if (current_user_can('manage_options')) {
            add_menu_page(
                __('Sporto registras', 'bea'),
                __('Sporto registras', 'bea'),
                'manage_options',
                $this->bea_slug,
                [$this,'settings'],
                bea('lib')->svg_sprite(BEA_DIR.'assets/img/sr.svg'),
                6
            );

            $this->hook = add_submenu_page(
                $this->bea_slug,
                __('Nustatymai', 'bea'),
                __('Nustatymai', 'bea'),
                'manage_options',
                'sr-admin-settings',
                [ $this, 'settings']
            );

            add_action('load-' . $this->hook, [$this, 'bulk_actions' ],99);
            
        }
    }

    public function bulk_actions()
    {
        global $wpdb;

        $screen = get_current_screen();
        if(!is_object($screen) || $screen->id != $this->hook) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(
                '<h1>' . __('Jums reikia aukštesnio lygio leidimo.', 'bea') . '</h1>' .
                '<p>' . __('Atsiprašome, jums neleidžiama keisti "Sporto registro" nustatymų.', 'bea') . '</p>',
                403
            );
        }

        if (!empty($_POST) && check_admin_referer('update', 'sr_settings_field')) {
            bea('notifications')->notice( __('Nustatymai sėkmingai išsaugoti.','bea'), 'success', true );
            update_option('sr_settings', $_POST['sr_settings']);
            $redirect = 'admin.php?page=sr-admin-settings';
          
            bea('notifications')->redirect( $redirect );
            exit;
        }
    }



    public function settings()
    {
        global $wpdb, $wp_roles, $wp_locale;

        if (!current_user_can('manage_options')) {
            wp_die(
                '<h1>' . __('Jums reikia aukštesnio lygio leidimo.', 'bea') . '</h1>' .
                '<p>' . __('Atsiprašome, jums neleidžiama keisti sporto registro nustatymų.', 'bea') . '</p>',
                403
            );
        }
        $messages = [];
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        $roles = $wp_roles->get_names();
        $pages = get_pages();
        $settings = get_option('sr_settings', []);
        include_once BEA_DIR . '/partials/settings/settings.php';
    }
}
