<?php

namespace app\queue\redis;

use app\service\EncrypterService;
use Illuminate\Database\Schema\Blueprint;
use support\Db;
use support\Log;
use Webman\RedisQueue\Consumer;

class GlobalLog implements Consumer
{
    // 要消费的队列名
    public $queue = 'global-log';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费
    public function consume($data)
    {
        try {
            $tableName = 'global_log_' . date('Ymd');
            $this->initTable($tableName);

            $cookie = $data['cookie'] ?? [];
            $appid = $data['appid'] ?? '';
            $query = $data['query'] ?? [];
            $ticket = $query['ticket'] ?? '';
            if (!$appid && $ticket) {
                //从ticket中解析appid
                try {
                    $ticketStr = (new EncrypterService())->decrypt($ticket);
                    $ticketArr = explode('|', $ticketStr);
                    $appid = $ticketArr[0] ?? '';
                } catch (\Exception $e) {
                    //ticket解密失败，可能是环境不匹配或者ticket不正确，不处理
                    $appid = 'ticket_error';
                }
            }

            DB::table($tableName)->insert([
                'ip'         => $data['ip'] ?? '',
                'uri'        => $data['uri'] ?? '',
                'method'     => $data['method'] ?? '',
                'appid'      => $appid,
                'traceid'    => $data['traceid'] ?? '',
                'referer'    => $data['referer'] ?? '',
                'user_agent' => $data['user_agent'] ?? '',
                'query'      => $query ? json_encode($query, JSON_UNESCAPED_UNICODE) : '',
                'errcode'    => $data['errcode'] ?? '',
                'response'   => $data['response'] ?? '',
                'exception'  => $data['exception'] ?? '',
                'exec_time'  => $data['exec_time'] ?? '',
                'cookie'     => $cookie ? json_encode($data['cookie'], JSON_UNESCAPED_UNICODE) : '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::error('global_log_queue_error', [
                'msg'   => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function initTable($tableName)
    {
        //判断global_log表是否存在，按天分表
        if (!Db::schema()->hasTable($tableName)) {
            Db::schema()->create($tableName, function (Blueprint $table) {
                $table->increments('id')->autoIncrement()->unsigned();
                $table->string('ip', 20)->nullable(true)->default(null)->comment('访问ip');
                $table->string('uri', 255)->nullable(true)->default(null)->comment('访问uri');
                $table->string('method', 10)->nullable(true)->default(null)->comment('get or post');
                $table->string('appid', 50)->nullable(true)->default(null)->comment('应用平台appid');
                $table->string('traceid', 255)->nullable(true)->default(null)->comment('traceid');
                $table->text('referer')->nullable(true)->default(null)->comment('来源页');
                $table->text('user_agent')->nullable(true)->default(null)->comment('user_agent');
                $table->text('query')->nullable(true)->default(null)->comment('请求参数');
                $table->string('errcode', 10)->nullable(true)->default(null)->comment('响应错误码');
                $table->text('response')->nullable(true)->default(null)->comment('响应结果');
                $table->text('exception')->nullable(true)->default(null)->comment('异常信息');
                $table->text('exec_time')->nullable(true)->default(null)->comment('执行时间，单位毫秒');
                $table->text('cookie')->nullable(true)->default(null)->comment('请求cookie');
                $table->dateTime('created_at')->nullable(true)->default(null);

                $table->index('ip', 'ip');
                $table->index('uri', 'uri');
                $table->index('appid', 'appid');
                $table->index('traceid', 'traceid');
                $table->index('created_at', 'created_at');

                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                $table->engine = 'InnoDB';
            });
        }
    }
}