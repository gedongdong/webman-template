<?php

namespace app\controller;

use app\enum\ErrorCode;
use support\Http;
use support\Request;

class IndexController
{
    public function index(Request $request)
    {
        return response('hello webman');
    }

    public function view(Request $request)
    {
        return view('index/view', ['name' => 'webman']);
    }

    public function json(Request $request)
    {
        $res = (new Http())->get('https://www.baidu.com', [
            'wd'  => 'aaa',
        ]);
        return json(apiSuccess([$res]));
    }

}
