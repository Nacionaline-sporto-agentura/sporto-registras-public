<?php defined('ABSPATH') || exit;?>
<?php
use proj4php\Point as ProjPoint;
use proj4php\Proj;
use proj4php\Proj4php;
use Brick\Geo\Point;
use Brick\Geo\IO\EWKBReader;

if (empty($args['data'])) {
    return;
}
$address = SR_Table::format_address($args['data']['address']);

$sportTypes = [];
if (isset($args['data']['sportTypes'])) {
    foreach ($args['data']['sportTypes'] as $sportType) {
        if (!empty($sportType['name'])) {
            $sportTypes[] = $sportType['name'];
        }
    }
}
if (!function_exists('build_lightbox_gallery')) {
    function build_lightbox_gallery($id, $photos)
    {
        $gallery = '';
        if (isset($photos) && !is_array($photos)) {
            return $gallery;
        }
        foreach ($photos as $photo) {
            if ($photo['representative'] == 1 && $photo['public'] == 1) {
                $gallery = '<a href="'.$photo['url'].'" data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="photo-gallery-'.$id.'" class="sport-base__photo" data-elementor-lightbox-title="' . $photo['description'] . '"><img src="'.$photo['url'].'" alt="'.$photo['description'].'"></a>';
                break;
            }
        }
        foreach ($photos as $photo) {
            if (isset($photo['public']) && $photo['public'] == 1) {
                $gallery .= '<a href="'.$photo['url'].'" data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="photo-gallery-'.$id.'" class="sport-base__photo" data-elementor-lightbox-title="' . $photo['description'] . '" style="background-image:url('.$photo['url'].');"></a>';
            }
        }
        return $gallery;
    }
}
$photos = '';
$photos_count = 0;
foreach ($args['data']['photos'] as $photo) {
    if (isset($photo['public']) && $photo['public'] == true) {
        $photos .= '<a href="'.$photo['url'].'" data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="photo-gallery" class="sport-base__photo" data-elementor-lightbox-title="' . $photo['description'] . '" style="background-image:url('.$photo['url'].');"></a>';
        $photos_count++;
    }
}

if (!function_exists('translate_places')) {
    function translate_places($number)
    {
        if ($number == 1) {
            return _n('vieta', 'vietos', $number, 'sr');
        } elseif ($number > 1 && $number < 10) {
            return _n('vietos', 'vietų', $number, 'sr');
        } else {
            return _n('vietos', 'vietų', $number, 'sr');
        }
    }
}

if (!function_exists('fix_url')) {
    function fix_url($url)
    {
        if (!preg_match("/^https?:\/\//", $url)) {
            $url = "https://" . $url;
        }
        return $url;
    }
}
?>


