<?php defined('ABSPATH') || exit;?>
<?php
$photos = '';
foreach($args['data'] as $photo){
    if(!empty($photo['public'])){
        $representative = $photo['representative'] ? 'img-representative' : '';
        $photos .= '<img src="' . $photo['url'] . '" alt="' . $photo['description'] . '" class="'.$representative.'">';
    }
}
echo $photos;