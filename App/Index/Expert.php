<?php
/*********************************************************************************
 * YPHP 0.1.0 国产PHP开发框架
 *-------------------------------------------------------------------------------
 * 版权所有: CopyRight By yphp.cn
 * 您可以自由使用该源码，但是在使用过程中，请保留作者信息。尊重他人劳动成果就是尊重自己
 *-------------------------------------------------------------------------------
 * Author:418250505 418250505@qq.com
 * Dtime:2014-11-25
 ***********************************************************************************/
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
use \App\User\ExpertData;


class Expert extends Action
{
    public $tpl;
    public $expertData;

    //配置
    public function conf()
    {
        $this->tpl = $this->getTpl();

        // 实例化Model
        $this->expertData = new ExpertData();

        $this->tpl->assign('modelName', '行业人物');
    }


    /**
     * 专家列表
     * @param string $action
     */
    public function lists(string $action)
    {
        $type = $this->expertData->getTypeList();
        $this->tpl->assign('type', $type);

        // 条件处理
        $tid = !empty(From::valGet('tid')) ? intval(From::valGet('tid')) : 1;
        $tag = !empty(From::valGet('tag')) ? trim(From::valGet('tag')) : '';

        $whereArray = [];
        if (!empty($tid)) $whereArray['zt_id'] = $tid;
        if (!empty($tag)) $whereArray['tag'] = $tag;

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        if (!empty($tid)) $Page->setQuery('tid', $tid);
        if (!empty($tag)) $Page->setQuery('tag', $tag);
        $list = $this->expertData->getDataList($whereArray, $Page);
        $pageList = $Page->getPage(Link::getLink('zhuanjia'));

        //右下面热门展会
        $zhanhuiBannerArray = array();
        $zhanhuibanner = $this->expertData->getZhanhuiBanner(25);;
        $zhanhuiBannerArray[] = $zhanhuibanner;
        $zhanhuibanner = $this->expertData->getZhanhuiBanner(26);;
        $zhanhuiBannerArray[] = $zhanhuibanner;
        $this->tpl -> assign('zhanhuiBanner', $zhanhuiBannerArray);

        //国内资讯
        $articleList = $this->expertData -> getArticleList(16, 10);    //获取国内咨询下的10条信息
        $this->tpl  -> assign('articleList', $articleList);

        $this->tpl->assign('list', $list);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('tid', $tid);
        $this->tpl->show('Expert/list.html');
    }


    /**
     * 专家详情
     * @param string $action
     */
    public function view(string $action)
    {
        $id     = intval(From::val('id'));

        // 获取资讯详情
        $articleInfo = $this->expertData->getInfo($id);

        // 上一页下一页
        $upInfo     = $this->expertData->getDataUp($id);
        $nextInfo   = $this->expertData->getDataNext($id);

        // 获取本周热门
        $heatList   = $this->expertData->getDataHeatList(['zt_id' => $articleInfo['zt_id']], 6);
        $this->tpl->assign('heatList', $heatList);

//        echo '<pre>';
//        print_r($heatList);
//        exit;
        $this->tpl->assign('articleInfo', $articleInfo);
        $this->tpl->assign('upInfo', $upInfo);
        $this->tpl->assign('nextInfo', $nextInfo);
        $this->tpl->show('Expert/view.html');
    }


}