<div class="sport-base__address"><svg class="sport-base__address__icon" xmlns="
http://www.w3.org/2000/svg"
width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg> <?php echo $address;?></div>
<ul class="sport-base__types tags-wrapper"><?php echo !empty($sportTypes) ? '<li class="tag">'.implode('</li><li class="tag">', $sportTypes).'</li>'.'<li class="more-button">'.__('Daugiau...', 'sr').'</li>' : '';?></ul>
<?php if ($photos_count > 0) { ?>
<div class="sport-base__photos__wrapper">
    <div class="sport-base__photos sport-base__photos__count-<?php echo $photos_count > 5 ? 5 : $photos_count;?>"><?php echo !empty($photos) ? $photos : '';?></div>
    <div class="sport-base__photos_navigation">
        <div class="sport-base__photos__pagination">
            <span class="sport-base__photos__pagination-bullet sport-base__photos__pagination-bullet-active"></span>
            <?php for ($i = 1; $i < $photos_count; $i++) { ?>
                <span class="sport-base__photos__pagination-bullet"></span>
            <?php } ?>
        </div>
        <div class="sport-base__photos__navigation"><button class="sport-base__photos_navigation__prev"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="arrow-left-icon" aria-hidden="true" focusable="false"><path d="M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"></path></svg></button><button class="sport-base__photos_navigation__next"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="arrow-right-icon" aria-hidden="true" focusable="false"><path d="M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"></path></svg></button></div>
    </div>
</div>
<?php } ?>
<div class="sport-base__data">
    <div class="sport-base__tabs">
        <div class="sport-base__tab-content" data-tab="overview">
            <h2><?php _e('Apžvalga', 'sr');?></h2>
            <ul class="sport-base__overview">
                <?php if (!empty($args['data']['publicWifi'])) { ?>
                    <li><svg class="sport-base__ico sport-base__ico-publicWifi" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5 13C6.86929 11.1677 9.38247 10.1414 12 10.1414C14.6175 10.1414 17.1307 11.1677 19 13" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M8.5 16.5C9.43464 15.5839 10.6912 15.0707 12 15.0707C13.3088 15.0707 14.5654 15.5839 15.5 16.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2 8.82C4.75011 6.36022 8.31034 5.00034 12 5.00034C15.6897 5.00034 19.2499 6.36022 22 8.82" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 20H12.01" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg> <span class="sport-base__label"><?php _e('Viešas WiFi internetas', 'sr');?></span></li>
                <?php } ?>
                <?php if (!empty($args['data']['parkingPlaces'])) { ?>
                    <li><svg class="sport-base__ico sport-base__ico-parkingPlaces" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M19 3H5C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V5C21 3.89543 20.1046 3 19 3Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9 17V7H13C13.7956 7 14.5587 7.31607 15.1213 7.87868C15.6839 8.44129 16 9.20435 16 10C16 10.7956 15.6839 11.5587 15.1213 12.1213C14.5587 12.6839 13.7956 13 13 13H9" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
 <span class="sport-base__label"><?php echo sprintf(__('Automobilių stovėjimo aikštelė (%d %s)', 'sr'), $args['data']['parkingPlaces'], translate_places($args['data']['parkingPlaces']));?></span></li>
                <?php } ?>
                <?php if (!empty($args['data']['methodicalClasses'])) { ?>
                    <li><svg class="sport-base__ico sport-base__ico-methodicalClasses" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2 3H22" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M21 3V14C21 14.5304 20.7893 15.0391 20.4142 15.4142C20.0391 15.7893 19.5304 16 19 16H5C4.46957 16 3.96086 15.7893 3.58579 15.4142C3.21071 15.0391 3 14.5304 3 14V3" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M7 21L12 16L17 21" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg> <span class="sport-base__label"><?php echo sprintf(__('Metodinių klasių (%d %s)', 'sr'), $args['data']['methodicalClasses'], __('vnt.', 'sr'));?></span></li>
                <?php } ?>
                <?php if (!empty($args['data']['saunas'])) { ?>
                    <li><svg class="sport-base__ico sport-base__ico-saunas" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_624_3513)">
