<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\service\OrderService;
use app\service\PaymentService;
use app\service\ExpressService;

/**
 * 订单管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Order extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function __construct()
    {
        // 调用父类前置方法
        parent::__construct();

        // 登录校验
        $this->IsLogin();

        // 权限校验
        $this->IsPower();
    }

    /**
     * 订单列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     */
    public function Index()
    {
        // 总数
        $total = OrderService::OrderTotal($this->form_where);

        // 分页
        $page_params = [
            'number'    =>  $this->page_size,
            'total'     =>  $total,
            'where'     =>  $this->data_request,
            'page'      =>  $this->page,
            'url'       =>  MyUrl('admin/order/index'),
        ];
        $page = new \base\Page($page_params);

        // 获取列表
        $data_params = [
            'm'         => $page->GetPageStarNumber(),
            'n'         => $this->page_size,
            'where'     => $this->form_where,
            'is_public' => 0,
            'user_type' => 'admin',
        ];
        $ret = OrderService::OrderList($data_params);

        // 发起支付 - 支付方式
        $pay_where = [
            'where' => ['is_enable'=>1, 'payment'=>config('shopxo.under_line_list')],
        ];
        $this->assign('buy_payment_list', PaymentService::BuyPaymentList($pay_where));

        // 快递公司
        $this->assign('express_list', ExpressService::ExpressList());

        // 加载百度地图api
        $this->assign('is_load_baidu_map_api', 1);

        // 基础参数赋值
        $this->assign('params', $this->data_request);
        $this->assign('page_html', $page->GetPageHtml());
        $this->assign('data_list', $ret['data']);
        return $this->fetch();
    }

    /**
     * 详情
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     */
    public function Detail()
    {
        if(!empty($this->data_request['id']))
        {
            // 条件
            $where = [
                ['is_delete_time', '=', 0],
                ['id', '=', intval($this->data_request['id'])],
            ];

            // 获取列表
            $data_params = [
                'm'         => 0,
                'n'         => 1,
                'where'     => $where,
                'is_public' => 0,
                'user_type' => 'admin',
            ];
            $ret = OrderService::OrderList($data_params);
            $data = (empty($ret['data']) || empty($ret['data'][0])) ? [] : $ret['data'][0];
            $this->assign('data', $data);
        }
        return $this->fetch();
    }

    /**
     * [Delete 订单删除]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Delete()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 删除操作
        $params = $this->data_request;
        $params['user_id'] = $params['value'];
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        $params['user_type'] = 'admin';
        return OrderService::OrderDelete($params);
    }

    /**
     * [Cancel 订单取消]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Cancel()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 取消操作
        $params = $this->data_request;
        $params['user_id'] = $params['value'];
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        return OrderService::OrderCancel($params);
    }

    /**
     * [Delivery 订单发货/取货]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Delivery()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 发货操作
        $params = $this->data_request;
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        return OrderService::OrderDelivery($params);
    }

    /**
     * [Collect 订单收货]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Collect()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 收货操作
        $params = $this->data_request;
        $params['user_id'] = $params['value'];
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        return OrderService::OrderCollect($params);
    }

    /**
     * [Confirm 订单确认]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Confirm()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 订单确认
        $params = $this->data_request;
        $params['user_id'] = $params['value'];
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        return OrderService::OrderConfirm($params);
    }

    /**
     * 订单支付
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-28
     * @desc    description
     */
    public function Pay()
    {
        $params = $this->data_request;
        $params['user'] = $this->admin;
        $params['user']['user_name_view'] = '管理员'.'-'.$this->admin['username'];
        return OrderService::AdminPay($params);
    }
}
?>