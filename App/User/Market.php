<?php
/**
 * @Copyright (C) 2016.
 * @Description Market
 * @FileName Market.php
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
class Market extends Action{
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
     * @desciption 效果营销
     */
    public function main(string $action){
        $Tpl = $this->getTpl();
        Tips::show('开发中...！', 'javascript: history.back();');
        $Tpl->show('User/market_main.html');
    }

    /**
     * @name visitor
     * @desciption 访客分析
     */
    public function visitor(string $action){
        $Tpl = $this->getTpl();
        Tips::show('开发中...！', 'javascript: history.back();');
        $Tpl->show('User/market_visitor.html');
    }

    /**
     * @name send
     * @desciption 询盘分析
     */
    public function send(string $action){
        $Tpl = $this->getTpl();
        Tips::show('开发中...！', 'javascript: history.back();');
        $Tpl->show('User/market_send.html');
    }
}