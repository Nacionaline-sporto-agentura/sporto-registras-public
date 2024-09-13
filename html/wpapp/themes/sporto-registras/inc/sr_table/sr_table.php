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
        } elseif (isset($var[$key])) {
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

            wp_localize_script(
                'sr-page-js',
                'objVars',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'map'=>[
                        'ico'=> SR_THEME_URL . '/assets/images/sr-pin-icon.png',
                        'ico_width'=> 40,
                        'ico_height'=> 46,
                        'zoom'=> 10,
                    ]
                )
            );
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
        if (is_page($this->sport_base_page) && $sr_id > 0) {
            $sr = $this->_request('/public/sportsBases/'. $sr_id);
            if ($sr instanceof WP_REST_Response && !isset($sr->data['code'])) {
                $page_content = $this->load_component('inc/sr_table/components/sport-base-page', ['data' => $sr->data]);

                $content = str_replace('[sport-base-title]', $sr->data['name'], $content);
                $content = str_replace('[content]', $page_content, $content);
            } else {
                $content = str_replace('[sport-base-title]', __('Sporto bazė nerasta', 'sr'), $content);
                $out = $this->show_404();
            }
        } elseif (is_page($this->organization_page) && $sr_id > 0) {
            $sr = $this->_request('/public/organizations/' . $sr_id);

            if ($sr instanceof WP_REST_Response && isset($sr->data['id'])) {

                $page_content = $this->load_component('inc/sr_table/components/organization-page', ['data' => $sr->data]);

                $content = str_replace('[organization-title]', $sr->data['name'], $content);
                $content = str_replace('[content]', $page_content, $content);
            } else {
                $content = str_replace('[organization-title]', __('Sporto organizacija nerasta', 'sr'), $content);
                $out = $this->show_404();
            }
        } else {
            if (is_page($this->sport_base_page)) {
                $content = str_replace('[sport-base-title]', __('Sporto bazė nerasta', 'sr'), $content);
            } elseif (is_page($this->organization_page)) {
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
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $sport = isset($params['sport']) ? $params['sport'] : '';

        $sportsTypes = $this->_request('/public/sportsTypes?pageSize=999&sort=name');
        $availableSports = $sportsTypes->data['rows'] ?? [];
        $sr = $this->_request('/sportsPersons/count/public');

        $order = isset($params['order']) ? $params['order'][0]['dir'] : 'asc';
        $orderColumn = isset($params['columns']) ? $params['columns'][$params['order'][0]['column']]['name'] : 'sportTypeName';

        $rows = $sr->data ?? [];

        if (!empty($sport)) {
            $sport_ids = (strpos($sport, '|') === false) ? [(int)$sport] : array_map('intval', explode('|', $sport));
            $filteredSportTypes = array_filter($availableSports, function ($sportType) use ($sport_ids) {
                return in_array($sportType['id'], $sport_ids, true);
            });
            $filteredSportTypeNames = array_column($filteredSportTypes, 'name');
        } else {
            $filteredSportTypeNames = [];
        }

        if (!empty($rows)) {
            foreach ($rows as $key => $row) {
                if (!empty($sport)) {
                    if (!in_array($row['sportTypeName'], $filteredSportTypeNames)) {
                        unset($rows[$key]);
                        continue;
                    }
                }
                $rows[$key] = array_merge([
                    'sportTypeName' => '-',
                    'coach' => '-',
                    'referee' => '-',
                    'amsInstructor' => '-',
                    'faSpecialist' => '-',
                    'faInstructor' => '-',
                    'athlete' => '-',
                ], $row);
            }
        }

        $totalRecords = count($rows);
        $rows = array_slice($rows, $start, $length);

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

        $sport = isset($params['sport']) ? $params['sport'] : '';
        $municipality = isset($params['municipality']) ? $params['municipality'] : '';
        $name = isset($params['name']) ? $params['name'] : '';
        $type = isset($params['type']) ? $params['type'] : '';

        $api_params = array(
            'page' => ($start / $length) + 1,
            'pageSize' => $length,
            'sort' => ($order === 'desc' ? '' : '-') . $orderColumn,
        );

        if (!empty($name)) {
            $api_params['query[name][$ilike]'] = '%'.$name.'%';
        }
        if (!empty($type)) {
            if (strpos($type, '|') === false) {
                $api_params['query[type][id][$in]'] = [$type];
            } else {
                $api_params['query[type][id][$in]'] = explode('|', $type);
            }
        }
        if (!empty($sport)) {
            if (strpos($sport, '|') === false) {
                $api_params['query[sportTypes][id][$in]'] = [$sport];
            } else {
                $api_params['query[sportTypes][id][$in]'] = explode('|', $sport);
            }
        }
        if (!empty($municipality)) {
            if (strpos($municipality, '|') === false) {
                $api_params['query[address][municipality.code][$in]'] = [$municipality];
            } else {
                $api_params['query[address][municipality.code][$in]'] = explode('|', $municipality);
            }
        }

        $query = http_build_query($api_params);
        $query = !empty($query) ? '?' . $query : '';
        $sr = $this->_request('/public/sportsBases' . $query);

        $rows = $sr->data['rows'] ?? [];
        $totalRecords = $sr->data['total'] ?? 0;

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
        $orderColumn = isset($params['columns']) ? $params['columns'][$params['order'][0]['column']]['data'] : 'name';


        $name = isset($params['name']) ? $params['name'] : '';
        $type = isset($params['type']) ? $params['type'] : '';
        $sport = isset($params['sport']) ? $params['sport'] : '';
        $support = isset($params['support']) ? $params['support'] : '';
        $nvo = isset($params['nvo']) ? $params['nvo'] : '';
        $nvs = isset($params['nvs']) ? $params['nvs'] : '';

        $api_params = array(
            'page' => ($start / $length) + 1,
            'pageSize' => $length,
            'sort' => ($order == 'asc' ? '' : '-').$orderColumn,
        );
        if (!empty($name)) {
            $api_params['query[name][$ilike]'] = '%'.$name.'%';
        }
        if (!empty($type)) {
            if (strpos($type, '|') === false) {
                $api_params['query[type][id][$in]'] = [$type];
            } else {
                $api_params['query[type][id][$in]'] = explode('|', $type);
            }
        }
        if (!empty($support)) {
            $api_params['query[hasBeneficiaryStatus]'] = true;
        }
        if (!empty($nvo)) {
            $api_params['query[nonGovernmentalOrganization]'] = true;
        }
        if (!empty($nvs)) {
            $api_params['query[nonFormalEducation]'] = true;
        }
        $query = http_build_query($api_params);
        $query = !empty($query) ? '?' . $query : '';

        $sr = $this->_request('/public/organizations' . $query);
        $rows = $sr->data['rows'] ?? [];
        $totalRecords = $sr->data['total'] ?? 0;

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


        if ($atts['id'] == 'sportsbases') {
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

  <button id="clearFilters">'.__('Išvalyti', 'sr').'</button>
</div>';
        } elseif ($atts['id'] == 'organizations') {
            $filter = '<div class="desc">
'.__('*NVO - Nevyriausybinė organizacija,   *NVŠ - Neformalusis vaikų švietimas', 'sr').'
</div><div id="filters">
  <div class="search-container"> 
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search search-icon"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    
    <input type="text" id="filter_organization_name" class="search-field" placeholder="'.__('Ieškoti pagal pavadinimą', 'sr').'" value="">
  </div>

  <div class="dropdown">
    <div class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
      <div id="filter_organization_type" class="filter-dropdown-toogle">'.__('Organizacijos tipas', 'sr').'</div>
      <span class="selected-count"></span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down dropdown-icon"><path d="m6 9 6 6 6-6"/></svg>
    </div>
    <nav class="dropdown-menu" aria-labelledby="dropdownToggle">
      <form id="filter_organization_type_form" method="get">
        '.$this->getOrganizationTypes().'
      </form>
    </nav>
  </div>

  <!--div class="dropdown">
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
  </div-->

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
        } elseif ($atts['id'] == 'sportpersons') {
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
        $sr = $this->_request('/public/sportsTypes?pageSize=999&sort=name');
        $html = '';
        if ($sr instanceof WP_REST_Response && isset($sr->data['rows'])) {
            foreach ($sr->data['rows'] as $sport) {
                $html .= '<label class="filter-label">
            <input type="checkbox" name="filter_sportbase_sport" value="'.$sport['id'].'" class="filter-checkbox">
            '.$sport['name'].'
            </label>';
            }
        }
        return $html;
    }
    private function getMunicipalities()
    {
        $body = json_encode([
            'filters' => []
        ]);
        $args = [
            'headers' => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
            'body'    => $body,
            'method'  => 'POST',
        ];
        $response = wp_remote_post('https://boundaries.biip.lt/v1/municipalities/search?sort_by=name&sort_order=asc&size=100', $args);
        $html = '';
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $municipalities = json_decode($response_body, true);
            foreach ($municipalities['items'] as $municipality) {
                $html .= '<label class="filter-label">
              <input type="checkbox" name="filter_sportbase_municipality" value="'.$municipality['code'].'" class="filter-checkbox">
              '.$municipality['name'].'
            </label>';
            }
        }
        return $html;
    }

    private function getOrganizationTypes()
    {
        $sr = $this->_request('/public/organizationTypes?pageSize=999&sort=name');
        $html = '';
        if ($sr instanceof WP_REST_Response && isset($sr->data['rows'])) {

            foreach ($sr->data['rows'] as $type) {
                $html .= '<label class="filter-label">
            <input type="checkbox" name="filter_sportbase_type" value="'.$type['id'].'" class="filter-checkbox">
            '.$type['name'].'
            </label>';
            }
        }
        return $html;
    }

    private function getSportbaseTypes()
    {
        $sr = $this->_request('/public/sportsBaseTypes?pageSize=999&sort=name');
        $html = '';
        if ($sr instanceof WP_REST_Response && isset($sr->data['rows'])) {
            foreach ($sr->data['rows'] as $type) {
                $html .= '<label class="filter-label">
            <input type="checkbox" name="filter_sportbase_type" value="'.$type['id'].'" class="filter-checkbox">
            '.$type['name'].'
            </label>';
            }
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
