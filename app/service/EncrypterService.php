<?php
/**
 * 加解密服务类（兼容laravel）
 * User: gedongdong
 * Date: 2020-08-25 18:57
 */

namespace app\service;


use support\Encrypter;

class EncrypterService
{
    protected $encrypter;

    public function __construct()
    {
        $key = env('ENCRYPT_KEY');
        if (!$this->encrypter instanceof Encrypter) {
            $this->encrypter = new Encrypter($key);
        }
    }

    public function encrypt($str)
    {
        return $this->encrypter->encrypt($str);
    }

    public function decrypt($str)
    {
        return $this->encrypter->decrypt($str);
    }
}