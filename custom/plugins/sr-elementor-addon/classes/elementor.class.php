<?php

class BEA_Elementor
{
    public function __construct()
    {
        add_action('plugins_loaded', array( &$this, 'init' ), 99);
    }
    public function init()
    {
        add_action('init', array(&$this,'clear_cache'));
        add_action('elementor/elements/categories_registered', array(&$this, 'add_category' ));
        add_action('elementor/editor/after_enqueue_styles', array(&$this, 'enqueue_scripts_styles'), 99);
        add_action('elementor/icons_manager/additional_tabs', array(&$this,'elementor_icons'));
        add_filter('elementor/fonts/additional_fonts', array(&$this,'register_custom_fonts'));
        if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '<')) {
            add_action('elementor/widgets/widgets_registered', array( $this, 'register_widgets' ));
            add_action('elementor/controls/controls_registered', array( $this, 'register_control' ));
        } else {
            add_action('elementor/widgets/register', array( $this, 'register_widgets' ));
            add_action('elementor/controls/register', array( $this, 'register_control' ));
        }
        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts_styles'));
    }
    private function is_dir_empty($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }
        return (count(glob("$dir/*")) === 0);
    }
    public function clear_cache()
    {

        if ($this->is_dir_empty(WP_CONTENT_DIR . '/uploads/elementor/css')) {
            if (! did_action('elementor/loaded')) {
                return;
            }
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
    }
    private function is_activated()
    {
        if(function_exists('elementor_load_plugin_textdomain')) {
            return true;
        } else {
            return false;
        }
    }
    private function is_edit_mode()
    {
        if(!$this->is_activated()) {
            return false;
        }

        return Elementor\Plugin::$instance->editor->is_edit_mode();
    }
    private function is_preview_mode()
    {
        if(!$this->is_activated()) {
            return false;
        }

        return Elementor\Plugin::$instance->preview->is_preview_mode();
    }
    private function is_preview_page()
    {
        return isset($_GET['preview_id']);
    }
    public function register_control($controls_registry)
    {
        $fonts = $controls_registry->get_control('font')->get_settings('options');
        $new_fonts = array_merge([
            'Atkinson Hyperlegible' => 'system'
        ], $fonts);
        $controls_registry->get_control('font')->set_settings('options', $new_fonts);
    }
    public function register_custom_fonts($elementor_fonts)
    {
        $custom_fonts = array(
            'atkinson-hyperlegible' => array(
                'label' => __('Atkinson Hyperlegible', 'bea'),
                'variants' => array( 'regular', 'bold' ),
                'category' => 'sans-serif',
                'family' => 'Atkinson Hyperlegible',
                'source' => 'local',
                'enqueue' => true,
                'fallback' => 'sans-serif',
            ),
        );

        $elementor_fonts = array_merge($elementor_fonts, $custom_fonts);

        $suffix = SCRIPT_DEBUG ? '' : '.min';
        if ($custom_fonts['atkinson-hyperlegible']['enqueue']) {
            bea('minify')->css(BEA_DIR .'assets/css/atkinson-hyperlegible.css', BEA_DIR .'assets/css/atkinson-hyperlegible.min.css');
            wp_enqueue_style('atkinson-hyperlegible-font', BEA_URI . 'assets/css/atkinson-hyperlegible' . $suffix . '.css');
        }
        return $elementor_fonts;
    }
    public function elementor_icons($tabs)
    {
        $tabs['bea-custom'] = [
            'name'          => 'bea-custom',
            'label'         => esc_html__('BĮIP ikonos', 'bea'),
            'prefix'        => 'bea-',
            'displayPrefix' => 'bea',
            'labelIcon'     => 'bea-favicon',
            'ver'           => '1.0.0',
            'fetchJson'     => BEA_URI . '/assets/js/bea-custom.json',
            'native'        => true,
        ];

        $tabs['gr-custom'] = [
            'name'          => 'gr-custom',
            'label'         => esc_html__('SR ikonos', 'bea'),
            'prefix'        => 'gr-',
            'displayPrefix' => 'gr',
            'labelIcon'     => 'bea-favicon',
            'ver'           => '1.0.0',
            'fetchJson'     => BEA_URI . '/assets/js/gr-custom.json',
            'native'        => true,
        ];

        return $tabs;
    }
    public function enqueue_scripts_styles()
    {
        $suffix = SCRIPT_DEBUG ? '' : '.min';
        if(SCRIPT_DEBUG) {
            bea('minify')->css(BEA_DIR .'/assets/css/elementor-editor.css', BEA_DIR .'/assets/css/elementor-editor.min.css');
            bea('minify')->css(BEA_DIR .'/assets/css/bea-custom.css', BEA_DIR .'/assets/css/bea-custom.min.css');
            bea('minify')->css(BEA_DIR .'/assets/css/gr-custom.css', BEA_DIR .'/assets/css/gr-custom.min.css');
            bea('minify')->js(BEA_DIR .'widgets/biip_off_canvas/assets/js/off_canvas_widget.js', BEA_DIR .'widgets/biip_off_canvas/assets/js/off_canvas_widget.min.js');
            bea('minify')->css(BEA_DIR .'widgets/biip_off_canvas/assets/css/off_canvas_widget.css', BEA_DIR .'widgets/biip_off_canvas/assets/css/off_canvas_widget.min.css');
        }

        wp_enqueue_style('bea-custom', BEA_URI .'/assets/css/bea-custom' . $suffix . '.css', array(), BEA_VERSION);
        wp_enqueue_style('gr-custom', BEA_URI .'/assets/css/gr-custom' . $suffix . '.css', array(), BEA_VERSION);
        if ($this->is_edit_mode() || $this->is_preview_page() || $this->is_preview_mode()) {
            wp_enqueue_style('bea-elementor-editor', BEA_URI .'/assets/css/elementor-editor' . $suffix . '.css', array(), BEA_VERSION);
        }
        wp_register_script('biip_off_canvas', BEA_URI . 'widgets/biip_off_canvas/assets/js/off_canvas_widget' . $suffix . '.js', [ 'jquery' ], '1.0.0', true);
        wp_register_style('biip_off_canvas', BEA_URI . 'widgets/biip_off_canvas/assets/css/off_canvas_widget' . $suffix . '.css');
    }
    public function add_category($elements_manager)
    {
        $elements_manager->add_category(
            'bea-elements',
            array(
                'title' => esc_html__('BIIP Elementai', 'bea'),
                'icon'  => 'fa fa-plug',
            )
        );
    }
    public function register_widgets($widgets_manager)
    {
        $widgets_dir = BEA_DIR . 'widgets/';

        if (!is_dir($widgets_dir)) {
            return;
        }
        $items = scandir($widgets_dir);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (is_dir($widgets_dir . $item)) {
                $widget_file = $widgets_dir . $item . '/' . $item . '.php';
                require $widget_file;

                $class = str_replace('_', ' ', $item);
                $class = ucwords($class);
                $class = str_replace(' ', '_', $class);

                if (class_exists($class)) {
                    if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '<')) {
                        $widgets_manager->register_widget_type(new $class());
                    } else {
                        $widgets_manager->register(new $class());
                    }
                }
            }
        }
    }

}
