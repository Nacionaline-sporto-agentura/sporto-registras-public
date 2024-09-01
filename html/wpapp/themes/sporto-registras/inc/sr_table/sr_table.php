<?php

defined('ABSPATH') || exit;

class SR_Table
{
    private $sport_base_page;
    private $organization_page;
    public function __construct()
    {

        $settings = get_option('sr_settings', []);
        $this->sport_base_page = $settings['sport_bases_page_id'] ?? 0;
        $this->organization_page = $settings['sport_organization_page_id'] ?? 0;

        add_filter('query_vars', array($this, 'query_vars'));
        add_filter('the_content', array($this, 'filter_the_content'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('init', array($this, 'register_rewrite_rule'));
        add_shortcode('sr_table', array($this, 'sr_table_shortcode'));
    }
    public static function fix_var($var, $key)
    {
        if (isset($var[$key]) && is_array($var[$key])) {
            return isset($var[$key]['name']) ? $var[$key]['name'] : $var[$key]['plot_or_building_number'];
        } elseif(isset($var[$key])) {
            return $var[$key] ?? null;
        }
    }
    public static function format_address($raw)
    {
        $street = self::fix_var($raw, 'street');
        $house = self::fix_var($raw, 'house');
        $apartment = self::fix_var($raw, 'apartment');
        $city = self::fix_var($raw, 'city');
        $municipality = self::fix_var($raw, 'municipality');

        $address = (isset($street) ? $street.' ' : '').
            (isset($house) ? $house.(isset($apartment) ? '-'.$apartment : '') : '').
            (isset($city) ? ', '. $city : '').
            (isset($municipality) ? ', '.$municipality : '');
        return $address;
    }
    public function query_vars($vars)
    {
        $vars[] = 'sr_id';
        $vars[] = 'sr_name';
        return $vars;
    }
    public function enqueue_styles()
    {
        if (is_page($this->sport_base_page) || is_page($this->organization_page)) {
            $maplibre_css_ver = $maplibre_js_ver = '4.3.2';
            wp_enqueue_style('maplibre-styles', '//unpkg.com/maplibre-gl@'.$maplibre_js_ver.'/dist/maplibre-gl.css', [], $maplibre_js_ver, 'all');
            wp_enqueue_script('maplibre-js', '//unpkg.com/maplibre-gl@'.$maplibre_css_ver.'/dist/maplibre-gl.js', ['jquery'], $maplibre_css_ver, true);
            $sr_page_js_ver  = date("ymd-His", filemtime(SR_THEME_DIR . '/inc/sr_table/sr_page.js'));
            $sr_page_css_ver  = date("ymd-His", filemtime(SR_THEME_DIR . '/inc/sr_table/sr_page.css'));
            wp_enqueue_style('sr-page-styles', SR_THEME_URL . '/inc/sr_table/sr_page.css', [], $sr_page_css_ver, 'all');
            wp_enqueue_script('sr-page-js', SR_THEME_URL . '/inc/sr_table/sr_page.js', ['jquery'], $sr_page_js_ver, true);
        }
    }
    public function register_rewrite_rule()
    {
        add_rewrite_rule('^sporto-bazes/([^/]+)/([^/]+)/?$', 'index.php?page_id=' . $this->sport_base_page . '&sr_id=$matches[1]&sr_name=$matches[2]', 'top');
        add_rewrite_rule('^sporto-organizacijos/([^/]+)/([^/]+)/?$', 'index.php?page_id=' . $this->organization_page . '&sr_id=$matches[1]&sr_name=$matches[2]', 'top');
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
            $sr = $this->_request('/sportsBases/'. $sr_id .'/public');
            $types_fields = $this->_request('/types/sportsBases/spaces/typesAndFields/public');

            if($sr instanceof WP_REST_Response && !isset($sr->data['code'])) {
                $page_content = $this->load_component('inc/sr_table/components/sport-base-page', ['data' => $sr->data, 'types_fields' => $types_fields->data]);

                $content = str_replace('[sport-base-title]', $sr->data['name'], $content);
                $content = str_replace('[content]', $page_content, $content);
            } else {
                $content = str_replace('[sport-base-title]', __('Sporto bazė nerasta', 'sr'), $content);
                $out = $this->show_404();
            }
        } elseif(is_page($this->organization_page) && $sr_id > 0) {
            $sr = $this->_request('/tenants/organizations/' . $sr_id .'/public');
            $types_fields = $this->_request('/types/sportsBases/spaces/typesAndFields/public');

            if($sr instanceof WP_REST_Response && isset($sr->data['id'])) {

                $page_content = $this->load_component('inc/sr_table/components/organization-page', ['data' => $sr->data, 'types_fields' => $types_fields->data]);

                $content = str_replace('[organization-title]', $sr->data['name'], $content);
                $content = str_replace('[content]', $page_content, $content);
            } else {
                $content = str_replace('[organization-title]', __('Sporto organizacija nerasta', 'sr'), $content);
                $out = $this->show_404();
            }
        } else {
            if(is_page($this->sport_base_page)) {
                $content = str_replace('[sport-base-title]', __('Sporto bazė nerasta', 'sr'), $content);
            } elseif(is_page($this->organization_page)) {
                $content = str_replace('[organization-title]', __('Sporto organizacija nerasta', 'sr'), $content);
            }
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

        register_rest_route('sport-register/v1', '/organizations', array(
            'methods' => 'GET',
            'callback' => array($this, 'organizations_list'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('sport-register/v1', '/sportpersons', array(
            'methods' => 'GET',
            'callback' => array($this, 'sport_persons_list'),
            'permission_callback' => '__return_true',
        ));
    }

    public function sport_persons_list(WP_REST_Request $request)
    {
        $params = $request->get_query_params();


        $sport = isset($params['sport']) ? $params['sport'] : '';
        $sr = $this->_request('/sportsPersons/count/public');
        $rows = $sr->data['rows'] ?? [];
        $totalRecords = $sr->data['total'] ?? 0;

        if(!$fake_data = get_transient('fake_data_sportpersons') && (isset($_GET['debug']) && $_GET['debug'] == 'true')) {
            include SR_THEME_DIR . '/inc/sr_table/fake_data.php';
            $fake_data = renderSportPersonsDataset(100);
            set_transient('fake_data_sportpersons', $fake_data, 60 * 60);
        }

        if (isset($sport) && !empty($sport)) {
            // If there's only one sport to filter
            if (strpos($sport, '|') === false) {
                $fake_data = array_filter($fake_data, function ($item) use ($sport) {
                    // Check if sportTypeName matches the search term
                    return $item['sportTypeName'] === $sport;
                });
            } else {
                // If there are multiple sports to filter
                $sports = explode('|', $sport);
                $fake_data = array_filter($fake_data, function ($item) use ($sports) {
                    // Check if sportTypeName is in the list of sports
                    return in_array($item['sportTypeName'], $sports);
                });
            }
        }


        $rows = array_values($fake_data);
        $totalRecords = count($fake_data);

        $data = (object) [];
        $data->recordsTotal = $totalRecords ?? 0;
        $data->recordsFiltered = $totalRecords ?? 0;
        $data->data = $rows;
        $data->draw = isset($params['draw']) ? intval($params['draw']) : 0;

        return new WP_REST_Response($data, 200);
    }

    public function sport_bases_list(WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = isset($params['search']['value']) ? sanitize_text_field($params['search']['value']) : '';
        $order = isset($params['order']) ? $params['order'][0]['dir'] : 'asc';
        $orderColumn = isset($params['columns']) ? $params['columns'][$params['order'][0]['column']]['name'] : 'id';

        $accessibility = isset($params['accessibility']) ? $params['accessibility'] : '';
        $sport = isset($params['sport']) ? $params['sport'] : '';
        $municipality = isset($params['municipality']) ? $params['municipality'] : '';
        $name = isset($params['name']) ? $params['name'] : '';
        $type = isset($params['type']) ? $params['type'] : '';

        $api_params = array(
            'page' => ($start / $length) + 1,
            'pageSize' => $length,
            'sort' => ($order === 'desc' ? '' : '-') . $orderColumn,
            'query[name]' => $search
        );
        $query = http_build_query($api_params);
        $query = !empty($query) ? '?' . $query : '';
        $sr = $this->_request('/sportsBases/public' . $query);

        $rows = $sr->data['rows'] ?? [];
        $totalRecords = $sr->data['total'] ?? 0;

        // cache fake data for 1 hour
        if(!$fake_data = get_transient('fake_data_sportsbases') && (isset($_GET['debug']) && $_GET['debug'] == 'true')) {
            include SR_THEME_DIR . '/inc/sr_table/fake_data.php';
            $fake_data = renderSportbasesDataset(100);
            set_transient('fake_data_sportsbases', $fake_data, 60 * 60);
        }

        if(isset($sport) && !empty($sport)) {
            if(strpos($sport, '|') === false) {
                $fake_data = array_filter($fake_data, function ($item) use ($sport) {
                    return in_array($sport, array_column($item['sportTypes'], 'name'));
                });
            } else {
                $sports = explode('|', $sport);
                $fake_data = array_filter($fake_data, function ($item) use ($sports) {
                    foreach ($sports as $s) {
                        if (!in_array($s, array_column($item['sportTypes'], 'name'))) {
                            return false;
                        }
                    }
                    return true;
                });
            }
        }

        if (isset($municipality) && !empty($municipality)) {
            if (strpos($municipality, '|') === false) {
                $fake_data = array_filter($fake_data, function ($item) use ($municipality) {
                    return strpos($item['municipality']['name'], $municipality) !== false;
                });
            } else {
                $municipalities = explode('|', $municipality);
                $fake_data = array_filter($fake_data, function ($item) use ($municipalities) {
                    foreach ($municipalities as $m) {
                        if (strpos($item['municipality']['name'], $m) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }
        }

        if (isset($type) && !empty($type)) {
            if (strpos($type, '|') === false) {
                $fake_data = array_filter($fake_data, function ($item) use ($type) {
                    return strpos($item['type']['name'], $type) !== false;
                });
            } else {
                $types = explode('|', $type);
                $fake_data = array_filter($fake_data, function ($item) use ($types) {
                    foreach ($types as $t) {
                        if (strpos($item['type']['name'], $t) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }
        }

        if(isset($accessibility) && $accessibility == 1) {
            $fake_data = array_filter($fake_data, function ($item) {
                return $item['accessibility'] == 1;
            });
        }

        if(isset($name) && !empty($name)) {
            $fake_data = array_filter($fake_data, function ($item) use ($name) {
                return stripos($item['name'], $name) !== false;
            });
        }

        $rows = array_slice($fake_data, $start, 10);
        $totalRecords = count($fake_data);

        $data = (object) [];
        $data->recordsTotal = $totalRecords ?? 0;
        $data->recordsFiltered = $totalRecords ?? 0;
        $data->data = $rows;
        $data->draw = isset($params['draw']) ? intval($params['draw']) : 0;
        return new WP_REST_Response($data, 200);
    }

    public function organizations_list(WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = isset($params['search']['value']) ? sanitize_text_field($params['search']['value']) : '';
        $order = isset($params['order']) ? $params['order'][0]['dir'] : 'asc';
        $orderColumn = isset($params['columns']) ? $params['columns'][$params['order'][0]['column']]['data'] : 'id';


        $name = isset($params['name']) ? $params['name'] : '';
        $type = isset($params['type']) ? $params['type'] : '';
        $sport = isset($params['sport']) ? $params['sport'] : '';
        $support = isset($params['support']) ? $params['support'] : '';
        $nvo = isset($params['nvo']) ? $params['nvo'] : '';
        $nvs = isset($params['nvs']) ? $params['nvs'] : '';

        $api_params = array(
            'page' => ($start / $length) + 1,
            'pageSize' => $length,
            'query[name]' => $search,
            'sort' => ($order == 'asc' ? '' : '-').$orderColumn,
        );

        $query = http_build_query($api_params);
        $query = !empty($query) ? '?' . $query : '';
        $sr = $this->_request('/tenants/organizations/public' . $query);
        $rows = $sr->data['rows'] ?? [];
        $totalRecords = $sr->data['total'] ?? 0;

        // cache fake data for 1 hour
        if(!$fake_data = get_transient('fake_data_organizations') && (isset($_GET['debug']) && $_GET['debug'] == 'true')) {
            include SR_THEME_DIR . '/inc/sr_table/fake_data.php';
            $fake_data = renderOrganizationsDataset(100);
            set_transient('fake_data_organizations', $fake_data, 60 * 60);
        }

        if(isset($sport) && !empty($sport)) {
            if(strpos($sport, '|') === false) {
                $fake_data = array_filter($fake_data, function ($item) use ($sport) {
                    return in_array($sport, array_column($item['sports'], 'name'));
                });
            } else {
                $sports = explode('|', $sport);
                $fake_data = array_filter($fake_data, function ($item) use ($sports) {
                    foreach ($sports as $s) {
                        if (!in_array($s, array_column($item['sports'], 'name'))) {
                            return false;
                        }
                    }
                    return true;
                });
            }
        }

        if (isset($municipality) && !empty($municipality)) {
            if (strpos($municipality, '|') === false) {
                $fake_data = array_filter($fake_data, function ($item) use ($municipality) {
                    return strpos($item['address'], $municipality) !== false;
                });
            } else {
                $municipalities = explode('|', $municipality);
                $fake_data = array_filter($fake_data, function ($item) use ($municipalities) {
                    foreach ($municipalities as $m) {
                        if (strpos($item['address'], $m) !== false) {
                            return true; // Bent viena savivaldybė atitinka
                        }
                    }
                    return false; // Jei jokios savivaldybės neatitinka
                });
            }
        }

        if (isset($type) && !empty($type)) {
            if (strpos($type, '|') === false) {
                $fake_data = array_filter($fake_data, function ($item) use ($type) {
                    return strpos($item['type']['name'], $type) !== false;
                });
            } else {
                $types = explode('|', $type);
                $fake_data = array_filter($fake_data, function ($item) use ($types) {
                    foreach ($types as $t) {
                        if (strpos($item['type']['name'], $t) !== false) {
                            return true; // Bent viena rūšis atitinka
                        }
                    }
                    return false; // Jei jokios rūšies neatitinka
                });
            }
        }

        if(isset($support) && $support == 1) {
            $fake_data = array_filter($fake_data, function ($item) {
                return $item['support'] == 1;
            });
        }
        if(isset($nvo) && $nvo == 1) {
            $fake_data = array_filter($fake_data, function ($item) {
                return $item['nvo'] == 1;
            });
        }
        if(isset($nvs) && $nvs == 1) {
            $fake_data = array_filter($fake_data, function ($item) {
                return $item['nvs'] == 1;
            });
        }

        if(isset($name) && !empty($name)) {
            $fake_data = array_filter($fake_data, function ($item) use ($name) {
                return stripos($item['name'], $name) !== false;
            });
        }

        $rows = array_slice($fake_data, $start, 10);
        $totalRecords = count($fake_data);


        $data = (object) [];
        $data->recordsTotal = $totalRecords ?? 0;
        $data->recordsFiltered = $totalRecords ?? 0;
        $data->data = $rows;
        $data->draw = isset($params['draw']) ? intval($params['draw']) : 0;
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

        $sr_table_css_ver  = date("ymd-His", filemtime(SR_THEME_DIR . '/inc/sr_table/sr_table.css'));
        wp_enqueue_style('sr-table-styles', SR_THEME_URL . '/inc/sr_table/sr_table.css', [], $sr_table_css_ver, 'all');
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
                'SPORT_REGISTER_API_URL' => SPORT_REGISTER_API_URL.'/public',
                'SPORT_BASE_URL' => get_permalink($sport_base_page),
                'I18N' => [
                    'READ_MORE' => __('Peržiūrėti', 'sr'),
                ]
            )
        );
        $filter = '';
        if($atts['id'] == 'sportsbases') {
            $filter = '<div id="filters">
  <div class="search-container"> 
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search search-icon"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    
    <input type="text" id="filter_sportbase_name" class="search-field" placeholder="'.__('Ieškoti pagal pavadinimą', 'sr').'" value="">
  </div>

  <div class="dropdown">
    <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
      <div id="filter_sportbase_type" class="filter-dropdown-toogle">Sporto bazės rūšis</div>
      <span class="selected-count"></span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
    </div>
    <nav class="dropdown-menu" aria-labelledby="dropdownToggle">
      <form id="filter_sportbase_type_form" method="get">
        '.$this->getSportbaseTypes().'
      </form>
    </nav>
  </div>

  <div class="dropdown">
    <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
      <div id="filter_sportbase_sport"  class="filter-dropdown-toogle">Sporto šaka</div>
      <span class="selected-count"></span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
    </div>
    <nav class="dropdown-menu" aria-labelledby="dropdownToggle">
      <form id="filter_sportbase_sport_form" method="get">
        '.$this->getSports().'
      </form>
    </nav>
  </div>

  <div class="dropdown">
    <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
      <div id="filter_sportbase_municipality"  class="filter-dropdown-toogle">Savivaldybė</div>
      <span class="selected-count"></span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
    </div>
    <nav class="dropdown-menu" aria-labelledby="dropdownToggle">
      <form id="filter_sportbase_municipality_form" method="get">
        '.$this->getMunicipalities().'
      </form>
    </nav>
  </div>

  <div class="checkbox-container">
    <input type="checkbox" id="filter_sportbase_accessility" name="filter_sportbase_accessility" class="custom-checkbox" value="1">
    <label for="filter_sportbase_accessility" class="custom-label">'.__('Pritaikyta negalią turintiems asmenims', 'sr').'</label>
  </div>

  <button id="clearFilters">'.__('Išvalyti', 'sr').'</button>
</div>';
        } elseif($atts['id'] == 'organizations') {
            $filter = '<div id="filters">
  <div class="search-container"> 
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search search-icon"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    
    <input type="text" id="filter_organization_name" class="search-field" placeholder="'.__('Ieškoti pagal pavadinimą', 'sr').'" value="">
  </div>

  <div class="dropdown">
    <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
      <div id="filter_organization_type" class="filter-dropdown-toogle">'.__('Sporto bazės rūšis', 'sr').'</div>
      <span class="selected-count"></span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
    </div>
    <nav class="dropdown-menu" aria-labelledby="dropdownToggle">
      <form id="filter_organization_type_form" method="get">
        '.$this->getOrganizationTypes().'
      </form>
    </nav>
  </div>

  <div class="dropdown">
    <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
      <div id="filter_organization_sport"  class="filter-dropdown-toogle">'.__('Sporto šaka', 'sr').'</div>
      <span class="selected-count"></span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
    </div>
    <nav class="dropdown-menu" aria-labelledby="dropdownToggle">
      <form id="filter_organization_sport_form" method="get">
        '.$this->getSports().'
      </form>
    </nav>
  </div>

  <div class="checkbox-container">
    <input type="checkbox" id="filter_organization_support" name="filter_organization_support" class="custom-checkbox" value="1">
    <label for="filter_organization_support" class="custom-label">'.__('Paramos gavėjas', 'sr').'</label>
  </div>

  <div class="checkbox-container">
    <input type="checkbox" id="filter_organization_nvo" name="filter_organization_nvo" class="custom-checkbox" value="1">
    <label for="filter_organization_nvo" class="custom-label">'.__('Atitinka NVO reikalavimus', 'sr').'</label>
  </div>

  <div class="checkbox-container">
    <input type="checkbox" id="filter_organization_nvs" name="filter_organization_nvs" class="custom-checkbox" value="1">
    <label for="filter_organization_nvs" class="custom-label">'.__('Akredituota NVŠ programa', 'sr').'</label>
  </div>

  <button id="clearFilters">'.__('Išvalyti', 'sr').'</button>
</div>';
        } elseif($atts['id'] == 'sportpersons') {
            $filter = '<div class="desc">
'.__('*AMS - Aukšto meistriškumo sporto,   *FA - Fizinio aktyvumo', 'sr').'
</div><div id="filters">
  <div class="dropdown">
    <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
      <div id="filter_sportpersons_sport"  class="filter-dropdown-toogle">'.__('Sporto šaka', 'sr').'</div>
      <span class="selected-count"></span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
    </div>
    <nav class="dropdown-menu" aria-labelledby="dropdownToggle">
      <form id="filter_sportpersons_sport_form" method="get">
        '.$this->getSports().'
      </form>
    </nav>
  </div><button id="clearFilters">'.__('Išvalyti', 'sr').'</button>
</div>';
        }

        return $filter.'<div class="table-responsive sr-table ' . $atts['class'] . '"><table id="' . $atts['id'] . '"></table></div>';
    }
    private function getSports()
    {
        $sports = ['Krepšinis', 'Futbolas', 'Plaukimas', 'Tenisas', 'Lengvoji atletika', 'Golfas', 'Volejbolas'];
        $html = '';
        foreach($sports as $sport) {
            $html .= '<label class="filter-label">
          <input type="checkbox" name="filter_sportbase_sport" value="'.$sport.'" class="filter-checkbox">
          '.$sport.'
        </label>';
        }
        return $html;
    }
    private function getMunicipalities()
    {
        $municipalities = [
          "Alytaus m. sav.",
          "Alytaus r. sav.",
          "Anykščių r. sav.",
          "Birštono sav.",
          "Biržų r. sav.",
          "Druskininkų sav.",
          "Elektrėnų sav.",
          "Ignalinos r. sav.",
          "Jonavos r. sav.",
          "Joniškio r. sav.",
          "Jurbarko r. sav.",
          "Kaišiadorių r. sav.",
          "Kalvarijos sav.",
          "Kauno m. sav.",
          "Kauno r. sav.",
          "Kazlų Rūdos sav.",
          "Kėdainių r. sav.",
          "Kelmės r. sav.",
          "Klaipėdos m. sav.",
          "Klaipėdos r. sav.",
          "Kretingos r. sav.",
          "Kupiškio r. sav.",
          "Lazdijų r. sav.",
          "Marijampolės sav.",
          "Mažeikių r. sav.",
          "Molėtų r. sav.",
          "Neringos sav.",
          "Pagėgių sav.",
          "Pakruojo r. sav.",
          "Palangos m. sav.",
          "Panevėžio m. sav.",
          "Panevėžio r. sav.",
          "Pasvalio r. sav.",
          "Plungės r. sav.",
          "Prienų r. sav.",
          "Radviliškio r. sav.",
          "Raseinių r. sav.",
          "Rietavo sav.",
          "Rokiškio r. sav.",
          "Skuodo r. sav.",
          "Šakių r. sav.",
          "Šalčininkų r. sav.",
          "Šiaulių m. sav.",
          "Šiaulių r. sav.",
          "Šilalės r. sav.",
          "Šilutės r. sav.",
          "Širvintų r. sav.",
          "Švenčionių r. sav.",
          "Tauragės r. sav.",
          "Telšių r. sav.",
          "Trakų r. sav.",
          "Ukmergės r. sav.",
          "Utenos r. sav.",
          "Varėnos r. sav.",
          "Vilkaviškio r. sav.",
          "Vilniaus m. sav.",
          "Vilniaus r. sav.",
          "Visagino sav.",
          "Zarasų r. sav."
    ];
        $html = '';
        foreach($municipalities as $municipality) {
            $html .= '<label class="filter-label">
          <input type="checkbox" name="filter_sportbase_municipality" value="'.$municipality.'" class="filter-checkbox">
          '.$municipality.'
        </label>';
        }
        return $html;
    }

    private function getOrganizationTypes()
    {
        $types = ['Sporto organizacijos tipas', 'Fizinio aktyvumo veiklas vykdanti organizacija', 'Savivaldybės sporto ir (ar) švietimo įstaiga', 'Skėtinė sporto organizacija', 'Sporto klubas', 'Sporto šakos federacija', 'Kita'];
        $html = '';
        foreach($types as $type) {
            $html .= '<label class="filter-label">
          <input type="checkbox" name="filter_sportbase_type" value="'.$type.'" class="filter-checkbox">
          '.$type.'
        </label>';
        }
        return $html;
    }

    private function getSportbaseTypes()
    {
        $types = ['Aerodromai', 'Automobilių trasos', 'Motociklų trasos', 'Futbolo aikštės', 'Krepšinio aikštės'];
        $html = '';
        foreach($types as $type) {
            $html .= '<label class="filter-label">
          <input type="checkbox" name="filter_sportbase_type" value="'.$type.'" class="filter-checkbox">
          '.$type.'
        </label>';
        }
        return $html;
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
            return new WP_REST_Response(array('error' => sprintf(__('Nepavyko gauti duomenų JSON formatu iš: %s', 'sr'), SPORT_REGISTER_API_URL.'/public'. $api_endpoint), 500));
        }
        if (!is_array($data) && !is_object($data)) {
            $data = array($data);
        }

        return new WP_REST_Response($data, 200);
    }
}

new SR_Table();
