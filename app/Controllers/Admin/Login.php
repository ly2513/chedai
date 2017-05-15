<?php
/**
 * User: yongli
 * Date: 17/5/12
 * Time: 18:05
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Admin;

use UserModel;
use Config\Services;

class Login extends \YP\Core\YP_Controller
{
    /**
     * 登录
     */
    public function login()
    {
        $this->display();
    }

    /**
     * 执行登录
     */
    public function doLogin()
    {
        $name    = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        // 用户是否存在
        $user = UserModel::select('*')->whereName($name)->get()->toArray();
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
        $session->set('uid', $user);
        // 登录成功
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