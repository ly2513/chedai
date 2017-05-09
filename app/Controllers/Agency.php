<?php
/**
 * User: yongli
 * Date: 17/5/9
 * Time: 13:47
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers;

use AgencyModel;

/**
 * Class Agency
 *
 * @package App\Controllers
 */
class  Agency extends Auth
{

    /**
     *  构造函数
     */
    public function initialization()
    {
        parent::initialization();
    }

    // 校验规则
    protected $rules = [
        'name'           => 'required|min_length[1]|max_length[128]',
        'license_num'    => 'required|is_natural|exact_length[13]',
        'build_time'     => 'required|is_date',
        'register_money' => 'required|numeric',
        'agency_id'      => 'required|min_length[1]|max_length[128]'
    ];

    // 提示信息
    protected $message = [
        'name'           => ['required' => '机构名称不能为空', 'min_length' => '最小长度为1', 'max_length' => '最大长度为128'],
        'license_num'    => ['required' => '营业执照编号', 'is_natural' => '请输入正确的营业执照编号', 'exact_length' => '输入的营业执照编号有误'],
        'build_time'     => ['required' => '成立时间不能为空', 'is_date' => '请输入正确的日期格式(如:2017-01-01)'],
        'register_money' => ['required' => '注册资金不能为空', 'numeric' => '请输入有效的值'],
        'agency_id'      => ['required' => '机构编号不能为空', 'min_length' => '最小长度为1', 'max_length' => '最大长度为128'],
    ];

    /**
     *
     */
    public function getAgency()
    {

    }

    /**
     * 显示添加机构
     */
    public function addAgency()
    {
        //        AgencyModel::
        $this->display();

    }

    /**
     * 执行添加
     */
    public function add()
    {
        //        P(file_get_contents('php://input'));
        // 开始校验
        if (!$this->validate($this->request, $this->rules, $this->message)) {
            // 校验失败,输出错误信息
            callBack(1, '', $this->errors);
        }
        $addData                = $this->request->getPost();
        $addData['create_time'] = time();
        $addData['update_time'] = time();
        $addData['create_by']   = 1;
        $addData['update_by']   = 1;
        P($addData);
        // 获得自增ID(机构ID)
        $id = AgencyModel::insertGetId($addData);
//        $data = AgencyModel::select('*')->get()->toArray();
        P($data);
        P($id);
    }

    public function updateAgency()
    {

    }

    public function deleteAgency()
    {

    }

}