<?php
/**
 * User: yongli
 * Date: 17/5/10
 * Time: 20:04
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Index;

use AgencyUserModel;
use Config\Services;

class Login extends Auth
{
    /**
     *  构造函数
     */
    public function initialization()
    {
        parent::initialization();
    }

    /**
     * 登录
     */
    public function Login()
    {
        $this->display();
    }

    /**
     * 执行登录操作
     */
    public function doLogin()
    {
        $phone    = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        // 用户是否存在
        $user = AgencyUserModel::select('*')->wherePhone($phone)->get()->toArray();
        if (!$user) {
            callBack(2, '', '用户不存在!');
        }
        $user = $user[0];
        // 账号已禁用
        if ($user['status'] == 2) {
            callBack(2, '', '账号已禁用!');
        }
        // 验证密码
        if (!password_verify(md5($password), $user['password'])) {
            callBack(2, '', '密码或用户名错误!');
        }
        // 实例化session对象
        $session = Services::session();
        unset($user['password']);
        $user['create_time'] = date('Y-m-d', $user['create_time']);
        //  设置session
        $session->set('pid', $user);
        // 登录成功
        callBack(0);
    }

    /**
     * 修改密码
     */
    public function changePassword()
    {
        $data = $this->request->getPost();
        if (!isset($_SESSION['pid'])) {
            callBack(2, '', '请登入后在修改密码!');
        }
        $agencyInfo = AgencyUserModel::select('*')->whereId($_SESSION['pid']['id'])->get()->toArray();
        if (!$agencyInfo) {
            callBack(2, '', '用户不存在!');
        }
        // 验证密码
        if (!password_verify(md5($data['old_password']), $agencyInfo[0]['password'])) {
            callBack(2, '', '原密码错误!');
        }
        // 验证两次输入的新密码是否一致
        if ($data['new_password'] !== $data['re_new_password']) {
            callBack(2, '', '两次输入的新密码不一致!');
        }
        $updateData['password'] = password_hash(md5($data['new_password']), PASSWORD_DEFAULT);
        unset($data);
        // 修改
        $status = AgencyUserModel::whereId($_SESSION['pid']['id'])->update($updateData);
        if (!$status) {
            callBack(2, '', '修改密码失败!');
        }
        callBack(0);

    }

    /**
     * 退出
     */
    public function out()
    {
        $session = Services::session();
        $session->destroy();
        callBack(0);
    }
}