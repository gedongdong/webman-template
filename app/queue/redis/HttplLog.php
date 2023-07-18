<?php

namespace app\queue\redis;

use Illuminate\Database\Schema\Blueprint;
use support\Db;
use support\Log;
use Webman\RedisQueue\Consumer;

class HttplLog implements Consumer
{
    // 要消费的队列名
    public $queue = 'http-log';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费
    public function consume($data)
    {
        try {
            $tableName = 'http_log_' . date('Ymd');
            $this->initTable($tableName);

            $request_data = $data['data'] ?? [];
            $exception = $data['exception'] ?? [];
            Db::table($tableName)->insert([
                'url'        => $data['url'] ?? '',
                'method'     => $data['method'] ?? '',
                'data'       => $request_data ? json_encode($request_data, JSON_UNESCAPED_UNICODE) : '',
                'trace_id'   => $data['trace_id'] ?? '',
                'exception'  => $exception ? json_encode($exception, JSON_UNESCAPED_UNICODE) : '',
                'response'   => $data['response'] ?? '',
                'exec_time'  => $data['exec_time'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::error('http log error', [
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
                $table->string('url', 255)->nullable(true)->default(null)->comment('访问url');
                $table->string('method', 10)->nullable(true)->default(null)->comment('get or post');
                $table->text('data')->nullable(true)->comment('请求参数');
                $table->string('trace_id', 200)->nullable(true)->comment('trace_id');
                $table->text('exception')->nullable(true)->comment('异常信息');
                $table->text('response')->nullable(true)->comment('响应结果');
                $table->string('exec_time', 10)->nullable(true)->comment('执行时长，单位毫秒');
                $table->dateTime('created_at')->nullable(true)->default(null);

                $table->index('trace_id', 'idx_trace_id');
                $table->index('created_at', 'idx_created_at');

                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                $table->engine = 'InnoDB';
            });
        }
    }
}