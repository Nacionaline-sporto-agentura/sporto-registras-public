<?php defined('ABSPATH') || exit;?>
<?php
if(empty($args['data'])) {
    return;
}
$settings = get_option('sr_settings', []);
$sport_bases_page_id = $settings['sport_bases_page_id'] ?? 0;
$sportbases_page = wp_get_post_parent_id($sport_bases_page_id);


$foundedAt = isset($args['data']['data']['foundedAt'])? date('Y', strtotime($args['data']['data']['foundedAt'])) : null;
$updatedAt = isset($args['data']['data']['foundedAt'])? date('Y-m-d', strtotime($args['data']['data']['foundedAt'])) : null;
$organization = [
    'id'=> isset($args['data']['id'])? $args['data']['id'] : null,
    'name'=> isset($args['data']['name'])? $args['data']['name'] : null,
    'code'=>isset($args['data']['code'])? $args['data']['code'] : null,
    'address'=>isset($args['data']['address'])? $args['data']['address'] : null,
    'email'=>isset($args['data']['email'])? $args['data']['email'] : null,
    'phone'=>isset($args['data']['phone'])? $args['data']['phone'] : null,
    'webPage'=>isset($args['data']['data']['url'])? $args['data']['data']['url'] : null,
    'foundedAt'=>$foundedAt,
    'updatedAt'=>$updatedAt,
    'legalForm'=>isset($args['data']['legalForm']['name'])? $args['data']['legalForm']['name'] : null,
    'type'=>isset($args['data']['type']['name'])? $args['data']['type']['name'] : null,
    'nonFormalEducation'=>isset($args['data']['data']['nonFormalEducation'])? $args['data']['data']['nonFormalEducation'] : null,
    'hasBeneficiaryStatus'=>isset($args['data']['data']['hasBeneficiaryStatus'])? $args['data']['data']['hasBeneficiaryStatus'] : null,
    'nonGovernmentalOrganization'=>isset($args['data']['data']['nonGovernmentalOrganization'])? $args['data']['data']['nonGovernmentalOrganization'] : null,
];

if(!function_exists('get_construction_date')) {
    function get_construction_date($publicSpaces)
    {
        $constructionDates = [];
        foreach($publicSpaces as $publicSpace) {
            if(!empty($publicSpace['constructionDate'])) {
                $constructionDates[] = $publicSpace['constructionDate'];
            }
        }
        if(!empty($constructionDates)) {
            return date('Y', strtotime(min($constructionDates)));
        }
        return null;
    }
}

if(!function_exists('fix_url')) {
    function fix_url($url)
    {
        if (!preg_match("/^https?:\/\//", $url)) {
            $url = "https://" . $url;
        }
        return $url;
    }
}
if(!function_exists('fix_var')) {
    function fix_var($var)
    {
        if (isset($var) && is_array($var)) {
            return isset($var['name'])? $var['name'] : $var['plot_or_building_number'];
        }else{
            return isset($var)? $var : '';
        }
        
    }
}
$organizationSportTypes = [];
foreach($args['data']['sportsBases'] as $i => $sportBase) {
    
    $address = SR_Table::format_address($sportBase['address']);

    $sportTypes = [];
    foreach($sportBase['sportTypes'] as $sportType) {
        if(empty($sportType['name'])){
            continue;
        }
        $name = $sportType['name'];
        $sportTypes[] = $name;
        $organizationSportTypes[$name] = 1;
    }
    $sport_base_id = $sportBase['id'] ?? '';
    $sport_base_name = $sportBase['name'] ?? '';
    $sport_bases[] = [
        'id'=>$sport_base_id,
        'name' => $sport_base_name,
        'address' => $address,
        'sport_types' => $sportTypes,
    ];
}
$organizationSportTypes = array_keys($organizationSportTypes);
?>

<div class="sport-base__address"><span class="sport-base__address__item"><span class="sport-base__address__icon"></span>
        <?php echo $organization['address'];?></span> <span class="sport-base__address__item"><span
            class="sport-base__code__icon"></span> <?php _e('Įm.k.','sr');?> <?php echo $organization['code'];?></span></div>
<ul class="sport-base__types"><?php echo !empty($organizationSportTypes) ? '<li>'.implode('</li><li>', $organizationSportTypes).'</li>' : '';?>
</ul>

