<?php
/**
 * @Copyright (C) 2016.
 * @Description Baike
 * @FileName Baike.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Banlie;
use \Libs\Frame\Action;
use \App\User\MyBanlieData;
use \Libs\Tag\Page;
use \Libs\Comm\From;
use \Libs\Frame\Url;
use \App\Pub\Tips;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use Libs\Comm\Http;
use \Libs\Tag\Db;

class Banlie extends Action{
    //配置
    public function conf(){
        $this->banlieData = new MyBanlieData();
        $this->tpl = $this -> getTpl();

        $this->tpl->assign('modelName', '班列信息');

        //箱卡集市
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT cb_id, cb_title, cb_market_price/100 AS cb_market_price FROM '.$Db -> getTableNameAll('container_box').' WHERE cb_is_del=0 AND cb_is_onsale=1 ORDER BY cb_ctime DESC LIMIT 5';
        $containerList = $Db->getData($sql);
        $this->tpl -> assign('containerList', $containerList);
    }

    /**
     * @name getPageParam 分页上需要设置的额外的参数
     * @return string
     */
    public function getPageParam(){
        $pathArray = array();
        $path = "";
        if($this->banlieData->trains){
            $pathArray['trains'] = $this->banlieData->trains;
        }
        if($this->banlieData->banlieCategory){
            $pathArray['banliecategory'] = $this->banlieData->banlieCategory;
        }
        if($this->banlieData->railway){
            $pathArray['railway'] = $this->banlieData->railway;
        }
        if($this->banlieData->operationCycle){
            $pathArray['operationcycle'] = $this->banlieData->operationCycle;
        }
        if($this->banlieData->banlieTitle){
            $pathArray['banlietitle'] = $this->banlieData->banlieTitle;
        }
        foreach ($pathArray as $key => $item){
            $path .= "&".$key."=".$item;
        }
        return $path;
    }

    /**
     * @name main
     * @desciption 班列列表
     */
    public function main()
    {
        $trains = $this->banlieData->geTrains();
        $banlieCategory = $this->banlieData->getBanlieCategory();
        $banlieCategoryList = [];
        if(count($banlieCategory) > 0) foreach ($banlieCategory as $val){
            $banlieCategoryList[$val['id']] = $val;
        }
        $railway  = $this->banlieData->getRailway();
        $operationCycle  = $this->banlieData->getOperationCycle();
        $options = $this->banlieData->getOptions();
        $pathUrl = Http::getUrlPath();
        $pageParam = $this->getPageParam();
        $banlieTitle = null;
        if($this->banlieData->banlieTitle){
            $banlieTitle = $this->banlieData->banlieTitle;
        }
        $trainsId = $this->banlieData->trains;
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size',20);
        $banlieList = $this->banlieData->getBanlieListPage($Page);
        if(count($banlieList) > 0) foreach ($banlieList as $key => $val){   //增加班列分类名称
            $category = $banlieCategoryList[$val['category']] ?? '';
            $banlieList[$key]['categoryName'] = $category['category'] ?? '';
        }
        $pageList       = $Page -> getPage(Link::getLink('banlie'));
        $daili = $this->banlieData->getDaili();
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('trains', $trains);
        $this->tpl->assign('trainsId', $trainsId);
        $this->tpl->assign('railway', $railway);
        $this->tpl->assign('pathUrl', $pathUrl);
        $this->tpl->assign('banlieTitle', $banlieTitle);
        $this->tpl->assign("pageParam",$pageParam);
        $this->tpl->assign('operationCycle', $operationCycle);
        $this->tpl->assign('banlieCategory', $banlieCategory);
        $this->tpl->assign('banlieList', $banlieList);
        $this->tpl->assign('options', $options);
        $this->tpl->assign('daili', $daili);
        $this->tpl -> show('Banlie/index.html');
//        $id     = intval(From::val('id'));
//        // 获取知道列表
//        $Page = Page::tag('Admin', 'PLST');
//        $Page->setParam('currPage', max(From::valInt('pg'), 1));
//        $Page->setParam('size',  2);
//        $baikeList = $this->baikeData->getBaikeListPage('a.bc_id='.$id,$Page);
//        $pageList       = $Page -> getPage(Link::getLink('baike'));
//
//
//        //获取百科分类
//        $categoryList = $this->baikeData->getCategoryList();
//        $this->tpl->assign('categoryList', $categoryList);
//
//        $cateInfo = $this->baikeData->getCategoryInfo($id);
//

//        $this->tpl->assign('cateInfo', $cateInfo);
//        $this->tpl->assign('baikeList', $baikeList);
//        $this->tpl->assign('pageList', $pageList);


    }

    /**
     * @name main
     * @desciption 班列详情
     */
    public function view()
    {
        $id = intval(From::val('id'));
        $banlieInfo = $this->banlieData->getBanlieData($id);
        $this->tpl->assign('banlie', $banlieInfo);

        $railway  = $this->banlieData->getRailway();
        $this->tpl->assign('railway', $railway);

        $dailiList = $this->banlieData->getDaili();
        $this->tpl->assign('dailiList', $dailiList);

        $operationCycle  = $this->banlieData->getOperationCycle();
        $this->tpl->assign('operationCycle', $operationCycle);

        $this->tpl -> show('Banlie/view.html');
    }
}