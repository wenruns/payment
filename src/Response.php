<?php
/**
 * 请求响应对象
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/25
 * Time: 14:38
 */

namespace wenruns\payment;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use wenruns\payment\Traits\XmlTrait;

class Response
{
    use XmlTrait;

    /**
     * 请求头信息
     * @var \string[][]
     */
    protected $headers;

    /**
     * 请求体
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $body;

    /**
     * 返回内容
     * @var string
     */
    protected $content;

    /**
     * 发起时间
     * @var
     */
    protected $start_time;

    /**
     * 结束时间
     * @var int
     */
    protected $end_time;

    /**
     * 发起客户端
     * @var
     */
    protected $client_ip;

    /**
     * 请求url
     * @var
     */
    protected $uri;

    /**
     * 请求参数
     * @var
     */
    protected $params;

    /**
     * 请求方式
     * @var
     */
    protected $method;


    /**
     * 源数据
     * @var
     */
    protected $origin_data;


    /**
     * 格式化后的数据
     * @var
     */
    protected $format_data;

    /**
     * api访问状态
     * @var
     */
    protected $api_access_status = false;

    protected $api_access_desc = '';


    /**
     * http请求状态码
     * @var
     */
    protected $http_status_code = '2000';

    /**
     * 业务逻辑状态
     * @var bool
     */
    protected $status = true;

    /**
     * 业务逻辑状态码
     * @var int
     */
    protected $code = 2000;

    /**
     * 业务逻辑描述
     * @var string
     */
    protected $desc = 'ok';

    /**
     * guzzle client->request(method, url, params) 返回的原始结果
     * @var null
     */
    protected $cur_response = null;

    /**
     * 请求异常信息
     * @var
     */
    protected $error_msg;

    /**
     * 异常信息 \Exception $e
     * @var null
     */
    protected $exception_info = null;


    /**
     * 当前请求之前发生的请求结果
     * @var null
     */
    protected $previous_response = null;


    /**
     * Response constructor.
     */
    public function __construct()
    {
//        $this->end_time = microtime(true);
        $this->client_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    }

    /**
     *
     * @param Response|null $response
     * @return $this
     */
    public function previousResponse(Response $response = null)
    {
        $this->previous_response = $response;
        return $this;
    }


    /**
     * 获取属性
     * @param $attr_name
     * @param null $default
     * @return |null
     */
    public function getAttribute($attr_name, $default = null)
    {
        $index = explode('.', $attr_name);
        $attr_name = array_shift($index);
        if (!isset($this->$attr_name)) {
            return $default;
        }
        $data = $this->$attr_name;
        foreach ($index as $dex) {
            if (isset($data[$dex])) {
                $data = $data[$dex];
            } else {
                return $default;
            }
        }
        return $data;
    }


    /**
     * 设置业务状态信息
     * @param bool $status
     * @param int $status_code
     * @param string $status_desc
     * @return $this
     */
    public function status($status = true, $status_code = 2000, $status_desc = 'ok')
    {
        $this->status = $status;
        $this->code = $status_code;
        $this->desc = $status_desc;
        return $this;
    }


    /**
     * 获取业务状态信息
     * @return array
     */
    public function getStatus()
    {
        return [
            'status' => $this->status,
            'code' => $this->code,
            'desc' => $this->desc,
        ];
    }


    /**
     * 设置curl返回信息
     * @param ResponseInterface $response
     * @return $this
     */
    public function curlResponse(ResponseInterface $response)
    {
        $this->cur_response = $response;
        $this->headers = $response->getHeaders();
        $this->body = $response->getBody();
        $this->content = $this->body->getContents();
        $this->http_status_code = $response->getStatusCode();
        if ($this->http_status_code == 200) {
            $this->api_access_status = true;
        }
        $this->api_access_desc = $response->getReasonPhrase();
        return $this;
    }


    /**
     * 设置异常信息
     * @param $errMsg
     * @param \Exception|null $e
     * @return $this
     */
    public function errorMsg($errMsg, \Exception $e = null)
    {
        $this->error_msg = $errMsg;
        $this->exception_info = $e;
        return $this;
    }


    /**
     * 设置请求发起时间
     * @param $start_time
     * @return $this
     */
    public function startTime($start_time)
    {
        $this->start_time = $start_time;
        return $this;
    }

    /**
     * 请求结束时间
     * @param $end_time
     * @return $this
     */
    public function endTime($end_time)
    {
        $this->end_time = $end_time;
        return $this;
    }

