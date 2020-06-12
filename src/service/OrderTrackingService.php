<?php
/**
 * 订单查询
 *
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/27
 * Time: 19:06
 */

namespace wenruns\payment\service;


//use App\Models\Bill;
use Illuminate\Database\Eloquent\Model;

use wenruns\payment\OnlinePaymentAbstractClass;
use wenruns\payment\Response;

class OrderTrackingService extends OnlinePaymentAbstractClass
{
    /**
     * 订单信息
     * @var
     */
    protected $bill_info;


    /**
     * 第三方平台订单号
     * @var string
     */
    protected $transaction_id = '';

    /**
     * 商户订单号
     * @var string
     */
    protected $order_id = '';


    /**
     * 设置商户订单id
     * @param $value
     * @return $this
     */
    public function orderId($value)
    {
        $this->order_id = $value;
        return $this;
    }

    /**
     * 设置平台订单id
     * @param $value
     * @return $this
     */
    public function transactionId($value)
    {
        $this->transaction_id = $value;
        return $this;
    }

    /**
     * 设置订单信息
     * @param Model $bill_info
     * @return $this
     */
    public function billInfo(Model $bill_info = null)
    {
        $this->bill_info = $bill_info;
        return $this;
    }


    /**
     * 东莞银行查询
     * @return mixed
     */
    protected function bodOrderQuery()
    {
        $res = $this->getRequestProvider()->bodOrderQuery($this->order_id, $this->transaction_id);
        if ($res->getOriginData('code') != '200') {
            $errMsg = $res->getOriginData('msg')
                ? $res->getOriginData('msg')
                : '';
            $res->errorMsg($errMsg);
        }
        return $res;
    }

    /**
     * 微信原生支付订单查询
     * @return mixed
     */
    protected function wxOrderQuery()
    {
        $res = $this->getRequestProvider()->weChatOrderQuery($this->transaction_id, $this->order_id);
        if ($res->getOriginData('return_code') != 'SUCCESS'
            || $res->getOriginData('result_code') != 'SUCCESS'
            || $res->getOriginData('trade_state') != 'SUCCESS') {
            $errMsg = empty($res->getOriginData('trade_state_desc'))
                ? empty($res->getOriginData('err_code_des'))
                    ? (empty($res->getOriginData('return_msg'))
                        ? '' : $res->getOriginData('return_msg'))
                    : $res->getOriginData('err_code_des')
                : $res->getOriginData('trade_state_desc');

            $res->errorMsg($errMsg);
        }
        return $res;
    }


    /**
     * @return bool|mixed
     */
    protected function onBefore()
    {
        // TODO: Implement onBefore() method.
        if (empty($this->order_id) && empty($this->transaction_id)) {
            return false;
        }
        return true;
    }

    /**
     * @return bool|mixed
     */
    protected function method()
    {
        // TODO: Implement method() method.
        return true;
    }

    /**
     * @return array
     */
    protected function options()
    {
        // TODO: Implement options() method.
        return [];
    }

    /**
     * @param Response $response
     * @return Response
     */
    protected function onAfter(Response $response)
    {
        // TODO: Implement onAfter() method.
        return $response;
    }
}