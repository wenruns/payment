<?php
/**
 * 微信原生支付，订单查询
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/24
 * Time: 13:34
 */

namespace wenruns\payment\service\WeChat;

use wenruns\payment\OnlinePaymentReqAbstractClass;
use wenruns\payment\Response;
use wenruns\payment\Traits\XmlTrait;

class WeChatOrderQueryService extends OnlinePaymentReqAbstractClass
{
    use XmlTrait;

    protected $pay_channel = 'wx_native';

    /**
     * 请求方式
     * @var string
     */
    protected $method = 'POST';

    /**
     * 请求url
     * @var string
     */
    protected $url = 'https://api.mch.weixin.qq.com/pay/orderquery';


    /**
     * 备用请求url
     * 当url请求失败后，尝试请求该url
     * @var string
     */
    protected $alternate_url = 'https://api2.mch.weixin.qq.com/pay/orderquery';


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
     * 请求参数
     * @param string $transaction_id
     * @param string $out_trade_no
     * @param string $sign_type
     * @return array
     * @throws \Exception
     */
    public function options($transaction_id = '', $out_trade_no = '', $sign_type = '')
    {
        $data = [
            'appid' => $this->appid,
            'mch_id' => $this->mch_id,
            'nonce_str' => $this->nonce,
            'out_trade_no' => empty($transaction_id) ? $out_trade_no : '',
            'transaction_id' => $transaction_id,
            'sign_type' => $sign_type,
        ];
        $data['sign'] = $this->getSignature($data);


        return [
            'body' => $this->toXml($data),
        ];
    }


    /**
     * 请求结果格式化
     * @param $response
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

}