    /**
     * 设置请求url
     * @param $url
     * @return $this
     */
    public function uri($url)
    {
        $this->uri = $url;
        return $this;
    }

    /**
     * 设置参数选项
     * @param array $options
     * @return $this
     */
    public function options(array $options)
    {
        $this->params = $this->filterParams($options);
        return $this;
    }

    /**
     * 设置请求方式
     * @param string $method
     * @return $this
     */
    public function method($method = 'GET')
    {
        $this->method = $method;
        return $this;
    }


    /**
     * 过滤参数
     * @param array $params
     * @return array
     */
    protected function filterParams(array $params)
    {
        foreach ($params as $key => $item) {
            $params[$key] = $this->checkParam($item);
        }
        return $params;
    }

    /**
     * 参数检查array|object|xml|json
     * @param $param
     * @return bool|mixed
     */
    protected function checkParam($param)
    {
        if (is_array($param)) {
            return $param;
        }
        if (is_object($param)) {
            return json_decode(json_encode($param), true);
        }
        if ($res = $this->xmlParse($param)) {
            return $res;
        }

        return json_decode($param, true);
    }


    public function setApiStatus($status, $desc = '')
    {
        $this->api_access_status = $status;
        $this->api_access_desc = $desc;
        return $this;
    }

    /**
     * 获取api请求状态
     * @return bool
     */
    public function getApiStatus()
    {
        return $this->api_access_status;
    }

    /**
     * 获取http请求状态码
     * @return mixed
     */
    public function getHttpStatusCode()
    {
        return $this->http_status_code;
    }

    /**
     * 获取api请求的描述
     * @return string
     */
    public function getApiDesc()
    {
        return $this->api_access_desc;
    }

    /**
     * 设置好源数据
     * @param $values
     * @return $this
     */
    public function setOriginData($values)
    {
        $this->origin_data = $values;
        return $this;
    }

    /**
     * 设置格式化后的数据
     * @param $values
     * @return $this
     */
    public function setFormatData($values)
    {
        $this->format_data = $values;
        return $this;
    }


    /**
     * 获取源数据
     * @param string $index
     * @param null $default
     * @return mixed
     */
    public function getOriginData($index = '', $default = null)
    {
        $data = $this->origin_data;
        $index = explode('.', $index);
        foreach ($index as $dex) {
            if ($dex) {
                if (isset($data[$dex])) {
                    $data = $data[$dex];
                } else {
                    return $default;
                }
            }
        }
        return $data;
    }

    /**
     * 获取格式化后的数据
     * @param string $index
     * @param null $default
     * @return mixed
     */
    public function getFormatData($index = '', $default = null)
    {
        $data = $this->format_data;
        $index = explode('.', $index);
        foreach ($index as $dex) {
            if ($dex) {
                if (isset($data[$dex])) {
                    $data = $data[$dex];
                } else {
                    return $default;
                }
            }
        }
        return $data;
    }


    /**
     * 获取头信息
     * @return \string[][]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 获取请求体
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * 获取请求返回内容
     * @param bool $origin
     * @return array|bool|mixed|string|null
     */
    public function getContent($origin = true)
    {
        if ($origin) {
            return $this->content;
        }

        if (is_array($this->content)) {
            return $this->content;
        }
        if (is_object($this->content)) {
            return json_decode(json_encode($this->content), true);
        }

        if ($res = $this->xmlParse($this->content)) {
            return $res;
        }
        return json_decode($this->content, true);
    }

    /**
     * 获取请求发起时间
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->start_time * 1000;
    }

    /**
     * 获取请求结束时间
     * @return int
     */
    public function getEndTime()
    {
        return $this->end_time * 1000;
    }

    /**
     * 获取请求耗时
     * @return int
     */
    public function getDuration()
    {
        return ($this->end_time - $this->start_time) * 1000;
    }


    /**
     * 获取ip地址
     * @return string
     */
    public function getClientIp()
    {
        return $this->client_ip;
    }

    /**
     * 获取请求方式
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 获取请求url
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * 获取请求参数
     * @param string $index
     * @param null $default
     * @return array|mixed|null
     */
    public function getParams($index = '', $default = null)
    {
        $params = $this->params;
        $index = explode('.', $index);
        foreach ($index as $dex) {
            if (isset($params[$dex])) {
                $params = $params[$dex];
            } else {
                return $default;
            }
        }
        return $params;
    }

    /**
     * 获取失败信息
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->error_msg;
    }


}