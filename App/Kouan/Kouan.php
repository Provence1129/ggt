<?php
/**
 * @Copyright (C) 2016.
 * @Description Baike
 * @FileName Baike.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Kouan;
use App\Article\ArticleModel;
use \Libs\Frame\Action;
use \App\User\MyKouanData;
use Libs\Tag\Db;
use \Libs\Tag\Page;
use \Libs\Comm\From;
use \Libs\Frame\Url;
use \App\Pub\Tips;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use Libs\Comm\Http;

class Kouan extends Action{
    //配置
    public function conf(){
        $this->kouanData = new MyKouanData();
        $this->tpl = $this -> getTpl();

        $this->tpl->assign('modelName', '口岸中心');
    }


    /**
     * @name main
     * @desciption 口岸列表
     */
    public function main()
    {
        $kw = From::valTrim('kw');
        if(strlen($kw) > 0 || isset($_GET['kw']) || isset($_POST['kw'])){
            $articleModel = new ArticleModel();
            $Tpl = $this->getTpl();
            $Db = Db::tag('DB.USER', 'GMY');
            $Page = Page::tag('ent', 'PLST');
            $Page -> setParam('size', 15);
            $Page -> setParam('currPage', max(From::valInt('pg'), 1));
            $limit = $Page -> getLimit();
            $whereString = 'ar_isdel=0 AND ar_status=1';
            if(strlen($kw) > 0){
                $whereString .= ($whereString==''?'':' AND ').'ar_title LIKE \'%'.$kw.'%\'';
                $Page -> setQuery('kw', $kw);
                $Tpl -> assign('kw', $kw);
            }
            $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('article').' WHERE '.$whereString.' ORDER BY ar_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
            $dataArray = $Db -> getData($sql);
            $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
            $Page -> setParam('totalNum', $totalNum);
            $dataList = [];
            if(isset($dataArray[0]) && is_array($dataArray[0])){
                foreach ($dataArray as $key => $val){
                    $val['tags']            = $articleModel -> getDataListTags([$val]);
                    $dataList[] = $val;
                }
            }
            $pageList = $Page -> getPage('/kouan/');
            $Tpl -> assign('pageList', $pageList);
            $Tpl -> assign('dataList', $dataList);
            // 获取文章Tags
            $tagsList       = $articleModel->getDataListTags($dataList);
            $Tpl->assign('tagsList', $tagsList);
            // 获取本周热门
            $heatList       = $articleModel->getDataHeatList([], 6);
            $Tpl->assign('heatList', $heatList);

            $Tpl -> show('Kouan/article_search.html');
            exit;
        }
        $type = From::val("type");
        $categoires = From::val("categoires");
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size',10);
        if(!$categoires){
            $categoires = 58;
        }
        $pageParam = "&type=".$type."&categoires=".$categoires;
        $ac_id = $this->kouanData->getChildrenCategoires($categoires);
        $list = $this->kouanData->getListPage($ac_id,$Page);
        $pageList = $Page -> getPage(Link::getLink('kouan'));
        $this->tpl->assign('pageParam', $pageParam);
        $this->tpl->assign('list', $list);
        $this->tpl->assign('pageList', $pageList);
        if($type == 2){
            $this->tpl -> show('Kouan/index2.html');
        }else{
            $this->tpl -> show('Kouan/index.html');
        }

    }
    /**
     * @name main
     * @desciption 口岸索索
     */
    public function search()
    {
        $categoriesArray= array("59"=>"港口口岸","60"=>"铁路口岸","61"=>"公路口岸","62"=>"空港口岸","63"=>"配套服务企业");
        $search = From::valTrim("search");
        $categories = From::valTrim("categories");
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size',10);
        $pageParam = "&A=kouan-search";
        if(!$categories){
            foreach ($categoriesArray as $key => $item){
                $categories .= $key.",'".$item."',";
            }
            $categories = substr($categories,0,strlen($categories)-1);
            if($search){
                $pageParam .= "&search=".$search;
            }

        }else{
            $pageParam .= "&categories=".$categories;
            if($search){
                $pageParam .= "&search=".$search;
            }
            $categories .= ",'".$categoriesArray[$categories]."'";
        }
        $this->tpl->assign('pageParam', $pageParam);
        $items = $this->kouanData->getDataList($categories,$search,$Page);
        $pageList = $Page -> getPage(Link::getLink('kouan'));
		$this->tpl->assign('categories', $categories);
        $this->tpl->assign('list', $items);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl -> show('Kouan/kouan_search.html');
    }
    /**
     * @name main
     * @desciption 口岸详情
     */
    public function view()
    {
        $id = From::val("id");
        if(!$id){
            throw new Exception('没有找到对应的ID');
        }
        $item = $this->kouanData->getData($id);
        $this->tpl->assign('kouanInfo', $item);
        $this->tpl -> show('Kouan/kouan_more.html');
    }
}