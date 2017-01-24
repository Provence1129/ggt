<?php
/**
 * @Copyright (C) 2016.
 * @Description Baike
 * @FileName Baike.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Zjzx;
use \Libs\Frame\Action;
use \App\User\MyZjzxData;
use \Libs\Tag\Page;
use \Libs\Comm\From;
use \App\Pub\Common;
use \Libs\Frame\Url;
use \App\Pub\Tips;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use Libs\Comm\Http;

class Zjzx extends Action{
    public  $ask_categrioes = array("我要发货","技术问题","行业政策","最优线路","业务操作","市场营销","其他");
    const SUCCESS   = "success";    // 成功
    const FAIL      = "fail";       // 失败
    //配置
    public function conf(){
        $this->ZjzxData = new MyZjzxData();
        $this->tpl = $this -> getTpl();
    }


    /**
     * @name main
     * @desciption 多联商圈列表
     */
    public function main()
    {
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size',10);
        $list = $this->ZjzxData->getListPage($Page);
        $pageList = $Page -> getPage(Link::getLink('zjzx'));
        $this->tpl->assign('list', $list);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('ask_categrioes',$this->ask_categrioes);
        $this->tpl -> show('Zjzx/index.html');
    }

    /**
     * @name main
     * @desciption 多联商圈详情
     */
    public function save()
    {
        $ask_theme = From::valTrim("ask_theme");
        $ask_categrioes = From::valTrim("ask_categrioes");
        if(!in_array($ask_categrioes,$this->ask_categrioes)){
            Tips::show('该咨询类别不存在', Link::getLink('zjzx'));
        }
        $detailed_description = From::valTrim("detailed_description");
        $contect = From::valTrim("contect");
        $mobile = From::valTrim("mobile");
        $email = From::valTrim("email");
        $company_name = From::valTrim("company_name");
        $publish_up = date("Y-m-d H:i:s");
        $published = 0;
        $result = $this->ZjzxData->save([
            'ask_theme'=>$ask_theme,
            'ask_categrioes'=>$ask_categrioes,
            'detailed_description'=>$detailed_description,
            'contect'=>$contect,
            'mobile'=>$mobile,
            'email'=>$email,
            'company_name'=>$company_name,
            'publish_up'=>$publish_up,
            'published'=>$published
        ]);
        if(static::SUCCESS == $result){
            Common::toUrl(Link::getLink('zjzx'));
        }
    }

    //多式联运溯源
    public function dlsy(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('Zjzx/dlsy.html');
    }
}