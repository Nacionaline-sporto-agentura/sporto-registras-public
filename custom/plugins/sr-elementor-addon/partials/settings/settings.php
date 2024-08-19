<?php if (!defined('WPINC')) {
    die;
}?>
<div class="wrap">
    <form id="bea-settings-form" method="post"  class="p-form needs-validation" novalidate>
        <h2><?php _e('Sporto registras nustatymai', 'bea');?></h2>
        <?php wp_nonce_field('update', 'sr_settings_field'); ?>
        <div class="form-wrapper mb-3">
            <h3><?php _e('Puslapio nuorodos', 'bea');?></h3>
            <div class="mb-3"><?php _e('Čia apibrėžti puslapio nuorodas, kurios bus naudojamos atitinkamo veiksmo nuorodai generuoti.', 'bea');?></div>
            <div class="mb-3">
                <label for="sr_settings_sport_bases_page_id" class="p-form-label"><?php _e('Sporto bazės vidinis puslapis', 'bea');?></label>
<?php
$args = array(
    'name'             => 'sr_settings[sport_bases_page_id]',
    'selected'         => (isset($settings['sport_bases_page_id']) && intval($settings['sport_bases_page_id'])) ? intval($settings['sport_bases_page_id']) : 0,
    'show_option_none' => __('Prašome nurodyti sporto bazės vidinio puslapio nuorodą', 'bea'),
    'class'            => 'p-form-control',
    'id'               => 'sr_settings_sport_bases_page_id'
);
wp_dropdown_pages($args);
?>
                <div class="invalid-feedback"><?php _e('Prašome nurodyti sporto bazės vidinio puslapio nuorodą', 'bea');?></div>
                <?php if(isset($settings['sport_bases_page_id'])) {?><p class="description"><a href="<?php echo get_permalink($settings['sport_bases_page_id'] ?? '');?>"><?php echo get_permalink($settings['sport_bases_page_id'] ?? '');?></a></p><?php } ?>
            </div>

            <div class="mb-3">
                <label for="sr_settings_sport_organization_page_id" class="p-form-label"><?php _e('Sporto organizacijos vidinis puslapis', 'bea');?></label>
<?php
$args = array(
    'name'             => 'sr_settings[sport_organization_page_id]',
    'selected'         => (isset($settings['sport_organization_page_id']) && intval($settings['sport_organization_page_id'])) ? intval($settings['sport_organization_page_id']) : 0,
    'show_option_none' => __('Prašome nurodyti sporto organizacijos vidinio puslapio nuorodą', 'bea'),
    'class'            => 'p-form-control',
    'id'               => 'sr_settings_sport_organization_page_id'
);
wp_dropdown_pages($args);
?>
                <div class="invalid-feedback"><?php _e('Prašome nurodyti sporto organizacijos vidinio puslapio nuorodą', 'bea');?></div>
                <?php if(isset($settings['sport_organization_page_id'])) {?><p class="description"><a href="<?php echo get_permalink($settings['sport_organization_page_id'] ?? '');?>"><?php echo get_permalink($settings['sport_organization_page_id'] ?? '');?></a></p><?php } ?>
            </div>

        </div>    
        
        <input type="hidden" name="action" value="update" />
        <?php submit_button(__('Saugoti', 'bea')); ?>
    </form>
    <div class="clear"></div>
</div>