<div class="sport-base__data">
    <?php if(!empty($sport_bases)) {?>
    <div class="sport-base__wrapper">
        <?php foreach($sport_bases as $sport_base) {  ?>
        <a class="sport-base__space" href="<?php echo get_the_permalink($sportbases_page).$sport_base['id'].'/'.sanitize_title($sport_base['name']); ?>">
            <div class="sport-base__space__heading">
                <h3 class="sport-base__space-title"><?php echo $sport_base['name'];?></h3>
            </div>
            <div class="sport-base__address"><span class="sport-base__address__item"><span class="sport-base__address__icon"></span>
        <?php echo $sport_base['address'];?></span></span></div>
            <div class="sport-base__space__meta">
                <?php if(!empty($sport_base['sport_types'])) { ?>
                <ul class="sport-base__types">
                    <?php foreach($sport_base['sport_types'] as $sportType) { ?>
                    <li><?php echo $sportType;?></li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </a>
        <?php } ?>
    </div>
    <?php }else{ ?>
        <div class="sport-base__wrapper">
            <div class="sport-base__space">
            <?php echo __('Ši organizacija neturi prisikirtų sporto bazių.','sr');?>
            </div>
        </div>
    <?php } ?>
    <div class="sport-base__manger">
        <?php if(!empty($organization)) { ?>
        <div class="sport-base__manger-contacts">
            
        <?php if(!empty($organization['email'])) {?>
            <div class="sport-base__manger-email">
                <span class="sport-base__ico sport-base__ico-envelope"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.666 3.33334H3.33268C2.41221 3.33334 1.66602 4.07954 1.66602 5.00001V15C1.66602 15.9205 2.41221 16.6667 3.33268 16.6667H16.666C17.5865 16.6667 18.3327 15.9205 18.3327 15V5.00001C18.3327 4.07954 17.5865 3.33334 16.666 3.33334Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18.3327 5.83334L10.8577 10.5833C10.6004 10.7445 10.3029 10.83 9.99935 10.83C9.69575 10.83 9.39829 10.7445 9.14102 10.5833L1.66602 5.83334" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg> </span> <a href="mailto:<?php echo $organization['email'];?>"><?php echo $organization['email'];?></a></div>
<?php } ?>

<?php if(!empty($organization['phone'])){?>
            <div class="sport-base__manage-phone"><span class="sport-base__ico sport-base__ico-phone"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_636_4964)"><path d="M18.3332 14.1V16.6C18.3341 16.8321 18.2866 17.0618 18.1936 17.2745C18.1006 17.4871 17.9643 17.678 17.7933 17.8349C17.6222 17.9918 17.4203 18.1112 17.2005 18.1856C16.9806 18.2599 16.7477 18.2876 16.5165 18.2667C13.9522 17.988 11.489 17.1118 9.32486 15.7083C7.31139 14.4289 5.60431 12.7218 4.32486 10.7083C2.91651 8.53433 2.04007 6.05916 1.76653 3.48333C1.7457 3.25288 1.77309 3.02063 1.84695 2.80135C1.9208 2.58207 2.03951 2.38057 2.1955 2.20968C2.3515 2.03879 2.54137 1.90225 2.75302 1.80876C2.96468 1.71527 3.19348 1.66688 3.42486 1.66666H5.92486C6.32928 1.66268 6.72136 1.80589 7.028 2.0696C7.33464 2.33332 7.53493 2.69953 7.59153 3.09999C7.69705 3.90005 7.89274 4.6856 8.17486 5.44166C8.28698 5.73993 8.31125 6.06409 8.24478 6.37573C8.17832 6.68737 8.02392 6.97342 7.79986 7.19999L6.74153 8.25833C7.92783 10.3446 9.65524 12.072 11.7415 13.2583L12.7999 12.2C13.0264 11.9759 13.3125 11.8215 13.6241 11.7551C13.9358 11.6886 14.2599 11.7129 14.5582 11.825C15.3143 12.1071 16.0998 12.3028 16.8999 12.4083C17.3047 12.4654 17.6744 12.6693 17.9386 12.9812C18.2029 13.2931 18.3433 13.6913 18.3332 14.1Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</g><defs><clipPath id="clip0_636_4964"><rect width="20" height="20" fill="white"/></clipPath></defs></svg>
                </span> <a href="tel:<?php echo $organization['phone'];?>"><?php echo $organization['phone'];?></a></div>
                <?php } ?>

                <?php if(!empty($organization['foundedAt'])) {?>
                <div class="sport-base__manage-foundedAt"><span class="sport-base__ico sport-base__ico-foundedAt"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M15.8333 3.33333H4.16667C3.24619 3.33333 2.5 4.07952 2.5 4.99999V16.6667C2.5 17.5871 3.24619 18.3333 4.16667 18.3333H15.8333C16.7538 18.3333 17.5 17.5871 17.5 16.6667V4.99999C17.5 4.07952 16.7538 3.33333 15.8333 3.33333Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M13.334 1.66667V5.00001" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.66602 1.66667V5.00001" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2.5 8.33333H17.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg></span> <?php echo sprintf(__('Įsteigta %d m.','sr'),$organization['foundedAt']);?></div>
<?php } ?>

<?php if(!empty($organization['type'])) {?>
<div class="sport-base__manage-type"><span class="sport-base__ico sport-base__ico-type"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2.5 5H17.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M3.33398 5V15L10.0007 18.3333L16.6673 15V5H3.33398Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.66602 5V16.6667" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M13.334 5V16.6667" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.66602 5.00001L9.99935 1.66667L13.3327 5.00001" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <?php echo $organization['type'];?></div>
<?php } ?>

<?php if(!empty($organization['legalForm'])) {?>
<div class="sport-base__manage-legalForm"><span class="sport-base__ico sport-base__ico-legalForm"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13.334 13.3333L15.834 6.66666L18.334 13.3333C17.609 13.875 16.734 14.1667 15.834 14.1667C14.934 14.1667 14.059 13.875 13.334 13.3333Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M1.66602 13.3333L4.16602 6.66666L6.66602 13.3333C5.94102 13.875 5.06602 14.1667 4.16602 14.1667C3.26602 14.1667 2.39102 13.875 1.66602 13.3333Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M5.83398 17.5H14.1673" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 2.5V17.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2.5 5.83333H4.16667C5.83333 5.83333 8.33333 5 10 4.16666C11.6667 5 14.1667 5.83333 15.8333 5.83333H17.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <?php echo $organization['legalForm'];?></div>
<?php } ?>

<?php if(!empty($organization['hasBeneficiaryStatus'])){?>
<div class="sport-base__manage-hasBeneficiaryStatus"><span class="sport-base__ico sport-base__ico-hasBeneficiaryStatus"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.6673 5L7.50065 14.1667L3.33398 10" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <?php _e('Turi paramos gavėjo statusą','sr');?></div>
<?php } ?>

<?php if(!empty($organization['nonGovernmentalOrganization'])){?>
<div class="sport-base__manage-nonGovernmentalOrganization"><span class="sport-base__ico sport-base__ico-nonGovernmentalOrganization"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.6673 5L7.50065 14.1667L3.33398 10" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <?php _e('Atitinka nevyriausybinėms organizacijoms keliamus reikalavimus','sr');?></div>
<?php } ?>

<?php if(!empty($organization['nonFormalEducation'])){?>
<div class="sport-base__manage-nonFormalEducation"><span class="sport-base__ico sport-base__ico-nonFormalEducation"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.6673 5L7.50065 14.1667L3.33398 10" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <?php _e('Gali vykdyti akredituotas neformaliojo vaikų švietimo programas','sr');?></div>
<?php } ?>



<div class="sport-base__manage-updatedAt"><span class="sport-base__ico sport-base__ico-updatedAt"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M15.8333 3.33334H4.16667C3.24619 3.33334 2.5 4.07953 2.5 5V16.6667C2.5 17.5871 3.24619 18.3333 4.16667 18.3333H15.8333C16.7538 18.3333 17.5 17.5871 17.5 16.6667V5C17.5 4.07953 16.7538 3.33334 15.8333 3.33334Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M13.334 1.66666V5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.66602 1.66666V5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2.5 8.33333H17.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.66602 11.6667H6.67435" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 11.6667H10.0083" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M13.334 11.6667H13.3423" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.66602 15H6.67435" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 15H10.0083" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M13.334 15H13.3423" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <?php _e('Duomenys pateikti:','sr');?> <?php echo $organization['updatedAt'];?></div>

        </div>
        <?php if(!empty($organization['webPage'])) {?>
        <a href="<?php echo fix_url($organization['webPage']);?>" target="_blank" rel="nofollow"
            class="sport-base__manager-www"><?php _e('Aplankykite interneto svetainę', 'sr');?><span
                class="sport-base__ico sport-base__ico-external"><svg width="24" height="24" viewBox="0 0 24 24"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 7H17V17" stroke="#003D2B" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M7 17L17 7" stroke="#003D2B" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </span></a>
        <?php } ?>
        <div class="sport-base__manager-map-wrapper">
            <div id="sport-base__manager-map" class="sport-base__manager-map"></div>
        </div>
        <?php } ?>

    </div>
</div>