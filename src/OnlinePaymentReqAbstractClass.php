<?php
/**
 * 请求扩展抽象类
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/23
 * Time: 18:07
 */

namespace wenruns\payment;


use wenruns\payment\service\OnlinePaymentReqService;

abstract class OnlinePaymentReqAbstractClass
{
    /**
     * 请求类型
     * @var string
     */
    protected $method = 'GET';


    /**
     * 请求url
     * @var string
     */
    protected $url = '';

    /**
     * 备用请求url
     * 当请求url失败后，尝试请求该url
     * @var string
     */
    protected $alternate_url = '';

    /**
     * 支付渠道
     * @var bool
     */
    protected $pay_channel = 'wx_native';


    /**
     * 莞付系统的应用Id
     * @var
     */
    protected $bod_appId;

    /**
     * 莞付系统的秘钥
     * @var
     */
    protected $bod_appSecret;

    /**
     * 填写接入方服务器IP，用于开放平台回溯业务发起端。
     * @var string
     */
    protected $clientID = '';

    /**
     * 详见“数据签名和验签”。申请密钥和申请token时为空。
     * @var string
     */
    protected $signature = '';

    /**
     * 由请求方保证全局唯一即可。
     * @var string
     */
    protected $nonce = '';

    /**
     * 精确到毫秒的请求发送时间，格式yyyyMMddhhmmssSSS。
     * @var string
     */
    protected $timestamp = '';

    /**
     * 详见“会话token”。申请密钥和获取token时为空。
     * @var string
     */
    protected $token = '';

    /**
     * 商户号（由莞付系统生成）
     * @var string
     */
    protected $mercId = '';

    /**
     * 渠道号（由莞付系统确认和分配）
     * @var string
     */
    protected $channelId = '';


    /**
     * 微信支付分配的公众账号ID（企业号corpid即为此appId）
     * @var string
     */
    protected $appid = '';

    /**
     * 微信商户平台设置的密钥key
     * @var string
     */
    protected $key = '';


    /**
     * 微信支付分配的商户号
     * @var string
     */
    protected $mch_id = '';


    /**
     * 线上支付请求服务提供者
     * @var OnlinePaymentReqService|null
     */
    protected $onlinePaymentReqService = null;


    /**
     * OnlinePaymentReqAbstractClass constructor.
     * @param OnlinePaymentReqService|null $onlinePaymentReqService
     */
    public function __construct(OnlinePaymentReqService $onlinePaymentReqService = null)
    {
        $this->onlinePaymentReqService = $onlinePaymentReqService;
    }

    /**
     * 初始化配置参数
     */
    protected function initData()
    {
        $this->bod_appId = env('BOD_BKQW_APPID', '');
        $this->bod_appSecret = env('BOD_BKQW_APPSECRET');
        $this->clientID = env('BOD_BKQW_CLIENTID', '');
        $this->mercId = env('BOD_BKQW_MERCID', '');
        $this->channelId = env('BOD_BKQW_CHANNELID', '');
        $this->appid = env('WECHAT_BKQW_APPID');
        $this->mch_id = env('WECHAT_BKQW_MCH_ID');
        $this->key = env('WECHAT_BKQW_PAY_KEY');

        $this->nonce = $this->getNonceStr();
        $this->timestamp = $this->getTimeStamp();
        $this->token = $this->getToken();
    }

    /**
     * 获取签名
     *
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function getSignature($data)
    {
        $sign = '';
        switch ($this->pay_channel) {
            case 'wx_bod':
                break;
            case 'wx_native':
            default:
                $sign = $this->makeSignatureWx($data);
        }
        return $sign;
    }

    /**
     * 生成微信支付签名
     * @param $data
     * @return string
     * @throws \Exception
     */
    protected function makeSignatureWx($data)
    {
        $data = array_filter($data);
        ksort($data);
        $string = $this->ToUrlParams($data);
        $string .= '&key=' . $this->key;
        if (!isset($data['sign_type']) || empty($data['sign_type']) || $data['sign_type'] == 'MD5') {
            return strtoupper(MD5($string));
        } else if (isset($data['sign_type']) && $data['sign_type'] == 'HMAC-SHA256') {
            return strtoupper(hash_hmac("sha256", $string, $this->key));
        } else {
            throw new \Exception('签名算法不正确');
        }
    }


    /**
     * 格式化参数格式化成url参数
     * @param $data
     * @param string $index
     * @return string
     */
    public function ToUrlParams($data, $index = '')
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $buff .= $this->ToUrlParams($v, empty($index) ? $k : $index . "[$k]");
            } elseif ($v != "") {
                $buff .= (empty($index) ? $k : $index . "[$k]") . "=$v&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }


    /**
     * 获取时间戳
     */
    public function getTimeStamp()
    {
        return date('YmdHis');
    }

    /**
     * 获取随机字符串
     * @param int $len
     * @param bool $upper
     * @return bool|string
     */
    public function getNonceStr($len = 32, $upper = false)
    {
        $arr = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
        shuffle($arr);
        $str = implode('', $arr);
//        $str = str_shuffle($str);
        $str = substr($str, mt_rand(0, mb_strlen($str) - $len), $len);
        if ($upper) {
            $str = strtoupper($str);
        }
        return $str;
    }

    /**
     * 获取token
     */
    public function getToken()
    {
        if (isset($this->wx_native) && $this->wx_native) { // 微信原生支付

        } else { // 东莞银行线上支付


        }
    }


    /**
     * 执行方法
     * @param $arguments
     * @return Response
     * @throws \Exception
     */
    public function exec($arguments)
    {
        $this->initData();

        if (empty($this->onlinePaymentReqService)) {
            $this->onlinePaymentReqService = new OnlinePaymentReqService();
        }

        $options = $this->options(...$arguments);

//        try {
        $response = $this->onlinePaymentReqService->request($this->getUrl(), $options, $this->getMethod());
        if (!$response->getApiStatus()) {
            $alternate_url = $this->getAlternateUrl();
            if ($alternate_url) {
                $response = $this->onlinePaymentReqService->request($alternate_url, $options, $this->getMethod(), $response);
            }
        }
//        } catch (\Exception $e) {
//            // 切换备用url
//            $alternate_url = $this->getAlternateUrl();
//            if (empty($alternate_url)) {
//                throw new \Exception($e);
//            }
//            $response = $this->onlinePaymentReqService->request($alternate_url, $options, $this->getMethod());
//        }

        return $this->format($response);
    }


    /**
     * 获取请求url
     * @throws \Exception
     */
    public function getUrl()
    {
        $url = isset($this->url) ? $this->url : '';
        if (empty($url)) {
            throw new \Exception('请设置请求URL');
        }
        return $url;
    }


    /**
     * 获取备用请求url
     * @return bool
     */
    public function getAlternateUrl()
    {
        return isset($this->alternate_url) && $this->alternate_url ? $this->alternate_url : false;
    }


    /**
     * 获取请求方式
     * @return string
     */
    public function getMethod()
    {
        return isset($this->method) ? $this->method : 'GET';
    }


    /**
     * 格式化请求返回结果
     * @param $response
     * @return Response
     */
    abstract public function format(Response $response);


    /**
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }
        throw new \Exception('没找到方法：' . $name);
    }
}