<?php
/**
 * Plugin Name:       BĮIP Elementor addons
 * Plugin URI:        https://www.pepa.lt
 * Description:       Elementor funkcijų išplėtimas
 * Version:           1.0.0
 * Author:            Petras Pauliūnas
 * Author URI:        mailto:petras.pauliunas@am.lt
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bea
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( defined( 'BEA_VERSION' ) || ! defined( 'ABSPATH' ) ) {
	return;
}

define('BEA_VERSION', '1.0.0');
define('BEA_BUILT', 100 );
define('BEA_FILE', __FILE__);
define('BEA_DIR', plugin_dir_path( __FILE__ ));
define('BEA_URI', plugin_dir_url( __FILE__ ));
define('BEA_SLUG', basename( BEA_DIR ) . '/' . basename( __FILE__ ));

function bea($subclass = null)
{
    global $bea;

    $args     = func_get_args();
    $subclass = array_shift($args);

    if (is_null($subclass) || ! is_string($subclass)) {
        return $bea;
    }

    return call_user_func_array(array( $bea, $subclass ), $args);
}

require BEA_DIR . 'classes/bea.class.php';

global $bea;
$bea = new BEA();