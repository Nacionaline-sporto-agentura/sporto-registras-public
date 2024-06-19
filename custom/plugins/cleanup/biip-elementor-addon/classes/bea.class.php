<?php 
class BEA{
    private $_classes = array();
    public function __construct()
    {
        register_activation_hook(BEA_FILE, [$this,'activate']);
        register_deactivation_hook(BEA_FILE, [$this,'deactivate']);

        require_once BEA_DIR . 'classes/i18n.class.php';
        require_once BEA_DIR . 'classes/minify.class.php';
        require_once BEA_DIR . 'classes/elementor.class.php';
        require_once BEA_DIR . 'classes/empty.class.php';

        $this->_classes = apply_filters(
            'bea_classes',
            array(
                'i18n'          => new BEA_i18n(),
                'minify'          => new BEA_Minify(),
                'elementor'     => new BEA_Elementor(),
                'empty'         => new BEA_Empty()
            )
        );
    }
    public function __call($class, $args)
    {

        if (! isset($this->_classes[ $class ])) {
            if (WP_DEBUG) {
            } else {
                $class = 'empty';
            }
        }

        return $this->_classes[ $class ];
    }

    public function activate(){
        return true;
    }
    public function deactivate(){
        return true;
    }
    public function init()
    {

        bea('i18n')->load_plugin_textdomain();
        do_action('bea', $this);
    }
}