<?php
/**
 * 东莞银行支付，订单查询
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/24
 * Time: 12:00
 */

namespace wenruns\payment\service\BodBank;

use wenruns\payment\OnlinePaymentReqAbstractClass;
use wenruns\payment\service\OnlinePaymentReqService;
use wenruns\payment\Response;

class BodOrderQueryService extends OnlinePaymentReqAbstractClass
{
    protected $pay_channel = 'wx_bod';

    /**
     * 请求方式
     * @var string
     */
    protected $method = 'POST';

    /**
     * 请求url
     * @var string
     */
    protected $url = 'https://bod.dgcb.com.cn:19003/gateway/gateway/orderquery'; // 测试环境
//    protected $url = 'https://kfyh.dongguanbank.cn:19003/gateway/orderquery'; // 正式环境

    /**
     * 备用url
     * 当请求url失败后，尝试请求该url
     * @var string
     */
    protected $alternate_url = '';


    /**
     * @var array
     */
    protected $tradeState = [
        'SUCCESS' => Bill::BILL_STATUS_S, //支付成功
        'REFUND' => Bill::BILL_STATUS_RU, //转入退款
        'NOTPAY' => Bill::BILL_STATUS_W, //未支付
        'CLOSED' => Bill::BILL_STATUS_C, //已关闭
        'REVOKED' => Bill::BILL_STATUS_R, //已撤销（付款码支付）
        'USERPAYING' => Bill::BILL_STATUS_U, //用户支付中（付款码支付）
        'PAYERROR' => Bill::BILL_STATUS_F, //支付失败(其他原因，如银行返回失败)
    ];


    /**
     * BodOrderQueryService constructor.
     * @param OnlinePaymentReqService|null $onlinePaymentReqService
     */
    public function __construct(OnlinePaymentReqService $onlinePaymentReqService = null)
    {
        parent::__construct($onlinePaymentReqService);
        $server = rtrim(env('BOD_ONLINE_PAYMENT_API_SERVER', 'http://39.108.171.155'), '/');
        $this->url = $server . '/bankservice/dgbankpay/api/v1/orderstatus';
    }

    /**
     * 参数处理
     * @param $appDeviceId
     * @param string $orderId
     * @param string $ptOrderId
     * @return array
     */
    public function options($orderId = '', $ptOrderId = '', $appDeviceId = '')
    {
        return [
            'json' => [
                'ptorderid' => $ptOrderId,
                'orderid' => empty($ptOrderId) ? $orderId : '',
//                'clientID' => $this->clientID,
//                'signature' => $this->signature,
//                'nonce' => $this->nonce,
//                'timestamp' => $this->timestamp,
//                'token' => $this->token,
//                'appDeviceId' => empty($appDeviceId) ? $this->bod_appId : $appDeviceId,
//                'bizContent' => [
//                    'mercId ' => $this->mercId,
//                    'orderId' => $orderId,
//                    'ptOrderId' => $ptOrderId,
//                ]
            ]
        ];
    }

    /**
     *  格式化返回结果
     * @param Response $response
     * @return Response
     */
    public function format(Response $response)
    {
        // TODO: Implement format() method.
        if ($response->getHttpStatusCode() != 200) {
            $response->errorMsg('请求失败，状态码：' . $response->getHttpStatusCode());
        }

        return $response;
    }


    /**
     * 获取文本内容
     * @param $string
     * @return mixed
     */
    protected function jsonParse($string)
    {
//        $string = '{"errorCode":"0000", "errorDesc":"ok", "signature":"1321323213213", "nonce":"46565465465", "timestamp":"15621645651", "bizContent":{ "respCode": "0000","respMsg": "","mercId": "4564654","orderId": "ZD465487965654546465","ptOrderId": "DBJ123165313265465465","payway": "01_WXPAY_AYTP","orderAmt": "100","discountAmt": "0.00","productId": "A4546546546","consumerId": "DJL5465sdf5DFs5df46","payStatus": "S","payMessage": "交易成功","payTime": "20200328122403","deviceInfo": "dasdf4sd5f46s5d4f65","attach": {"hello":"123"},"isRefund": "1"} }';
        $data = json_decode($string, true);
        return $data;
    }


}