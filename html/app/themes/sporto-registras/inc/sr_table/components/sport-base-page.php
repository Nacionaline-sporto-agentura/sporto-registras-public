<?php defined('ABSPATH') || exit;?>
<?php
if(empty($args['data'])) {
    return;
}

$address = sprintf('%s %s, %s, %s', $args['data']['address']['street'], $args['data']['address']['house'], $args['data']['address']['city'], $args['data']['address']['municipality']);

$sportTypes = [];
foreach($args['data']['publicSpaces'] as $publicSpaces) {
    foreach($publicSpaces['sportTypes'] as $sportType) {
        $sportTypes[] = $sportType['name'];
    }
}
$photos = '';
$photos_count = 0;
foreach($args['data']['photos'] as $photo) {
    if(empty($photo['public'])) { //!
        $photos .= '<div class="sport-base__photo" title="' . $photo['description'] . '" style="background-image:url('.$photo['url'].');"></div>';
        $photos_count++;
    }
}

$type_fields = [];
foreach($args['types_fields'] as $tf) {
    $type_fields[$tf['id']] = $tf['field']['title'];
}
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

if(!function_exists('translate_places')) {
    function translate_places($number)
    {
        if ($number == 1) {
            return _n('vieta', 'vietos', $number, 'sr');
        } elseif ($number > 1 && $number < 10) {
            return _n('vietos', 'vietos', $number, 'sr');
        } else {
            return _n('vietų', 'vietos', $number, 'sr');
        }
    }
}
$construction_year = get_construction_date($args['data']['publicSpaces']);
$technicalConditionClass = [
    1 => 'sport-base__space__technicalCondition--excellent', //Puiki
    2 => 'sport-base__space__technicalCondition--good', //Gera
    3 => 'sport-base__space__technicalCondition--average', //Vidutinė
    4 => 'sport-base__space__technicalCondition--bad', //Bloga
    5 => 'sport-base__space__technicalCondition--verybad', // Labai bloga
];
if(!function_exists('fix_url')) {
    function fix_url($url)
    {
        if (!preg_match("/^https?:\/\//", $url)) {
            $url = "https://" . $url;
        }
        return $url;
    }
}
?>

