<?php 
class BEA_i18n{
    public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'bea',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
}