<?php
/**
 * User: yongli
 * Date: 17/5/10
 * Time: 17:46
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Admin;

use CreditorRightModel;
use AgencyBackListModel;
use YP\Libraries\YP_Pagination as Pagination;

/**
 * Class StatisticalReport
 * 统计报表控制器
 *
 * @package App\Controllers\Admin
 */
class StatisticalReport extends Auth
{

    /**
     * 还款状态
     *
     * @var array
     */
    protected $status = [
        1 => '正常还款',
        2 => '0~15天逾期',
        3 => '15~30天逾期',
        4 => '30天以上逾期',
        5 => '拖车',
        6 => '二押',
        7 => '正常结清'
    ];

    /**
     * 抵押方式
     *
     * @var array
     */
    protected $way = [
        1 => '押证',
        2 => '押车',
        3 => '按揭押车',
        4 => '按揭不押车'
    ];

    /**
     * 客户列表
     */
    public function getCustomer()
    {
        //获取总客户数据
        $data = CreditorRightModel::select([
            'id',
            'name',
            'card_id',
            'car_id',
            'phone',
            'money',
            'create_time',
            'create_by',
        ])->get();

        //循环重组数据
        $tempArr = array();
        foreach ($data as $v) {
            if (array_key_exists($v['card_id'], $tempArr)) { //存在
                $tempArr[$v['card_id']]['num_time']++;
                $tempArr[$v['card_id']]['money'] += $v['money'];
                array_push($tempArr[$v['card_id']]['phone'], $v['phone']);
                array_push($tempArr[$v['card_id']]['car_id'], $v['car_id']);
            } else {
                $tempArr[$v['card_id']] = array(
                    'name' => $v['name'],
                    'card_id' => $v['card_id'],
                    'phone' => array($v['phone']),
                    'car_id' => array($v['car_id']),
                    'num_time' => 1,
                    'money' => $v['money'],
                    'create_time' => $v['create_time'],
                    'by_name' => $v->getAgency['name'],
                    'agency_name' => $v->getAgency['agency_name'],
                );
            }
        }

        $this->assign('data', $tempArr);
        $this->display();
    }

    /**
     * 客户预览
     */
    public function viewCustomer()
    {

    }

    /**
     * 债权列表
     */
    public function getCreditorRight()
    {
        //接收参数
        $name        = $this->request->getGet('name') ? $this->request->getGet('name') : '';
        $card_id        = $this->request->getGet('card_id') ? $this->request->getGet('card_id') : '';

        //分页
        $pagination  = new Pagination();
        $url         = '/Admin/StatisticalReport/getCreditorRight';
        $uri_segment = 3;
        $build       = CreditorRightModel::select('*');

        //筛选条件
        if ($name) {
            $build->where('name', 'like', $name);
        }
        if ($card_id) {
            $build->where('card_id', 'like', $card_id);
        }

        $agencyData = $build->get()->toArray();

        //设置分页类总条数，跳转链接
        $config = setPageConfig(count($agencyData), $url, $this->page, 1);
        // 配置分页
        $pagination->initialize($config);
        // 生成页码
        $page = $pagination->create_links();

        //获取总客户数据
        $build = CreditorRightModel::select([
            'id',
            'name',
            'card_id',
            'car_id',
            'phone',
            'money',
            'loan_time',
            'method',
            'status',
            'create_time',
            'create_by',
        ]);

        //筛选条件
        if ($name) {
            $build->where('name', 'like', $name);
        }
        if ($card_id) {
            $build->where('card_id', 'like', $card_id);
        }

        $data = $build->skip(($this->page - 1) * 1)->take(1)->get();

        //循环匹配抵押方式与还款状态
        foreach ($data as $k=>$v) {
            $v['method'] = $this->way[$v['method']];
            $v['status'] = $this->status[$v['status']];
            $data[$k] = $v;
        }

        $this->assign('page', $page);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 黑名单
     */
    public function getBackList()
    {
        //不良客户
        $data = CreditorRightModel::select([
            'id',
            'name',
            'card_id',
            'car_id',
            'phone',
            'create_by',
        ])->where('status', '4')->get();

        //黑中介
        $data2 = AgencyBackListModel::select(['agency_user_id', 'name', 'phone', 'agency_name', 'create_time'])->get()->toArray();
        //循环匹配客户总数、总订单数据
        foreach($data2 as $k=>$v) {
            $creditArr = CreditorRightModel::select('*')->where('create_by', $v['agency_user_id'])->get()->toArray();
            $v['order_total'] = empty($creditArr) ? 0 : count($creditArr); //订单总数
            $arr = array();
            foreach($creditArr as $v2) {
                $arr[$v2['card_id']] =  1;
            }
            $v['customer_total'] = count($arr); //客户总数
            $data2[$k] = $v;
        }

        $this->assign('data', $data);
        $this->assign('data2', $data2);
        $this->display();
    }

    /**
     * ajax 获取不良客户、黑中介列表
     */
   public function getBkByAjax() {
        //接收参数
        $type = $this->request->getPost('type');
        // 默认查询不良客户 1:不良客户 2: 黑中介
        $type = $type ? $type : 1;

        if ($type == 1) {
            $data = CreditorRightModel::select([
                'id',
                'name',
                'card_id',
                'car_id',
                'phone',
            ])->where('status', '4')->get();
        } else {

        }

        callBack(0, $data);
   }

}