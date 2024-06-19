<?php
defined('ABSPATH') || exit;

class SR_Frontpage
{
    private $sport_base_page;
    public function __construct()
    {
        add_shortcode('sr_tab', array($this, 'sr_tab_shortcode'));
        add_shortcode('sr_map', array($this, 'sr_map_shortcode'));
    }

    public function sr_tab_shortcode($atts)
    {
        // Attributes
        $atts = shortcode_atts(
            array(
                'color' => 'black',
                'key' => '',
                'count' => 0,
                'href' => '#',
            ),
            $atts,
            'sr_tab'
        );

        $url = SPORT_REGISTER_API_URL.'/sportsRegister/count';
        $response = wp_remote_get($url);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        if (!empty($data) && isset($data->{$atts['key']})) {
            $atts['count'] = $data->{$atts['key']};
            if ($atts['count'] > 1000) {
                $atts['count'] = round($atts['count'] / 1000, 1) . ' tūkst.';
            } else {
                $atts['count'] = $atts['count'] . ' vnt.';
            }
        }
        return '<div class="sr-tab"><a href="' . $atts['href'] . '" class="sr-link"><span class="sr-label">' . $atts['count'] . '</span><span class="sr-arrow arrow-' . $atts['color'] . '"></span></a></div>';
    }

    public function sr_map_shortcode($atts)
    {
        // Attributes
        $atts = shortcode_atts(
            array(
                'color' => 'black',
                'key' => '',
                'count' => 0,
                'href' => '#',
            ),
            $atts,
            'sr_map'
        );

        $maplibre_css_ver = $maplibre_js_ver = '4.3.2';
        wp_enqueue_style('maplibre-styles', '//unpkg.com/maplibre-gl@'.$maplibre_js_ver.'/dist/maplibre-gl.css', [], $maplibre_js_ver, 'all');
        wp_enqueue_script('maplibre-js', '//unpkg.com/maplibre-gl@'.$maplibre_css_ver.'/dist/maplibre-gl.js', ['jquery'], $maplibre_css_ver, true);
        $sr_frontpage_js_ver  = date("ymd-His", filemtime(SR_THEME_DIR . '/inc/sr_frontpage/sr_frontpage.js'));
        $sr_frontpage_css_ver  = date("ymd-His", filemtime(SR_THEME_DIR . '/inc/sr_frontpage/sr_frontpage.css'));
        wp_enqueue_style('sr-frontpage-styles', SR_THEME_URL . '/inc/sr_frontpage/sr_frontpage.css', [], $sr_frontpage_css_ver, 'all');
        wp_enqueue_script('sr-frontpage-js', SR_THEME_URL . '/inc/sr_frontpage/sr_frontpage.js', ['jquery'], $sr_frontpage_js_ver, true);

        return '<div class="sr-map__wrapper"><div id="sr-map"></div></div>';
    }
}
new SR_Frontpage();