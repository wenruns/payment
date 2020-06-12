<?php
/**
 * 请求服务提供者
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/24
 * Time: 11:56
 */

namespace wenruns\payment\service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use wenruns\payment\Response;
use wenruns\payment\service\BodBank\BodOrderQueryService;
use wenruns\payment\service\BodBank\BodRecFileReqService;
use wenruns\payment\service\WeChat\WeChatOrderQueryService;
use wenruns\payment\service\WeChat\WeChatRecFileReqService;

class OnlinePaymentReqService
{

    protected $timeout = 10;

    /**
     * 实例缓存对象
     * @var array
     */
    protected $object_cache = [];


    /**
     * 扩展方法
     * @var array
     */
    protected $extensions = [
        'bodRecFileReq' => BodRecFileReqService::class,
        'bodOrderQuery' => BodOrderQueryService::class,
        'weChatOrderQuery' => WeChatOrderQueryService::class,
        'weChatRecFileReq' => WeChatRecFileReqService::class,
    ];


    /**
     * 请求客户端对象
     * @var Client|null
     */
    protected $client = null;


    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * 发送请求
     * @param $url
     * @param array $options
     * @param string $method
     * @param Response|null $previousResponse
     * @return Response
     */
    public function request($url, $options = [], $method = 'GET', Response $previousResponse = null)
    {
        if (!isset($options['timeout'])) {
            $options['timeout'] = $this->timeout;
        }
        $response = new Response();
        $response->startTime(microtime(true))->previousResponse($previousResponse);

        try {
            $res = $this->client->request($method, $url, $options);
            $response->uri($url)->options($options)->curlResponse($res)->method($method);
        } catch (\Exception $e) {
            $response->uri($url)->options($options)->method($method)->errorMsg($e->getMessage(), $e)->setApiStatus(false, $e->getMessage());
        } catch (RequestException $e) {
            $response->uri($url)->options($options)->method($method)->errorMsg($e->getMessage(), $e)->setApiStatus(false, $e->getMessage());
        } catch (\HttpRequestException $e) {
            $response->uri($url)->options($options)->method($method)->errorMsg($e->getMessage(), $e)->setApiStatus(false, $e->getMessage());
        }
        $response->endTime(microtime(true));
        return $response;
    }


    /**
     * 获取实例
     * @param $name
     * @return OnlinePaymentReqAbstractClass
     * @throws \Exception
     */
    protected function getObject($name)
    {
        if (isset($this->object_cache[$name])) {
            return $this->object_cache[$name];
        }
        if (isset($this->extensions[$name])) {
            $object_name = $this->extensions[$name];
            $this->object_cache[$name] = new $object_name($this);
            return $this->object_cache[$name];
        }
        throw new \Exception('找不到方法：“' . $name . '”');
    }

    /**
     * 执行请求
     * @param $name
     * @param $arguments
     * @return Response
     * @throws \Exception
     */
    public function execMethod($name, $arguments)
    {
        return $this->getObject($name)->exec($arguments);
    }


    /**
     * 参数检查是否齐全
     * @param $name
     * @param $method
     * @param $arguments
     * @throws \ReflectionException
     */
    protected function checkArguments($name, $method, $arguments)
    {
        $ReflectionMethod = new \ReflectionMethod($this->getObject($name), $method);
        $params = $ReflectionMethod->getParameters();
        $paramStr = '';
        $isLack = false;
        foreach ($params as $key => $item) {
            $paramStr .= '$' . $item->name;
            if ($item->isDefaultValueAvailable()) {
                $paramStr .= ' = ' . ($item->getDefaultValue() === '' ? "''" : $item->getDefaultValue());
            } else if (!isset($arguments[$key])) {
                $isLack = true;
            }
            $paramStr .= ', ';
        }
        if ($isLack) {
            throw new \Exception('方法' . $name . '缺少参数, 参考' . $name . '(' . trim(trim($paramStr), ',') . ')');
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return Response
     * @throws \ReflectionException
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $this->checkArguments($name, 'options', $arguments);
        return $this->execMethod($name, $arguments);
    }
}