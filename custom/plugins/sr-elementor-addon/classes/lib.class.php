<?php
class BEA_Lib
{
    public function svg_sprite($filepath)
    {
        if (file_exists($filepath)) {

            $filetype = pathinfo($filepath, PATHINFO_EXTENSION);

            if ($filetype === 'svg') {
                $filetype .= '+xml';
            }

            $get_img = file_get_contents($filepath);
            return 'data:image/' . $filetype . ';base64,' . base64_encode($get_img);
        }
    }
}