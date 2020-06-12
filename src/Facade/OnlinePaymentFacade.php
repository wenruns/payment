<?php
/**
 * 线上支付门面
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/30
 * Time: 8:51
 */

namespace wenruns\payment\Facade;


use Illuminate\Database\Eloquent\Model;
use wenruns\payment\service\BillFileAnalysisService;
use wenruns\payment\service\OnlinePaymentReqService;
use wenruns\payment\service\OrderTrackingService;
use wenruns\payment\service\PlaceAnOrderService;

class OnlinePaymentFacade
{
    /**
     * 服务提供者
     * @var
     */
    protected static $service;

    /**
     * 订单查询服务提供者
     * @var
     */
    protected static $order_query_service;

    protected static $place_order;


    /**
     * 订单历史列表查询服务
     * @var
     */
    protected static $order_history_list_query_service;

    /**
     * @return BillFileAnalysisService
     */
    protected static function getOrderHistoryListQueryService()
    {
        if (empty(self::$order_history_list_query_service)) {
            self::$order_history_list_query_service = new BillFileAnalysisService(self::getService());
        }
        return self::$order_history_list_query_service;
    }

    /**
     * @return OnlinePaymentReqService
     */
    protected static function getService()
    {
        if (empty(self::$service)) {
            self::$service = new OnlinePaymentReqService();
        }
        return self::$service;
    }

    /**
     * 下单服务
     * @return PlaceAnOrderService
     */
    protected static function getPlaceOrder()
    {
        if (empty(self::$place_order)) {
            self::$place_order = new PlaceAnOrderService(self::getService());
        }
        return self::$place_order;
    }

    /**
     * 获取订单服务提供者
     * @return OrderTrackingService
     */
    protected static function getOrderQueryService()
    {
        if (empty(self::$order_query_service)) {
            self::$order_query_service = new OrderTrackingService(self::getService());
        }
        return self::$order_query_service;
    }

    /**
     * 下载东莞银行线上支付账单
     * @param $trade_date
     * @param bool $force
     * @param string $app_device_id
     * @return \App\Services\OnlinePayment\Response
     * @throws \Exception
     */
    public static function billFileReqBod($trade_date, $force = false, $app_device_id = '')
    {
        return self::getOrderHistoryListQueryService()
            ->request('bodRecFileReq')
            ->tradeDate($trade_date)
            ->force($force)
            ->appDeviceId($app_device_id)
            ->run();
    }

    /**
     * 下载微信支付账单
     * @param $trade_date
     * @param bool $force
     * @return \App\Services\OnlinePayment\Response
     * @throws \Exception
     */
    public static function billFileReqWx($trade_date, $force = false)
    {
        return self::getOrderHistoryListQueryService()
            ->request('weChatRecFileReq')
            ->force($force)
            ->tradeDate($trade_date)
            ->run();
    }


    /**
     * 订单查询（东莞银行、微信原生支付）
     * @param string $order_id
     * @param string $transaction_id
     * @param Model|null $bill_info
     * @return \App\Services\OnlinePayment\Response
     * @throws \Exception
     */
    public static function orderQuery($order_id = '', $transaction_id = '', Model $bill_info = null)
    {
        return self::getOrderQueryService()
            ->orderId($order_id)
            ->transactionId($transaction_id)
            ->billInfo($bill_info)
            ->run();
    }

    /**
     * 东莞银行账单历史
     * @param $tranDate
     * @param string $appDeviceId
     * @return mixed
     */
    public static function bodRecFileReq($tranDate, $appDeviceId = '')
    {
        return self::bodRecFileReqExec($tranDate, $appDeviceId);
    }

    /**
     * 东莞银行账单查询
     * @param string $orderId
     * @param string $ptOrderId
     * @param string $appDeviceId
     * @return mixed
     */
    public static function bodOrderQuery($orderId = '', $ptOrderId = '', $appDeviceId = '')
    {
        return self::bodOrderQueryExec($orderId, $ptOrderId, $appDeviceId);
    }

    /**
     * 微信订单查询
     * @param string $transaction_id
     * @param string $out_trade_no
     * @param string $sign_type
     * @return mixed
     */
    public static function weChatOrderQuery($transaction_id = '', $out_trade_no = '', $sign_type = '')
    {
        return self::weChatOrderQueryExec($transaction_id, $out_trade_no, $sign_type);
    }


    /**
     * 微信账单历史请求
     * @param $tranDate 交易日期
     * @return mixed
     */
    public static function weChatRecFileReq($tranDate)
    {
        return self::weChatRecFileReqExec($tranDate);
    }


    /**
     * 下单服务
     * @param string $method
     * @return \App\Services\OnlinePayment\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function placeAnOrder($method = '')
    {
        $res = self::getPlaceOrder()->run();
        return $res;
    }


    /**
     * @param $name
     * @param $arguments
     * @return \wenruns\payment\service\Response
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
        $name = rtrim($name, 'Exec');
        return self::getService()->execMethod($name, $arguments);
    }

}