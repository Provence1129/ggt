<?php
/**
 * @Copyright (C) 2016.
 * @Description Baike
 * @FileName Baike.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Dlsq;
use \Libs\Frame\Action;
use \App\User\MyDlsqData;
use \Libs\Tag\Page;
use \Libs\Comm\From;
use \App\Article\ArticleModel;
use \Libs\Frame\Url;
use \App\Pub\Tips;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use Libs\Comm\Http;

class Dlsq extends Action{
    //配置
    public function conf(){
        $this->DlsqData = new MyDlsqData();
        $this->tpl = $this -> getTpl();

        $this->tpl->assign('modelName', '多联商圈');
    }


    /**
     * @name main
     * @desciption 多联商圈列表
     */
    public function main()
    {
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $whereArray  = [];
        $sq_category              = From::valTrim('sq_category');
        $pageParam = null;
        if(!empty($sq_category)){
            $whereArray['sq_category'] = $sq_category;
            $Page -> setQuery('sq_category', $sq_category);
            $this->tpl -> assign('sq_category', $sq_category);
            $pageParam .= "&sq_category=".$sq_category;
        }
        $sq_region              = From::valTrim('sq_region');
        if(!empty($sq_region)){
            $whereArray['sq_region'] = $sq_region;
            $Page->setQuery('sq_region', $sq_region);
            $this->tpl->assign('sq_region', $sq_region);
            $pageParam .= "&sq_region=" . $sq_region;
        }

        $sq_kw              = From::valTrim('kw');
        if(!empty($sq_kw)){
            $whereArray['sq_company_title'] = $sq_kw;
            $Page->setQuery('kw', $sq_kw);
            $this->tpl->assign('kw', $sq_kw);
            $pageParam .= "&kw=" . $sq_kw;
        }

        $listTemp = $this->DlsqData->getDataList($Page,$whereArray);
        foreach($listTemp as $key=>$val){
            $val['description'] = $this->trimall($val['description']);
            $list[] = $val;
        }
        $sqCategory = $this->DlsqData->getSqcat();
        $sqregion = $this->DlsqData->getRegion();
        $pageList = $Page -> getPage(Link::getLink('dlsq'));
        $this->tpl->assign('pageParam', $pageParam);
        $this->tpl->assign('list', $list);
        $this->tpl->assign('sqCategory', $sqCategory);
        $this->tpl->assign('sqregion', $sqregion);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl -> show('Dlsq/index.html');
    }

    /**
     * @name main
     * @desciption 多联商圈详情
     */
    public function view()
    {
        $id = From::val("id");
        $item = $this->DlsqData->getData($id);
        $sqCategory = $this->DlsqData->getSqcat();
        $sq_category = $item['sq_category'];
        $this->tpl->assign('sqCategory', $sqCategory);
        $this->tpl->assign('sq_category', $sq_category);
        $this->tpl->assign('iteminfo', $item);
        $ArticleModel = new ArticleModel();
        $articleList = $ArticleModel -> getArticleList(16, 5);    //获取国内咨询下的10条信息
        $this->tpl -> assign('articleList', $articleList);
        $this->tpl -> show('Dlsq/view.html');
    }


    function trimall($str){
        if(!empty($str)){
            $str = strip_tags($str);
            $qian = array(" ","　","\t","\n","\r");
            return str_replace($qian, '', $str);
        }
        return '';
    }
}