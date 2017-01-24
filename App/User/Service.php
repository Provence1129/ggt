<?php
/**
 * @Copyright (C) 2016.
 * @Description Service
 * @FileName Service.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;
use \App\Pub\Common;
use App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use Libs\Comm\Valid;
use \Libs\Frame\Action;
use \Libs\Frame\Url;
use \App\Auth\MyAuth;
use \Libs\Plugins\Checkcode\Checkcode;
class Service extends Action{
    //配置
    public function conf(){
        $Tpl = $this -> getTpl();
        $page = [];
        $page['Title']          = '港港通国际多式联运门户网';
        $page['Keywords']       = '行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布';
        $page['Description']    = '国内首家专业性多式联运行业门户网站，集行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布等功能和内容';
        $Tpl -> assign('page', $page);
    }

    /**
     * @name main
     * @desciption 增值服务
     */
    public function main(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/service_main.html');
    }

    /**
     * @name appmy
     * @desciption 我的应用
     */
    public function appmy(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/service_appmy.html');
    }

    /**
     * @name appall
     * @desciption 全部应用
     */
    public function appall(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/service_appall.html');
    }

    /**
     * @name appview
     * @desciption 应用详细
     */
    public function appview(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/service_appview.html');
    }
}