<?php

class SR_Table
{
    private $sport_base_page;
    public function __construct()
    {
        $this->sport_base_page = 358;
        add_filter('query_vars', array($this, 'query_vars'));
        add_filter('the_content', array($this, 'filter_the_content'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('init', array($this, 'register_rewrite_rule'));
        add_shortcode('sr_table', array($this, 'sr_table_shortcode'));
    }
    public function query_vars($vars)
    {
        $vars[] = 'sr_id';
        $vars[] = 'sr_name';
        return $vars;
    }
    public function register_rewrite_rule()
    {
        add_rewrite_rule('^sporto-bazes/([^/]+)/([^/]+)/?$', 'index.php?page_id=' . $this->sport_base_page . '&sr_id=$matches[1]&sr_name=$matches[2]', 'top');
    }
    public function show_404()
    {
        return $this->load_component('inc/sr_table/components/404');
    }
    public function filter_the_content($content)
    {
        $sr_id = absint(get_query_var('sr_id'));
        $out = '';
        if(is_page($this->sport_base_page) && $sr_id > 0) {
            $sr = $this->_request('/sportsbases/' . $sr_id);

            if($sr instanceof WP_REST_Response && !isset($sr->data['code'])) {
                $content = str_replace('[sport-base-title]', $sr->data['name'], $content);
                $out = $this->load_component('inc/sr_table/components/photos', ['data' => $sr->data['photos']]);
            } else {
                $content = str_replace('[sport-base-title]', __('Sporto bazė nerasta', 'sr'), $content);
                $out = $this->show_404();
            }
        } else {
            $content = str_replace('[sport-base-title]', __('Sporto bazė nerasta', 'sr'), $content);
            $out = $this->show_404();
        }

        $content = str_replace('[content]', $out, $content);
        return $content;
    }

    public function load_component($path, $args = null)
    {
        ob_start();
        include_once locate_template($path . '.php', true, false, $args);
        return ob_get_clean();
    }

    public function register_rest_routes()
    {
        register_rest_route('sport-register/v1', '/sportbases', array(
            'methods' => 'GET',
            'callback' => array($this, 'sport_bases_list'),
            'permission_callback' => '__return_true',
        ));
    }

    public function sport_bases_list(WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = sanitize_text_field($params['search']) ?? '';
        $order = $params['order'][0]['dir'] ?? 'asc';
        $orderColumn = $params['columns'][$params['order'][0]['column']]['data'] ?? 'id';

        $api_params = array(
            'page' => ($start / $length) + 1,
            'pageSize' => $length,
            'sort' => ($order === 'desc' ? '' : '-') . $orderColumn,
            //'fields' => 'id,huntingAreas,elderships,municipalities,dateTime,species,target',
            'populate' => '',
            'searchPublic' => $search
        );
        $query = http_build_query($api_params);
        $query = !empty($query) ? '?' . $query : '';

        $sr = $this->_request('/sportsbases' . $query);

        $data = (object) [];
        $data->recordsTotal = $sr->data['total'] ?? 0;
        $data->recordsFiltered = $sr->data['total'] ?? 0;
        $data->data = $sr->data['rows'];
        $data->draw = intval($params['draw']);
        return new WP_REST_Response($data, 200);
    }

    public function sr_table_shortcode($atts, $content = null)
    {
        $atts = shortcode_atts(
            array(
                'id' => '',
                'class' => '',
            ),
            $atts,
            'sr_table'
        );

        wp_enqueue_script('datatables-js', '//cdn.datatables.net/v/dt/dt-1.12.1/datatables.min.js', ['jquery'], '1.12.1', true);
        wp_enqueue_style('datatable-css', '//cdn.datatables.net/v/dt/dt-1.12.1/datatables.min.css');

        wp_enqueue_script('buttons-js', '//cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js', ['jquery'], '2.2.3', true);
        wp_enqueue_script('datatables-buttons-js', '//cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js', ['jquery'], '2.2.3', true);
        wp_enqueue_script('datatables--responsivejs', '//cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js', ['jquery'], '2.2.3', true);

        wp_enqueue_style('responsive-datatable-css', '//cdn.datatables.net/responsive/2.3.0/css/responsive.dataTables.min.css');
        wp_enqueue_style('buttons-datatable-css', '//cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css');

        wp_enqueue_style('sr-table-styles', SR_THEME_URL . '/inc/sr_table/sr_table.css', [], '1.0', 'all');
        $sr_table_js_ver  = date("ymd-His", filemtime(SR_THEME_DIR . '/inc/sr_table/sr_table.js'));
        wp_enqueue_script('sr-table-js', SR_THEME_URL . '/inc/sr_table/sr_table.js', ['jquery'], $sr_table_js_ver, true);

        $settings = get_option('settings', []);
        $sport_base_page = $settings['sport_base_page'] ?? 0;

        wp_localize_script(
            'sr-table-js',
            'sr_table_vars',
            array(
                'TABLE_ID' => $atts['id'],
                'THEME_URL' => SR_THEME_URL,
                'REST_URL' => rest_url(),
                'SPORT_REGISTER_API_URL' => SPORT_REGISTER_API_URL,
                'SPORT_BASE_URL' => get_permalink($sport_base_page),
                'I18N' => [
                    'READ_MORE' => __('Peržiūrėti', 'sr'),
                ]
            )
        );

        return '<div class="sr-table ' . $atts['class'] . '"><table id="' . $atts['id'] . '"></table></div>';
    }

    private function _request($api_endpoint)
    {
        $response = wp_remote_get(SPORT_REGISTER_API_URL . $api_endpoint);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return new WP_REST_Response(array('error' => $error_message), 500);
        }
        $body = wp_remote_retrieve_body($response);
        if (is_numeric($body)) {
            return new WP_REST_Response((int)$body, 200);
        }
        $data = json_decode($body, true);
        if (null === $data && strtolower($body) !== "null") {
            return new WP_REST_Response(array('error' => sprintf(__('Nepavyko gauti duomenų JSON formatu iš: %s', 'sr'), SPORT_REGISTER_API_URL . $api_endpoint), 500));
        }
        if (!is_array($data) && !is_object($data)) {
            $data = array($data);
        }

        return new WP_REST_Response($data, 200);
    }
}

new SR_Table();
