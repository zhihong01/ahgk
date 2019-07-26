<?php

class utility extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function createCaptcha() {
        session_start();
        unset($_SESSION['captcha_chars']);
        
        $font = "./media/font/vfont.ttf";
        $captcha_chars = substr(str_shuffle("ABCDEFGHJKLMNPRSTUVWXY23456789"), 0, 4); // 0123456789
        $_SESSION['captcha_chars'] = $captcha_chars;
        $im = imagecreatetruecolor(80, 30);
        $color = imagecolorallocate($im, 34, 160, 184);
        $bg = imagecolorallocate($im, 240, 240, 240);
        imagefill($im, 1, 1, $bg);
        if (function_exists("ImageTTFText")) {
            for ($i = 0; $i < 4; $i++) {
                ImageTTFText($im, 18, rand(-10, 25), 16 * $i + 8, 25, $color, $font, $captcha_chars[$i]);
            }
        } else {
            for ($i = 0; $i < 4; $i++) {
                imagechar($im, 5, 20 * $i + 5, rand(0, 15), $captcha_chars[$i], $color);
            }
        }
        header('Content-type:image/jpeg');
        ImageJpeg($im);
        imagedestroy($im);
        exit();
    }

    //生成拼音字母
    public function getPyInitial() {
        $text = $this->input->get("text");
        $this->load->library('PYInitials');
        $py = new PYInitials();

        exit(strtolower($py->getInitials($text)));
    }

    private function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return array($r, $g, $b);
    }

    public function phpinfo() {
        echo phpinfo();
    }

    public function txt2Img() {
        $txt = $this->input->get('txt');
        $key = $this->input->get('key');
        $font_size = (int) $this->input->get('size');
        $font_color = $this->input->get('color');
        $bg_color = $this->input->get('bg');
        $max_width = (int) $this->input->get('max_width');

        header('Content-type:image/png');
        if (empty($txt) || empty($key)) {
            exit(OK1);
        }

        //if (md5($this->api_key . urlencode($txt)) !== $key) {
        //    exit();
        //}

        $filename = './cache/' . crc32($key) . '.png';
        if (file_exists($filename)) {
            $data = file_get_contents($filename);
            echo($data);
            exit();
        }

        if (empty($font_size)) {
            $font_size = 24;
        }
        $font = "./media/font/fzdh.ttf";
        $txt = urldecode($txt);

        $info = ImageTTFBBox($font_size, 0, $font, $txt);
        $image_width = abs($info[4] - $info[0]) + 10;
        $image_height = abs($info[5] - $info[1]) + 10;

        $im = imagecreatetruecolor($image_width, $image_height);

        if ($font_color) {
            $fcolor = $this->hex2rgb($font_color);
            $color = imagecolorallocate($im, $fcolor[0], $fcolor[1], $fcolor[2]);
        } else {
            $color = imagecolorallocate($im, 0, 0, 0);
        }
        if ($bg_color) {
            $bcolor = $this->hex2rgb($bg_color);
            $bg = imagecolorallocate($im, $bcolor[0], $bcolor[1], $bcolor[2]);
        } else {
            $bg = imagecolorallocate($im, 255, 255, 255);
        }

        imagefill($im, 0, 0, $bg);

        $x = 5;
        $y = $image_height - ($font_size / 2);

        ImageTTFText($im, $font_size, 0, $x, $y, $color, $font, $txt);
        try {
            if ($max_width > 10 && $image_width > $max_width) {
                $dst = imagecreatetruecolor($max_width, $image_height);
                imagecopyresampled($dst, $im, 0, 0, 0, 0, $max_width, $image_height, $image_width, $image_height);
                ImagePNG($dst);
                ImagePNG($dst, $filename);
            } else {
                ImagePNG($im);
                ImagePNG($im, $filename);
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }

        imagedestroy($im);
        exit();
    }

}

?>