<path d="M10 5C10.5523 5 11 4.55228 11 4C11 3.44772 10.5523 3 10 3C9.44772 3 9 3.44772 9 4C9 4.55228 9.44772 5 10 5Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M15 18V21" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 11V15H17L20 21" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M5 15V11L8 8H13V12" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2 18H12" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18 8C18 5.5 16 5.5 16 3" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M22 8C22 5.5 20 5.5 20 3" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</g>
<defs>
<clipPath id="clip0_624_3513">
<rect width="24" height="24" fill="white"/>
</clipPath>
</defs>
</svg> <span class="sport-base__label"><?php echo sprintf(__('Pirties patalpų (%d %s)', 'sr'), $args['data']['saunas'], __('vnt.', 'sr'));?></span></li>
                <?php } ?>
                <?php if (!empty($args['data']['constructionDate'])) { ?>
                    <li><svg class="sport-base__ico sport-base__ico-constructionDate" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2 18C2 18.2652 2.10536 18.5196 2.29289 18.7071C2.48043 18.8946 2.73478 19 3 19H21C21.2652 19 21.5196 18.8946 21.7071 18.7071C21.8946 18.5196 22 18.2652 22 18V16C22 15.7348 21.8946 15.4804 21.7071 15.2929C21.5196 15.1054 21.2652 15 21 15H3C2.73478 15 2.48043 15.1054 2.29289 15.2929C2.10536 15.4804 2 15.7348 2 16V18Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 10V5C10 4.73478 10.1054 4.48043 10.2929 4.29289C10.4804 4.10536 10.7348 4 11 4H13C13.2652 4 13.5196 4.10536 13.7071 4.29289C13.8946 4.48043 14 4.73478 14 5V10" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M4 15V12C4 10.4087 4.63214 8.88258 5.75736 7.75736C6.88258 6.63214 8.4087 6 10 6" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M14 6C15.5913 6 17.1174 6.63214 18.2426 7.75736C19.3679 8.88258 20 10.4087 20 12V15" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg> <span class="sport-base__label"><?php echo sprintf(__('Pastatyta %d m.', 'sr'), $args['data']['constructionDate']); ?></span></li>
                <?php } ?>
            </ul>
        </div>
        <div class="sport-base__tab-content" data-tab="spaces">
            <h2><?php _e('Erdvės', 'sr');?></h2>
            <?php if (!empty($args['data']['spaces'])) { ?>
            <?php foreach ($args['data']['spaces'] as $spaces) { ?>
                <div class="sport-base__space">
                    <div class="sport-base__space--wrapper">
                        <div class="sport-base__space--photo">
                            <?php echo build_lightbox_gallery($spaces['id'], $spaces['photos']);?>
                        </div>
                        <div class="sport-base__space--info">
                            <div>
                                <div class="sport-base__space__heading">
                                    <h3 class="sport-base__space-title"><?php echo $spaces['name'];?></h3>
                                    <div class="sport-base__space__technicalCondition" style="color:<?php echo $spaces['technicalCondition']['color'];?>;background-color:<?php echo $spaces['technicalCondition']['color'];?>0f"><?php echo $spaces['technicalCondition']['name'];?></div>
                                </div>
                                <div class="sport-base__space__type"><?php echo $spaces['type']['name'];?></div>
                            </div>
                            <div class="sport-base__space__meta">
                                <div>
                                <?php
                                    $spaces['sportTypes'] = array_filter($spaces['sportTypes']);
                if (!empty($spaces['sportTypes'])) {
                    ?>
                                <ul class="sport-base__types tags-wrapper">
                                    <?php foreach ($spaces['sportTypes'] as $sportType) { ?>
                                        <li class="tag"><?php echo $sportType['name'];?></li>
                                    <?php } ?>
                                    <li class="more-button"><?php _e('Daugiau...', 'sr');?></li>
                                </ul>
                                <?php } ?>
                                </div>
                                <div class="sport-base__space__more"><span class="expand-text"><?php _e('Detaliau', 'sr');?></span><span class="collapse-text"><?php _e('Suskleisti', 'sr');?></span> <span class="btn-more sport-base__ico sport-base__ico-constructionDate"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7 7H17V17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M7 17L17 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sport-base__space_additionalValues_wrapper">
                        <?php
                if (!empty($spaces['additionalValues'])) { ?>
                            <ul class="sport-base__space__additionalValues">
                                <?php foreach ($spaces['additionalValues'] as $additionalValue) {
                                    if ($additionalValue['value'] == 'true' || $additionalValue['value'] == 'false') {
                                        $additionalValue['value'] = $additionalValue['value'] == 'true' ? __('Taip', 'sr') : __('Ne', 'sr');
                                    }
                                    ?>
                                    <li><span class="sport-base__space__additional-label"><?php echo $additionalValue['name'];?></span><span class="sport-base__space__additional-value"><?php echo $additionalValue['value'];?></span></li>
                                <?php } ?>  
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <?php } ?>
        </div>
        <?php  if (!empty($args['data']['tenants'])) {?>
        <div class="sport-base__tab-content" data-tab="organizations">
            <h2><?php _e('Organizacijos veikiančios sporto bazėje', 'sr');?></h2>
            <?php foreach ($args['data']['tenants'] as $tenant) { ?>
                <div class="sport-base__space">
                    <h3 class="sport-base__space-title"><?php echo $tenant['name'];?></h3>
                    <div class="sport-base__space__type rm-margin"><?php echo $tenant['code'];?></div>
                </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php if (!empty($args['data']['tenant'])) {

        $tenant = $args['data']['tenant']; ?>
    <div class="sport-base__manger">

        <div class="sport-base__manger-title"><?php _e('Kontaktai', 'sr');?></div>
        <div class="sport-base__manger-contacts"> 
        <?php if (!empty($args['data']['email'])) {?>
            <div class="sport-base__manger-email"><svg class="sport-base__ico sport-base__ico-envelope" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg> <a href="mailto:<?php echo $args['data']['email'];?>"><?php echo $args['data']['email'];?></a></div><?php } ?>
<?php if (!empty($args['data']['phone'])) {?>
            <div class="sport-base__manage-phone"><svg class="sport-base__ico sport-base__ico-phone" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M22.0004 16.92V19.92C22.0016 20.1985 21.9445 20.4742 21.8329 20.7294C21.7214 20.9845 21.5577 21.2136 21.3525 21.4019C21.1473 21.5901 20.905 21.7335 20.6412 21.8227C20.3773 21.9119 20.0978 21.9451 19.8204 21.92C16.7433 21.5856 13.7874 20.5342 11.1904 18.85C8.77425 17.3147 6.72576 15.2662 5.19042 12.85C3.5004 10.2412 2.44866 7.271 2.12042 4.18001C2.09543 3.90347 2.1283 3.62477 2.21692 3.36163C2.30555 3.09849 2.44799 2.85669 2.63519 2.65163C2.82238 2.44656 3.05023 2.28271 3.30421 2.17053C3.5582 2.05834 3.83276 2.00027 4.11042 2.00001H7.11042C7.59573 1.99523 8.06621 2.16708 8.43418 2.48354C8.80215 2.79999 9.0425 3.23945 9.11042 3.72001C9.23704 4.68007 9.47187 5.62273 9.81042 6.53001C9.94497 6.88793 9.97408 7.27692 9.89433 7.65089C9.81457 8.02485 9.62928 8.36812 9.36042 8.64001L8.09042 9.91001C9.51398 12.4136 11.5869 14.4865 14.0904 15.91L15.3604 14.64C15.6323 14.3711 15.9756 14.1859 16.3495 14.1061C16.7235 14.0263 17.1125 14.0555 17.4704 14.19C18.3777 14.5286 19.3204 14.7634 20.2804 14.89C20.7662 14.9585 21.2098 15.2032 21.527 15.5775C21.8441 15.9518 22.0126 16.4296 22.0004 16.92Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
 <a href="tel:<?php echo str_replace(' ', '', $args['data']['phone']);?>"><?php echo $args['data']['phone'];?></a></div>
<?php } ?>
        </div>
        <?php if (!empty($args['data']['webPage'])) {?>
        <a href="<?php echo fix_url($args['data']['webPage']);?>" target="_blank" rel="nofollow" class="sport-base__manager-www"><?php _e('Aplankykite interneto svetainę', 'sr');?><svg class="sport-base__ico sport-base__ico-external" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 7H17V17" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7 17L17 7" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg></a>
        <?php } ?>

        <?php if (!empty($args['data']['geom'])) {
            $reader = new EWKBReader();
            $point = $reader->read(hex2bin($args['data']['geom']));
            $y = $point->y();
            $x = $point->x();

            $proj4 = new Proj4php();
            $projLKS = new Proj('EPSG:3346', $proj4);
            $projWGS = new Proj('EPSG:4326', $proj4);
            $pointLKS = new ProjPoint($x, $y, $projLKS);
            $pointWGS = $proj4->transform($projWGS, $pointLKS);

            $latitude = $pointWGS->y;
            $longitude = $pointWGS->x;
            ?>
        <div class="sport-base__manager-map-wrapper" data-lat="<?php echo $latitude;?>" data-lng="<?php echo $longitude;?>" ><div id="sport-base__manager-map" class="sport-base__manager-map"></div></div>
        <?php } ?>
    
    </div>
    <?php } ?>
</div>