<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once __DIR__ . '/config.class.php';

class Captcha
{
    private $image;
    private string $imageData;
    private int $id;
    private string $code;

    public function __construct()
    {
        if (!function_exists('imagewebp')) {
            trigger_error('Could not find "imagewebp" function. Please ensure that the gd2 module with webp support is installed and enabled', E_USER_ERROR);
        }
    }

    public function createCaptcha(): void
    {
        $this->id = crc32(openssl_random_pseudo_bytes(8));
        $this->code = $this->generateCode();
        $this->createImage();
        $this->drawCode();
        ob_start();
        imagewebp($this->image, null, 10);
        $this->imageData = ob_get_clean();
        mt_srand(random_int(0, PHP_INT_MAX));
    }

    public function getImageUrl(): string
    {
        return sprintf('data:image/webp;base64,%s', base64_encode($this->imageData));
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
        for ($i = 0; $i >= -Config::getInt(Config::SECTION_CAPTCHA, 'validity_minutes'); $i--) {
            if (Config::getBoolean(Config::SECTION_CAPTCHA, 'case_sensitive')) {
                $result = strcasecmp($code, $instance->generateCode($i)) == 0;
            } else {
                $result = strcmp(strtolower($code), strtolower($instance->generateCode($i))) == 0;
            }
            if ($result) break;
        }
        mt_srand(random_int(0, PHP_INT_MAX));
        return $result;
    }

    private function generateCode(int $offset = 0): string
    {
        $chars = str_split(Config::get(Config::SECTION_CAPTCHA, 'chars'));
        $date = strtotime(date('Y-m-d H:i:00')) + ($offset * 60);
        mt_srand(crc32($this->id . Config::get(Config::SECTION_BASE, 'random_secret')) + $date);
        $code = '';
        for ($i = 0; $i < Config::getInt(Config::SECTION_CAPTCHA, 'length'); $i++) {
            $code .= $chars[mt_rand(0, count($chars) - 1)];
        }
        return $code;
    }

    private function createImage(): void
    {
        $this->image = imagecreatetruecolor(Config::getInt(Config::SECTION_CAPTCHA, 'width'), Config::getInt(Config::SECTION_CAPTCHA, 'height'));
        for ($i = 0; $i < Config::getInt(Config::SECTION_CAPTCHA, 'security'); $i++) {
            $rand_x = mt_rand(0, Config::getInt(Config::SECTION_CAPTCHA, 'width'));
            $rand_y = mt_rand(0, Config::getInt(Config::SECTION_CAPTCHA, 'height'));

            imagefilledrectangle($this->image, $rand_x, $rand_y, $rand_x + Config::getInt(Config::SECTION_CAPTCHA, 'rect_size'), $rand_y + Config::getInt(Config::SECTION_CAPTCHA, 'rect_size'),
                imagecolorexact($this->image, mt_rand(0, 180), mt_rand(0, 180), mt_rand(0, 180))
            );
        }
    }

    private function drawCode(): void
    {
        for ($i = 0; $i < Config::getInt(Config::SECTION_CAPTCHA, 'length'); $i++) {
            imagefttext($this->image,
                Config::getInt(Config::SECTION_CAPTCHA, 'fontsize'),
                mt_rand(-20, 20),
                (int)floor(10 + $i * (Config::getInt(Config::SECTION_CAPTCHA, 'width') / Config::getInt(Config::SECTION_CAPTCHA, 'length')) - mt_rand(0, 8)),
                Config::getInt(Config::SECTION_CAPTCHA, 'fontsize') + 20 + mt_rand(-5, 5),
                imagecolorexact($this->image, mt_rand(150, 255), mt_rand(150, 255), mt_rand(150, 255)),
                Config::get(Config::SECTION_CAPTCHA, 'font'),
                $this->code[$i]
            );
        }
    }
}
