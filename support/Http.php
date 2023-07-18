<?php
declare(strict_types=1);

namespace support;

use GuzzleHttp\Client;

class Http
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    public function __construct()
    {
        $this->createHttpClient();
    }

    /**
     * get请求
     * @param string $url
     * @param array $query
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    public function get(string $url, array $query = [])
    {
        $data = [
            'query' => $query,
        ];

        return $this->exec('GET', $url, $data);
    }

    /**
     * post请求
     * @param string $url
     * @param array $params post form参数
     * @param array $headers 请求头
     * @param bool $json 参数是否是json格式
     * @return false|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    public function post(string $url, array $params = [], array $headers = [], bool $json = false)
    {
        $data = ['headers' => $headers];
        if ($json) {
            $data['json'] = $params;
        } else {
            $data['form_params'] = $params;
        }

        return $this->exec('POST', $url, $data);
    }

    /**
     * @param $method
     * @param $url
     * @param $data
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    private function exec($method, $url, $data): string
    {
        $exception = null;
        $start = microtime(true);
        try {
            $response = $this->httpClient->request($method, $url, $data);
            $result = $response->getBody()->getContents();
        } catch (\Throwable $e) {
            $exception = $e;
        }

        $end = microtime(true);
        $exec_time = round(($end - $start) * 1000, 2);
        $log = [
            'url'       => $url,
            'method'    => $method,
            'data'      => $data,
            'trace_id'  => Context::get('traceid', ''),
            'exception' => $exception ? [$exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString()] : [],
            'response'  => $result ?? '',
            'exec_time' => $exec_time,
        ];
        \Webman\RedisQueue\Redis::send('http-log', $log);

        if ($exception instanceof \Throwable) {
            Log::error('http request error:' . $exception->getMessage(), $log);
            throw $exception;
        }
        return $result ?? '';
    }

    private function createHttpClient()
    {
        if (!$this->httpClient instanceof Client) {
            $this->httpClient = new Client([
                'timeout' => config('http.timeout', 5),
            ]);
        }
    }

}