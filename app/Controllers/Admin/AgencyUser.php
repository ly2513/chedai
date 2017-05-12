<?php
/**
 * User: yongli
 * Date: 17/5/10
 * Time: 08:59
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Admin;

use AgencyModel;
use AgencyUserModel;
use AgencyBackListModel;
use YP\Libraries\YP_Pagination as Pagination;

/**
 * Class AgencyUser
 *
 */
class AgencyUser extends Auth
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
        'name'      => 'required|min_length[1]|max_length[128]',
        'phone'     => 'required|is_natural|exact_length[11]',
        'agency_id' => 'required|min_length[1]|max_length[11]'
    ];

    // 提示信息
    protected $message = [
        'name'      => ['required' => '机构名称不能为空', 'min_length' => '最小长度为1', 'max_length' => '最大长度为128'],
        'phone'     => ['required' => '账号不能为空', 'is_natural' => '请输入正确的手机号码', 'exact_length' => '输入的手机号码有误'],
        'agency_id' => ['required' => '所属机构不能为空', 'min_length' => '最小长度为1', 'max_length' => '最大长度为11'],
    ];

    /**
     * 获得机构成员
     */
    public function getAgencyUser()
    {
        $pagination  = new Pagination();
        $url         = '/Admin/AgencyUser/getAgencyUser';
        $uri_segment = 3;
        $name        = $this->request->getGet('name') ? $this->request->getGet('name') : '';
        $build       = AgencyUserModel::select('*');
        if ($name) {
            $build->where('name', 'like', $name);
        }
        $agencyData = $build->get()->toArray();
        //设置分页类总条数，跳转链接
        $config = setPageConfig(count($agencyData), $url, $this->page, 1);
        // 配置分页
        $pagination->initialize($config);
        //获得账号数据
        $build = AgencyUserModel::select(['id', 'phone', 'name', 'agency_name', 'status', 'create_time', 'status']);
        if ($name) {
            $build->where('name', 'like', $name);
        }
        $data = $build->skip(($this->page - 1) * 1)->take(1)->get()->toArray();
        //        $sql = AgencyUserModel::select('*')->where('name', 'like', $name)->skip(($this->page - 1) * 1)->take(1)->toSql();
        //        P($sql);
        // 生成页码
        $page = $pagination->create_links();
        foreach ($data as $key => $value) {
            $data[$key]['create_time'] = date('Y-m-d', $value['create_time']);
            $data[$key]['status']      = $value['status'] == 1 ? '已启用' : '已停用';
        }
        $this->assign('page', $page);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 显示添加机构
     */
    public function addAgencyUser()
    {
        $agencyData = $this->getAgencyList();
        $this->assign('data', $agencyData);
        $this->display();
    }

    /**
     * 执行添加
     */
    public function add()
    {
        $agency = $this->getAgencyList(true);
        // 开始校验
        if (!$this->validate($this->request, $this->rules, $this->message)) {
            // 校验失败,输出错误信息
            callBack(1, '', $this->errors);
        }
        $addData = $this->request->getPost();
        //        $agencyData = AgencyModel::select('*')->whereName($addData['name'])->get()->first();
        // 检测机构成员是否已存在
        $agencyData = AgencyUserModel::select('*')->wherePhone($addData['phone'])->get()->toArray();
        if ($agencyData) {
            callBack(2, '', '该成员已存在');
        }
        $addData['img']         = $addData['img'] ? rtrim($addData['img'], ',') : 'Static/images/head.png';
        $addData['agency_name'] = $agency[$addData['agency_id']] ?? '';
        // TODO 以后密码要随机,现在是写死的
        //        $password = getPassword(6);
        $password               = 'admin';
        $addData['password']    = password_hash(md5(md5($password)), PASSWORD_DEFAULT);
        $addData['create_time'] = time();
        $addData['update_time'] = time();
        $addData['create_by']   = 1;
        $addData['update_by']   = 1;
        // 获得自增ID(机构ID) 插入数据
        $id = AgencyUserModel::insertGetId($addData);
        if (!$id) {
            callBack(3, '操作失败');
        }
        // 添加成功
        callBack(0);

    }

    /**
     * 编辑机构
     *
     * @param $id
     */
    public function updateAgencyUser($id)
    {
        $data = AgencyUserModel::select([
            'id',
            'phone',
            'name',
            'img',
            'agency_id'
        ])->whereId($id)->get()->toArray();
        if (!$data) {
            callBack(3, '', '无此成员');
        }
        $agencyData = $this->getAgencyList();
        $this->assign('agencyData', $agencyData);
        $this->assign('data', $data[0]);
        $this->display();
    }

    /**
     * 执行编辑
     */
    public function update()
    {
        $agency = $this->getAgencyList(true);
        // 开始校验
        if (!$this->validate($this->request, $this->rules, $this->message)) {
            // 校验失败,输出错误信息
            callBack(1, '', $this->errors);
        }
        $addData    = $this->request->getPost();
        // 该手机号已注册
        $agencyData = AgencyUserModel::select('*')->wherePhone($addData['phone'])->where('id', '!=',
            $addData['id'])->get()->toArray();
        //                $sql = AgencyModel::select('*')->whereName($addData['name'])->where('id','!=',$addData['id'])->toSql();
        //        P($sql);die;
        if ($agencyData) {
            callBack(2, '', '该机构已存在');
        }
        // 跟新的数据
        $addData['img']         = $addData['img'] ? rtrim($addData['img'], ',') : 'Static/img/timg.jpg';
        $addData['agency_name'] = $agency[$addData['agency_id']] ?? '';
        $addData['update_time'] = time();
        $addData['update_by']   = 1;
        $status                 = AgencyUserModel::whereId($addData['id'])->update($addData);
        if (!$status) {
            callBack(3, '操作失败');
        }
        // 添加成功
        callBack(0);
    }

    /**
     * 查看数据
     *
     * @param $id
     */
    public function viewAgencyUser($id)
    {
        $data = AgencyUserModel::select([
            'id',
            'phone',
            'name',
            'img',
            'agency_name'
        ])->whereId($id)->get()->toArray();
        $data = $data ? $data[0] : [];
        callBack(0, $data);
    }

    /**
     * 加入黑名单
     *
     * @param $id
     */
    public function backAgencyUser($id)
    {
        $backInfo = AgencyBackListModel::select('*')->whereAgencyUserId($id)->get()->toArray();
        //        $back = AgencyBackListModel::select('*')->where('agency_user_id',$id)->toSql();
        // 已加入黑名单
        if ($backInfo) {
            callBack(3, '', '已加入黑名单');
        }
        $data = AgencyUserModel::select([
            'id',
            'phone',
            'name',
            'agency_name',
            'agency_id'
        ])->whereId($id)->get()->toArray();
        // 此机构不存在
        if (!$data) {
            callBack(3, '', '此机构不存在');
        }
        $data[0]['agency_user_id'] = $data[0]['id'];
        unset($data[0]['id']);
        // 移除到黑名单
        $status = AgencyBackListModel::insertGetId($data[0]);
        if (!$status) {
            callBack(3, '', '加入黑名单失败');
        }
        callBack(0);
    }

    /**
     * 删除机构
     *
     * @param $id
     */
    public function deleteAgency($id)
    {
        $addData['id']        = $id;
        $addData['is_delete'] = 1;
        $status               = AgencyModel::whereId($id)->update($addData);
        if (!$status) {
            callBack(3, '操作失败');
        }
        // 添加成功
        callBack(0);
    }

    /**
     * 获取机构下拉列表或key与value的映射关系
     *
     * @param bool $type true:key与value的映射关系;false:机构下拉列表
     *
     * @return array
     */
    public function getAgencyList($type = false)
    {
        $agencyData = AgencyModel::select(['id', 'name'])->get()->toArray();
        $data       = [];
        if ($type) {
            foreach ($agencyData as $key => $value) {
                $data[$value['id']] = $value['name'];
            }
        } else {
            $data = $agencyData;
        }

        return $data;
    }

    /**
     * 上传图片
     */
    public function uploadImg()
    {
        // 上传目录
        $uploadDir = FRONT_PATH . 'Upload/' . date('Y/m/d', time());
        // 获得后缀信息
        $file   = pathinfo($_FILES['file']['name']);
        $upload = new \YP\Libraries\YP_Upload($_FILES['file']['tmp_name'], time() . '.' . $file['extension']);
        $upload->move($uploadDir);
        // 获得上传后的图片名称
        $name = $upload->getName();
        // 上传失败
        if ($upload->getError()) {
            callBack($upload->getError(), '', '上传失败');
        }
        // 返回上传的图片路径
        callBack(0, 'Upload/' . $name);
    }

}