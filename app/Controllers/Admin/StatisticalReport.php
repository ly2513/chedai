<?php
/**
 * User: yongli
 * Date: 17/5/10
 * Time: 17:46
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Admin;

/**
 * Class StatisticalReport
 * 统计报表控制器
 *
 * @package App\Controllers\Admin
 */
class StatisticalReport extends Auth
{

    /**
     * 客户列表
     */
    public function getCustomer()
    {
        $this->display();
    }

    /**
     * 债权列表
     */
    public function getCreditorRight()
    {
        $this->display();
    }

    /**
     * 黑名单
     */
    public function getBackList()
    {
        $this->display();
    }

}