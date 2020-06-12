<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/3/30
 * Time: 14:57
 */

namespace wenruns\payment;


use wenruns\payment\service\OnlinePaymentReqService;

abstract class OnlinePaymentAbstractClass
{
    protected $request_provider = null;

    public function __construct(OnlinePaymentReqService $request_provider = null)
    {
        $this->request_provider = $request_provider;
    }

    public function requestProvider(OnlinePaymentReqService $request_provider)
    {
        $this->request_provider = $request_provider;
        return $this;
    }


    /**
     *
     * @return OnlinePaymentReqService
     */
    protected function getRequestProvider()
    {
        if (empty($this->request_provider)) {
            $this->request_provider = new OnlinePaymentReqService();
        }
        return $this->request_provider;
    }


    /**
     * 执行操作
     * @return Response
     * @throws \Exception
     */
    public function run()
    {
        if (false === $this->onBefore()) {
            $response = new Response();
            $response->status(false, 2001, '业务逻辑在onBefore中被终止。');
            return $response;
        }
        $method = $this->method();
        $options = $this->options();
        if ($method instanceof OnlinePaymentReqAbstractClass) {
            $response = $method->exec($options);
        } else if ($method instanceof Response) {
            $response = $method;
        } else if ($method === false) {
            $response = new Response();
            $response->status(false, 2002, '业务逻辑在method中被终止。');
        } else {
            $response = $this->getRequestProvider()->$method(...$options);
        }
        $response = $this->onAfter($response);
        return $response;
    }


    /**
     *
     * @return mixed
     */
    abstract protected function method();

    /**
     * @return array
     */
    abstract protected function options();

    /**
     * @return mixed
     */
    abstract protected function onBefore();

    /**
     * @param Response $response
     * @return Response
     */
    abstract protected function onAfter(Response $response);
}