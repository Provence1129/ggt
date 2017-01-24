<?php
/**
 * @Copyright (C) 2016.
 * @Description Ydyl
 * @FileName Ydyl.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;

use \Libs\Frame\Action;
use \Libs\Comm\From;
use \Libs\Tag\Page;
use \Libs\Frame\Url;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use \App\Pub\Tips;
use \App\Article\ArticleModel;

class Ydyl extends Action
{
    public $tpl;
    public $ydylData;
    public $articleModel;

    //配置
    public function conf()
    {
        $this->tpl = $this->getTpl();

        // 实例化Model
        $this->ydylData = new YdylData();

        // 实例化Model
        $this->articleModel = new ArticleModel();

        $this->tpl->assign('modelName', '一带一路');
    }

    //Main
    public function main(string $action)
    {
        // 获取风格
        $style   = $this->ydylData->getStyle();
        $this->tpl->assign('style', $style);

        // 获取类型
        $type   = $this->ydylData->getType();
        $this->tpl->assign('type', $type);

        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));

        // 丝路动态
        $Page->setParam('size', 3);
        $Page->setQuery('cid', 48);
        $dtlist    = $this->articleModel->getDataList(['ac_id'=>48], $Page);
        $this->tpl->assign('dtlist', $dtlist);

        // 丝路解析
        $Page->setParam('size', 6);
        $Page->setQuery('cid', 49);
        $jxlist    = $this->articleModel->getDataList(['ac_id'=>49], $Page);
        $this->tpl->assign('jxlist', $jxlist);

        // 条件处理
        $whereString = "";

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 10);
        $list = $this->ydylData->getList($whereString, $Page);

        $topslide = $this->ydylData->getSlide();
        $slide = @json_decode($topslide['image'], TRUE);
        $imageArray = array();
        if(is_array($slide) && count($slide) > 0) foreach ($slide as $item){
            $array = array();
            if(is_array($item)){
                $array['image']     = $item['image'] ?? '';
                $array['imageurl']  = $item['imageurl'] ?? '';
            }else{
                $array['image']     = $item;
                $array['imageurl']  = $item;
            }
            $imageArray[] = $array;
        }
        $this->tpl -> assign('slide', $imageArray);
        $this->tpl -> assign('topslide', $topslide);

        // 分页
        $pageList = $Page->getPage(Link::getLink('ydyl') . '?A=ydyl-slfq');
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('list', $list);
        $this->tpl->show('Ydyl/index.html');
    }

    /**
     * 资讯列表
     * @param string $action
     */
    public function news(string $action)
    {
        $cid        = intval(From::val('id'));

        // 获取当前栏目
        $seoInfo   = $this->articleModel->getCategoryInfo($cid);

        // 条件处理
        $whereArray = [];
        if (!empty($cid)) $whereArray['ac_id'] = $cid;

        // 获取资讯列表
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 8);
        $Page->setQuery('id', $cid);

        // 获取文章列表
        $articleList    = $this->articleModel->getDataList($whereArray, $Page);
        $pageList       = $Page -> getPage(Link::getLink('ydyl').'?A=ydyl-news');
        $this->tpl->assign('articleList', $articleList);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('cid', $cid);


        // 丝路风情
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', 1);
        $Page->setParam('size', 6);
        $list = $this->ydylData->getList($whereString='', $Page);
        $this->tpl->assign('list', $list);

        $this->tpl->show('Ydyl/news.html');
    }

    //slfq
    public function slfq(string $action)
    {
        $sid = From::valInt('sid');
        $tid = From::valInt('tid');
        $this->tpl->assign('sid', $sid);
        $this->tpl->assign('tid', $tid);

        // 获取风格
        $style   = $this->ydylData->getStyle();
        $this->tpl->assign('style', $style);

        // 获取类型
        $type   = $this->ydylData->getType();
        $this->tpl->assign('type', $type);

        // 条件处理
        $whereString = "";
        if(!empty($sid)) $whereString .= ($whereString == ''?'':' AND ')."ys_id={$sid}";
        if(!empty($tid)) $whereString .= ($whereString == ''?'':' AND ')."yt_id={$tid}";

            // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 10);
        if(!empty($sid)) $Page->setQuery('sid', $sid);
        if(!empty($tid)) $Page->setQuery('tid', $tid);
        $list = $this->ydylData->getList($whereString, $Page);

        // 分页
        $pageList = $Page->getPage(Link::getLink('ydyl') . '?A=ydyl-slfq');
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('list', $list);
        $this->tpl->show('Ydyl/slfq.html');
    }

    //slfqview
    public function slfqview(string $action)
    {
        $id         = From::valInt('id');
        if(empty($id)) Tips::show('获取失败', Link::getLink('ydyl'));

        // 获取风格
        $style   = $this->ydylData->getStyle();
        $this->tpl->assign('style', $style);

        // 获取类型
        $type   = $this->ydylData->getType();
        $this->tpl->assign('type', $type);

        // 条件处理
        $whereString = "yy_id={$id}";
        $info = $this->ydylData->getInfo($whereString);

        // 上一页
        $infoUp = $this->ydylData->getInfo("yy_id<{$id} ORDER BY yy_id DESC");
        $this->tpl->assign('infoUp', $infoUp);

        // 下一页
        $infoNext = $this->ydylData->getInfo("yy_id>{$id} ORDER BY yy_id ASC");
        $this->tpl->assign('infoNext', $infoNext);

        $this->tpl->assign('info', $info);
        $this->tpl->show('Ydyl/slfqview.html');
    }
}