<div class="sport-base__address"><span class="sport-base__address__icon"></span> <?php echo $address;?></div>
<ul class="sport-base__types"><?php echo !empty($sportTypes) ? '<li>'.implode('</li><li>', $sportTypes).'</li>' : '';?></ul>
<?php if($photos_count > 0) { ?>
<div class="sport-base__photos__wrapper">
    <div class="sport-base__photos sport-base__photos__count-<?php echo $photos_count;?>"><?php echo !empty($photos) ? $photos : '';?></div>
    <div class="sport-base__photos_navigation">
        <div class="sport-base__photos__pagination">
            <span class="sport-base__photos__pagination-bullet sport-base__photos__pagination-bullet-active"></span>
            <?php for($i = 1; $i < $photos_count; $i++) { ?>
                <span class="sport-base__photos__pagination-bullet"></span>
            <?php } ?>
        </div>
        <div class="sport-base__photos__navigation"><button class="sport-base__photos_navigation__prev"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="arrow-left-icon" aria-hidden="true" focusable="false"><path d="M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"></path></svg></button><button class="sport-base__photos_navigation__next"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="arrow-right-icon" aria-hidden="true" focusable="false"><path d="M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"></path></svg></button></div>
    </div>
</div>
<?php } ?>
<div class="sport-base__data">
    <div class="sport-base__tabs">
        <div class="sport-base__tabs-header">
            <div class="sport-base__tab sport-base__tab--active" data-tab="overview"><?php _e('Apžvalga', 'sr');?></div>
            <?php if(!empty($args['data']['publicSpaces'])) {?><div class="sport-base__tab" data-tab="spaces"><?php _e('Erdvės', 'sr');?></div><?php } ?>
            <?php if(!empty($args['data']['publicTenants'])) {?><div class="sport-base__tab" data-tab="organizations"><?php _e('Organizacijos', 'sr');?></div><?php } ?>
        </div>
        <div class="sport-base__tab-content" data-tab="overview">
            <ul class="sport-base__overview">
                <?php if(!empty($args['data']['publicWifi'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-publicWifi"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5 13C6.86929 11.1677 9.38247 10.1414 12 10.1414C14.6175 10.1414 17.1307 11.1677 19 13" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M8.5 16.5C9.43464 15.5839 10.6912 15.0707 12 15.0707C13.3088 15.0707 14.5654 15.5839 15.5 16.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2 8.82C4.75011 6.36022 8.31034 5.00034 12 5.00034C15.6897 5.00034 19.2499 6.36022 22 8.82" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 20H12.01" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <span class="sport-base__label"><?php _e('Viešas WiFi internetas', 'sr');?></span></li>
                <?php } ?>
                <?php if(!empty($args['data']['parkingPlaces'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-parkingPlaces"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M19 3H5C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V5C21 3.89543 20.1046 3 19 3Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9 17V7H13C13.7956 7 14.5587 7.31607 15.1213 7.87868C15.6839 8.44129 16 9.20435 16 10C16 10.7956 15.6839 11.5587 15.1213 12.1213C14.5587 12.6839 13.7956 13 13 13H9" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <span class="sport-base__label"><?php echo sprintf(__('Automobilių stovėjimo aikštelė su %d %s', 'sr'), $args['data']['parkingPlaces'], translate_places($args['data']['parkingPlaces']));?></span></li>
                <?php } ?>
                <?php if(!empty($args['data']['dressingRooms'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-dressingRooms"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_624_3523)">
<path d="M12 11.41L3 17.41H21L12 11.41Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M7.30957 8.94C7.30957 7.64 8.35957 6.59 9.65957 6.59C10.9596 6.59 12.0096 7.64 12.0096 8.94V11.41" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</g>
<defs>
<clipPath id="clip0_624_3523">
<rect width="24" height="24" fill="white"/>
</clipPath>
</defs>
</svg>
</span> <span class="sport-base__label"><?php echo sprintf(__('Persirengimo patalpa su %d %s', 'sr'), $args['data']['dressingRooms'], translate_places($args['data']['dressingRooms']));?></span></li>
                <?php } ?> 
                <?php if(!empty($args['data']['methodicalClasses'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-methodicalClasses"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2 3H22" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M21 3V14C21 14.5304 20.7893 15.0391 20.4142 15.4142C20.0391 15.7893 19.5304 16 19 16H5C4.46957 16 3.96086 15.7893 3.58579 15.4142C3.21071 15.0391 3 14.5304 3 14V3" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M7 21L12 16L17 21" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <span class="sport-base__label"><?php echo sprintf(__('Metodinės klasės su %d %s', 'sr'), $args['data']['methodicalClasses'], translate_places($args['data']['methodicalClasses']));?></span></li>
                <?php } ?>
                <?php if(!empty($args['data']['saunas'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-saunas"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
</svg>
</span> <span class="sport-base__label"><?php echo sprintf(__('Pirties patalpa su %d %s', 'sr'), $args['data']['saunas'], translate_places($args['data']['saunas']));?></span></li>
                <?php } ?>
                <?php if(!empty($args['data']['diningPlaces'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-diningPlaces"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_624_3518)">
<path d="M8 3V21" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10.1895 3L10.9895 8.24C11.0795 9.74 9.70955 11 7.98955 11C6.26955 11 4.89955 9.74 4.98955 8.24L5.78955 3" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M15 21V3L18.85 11.69C19.19 12.46 18.93 13.39 18.24 13.82L15 15.83" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</g>
<defs>
<clipPath id="clip0_624_3518">
<rect width="24" height="24" fill="white"/>
</clipPath>
</defs>
</svg>
</span> <span class="sport-base__label"><?php echo sprintf(__('Maitinimo vieta su %d %s', 'sr'), $args['data']['diningPlaces'], translate_places($args['data']['diningPlaces']));?></span></li>
                <?php } ?>
                <?php if(!empty($args['data']['accommodationPlaces'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-accommodationPlaces"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H6C4.93913 15 3.92172 15.4214 3.17157 16.1716C2.42143 16.9217 2 17.9391 2 19V21" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M22 21V19C21.9993 18.1137 21.7044 17.2528 21.1614 16.5523C20.6184 15.8519 19.8581 15.3516 19 15.13" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <span class="sport-base__label"><?php echo sprintf(__('Apgyvendinimas su %d %s', 'sr'), $args['data']['accommodationPlaces'], translate_places($args['data']['accommodationPlaces']));?></span></li>
                <?php } ?>
                <?php if(!empty($args['data']['disabledAccessible'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-disabledAccessible"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16 5C16.5523 5 17 4.55228 17 4C17 3.44772 16.5523 3 16 3C15.4477 3 15 3.44772 15 4C15 4.55228 15.4477 5 16 5Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18 19L19 12L13 13" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M5 8L8 5L13.5 8L11.14 11.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M4.23989 14.5C3.95773 15.4231 3.94878 16.4081 4.21411 17.3361C4.47944 18.2641 5.00772 19.0955 5.73517 19.73C6.46262 20.3644 7.35815 20.7746 8.31365 20.9113C9.26916 21.0479 10.2438 20.9051 11.1199 20.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M13.7599 17.5C14.042 16.5769 14.051 15.5919 13.7857 14.6639C13.5203 13.7359 12.9921 12.9045 12.2646 12.2701C11.5372 11.6356 10.6416 11.2254 9.68612 11.0887C8.73062 10.9521 7.75599 11.0949 6.87988 11.5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <span class="sport-base__label"><?php _e('Pritaikyta žmonėms su judėjimo negalia', 'sr');?></span></li>
                <?php } ?>
                <?php if(!empty($args['data']['blindAccessible'])) { ?>
                    <li><span class="sport-base__ico sport-base__ico-blindAccessible"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6 19C8.20914 19 10 17.2091 10 15C10 12.7909 8.20914 11 6 11C3.79086 11 2 12.7909 2 15C2 17.2091 3.79086 19 6 19Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18 19C20.2091 19 22 17.2091 22 15C22 12.7909 20.2091 11 18 11C15.7909 11 14 12.7909 14 15C14 17.2091 15.7909 19 18 19Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M14 15C14 14.4696 13.7893 13.9609 13.4142 13.5858C13.0391 13.2107 12.5304 13 12 13C11.4696 13 10.9609 13.2107 10.5858 13.5858C10.2107 13.9609 10 14.4696 10 15" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M2.5 13L5 7C5.7 5.7 6.4 5 8 5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M21.5 13L19 7C18.3 5.7 17.5 5 16 5" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <span class="sport-base__label"><?php _e('Pritaikyta žmonėms su regėjimo negalia', 'sr');?></span></li>
                <?php } ?>
                <?php if(!empty($construction_year)) { ?>
                    <li><span class="sport-base__ico sport-base__ico-constructionDate"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2 18C2 18.2652 2.10536 18.5196 2.29289 18.7071C2.48043 18.8946 2.73478 19 3 19H21C21.2652 19 21.5196 18.8946 21.7071 18.7071C21.8946 18.5196 22 18.2652 22 18V16C22 15.7348 21.8946 15.4804 21.7071 15.2929C21.5196 15.1054 21.2652 15 21 15H3C2.73478 15 2.48043 15.1054 2.29289 15.2929C2.10536 15.4804 2 15.7348 2 16V18Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 10V5C10 4.73478 10.1054 4.48043 10.2929 4.29289C10.4804 4.10536 10.7348 4 11 4H13C13.2652 4 13.5196 4.10536 13.7071 4.29289C13.8946 4.48043 14 4.73478 14 5V10" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M4 15V12C4 10.4087 4.63214 8.88258 5.75736 7.75736C6.88258 6.63214 8.4087 6 10 6" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M14 6C15.5913 6 17.1174 6.63214 18.2426 7.75736C19.3679 8.88258 20 10.4087 20 12V15" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <span class="sport-base__label"><?php echo sprintf(__('Pastatyta %d m.', 'sr'), $construction_year); ?></span></li>
                <?php } ?>
            </ul>
        </div>
        <div class="sport-base__tab-content" data-tab="spaces">
            <?php if(!empty($args['data']['publicSpaces'])) {?>
            <?php foreach($args['data']['publicSpaces'] as $publicSpace) { ?>
                <div class="sport-base__space">
                    <div class="sport-base__space__heading">
                        <h3 class="sport-base__space-title"><?php echo $publicSpace['name'];?></h3>
                        <div class="sport-base__space__technicalCondition <?php echo $technicalConditionClass[$publicSpace['technicalCondition']['id']];?>"><?php echo $publicSpace['technicalCondition']['name'];?></div>
                    </div>
                    <div class="sport-base__space__type"><?php echo $publicSpace['type']['name'];?></div>
                    <div class="sport-base__space__meta">
                        <?php if(!empty($publicSpace['sportTypes'])) { ?>
                        <ul class="sport-base__types">
                            <?php foreach($publicSpace['sportTypes'] as $sportType) { ?>
                                <li><?php echo $sportType['name'];?></li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                        
                        <div class="sport-base__space__more"><span class="expand-text"><?php _e('Detaliau', 'sr');?></span><span class="collapse-text"><?php _e('Suskleisti', 'sr');?></span> <span class="btn-more sport-base__ico sport-base__ico-constructionDate"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 7H17V17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7 17L17 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            </span>
                        </div>
                    </div>
                    <div class="sport-base__space_additionalValues_wrapper">
                        <?php if(!empty($publicSpace['additionalValues'])) { ?>
                            <ul class="sport-base__space__additionalValues">
                                <?php foreach($publicSpace['additionalValues'] as $key => $value) { ?>
                                    <li><span class="sport-base__space__additional-label"><?php echo $type_fields[$key];?></span><span class="sport-base__space__additional-value"><?php echo $value;?></span></li>
                                <?php } ?>  
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <?php } ?>
        </div>
        <?php if(!empty($args['data']['publicTenants'])) {?>
        <div class="sport-base__tab-content" data-tab="organizations">
            <?php foreach($args['data']['publicTenants'] as $publicTenant) { ?>
                <div class="sport-base__space">
                    <h3 class="sport-base__space-title"><?php echo $publicTenant['companyName'];?></h3>
                    <div class="sport-base__space__type"><?php echo $publicTenant['companyCode'];?></div>
                </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php if(!empty($args['data']['publicTenants'])) { ?>
    <div class="sport-base__manger">
        <?php foreach($args['data']['publicTenants'] as $publicTenant) { ?>
        <div class="sport-base__manger-title"><?php echo $publicTenant['companyName'];?></div>
        <div class="sport-base__manger-contacts"> 
            <div class="sport-base__manger-email"><span class="sport-base__ico sport-base__ico-envelope"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <?php echo $publicTenant['companyName'];?></div>
            <div class="sport-base__manage-phone"><span class="sport-base__ico sport-base__ico-phone"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M22.0004 16.92V19.92C22.0016 20.1985 21.9445 20.4742 21.8329 20.7294C21.7214 20.9845 21.5577 21.2136 21.3525 21.4019C21.1473 21.5901 20.905 21.7335 20.6412 21.8227C20.3773 21.9119 20.0978 21.9451 19.8204 21.92C16.7433 21.5856 13.7874 20.5342 11.1904 18.85C8.77425 17.3147 6.72576 15.2662 5.19042 12.85C3.5004 10.2412 2.44866 7.271 2.12042 4.18001C2.09543 3.90347 2.1283 3.62477 2.21692 3.36163C2.30555 3.09849 2.44799 2.85669 2.63519 2.65163C2.82238 2.44656 3.05023 2.28271 3.30421 2.17053C3.5582 2.05834 3.83276 2.00027 4.11042 2.00001H7.11042C7.59573 1.99523 8.06621 2.16708 8.43418 2.48354C8.80215 2.79999 9.0425 3.23945 9.11042 3.72001C9.23704 4.68007 9.47187 5.62273 9.81042 6.53001C9.94497 6.88793 9.97408 7.27692 9.89433 7.65089C9.81457 8.02485 9.62928 8.36812 9.36042 8.64001L8.09042 9.91001C9.51398 12.4136 11.5869 14.4865 14.0904 15.91L15.3604 14.64C15.6323 14.3711 15.9756 14.1859 16.3495 14.1061C16.7235 14.0263 17.1125 14.0555 17.4704 14.19C18.3777 14.5286 19.3204 14.7634 20.2804 14.89C20.7662 14.9585 21.2098 15.2032 21.527 15.5775C21.8441 15.9518 22.0126 16.4296 22.0004 16.92Z" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</span> <?php echo $publicTenant['companyName'];?></div>
        </div>
        <?php if(!empty($args['data']['webPage'])) {?>
        <a href="<?php echo fix_url($args['data']['webPage']);?>" target="_blank" rel="nofollow" class="sport-base__manager-www"><?php _e('Aplankykite interneto svetainę', 'sr');?><span class="sport-base__ico sport-base__ico-external"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 7H17V17" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7 17L17 7" stroke="#003D2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
</span></a>
        <?php } ?>
        <div class="sport-base__manager-map-wrapper"><div id="sport-base__manager-map" class="sport-base__manager-map"></div></div>
        <?php } ?>
    
    </div>
    <?php } ?>
</div>

<?php //print_r($args['data']);?>