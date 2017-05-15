<?php
/**
 * User: yongli
 * Date: 17/5/9
 * Time: 13:46
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Admin;

use \YP\Core\YP_Controller as Controller;

/**
 * Class Auth 权限认证类
 *
 */
class Auth extends Controller
{
    /**
     * 每页显示多少条
     *
     * @var int
     */
    public $perPage = 1;

    /**
     * 当前页码
     *
     * @var int
     */
    public $page = 1;

    /**
     * 构造函数
     */
    public function initialization()
    {
        parent::initialization(); // TODO: Change the autogenerated stub
        $this->page = $this->request->getGet('per_page')?? 1;
        $this->checkLogin();
    }

    /**
     * 检测是否登录
     */
    public function checkLogin()
    {
        if (!isset($_SESSION['uid'])) {
            //            callBack(2,'','请登入后在操作');
            $url = 'http://' . $_SERVER['HTTP_HOST'];
            header("Location: $url/Admin/Login/login");
            exit;
        }
    }

}