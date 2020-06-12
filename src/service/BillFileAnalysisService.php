<?php
/**
 * 请求账单历史，并解析数据，保存数据库
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/26
 * Time: 16:34
 */

namespace wenruns\payment\service;

use wenruns\payment\OnlinePaymentAbstractClass;
use wenruns\payment\Response;

class BillFileAnalysisService extends OnlinePaymentAbstractClass
{
    /**
     * 请求方法
     * @var
     */
    protected $request_method;

    /**
     * 是否开启强制请求
     * @var bool
     */
    protected $force = false;

    /**
     * 设备唯一标识
     * @var string
     */
    protected $app_device_id = '';

    /**
     * 交易日期
     * @var
     */
    protected $trade_date;

    /**
     * @return bool|mixed
     */
    protected function onBefore()
    {
        // TODO: Implement onBefore() method.
//        return $this->checkParams() && $this->hasRequested();
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

    /**
     * @return mixed
     */
    protected function method()
    {
        // TODO: Implement method() method.
        return $this->request_method;
    }

    /**
     * @return array
     */
    protected function options()
    {
        // TODO: Implement options() method.
        return [
            $this->trade_date,
            $this->app_device_id,
        ];
    }

    /**
     * 是否强制请求
     * @param bool $force
     * @return $this
     */
    public function force($force = true)
    {
        $this->force = $force;
        return $this;
    }

    /**
     * 设置参数app_device_id
     * @param $appDeviceId
     * @return $this
     */
    public function appDeviceId($appDeviceId)
    {
        $this->app_device_id = $appDeviceId;
        return $this;
    }

    /**
     * 设置请求方法
     * @param $method
     * @return $this
     */
    public function request($method)
    {
        $this->request_method = $method;
        return $this;
    }

    /**
     * 设置交易日期
     * @param $trade_date
     * @return $this
     */
    public function tradeDate($trade_date)
    {
        $this->trade_date = $trade_date;
        return $this;
    }


    /**
     * 检测参数是否正常
     * @return bool
     */
    protected function checkParams()
    {
        if (empty($this->request_method) || empty($this->trade_date)) {
            return false;
        }
        return true;
    }

    /**
     * 判断该交易日期的清单历史是否已请求过
     * @return bool
     */
    protected function hasRequested()
    {
        return true;
    }
}