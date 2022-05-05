<?php
require_once('config.inc.php');

const captcha_width = 200;
const captcha_height = 60;
const captcha_length = 6;
const captcha_chars = '0123456789';
const captcha_font_file = 'include/SportsballRegular-RxlM.ttf';
const captcha_font_size = 18;
const captcha_security = 5000;
const captcha_size_rects = 6;
const captcha_case_sensitive = false;
const captcha_validity_minutes = 5;

class Captcha
{
    private $image;
    private string $imageData;
    private int $id;
    private string $code;

    public function __construct()
    {
        if (!function_exists('imagejpeg')) {
            trigger_error('Could not find the "imagejpeg" function, please ensure that the gd2 module is installed and enabled');
        }
    }

    public function createCaptcha(): void
    {
        $this->id = crc32(openssl_random_pseudo_bytes(8));
        $this->code = $this->generateCode();
        $this->createImage();
        $this->drawCode();
        ob_start();
        imagejpeg($this->image, null, 50);
        $this->imageData = ob_get_clean();
    }

    public function getImageUrl(): string
    {
        return sprintf("data:image/jpeg;base64,%s", base64_encode($this->imageData));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public static function verifyCode(string $code, int $id): bool
    {
        $instance = new Captcha();
        $instance->id = $id;
        $result = false;
        for ($i = 0; $i >= -captcha_validity_minutes; $i--) {
            if (captcha_case_sensitive) {
                $result = strcasecmp($code, $instance->generateCode($i)) == 0;
            } else {
                $result = strcmp(strtolower($code), strtolower($instance->generateCode($i))) == 0;
            }
            if ($result) break;
        }
        return $result;
    }

    private function generateCode(int $offset = 0): string
    {
        $chars = str_split(captcha_chars, 1);
        $date = strtotime(date('Y-m-d H:i:00')) + ($offset * 60);
        srand(crc32($this->id . random_secret) + $date);
        $code = '';
        for ($i = 0; $i < captcha_length; $i++) {
            $code .= $chars[rand(0, count($chars) - 1)];
        }
        srand(mt_rand());
        return $code;
    }

    private function createImage(): void
    {
        $this->image = imagecreatetruecolor(captcha_width, captcha_height);
        for ($i = 0; $i < captcha_security; $i++) {
            $rand_x = rand(0, captcha_width);
            $rand_y = rand(0, captcha_height);

            imagefilledrectangle($this->image, $rand_x, $rand_y, $rand_x + captcha_size_rects, $rand_y + captcha_size_rects,
                imagecolorexact($this->image, rand(0, 180), rand(0, 180), rand(0, 180))
            );
        }
    }

    private function drawCode(): void
    {
        for ($i = 0; $i < captcha_length; $i++) {
            imagefttext($this->image,
                captcha_font_size,
                rand(-20, 20),
                10 + $i * (captcha_width / captcha_length) - rand(0, 8),
                captcha_font_size + 20 + rand(-5, 5),
                imagecolorexact($this->image, rand(150, 255), rand(150, 255), rand(150, 255)),
                captcha_font_file,
                $this->code[$i]
            );
        }
    }
}
