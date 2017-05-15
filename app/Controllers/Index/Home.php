<?php
/**
 * User: yongli
 * Date: 17/5/10
 * Time: 19:52
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Index;

use CreditorRightModel;
use AgencyUserModel;
use CustomerBlacklistModel;
use AgencyBackListModel;
use CustomerModel;

class Home extends Auth
{
    /**
     *  构造函数
     */
    public function initialization()
    {
        parent::initialization();
    }

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

    // 校验规则
    protected $rules = [
        'name'      => 'required|min_length[1]|max_length[128]',
        'card_id'   => 'required|exact_length[18]',
        'province'  => 'required|exact_length[2]',
        'city'      => 'required|exact_length[1]',
        'car_id'    => 'required|exact_length[5]',
        'money'     => 'required|min_length[1]|max_length[12]',
        'loan_time' => 'required|is_date',
        'method'    => 'required|is_natural|exact_length[1]',
        'phone'     => 'required|is_natural|exact_length[6]',
        'status'    => 'required|is_natural|exact_length[1]'
    ];

    // 提示信息
    protected $message = [
        'name'      => ['required' => '机构名称不能为空', 'min_length' => '最小长度为1', 'max_length' => '最大长度为128'],
        'card_id'   => ['required' => '身份证号不能空', 'exact_length' => '请输入合法身份证号'],
        'province'  => ['required' => '身份简称不能为空', 'exact_length' => '请输入合法的省份简称'],
        'city'      => ['required' => '城市字母不能为空', 'exact_length' => '请输入合法的城市字母'],
        'car_id'    => ['required' => '车牌照不能不空', 'exact_length' => '请输入五位的的字母和数字组合的车牌号'],
        'phone'     => ['required' => '账号不能为空', 'is_natural' => '请输入正确的手机后六位号码', 'exact_length' => '请输入手机后六位'],
        'money'     => ['required' => '抵押方式不能为空', 'min_length' => '最小长度为1', 'max_length' => '最大长度为12'],
        'loan_time' => ['required' => '借款时间不能为空', 'is_date' => '请输入格式(如: 2017-01-01)日期'],
        'method'    => ['required' => '抵押方式不能为空', 'is_natural' => '请输入合法的抵押方式', 'exact_length' => '请输入正确的抵押方式'],
        'status'    => ['required' => '还款状态不能为空', 'exact_length' => '请选择合法的还款状态'],
    ];

    public function getIndex()
    {
        $this->assign('userInfo', $_SESSION['pid']);
        $this->display();
    }

    /**
     * 查询业务
     */
    public function search()
    {
        $addData              = $this->request->getPost();
        $condition['name']    = $addData['username'];
        $condition['card_id'] = $addData['card'];
        $condition['phone']   = substr($addData['s_phone'], -6);
        $build                = CreditorRightModel::select('id', 'name', 'money', 'loan_time', 'method', 'status',
            'create_by')->where($condition)->orWhere('phone', $addData['s_phone']);
        if ($addData['s_car_id']) {
            $condition['car_id'] = $addData['s_province'] . $addData['s_city'] . $addData['s_car_id'];
            $build->whereCarId($condition['car_id']);
        }
        // 查询数据
        $data = $build->orWhere('phone', $addData['s_phone'])->get()->toArray();
        // 替换手机号码(将手机号码补全)
        if ($data) {
            $updateData['phone'] = $addData['s_phone'] . '';
            $ids                 = array_column($data, 'id');
            CreditorRightModel::whereIn('id', $ids)->update($updateData);
        }
        $agency = $this->getAgencyUser();
        foreach ($data as $key => $value) {
            $data[$key]['create_by'] = $agency[$value['create_by']] ?? '';
            $data[$key]['method']    = $this->way[$value['method']] ?? '';
            $data[$key]['status']    = $this->status[$value['status']] ?? '';
            $data[$key]['loan_time'] = date('Y-m-d', $value['loan_time']);
        }
        $data = $data ? $data : [];
        callBack(0, $data);
    }

    /**
     * 添加债权信息
     */
    public function add()
    {
        // TODO 这期先关闭校验 开始校验
        //        if (!$this->validate($this->request, $this->rules, $this->message)) {
        //            // 校验失败,输出错误信息
        //            callBack(1, '', $this->errors);
        //        }
        $addData                = $this->request->getPost();
        $addData['car_id']      = $addData['province'] . $addData['city'] . $addData['car_id'];
        $addData['loan_time']   = strtotime($addData['loan_time']);
        $addData['create_time'] = time();
        $addData['update_time'] = time();
        $addData['create_by']   = $_SESSION['uid'] ?? 1;
        unset($addData['province'], $addData['city']);
        // 添加数据
        $id = CreditorRightModel::insertGetId($addData);
        if (!$id) {
            callBack(2, '', '添加失败!');
        }
        callBack(0);
    }

    /**
     * 获得债权信息
     *
     * @param $id
     */
    public function getCreditById($id)
    {
        $data = CreditorRightModel::select([
            'id',
            'name',
            'card_id',
            'car_id',
            'phone',
            'money',
            'loan_time',
            'method',
            'status'
        ])->whereId($id)->whereCreateBy(1)->get()->toArray();
        foreach ($data as $key => $value) {
            $data[$key]['province']  = mb_substr($value['car_id'], 0, 1);
            $data[$key]['city']      = mb_substr($value['car_id'], 1, 1);
            $data[$key]['car_id']    = mb_substr($value['car_id'], -5);
            $data[$key]['loan_time'] = date('Y-m-d', $value['loan_time']);
        }
        $data = $data ? $data[0] : [];
        callBack(0, $data);
    }

    /**
     * 保存债权信息
     *
     */
    public function update()
    {
        $addData                = $this->request->getPost();
        $addData['car_id']      = $addData['province'] . $addData['city'] . $addData['car_id'];
        $addData['loan_time']   = strtotime($addData['loan_time']);
        $addData['update_time'] = time();
        $addData['create_by']   = $_SESSION['uid'] ?? 1;
        unset($addData['province'], $addData['city']);
        // 保存数据
        $id = CreditorRightModel::whereId($addData['id'])->update($addData);
        if (!$id) {
            callBack(2, '', '保存失败!');
        }
        callBack(0);
    }

    /**
     * 删除债权
     *
     * @param $id
     */
    public function delete($id)
    {
        $updateData['is_delete'] = 1;
        $status                  = CreditorRightModel::whereId($id)->update($updateData);
        if (!$status) {
            callBack(2, '', '删除失败!');
        }
        callBack(0);
    }

    /**
     * 征信查询
     */
    public function searchCredit()
    {
        $this->assign('userInfo', $_SESSION['pid']);
        $this->display();
    }

    /**
     * 黑名单
     */
    public function backList()
    {
        $this->assign('userInfo', $_SESSION['pid']);
        $this->display();
    }

    /**
     * 查询不良客户及黑中介
     */
    public function getBackList()
    {
        $type = $this->request->getPost('type');
        // 默认查询不良客户 1:不良客户 2: 黑中介
        $type = $type ? $type : 1;
        if ($type == 2) {
            $blacklist = AgencyBackListModel::select(['agency_user_id', 'name', 'phone', 'img'])->get()->toArray();
        } else {
            $blacklist = CustomerBlacklistModel::select([
                'id',
                'name',
                'card_id',
                'phone',
                'car_id',
                'agency'
            ])->get()->toArray();
        }
        callBack(0, $blacklist);
    }

    /**
     * 车辆评估
     */
    public function carAssessment()
    {
        $this->assign('userInfo', $_SESSION['pid']);
        $this->display();
    }

    /**
     * 业务管理
     */
    public function business()
    {
        $this->assign('userInfo', $_SESSION['pid']);
        $this->display();
    }

    /**
     * 客户管理和债权客户数据
     */
    public function getCustomerAndCreditData()
    {
        $type = $this->request->getPost('type');
        // 默认查询不良客户 1:客户管理 2: 债权管理
        $type = $type ? $type : 2;
        if ($type == 2) {
            $data = CreditorRightModel::select([
                'id',
                'name',
                'card_id',
                'car_id',
                'phone',
                'method',
                'status',
                'money',
                'loan_time'
            ])->get()->toArray();
            foreach ($data as $key => $value) {
                $data[$key]['method']    = $this->way[$value['method']] ?? '';
                $data[$key]['status']    = $this->status[$value['status']] ?? '';
                $data[$key]['loan_time'] = date('Y-m-d', $value['loan_time']);
            }
        } else {
            $build = CustomerModel::select(['name', 'card_id', 'car_id', 'phone']);
            if ($this->request->getPost('name')) {
                $build->whereName($this->request->getPost('name'));
            }
            $data = $build->get()->toArray();
        }
        callBack(0, $data);
    }

    /**
     * 催收管理
     */
    public function collection()
    {
        $this->assign('userInfo', $_SESSION['pid']);
        $this->display();
    }

    /**
     * 获得中介人员所对应的的组织机构
     *
     * @return array
     */
    protected function getAgencyUser()
    {
        $data   = AgencyUserModel::select('id', 'agency_name')->get()->toArray();
        $result = [];
        foreach ($data as $key => $value) {
            $result[$value['id']] = $value['agency_name'];
        }

        return $result;
    }
}