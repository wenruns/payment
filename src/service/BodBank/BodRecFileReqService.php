<?php
/**
 * 东莞银行支付，订单历史查询，数据解析，保存数据库
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/24
 * Time: 11:53
 */

namespace wenruns\payment\service\BodBank;

use wenruns\payment\OnlinePaymentReqAbstractClass;
use wenruns\payment\Response;
use wenruns\payment\service\OnlinePaymentReqService;
use wenruns\payment\Traits\AliOssTrait;

class BodRecFileReqService extends OnlinePaymentReqAbstractClass
{
    use AliOssTrait;

    protected $pay_channel = 'wx_bod';

    /**
     * 请求方法
     * @var string
     */
    protected $method = 'POST';

    /**
     * 请求url
     * @var string
     */
    protected $url = 'https://bod.dgcb.com.cn:19003/gateway/gateway/recFileReq'; // 测试环境
//    protected $url = 'https://kfyh.dongguanbank.cn:19003/gateway/recFileReq'; // 正式环境


    protected $download_file_url = 'http://bod.dgcb.com.cn:19003/bankservice/dgbankpay/api/v1/filedownquery';

    /**
     * 备用请求url
     * 当请求url失败后，尝试请求该url
     * @var string
     */
    protected $alternate_url = '';

    protected $trade_date = '';


    public function __construct(OnlinePaymentReqService $onlinePaymentReqService = null)
    {
        parent::__construct($onlinePaymentReqService);
        $server = rtrim(env('BOD_ONLINE_PAYMENT_API_SERVER', 'http://39.108.171.155'), '/');

        $this->url = $server . '/bankservice/dgbankpay/api/v1/orderaccount';
        $this->download_file_url = $server . '/bankservice/dgbankpay/api/v1/filedownquery';

        $this->upload_bill_file_to_ali_oss = env('UPLOAD_BILL_FILE_TO_ALI_OSS', true);
        $this->delete_local_file = env('DELETE_LOCAL_BILL_FILE', true);
    }


    /**
     * 请求参数处理
     * @param $app_device_id
     * @param $trade_date
     * @return array
     */
    public function options($trade_date, $app_device_id = '')
    {
        $this->trade_date = $trade_date;
        return [
            'json' => [
                'trandate' => $trade_date,
//                'clientID' => $this->clientID,
//                'signature' => $this->signature,
//                'nonce' => $this->nonce,
//                'timestamp' => $this->timestamp,
//                'token' => $this->token,
//                'appDeviceId' => empty($app_device_id) ? $this->bod_appId : $app_device_id,
//                'bizContent' => [
//                    'channelId' => $this->channelId,
//                    'mercId ' => $this->mercId,
//                    'tranDate' => $trade_date,
//                ]
            ],
            'timeout' => 30,
        ];
    }


    /**
     * 请求账单文件下载地址
     * @param Response $response
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getFileContent(Response $response)
    {
        $options = [
            'json' => [
                'filename' => 'GPY_' . $this->mercId . '_' . $this->trade_date . '.txt',
                'fieldate' => $this->trade_date,
            ]
        ];
        $res = $this->onlinePaymentReqService->request($this->download_file_url, $options, 'POST', $response);
        $content = json_decode($res->getContent(), true);
        if ($content['code'] != 200) {
            $res->errorMsg($content['msg'])->status(false, $content['code'], $content['msg']);
            return $res;
        }
        $data = json_decode($content['data'], true);
//        dd($res, $data);
        try {
            foreach ($data['urls-list'] as $key => $item) {
                $res = $this->downFile($item['url'], $item['fileName'], $res);
            }
        } catch (\Exception $e) {
            $res->errorMsg('请求api发生错误', $e)->status(false, '2001', '解析账单文件失败');
        }
        return $res;
    }

    /**
     * 下载账单文件
     * @param $url
     * @param $filename
     * @param Response $response
     * @return bool
     * @throws \Exception
     */
    protected function downFile($url, $filename, Response $response)
    {
        if (empty($url)) {
            return $response;
        }
        $res = $this->onlinePaymentReqService->request($url, [], 'GET', $response);
        $content = $res->getContent();
        return $this->parse($content, $res);
    }


    /**
     * 格式化请求结果
     * @param Response $response
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function format(Response $response)
    {
//        // TODO: Implement format() method.
        $content = $response->getContent();
        if (empty($content)) {
            return $response;
        }
        $content = json_decode($content, true);
        if ($content['code'] != 200) {
            $response->errorMsg($content['msg'])->status(false, $content['code'], $content['msg']);
            return $response;
        }
        if (empty($content['data'])) {
            $response = $this->getFileContent($response);
        } else {
            $response = $this->parse($content['data'], $response);
        }
        return $response;
    }


    /**
     * 账单内容解析
     * @param $content
     * @param Response $response
     * @return Response|bool
     */
    protected function parse($content, Response $response)
    {
        return $response;
    }
}