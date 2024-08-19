<?php

class BEA_Notifications
{
    private $hook;
    public function __construct()
    {
        if(is_admin()){
            add_action( 'admin_enqueue_scripts', array( &$this, 'maybe_add_admin_header' ) );
			add_action( 'bea_admin_header', array( &$this, 'add_admin_header' ) );
            add_filter( 'admin_body_class', array( &$this, 'admin_body_class' ) );
        }
    }
    public function maybe_add_admin_header( $screen ) {

		global $parent_file;
		if ( 'sr-admin' != $parent_file ) {
            return;
        }

		do_action( 'bea_admin_header' );
	}
    public function add_admin_header() {
        wp_enqueue_style('bea-admin-ui-css', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css', [], BEA_VERSION, false);
        wp_enqueue_style('bea', BEA_URI . 'assets/css/forms.css', array(), BEA_VERSION, 'all');
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
	}
    function redirect( $location, $status = 302, $x_redirect_by = 'BEA' ) {
        return wp_redirect( $location, $status, $x_redirect_by );
    }
    public function notice( $args, $type = '', $once = false, $key = null, $capability = true, $screen = null, $append = false ) {

        global $bea_notices;
    
        
        if ( true === $key ) {
            $capability = true;
            $key        = null;
        }
    
        if ( ! is_array( $args ) ) {
            $args = array(
                'text' => $args,
                'type' => in_array( $type, array( 'success', 'error', 'info', 'warning' ) ) ? $type : 'success',
                'once' => $once,
                'key'  => $key ? $key : uniqid(),
            );
        }
        
        if ( true === $capability ) {
            $capability = get_current_user_id();
            // no logged in user => only for admins
            if ( ! $capability ) {
                $capability = 'manage_options';
            }
        }
        
        $args = wp_parse_args(
            $args,
            array(
                'text'   => '',
                'type'   => 'success',
                'once'   => false,
                'key'    => uniqid(),
                'cb'     => null,
                'cap'    => $capability,
                'screen' => $screen,
            )
        );
        
        if ( empty( $args['key'] ) ) {
            $args['key'] = uniqid();
        }
    
        if ( is_numeric( $args['once'] ) && $args['once'] < 1600000000 ) {
            $args['once'] = time() + $args['once'];
        }
    
        $bea_notices = get_option( 'bea_notices' );
        if ( ! is_array( $bea_notices ) ) {
            $bea_notices = array();
        }
    
        if ( $append && isset( $bea_notices[ $args['key'] ] ) ) {
            $args['text'] = $bea_notices[ $args['key'] ]['text'] . '<br>' . $args['text'];
        }
        
        $bea_notices[ $args['key'] ] = array(
            'text'   => $args['text'],
            'type'   => $args['type'],
            'once'   => $args['once'],
            'cb'     => $args['cb'],
            'cap'    => $args['cap'],
            'screen' => $args['screen'],
        );
      
        update_option( 'bea_notices', $bea_notices );
        update_option( 'bea_notices_count', count( $bea_notices ) );
        
        return $args['key'];
    }

    public function remove_notice( $key ) {

        global $bea_notices;
    
        $bea_notices = get_option( 'bea_notices', array() );
    
        if ( isset( $bea_notices[ $key ] ) ) {
    
            unset( $bea_notices[ $key ] );
    
            update_option( 'bea_notices', $bea_notices );
            update_option( 'bea_notices_count', count( $bea_notices ) );
    
            do_action( 'bea_remove_notice', $key );
            do_action( 'bea_remove_notice_' . $key );
    
        }
    
        return true;
    }

    public function admin_body_class( $classes = '' ) {

		global $bea_notices;

		$count = get_option( 'bea_notices_count' );
		if ( ! $count ) {
			return $classes;
		}

		$bea_notices = get_option( 'bea_notices' );

		$screens              = wp_list_pluck( $bea_notices, 'screen' );
		$displayed_everywhere = array_filter( $screens, 'is_null' );
		if ( ! empty( $displayed_everywhere ) ) {
			$classes .= ' bea-has-notices';
		}

		return $classes;
	}

    public function save_admin_notices() {

		global $bea_notices;

		$notices = empty( $bea_notices ) ? null : (array) $bea_notices;
		$count   = ! empty( $notices ) ? count( $notices ) : 0;

		update_option( 'bea_notices', $notices );
		update_option( 'bea_notices_count', $count );
	}

    public function admin_notices() {

		global $bea_notices;

		$count = get_option( 'bea_notices_count' );
		if ( ! $count ) {
			return;
		}
		$bea_notices = get_option( 'bea_notices' );
        
		if ( ! $bea_notices ) {
			return;
		}

		$successes = array();
		$errors    = array();
		$infos     = array();
		$warnings  = array();
		$dismiss   = isset( $_GET['bea_remove_notice_all'] ) ? esc_attr( $_GET['bea_remove_notice_all'] ) : false;

		if ( ! is_array( $bea_notices ) ) {
			$bea_notices = array();
		}
		if ( isset( $_GET['bea_remove_notice'] ) && isset( $bea_notices[ $_GET['bea_remove_notice'] ] ) ) {
			unset( $bea_notices[ $_GET['bea_remove_notice'] ] );
		}

		$notices = array_reverse( $bea_notices, true );
       
		foreach ( $notices as $id => $notice ) {

			if ( isset( $notice['cap'] ) && ! empty( $notice['cap'] ) ) {

				// specific users or admin
				if ( is_numeric( $notice['cap'] ) ) {
					if ( get_current_user_id() != $notice['cap'] && ! current_user_can( 'manage_options' ) ) {
						continue;
					}

					// certain capability
				} elseif ( ! current_user_can( $notice['cap'] ) ) {
						continue;
				}
			}
			if ( isset( $notice['screen'] ) && ! empty( $notice['screen'] ) ) {
				$screen = get_current_screen();
				if ( ! in_array( $screen->id, (array) $notice['screen'] ) ) {
					continue;
				}
			}

			$type        = esc_attr( $notice['type'] );
			$dismissable = ! $notice['once'] || is_numeric( $notice['once'] );

			$classes = array( 'notice', 'bea-notice', 'notice-' . $type );
			if ( 'success' == $type ) {
				$classes[] = 'updated';
			}
			if ( 'error' == $type ) {
				$classes[] = 'error';
			}
			if ( $dismissable ) {
				$classes[] = 'bea-notice-dismissable';
			}

			$msg = '<div data-id="' . esc_attr( $id ) . '" id="bea-notice-' . esc_attr( $id ) . '" class="' . implode( ' ', $classes ) . '">';

			$text = ( isset( $notice['text'] ) ? $notice['text'] : '' );
			$text = isset( $notice['cb'] ) && function_exists( $notice['cb'] )
				? call_user_func( $notice['cb'], $text )
				: $text;

			if ( $text === false ) {
				continue;
			}
			if ( ! is_string( $text ) ) {
				$text = print_r( $text, true );
			}

			if ( 'error' == $type ) {
				$text = '<strong>' . $text . '</strong>';
			}

			$msg .= ( $text ? $text : '&nbsp;' );

			if ( $dismissable ) {
				$msg .= '<a class="notice-dismiss" title="' . esc_attr__( 'Atsisakyti šio pranešimo (spustelėkite Alt, kad atsisakytumėte visų pranešimų)', 'bea' ) . '" href="' . add_query_arg( array( 'bea_remove_notice' => $id ) ) . '">' . esc_attr__( 'Atsisakyti', 'bea' ) . '<span class="screen-reader-text">' . esc_attr__( 'Atsisakyti šio pranešimo (spustelėkite Alt, kad atsisakytumėte visų pranešimų)', 'bea' ) . '</span></a>';

				$bea_notices[ $id ]['seen'] = true;
				if ( is_numeric( $notice['once'] ) && (int) $notice['once'] - time() < 0 ) {
					unset( $bea_notices[ $id ] );
					if ( isset( $notice['seen'] ) ) {
						continue;
					}
				}
			} else {
				unset( $bea_notices[ $id ] );
			}

			$msg .= '</div>';
           
			if ( $notice['type'] == 'success' && $dismiss != 'success' ) {
				$successes[] = $msg;
			}

			if ( $notice['type'] == 'error' && $dismiss != 'error' ) {
				$errors[] = $msg;
			}

			if ( $notice['type'] == 'info' && $dismiss != 'info' ) {
				$infos[] = $msg;
			}

			if ( $notice['type'] == 'warning' && $dismiss != 'warning' ) {
				$warnings[] = $msg;
			}

			if ( 'success' == $dismiss && isset( $bea_notices[ $id ] ) ) {
				unset( $bea_notices[ $id ] );
			}

			if ( 'error' == $dismiss && isset( $bea_notices[ $id ] ) ) {
				unset( $bea_notices[ $id ] );
			}

			if ( 'info' == $dismiss && isset( $bea_notices[ $id ] ) ) {
				unset( $bea_notices[ $id ] );
			}

			if ( 'warning' == $dismiss && isset( $bea_notices[ $id ] ) ) {
				unset( $bea_notices[ $id ] );
			}
		}

		echo implode( '', $successes );
		echo implode( '', $errors );
		echo implode( '', $infos );
		echo implode( '', $warnings );

		add_action( 'shutdown', array( &$this, 'save_admin_notices' ) );
	}
}
