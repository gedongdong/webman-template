<?php
/**
 * Here is your custom functions.
 */

function apiSuccess(array $data = [])
{
    $res = [
        'code' => \app\enum\ErrorCode::SUCCESS,
        'msg'  => 'ok',
    ];
    if ($data) {
        $res['data'] = $data;
    }
    return $res;
}

function apiError(int $code, string $msg, array $data = [], array $trace = [])
{
    $res = [
        'code' => $code,
        'msg'  => $msg,
    ];
    if ($data) {
        $res['data'] = $data;
    }
    if ($trace) {
        $res['trace'] = $trace;
    }
    return $res;
}