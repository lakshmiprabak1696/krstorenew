<?php

class ModelToolImage extends Model {

    public function resize($filename, $new_width, $new_height) {
        if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
            //return;
        }
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $image_old = $filename;
        if ($filename == 'no_image.png') {
            $image_old = DIR_IMAGE . $filename;
            $image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int) $new_width . 'x' . (int) $new_height . '.' . $extension;
        }
        $image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int) $new_width . 'x' . (int) $new_height . '.' . $extension;
        if (!is_file(DIR_IMAGE . $image_new)) {


            $path = '';

            $directories = explode('/', dirname($image_new));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }
            $filename = 'https://storage.googleapis.com/albumzen/' . $filename;
           
            if ($data = @getimagesize($filename))  
            {
            
            } else {
                $extension = 'png';
                $filename = 'https://storage.googleapis.com/albumzen/no_image.png';
            }
            list($width, $height) = getimagesize($filename);
            //$new_width = $width * $percent;
            //$new_height = $height * $percent;
            // Resample
            $image_p = imagecreatetruecolor($new_width, $new_height);
            if ($extension == 'png') {
                /*  imagesavealpha($image_p, true);

                  $image = imagecreatefrompng ($filename);
                  imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                  imagepng($image_p, DIR_IMAGE . $image_new, 9); */

                $image_p = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefrompng($filename);
                // Prepare alpha channel for transparent background
                $alpha_channel = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
                imagecolortransparent($image_p, $alpha_channel);
                // Fill image
                imagefill($image_p, 0, 0, $alpha_channel);
                // Copy from other
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                // Save transparency
                imagesavealpha($image_p, true);
                // Save PNG
                imagepng($image_p, DIR_IMAGE . $image_new, 9);
            } else if ($extension == 'jpg' || $extension == 'jpeg') {
                $image = imagecreatefromjpeg($filename);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($image_p, DIR_IMAGE . $image_new, 100);
            }
        }
        $image_new = str_replace(' ', '%20', $image_new);

        if ($this->request->server['HTTPS']) {
            return HTTP_CATALOG . 'image/' . $image_new;
        } else {
            return HTTPS_CATALOG . 'image/' . $image_new;
        }
    }

}
