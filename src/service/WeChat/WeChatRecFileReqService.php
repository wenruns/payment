<?php
/**
 * 微信原生支付订单历史查询
 *
 * Created by PhpStorm.
 * User: wen
 * Date: 2020/3/26
 * Time: 16:51
 */

namespace wenruns\payment\service\WeChat;

use wenruns\payment\OnlinePaymentReqAbstractClass;
use wenruns\payment\Response;
use wenruns\payment\service\OnlinePaymentReqService;
use wenruns\payment\Traits\AliOssTrait;
use wenruns\payment\Traits\XmlTrait;

class WeChatRecFileReqService extends OnlinePaymentReqAbstractClass
{

    use XmlTrait;
    use AliOssTrait;

    /**
     * 是否微信原生支付
     * @var bool
     */
    protected $pay_channel = 'wx_native';

    /**
     * 账单文件api
     * @var string
     */
    protected $url = 'https://api.mch.weixin.qq.com/pay/downloadbill';


    /**
     * 账单文件api备用url
     * @var string
     */
    protected $alternate_url = 'https://api2.mch.weixin.qq.com/pay/downloadbill';

    /**
     * 账单文件api请求方式
     * @var string
     */
    protected $method = 'POST';


    public function __construct(OnlinePaymentReqService $onlinePaymentReqService = null)
    {
        parent::__construct($onlinePaymentReqService);

        $this->upload_bill_file_to_ali_oss = env('UPLOAD_BILL_FILE_TO_ALI_OSS', true);
        $this->delete_local_file = env('DELETE_LOCAL_BILL_FILE', true);
    }

    /**
     * 解析日志字段
     * @var array
     */
    protected $log_fields = [
        'total_number',// int(11) DEFAULT NULL COMMENT '总交易数',
        'total_amount',// decimal(11,2) DEFAULT NULL COMMENT '应结订单总金额',
        'total_refund',// decimal(11,2) DEFAULT NULL COMMENT '退款总金额',
        'total_voucher',// decimal(11,2) DEFAULT NULL COMMENT '充值券退款总金额',
        'total_service',// decimal(11,2) DEFAULT NULL COMMENT '手续费总金额',
        'total_order',// decimal(11,2) DEFAULT NULL COMMENT '订单总金额',
        'total_req_refund',// decimal(11,2) DEFAULT NULL COMMENT '申请退款总金额',
    ];


    /**
     * 解析数据流字段
     * @var array
     */
    protected $map_fields = [
        '商户号' => 'mch_id',// varchar(32) DEFAULT NULL COMMENT '商户号',
        '特约商户号' => 'special_mch_id',// varchar(32) DEFAULT NULL COMMENT '特约商户号',
        '设备号' => 'device_ip',// varchar(32) DEFAULT NULL COMMENT '设备号',
    ];


    protected $trade_date = '';

    /**
     * @param Response $response
     * @return Response
     */
    public function format(Response $response)
    {
        // TODO: Implement format() method.
        $content = $response->xmlParse($response->getContent());
        if ($content) {
            $response->setOriginData($content);
            return $response;
        }

        $response = $this->parse($response);
        return $response;
    }


    /**
     * @param $trade_date
     * @return array
     * @throws \Exception
     */
    public function options($trade_date)
    {
        $this->trade_date = $trade_date;
        $data = [
            'appid' => $this->appid, //微信分配的公众账号ID
            'mch_id' => $this->mch_id, //微信支付分配的商户号
            'nonce_str' => $this->nonce, //随机字符串，不长于32位。推荐随机数生成算法
            'bill_date' => $trade_date, //下载对账单的日期，格式：20140603
            'bill_type' => 'ALL', // ALL（默认值），返回当日所有订单信息（不含充值退款订单）SUCCESS，返回当日成功支付的订单（不含充值退款订单）REFUND，返回当日退款订单（不含充值退款订单）RECHARGE_REFUND，返回当日充值退款订单
//            'tar_type' => '', // 非必传参数，固定值：GZIP，返回格式为.gzip的压缩包账单。不传则默认为数据流形式。
        ];
        $data['sign'] = $this->getSignature($data);
        return [
            'body' => $this->toXml($data)
        ];
    }


    /**
     * 解析账单文本
     * @param Response $response
     * @return bool
     */
    protected function parse(Response $response)
    {
        return $response;
    }

}