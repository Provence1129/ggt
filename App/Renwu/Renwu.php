<?php
/**
 * @Copyright (C) 2016.
 * @Description Renwu
 * @FileName Renwu.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types = 1);//strict
namespace App\Renwu;

use \Libs\Frame\Action;
use \Libs\Comm\From;
use \Libs\Tag\Page;
use \Libs\Frame\Url;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use \App\Pub\Tips;
use \App\Article\ArticleModel;
use \App\User\ExpertData;


class Renwu extends Action
{
    public $tpl;
    public $renwuModel;
    public $articleModel;
    public $expertData;

    //配置
    public function conf()
    {
        $this->tpl = $this->getTpl();

        // 实例化Model
        $this->renwuModel = new RenwuModel();

        // 实例化Model
        $this->articleModel = new articleModel();

        // 专家
        $this->expertData = new ExpertData();

        $this->tpl->assign('modelName', '行业人物');
    }

    //Main
    public function main(string $action)
    {
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 6);

        // 获取文章列表
        $whereArray = [];
        if (!empty($id)) $whereArray['ar_id'] = $id;
        $all        = $this->renwuModel->getList(['ac_id'=>'31,34,35'], $Page);
        $lingdao    = $this->renwuModel->getList(['ac_id'=>17], $Page);
        $hangye     = $this->expertData->getDataList([], $Page); // $this->renwuModel->getList(['ac_id'=>19], $Page);
        $zhuanfang  = $this->renwuModel->getList(['ac_id'=>34], $Page);
        $yulu       = $this->renwuModel->getList(['ac_id'=>35], $Page);

        // 获取推荐列表
        $commendList    = $this->articleModel->getDataCommendList(['ac_id' => '17,19,20,21'], 4);
        $this->tpl->assign('commendList', $commendList);

        $this->tpl->assign('all', $all);
        $this->tpl->assign('lingdao', $lingdao);
        $this->tpl->assign('hangye', $hangye);
        $this->tpl->assign('zhuanfang', $zhuanfang);
        $this->tpl->assign('yulu', $yulu);
        $this->tpl->show('Renwu/main.html');
    }

    // 人物列表页
    public function lists(string $action)
    {
        $ac_id = intval(From::val('cid'));
        if($ac_id){
            $Page = Page::tag('Admin', 'PLST');
            $Page->setParam('currPage', max(From::valInt('pg'), 1));
            $Page->setParam('size', 6);
            $Page->setQuery('cid', $ac_id);

            $lists      = $this->renwuModel->getList(['ac_id'=>$ac_id], $Page);
            $pageList   = $Page -> getPage(Link::getLink('renwu').'?A=renwu-lists');
            $this->tpl->assign('lists', $lists);
            $this->tpl->assign('pageList', $pageList);
        }
        $yulu       = $this->renwuModel->getList(['ac_id'=>35], $Page);
        $this->tpl->assign('yulu', $yulu);
        $this->tpl->show('Renwu/lists.html');
    }

    // 多联精英
    public function jingying(string $action)
    {
        $this->tpl->show('Renwu/jingying.html');
    }

}