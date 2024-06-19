<?php
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Utils;

if (! defined('ABSPATH')) {
    exit;
}

class Biip_Off_Canvas extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'biip_off_canvas';
    }

    public function get_title()
    {
        return esc_html__('BĮIP - Off Canvas', 'bea');
    }

    public function get_icon()
    {
        return 'eicon-table-of-contents';
    }

    public function get_categories()
    {
        return [ 'elements' ];
    }

    public function get_keywords()
    {
        return [ 'biip','offcanvas' ];
    }

    public function get_style_depends()
    {
        return [ 'biip_off_canvas' ];
    }

    public function get_script_depends()
    {
        return [ 'biip_off_canvas' ];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'additional_options',
            [
                'label' => esc_html__('Off canvas data', 'bea'),
            ]
        );

        $this->add_control(
            'menu_icon',
            [
                'label' => esc_html__('Menu icon', 'bea'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'bea bea-menu_black_24dp',
                    'library' => 'bea-custom',
                ],
            ]
        );

        $this->add_control(
            'close_icon',
            [
                'label' => __('Close icon', 'bea'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'bea bea-close_black_24dp',
                    'library' => 'bea-custom',
                ],
            ]
        );

        $this->add_control(
            'menu_icon_size',
            [
                'label' => esc_html__('Menu Icon Size', 'bea'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'size' => 32,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .biip-off-canvas-open .biip-icon-wrapper i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'close_icon_size',
            [
                'label' => __('Close Icon Size', 'bea'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'size' => 32,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .biip-off-canvas-close .biip-icon-wrapper i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content',
            [
                'label' => __('Content', 'bea'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __('Default content', 'bea'),
            ]
        );

        $this->end_controls_section();

    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        ?>
		<div class="biip-off-canvas-content">
			<a class="biip-off-canvas-close" style="display:none">
				<div class="biip-icon-wrapper">
					<?php \Elementor\Icons_Manager::render_icon($settings['close_icon'], [ 'aria-hidden' => 'true' ]); ?>
				</div>
			</a>
			<?php echo do_shortcode($settings['content']);?>
		</div>
		<a class="biip-off-canvas-open">
			<div class="biip-icon-wrapper">
				<?php \Elementor\Icons_Manager::render_icon($settings['menu_icon'], [ 'aria-hidden' => 'true' ]); ?>
			</div>
		</a>
		<div class="biip-off-canvas-overlay"></div>
	<?php
    }
}
