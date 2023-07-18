<?php

namespace app\enum;

class ErrorCode
{
    public const SUCCESS = 200;

    public const SERVER_ERROR = 500;

    //加密错误
    public const ENCRYPTER_ERROR = 3000;
    //解密错误
    public const EDCRYPTER_ERROR = 3001;
}