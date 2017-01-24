<?php
/**
 * @Copyright (C) 2016.
 * @Description Baike
 * @FileName Baike.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Daili;
use \Libs\Frame\Action;
use \App\User\MyDailiData;
use \Libs\Tag\Page;
use \Libs\Comm\From;
use \Libs\Frame\Url;
use \App\Pub\Tips;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use Libs\Comm\Http;

class Daili extends Action{
    //配置
    public function conf(){
        $this->dailiData = new MyDailiData();
        $this->tpl = $this -> getTpl();

        $this->tpl->assign('modelName', '国外代理');
    }

    /**
     * @name main
     * @desciption 国外代理首页
     */
    public function main()
    {
        $area =  $this->dailiData->getArea();
        $getArea              = From::valTrim('area');//国外代理的分类
        $whereArray = [];
        $pageParam ="";
        if($getArea > 0){
            $whereArray['area'] = $getArea;
            $this->tpl -> assign('areaid', $getArea);
            $pageParam .= "&area=".$getArea;//显示在分页中的国外代理分类的参数
        }
        $name              = From::valTrim('name');
        if(strlen($name) > 0&&$name!="0"){
            $whereArray['name'] = $name;
            $this->tpl -> assign('name', $name);
            $pageParam .= "&name=".$name;//显示在分页中的公司名称的参数
        }

        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size',5);
        $areaList = $this->dailiData->getListPage($whereArray,$Page);//获取列表数据
        $pageListPage       = $Page -> getPage(Link::getLink('daili'));
        $this->tpl->assign('pageList', $pageListPage);
        $this->tpl->assign('areaList', $areaList);
        $this->tpl->assign('area', $area);
        $this->tpl->assign('getArea', $getArea);
        $this->tpl->assign('pageParam', $pageParam);
        $this->tpl -> show('Daili/index.html');
    }

}