<?php
/**
 * @Copyright (C) 2016.
 * @Description Article
 * @FileName Article.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Article;

use \Libs\Frame\Action;
use \Libs\Comm\From;
use \App\User\MyBaikeData;
use \App\Pub\Tips;
use \Libs\Frame\Url;
use \Libs\Comm\File;
use \Libs\Load;
use \Libs\Tag\Page;
use \App\Pub\Link;

class Article extends Action
{
    public $tpl;
    public $articleModel;
    public $userid = 0;

    //配置
    public function conf(){
        $this->tpl = $this -> getTpl();

        // 获取栏目分类列表
        // 实例化Model
        $this->articleModel = new ArticleModel();


        //获取 热点 百科列表
        $baikeData = new MyBaikeData();
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('size', 6);
        $baikeList = $baikeData->getList("bk_status=0", $Page, 'bk_hits DESC');
        $this->tpl->assign('baikeList', $baikeList);
        $this->userid   = $_SESSION['TOKEN']['INFO']['id'];
        $this->tpl->assign('userid', $this->userid);
    }


    /**
     * 资讯频道页
     * @param  string $action [description]
     * @return [type]         [description]
     */
    public function index(string $action)
    {
        $cid        = intval(From::val('cid'));
        $tid        = intval(From::val('tid'));
        $title      = From::valTrim('tag');

        // 获取当前栏目
        $catrgoryList   = $this->articleModel->getCategoryList($cid);
        $catrgoryInfo   = $this->articleModel->getCategoryInfo($cid);
        $seoInfo        = $catrgoryInfo;
        if(!empty($tid)){
            $cids           = $tid;
            $seoInfo        = $this->articleModel->getCategoryInfo($tid);
        }else{
            $cids           = $this->articleModel->getCategoryIds($catrgoryList);
        }

        // 模块名称
        $this->tpl->assign('modelName', $seoInfo['ac_name']);

        // 条件处理
        $whereArray = [];
        if (!empty($cids)) $whereArray['ac_id'] = $cids;
        if (!empty($title)) $whereArray['ar_title'] = $title;

        // 获取资讯列表
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        if(!empty($title)) $Page->setQuery('tag', $title);
        if(!empty($cid)) $Page->setQuery('cid', $cid);
        if(!empty($tid)) $Page->setQuery('tid', $tid);

        // 获取文章列表
        $articleList    = $this->articleModel->getDataList($whereArray, $Page);
        $pageList       = $Page -> getPage(Link::getLink('news'));

        // 获取文章Tags
        $tagsList       = $this->articleModel->getDataListTags($articleList);
        $this->tpl->assign('tagsList', $tagsList);

        // 获取推荐列表
        $commendList    = $this->articleModel->getDataCommendList(['ac_id' => $cids], 4);
        $this->tpl->assign('commendList', $commendList);

        // 获取本周热门
        $heatList       = $this->articleModel->getDataHeatList(['ac_id' => $cids], 6);
        $this->tpl->assign('heatList', $heatList);


        // 打印数据
        $this->tpl->assign('catrgoryList', $catrgoryList);
        $this->tpl->assign('catrgoryInfo', $catrgoryInfo);
        $this->tpl->assign('seoInfo', $seoInfo);
        $this->tpl->assign('articleList', $articleList);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('currNav', isset($catrgoryList[$tid]) ? $catrgoryList[$tid] : []);
        $this->tpl->assign('tid', $tid);
        $this->tpl->assign('cid', $cid);
        $this->tpl->show('Article/index.html');
    }

    /**
     * 资讯列表
     * @return [type] [description]
     */
    public function lists()
    {
        $this->tpl->show('Article/lists.html');
    }

    /**
     * 资讯详情
     * @return [type] [description]
     */
    public function detail()
    {
        $id     = intval(From::val('id'));
        $cid    = intval(From::val('cid'));

        //评论
        if($_POST){
            if(!$this->userid) Tips::show('您没有权限进行此操作，请先登录！', Link::getLink('signin'));
            $id = intval(From::valTrim('id'));
            $data['ar_id'] = $id;
            $data['us_id'] = $this->userid;
            $data['ac_text'] = From::valTrim('ac_text');
            if($this->articleModel->saveComment($data)){
                Tips::show('评论发表成功！','/news/detail.php?cid='.$cid.'&id='.$id);
            }
        }

//        // 获取栏目信息
//        $catrgoryList   = $this->articleModel->getCategoryList($cid);
//        $cids           = $this->articleModel->getCategoryIds($catrgoryList);

        // 条件处理
        $whereArray = [];
        if (!empty($id)) $whereArray['ar_id'] = $id;
        if (!empty($cids)) $whereArray['ac_id'] = $cid;
        $this->articleModel->setDataViewInc($id);

        // 获取资讯详情
        $articleInfo = $this->articleModel->getDataInfo($whereArray);

        if(empty($cid)) $cid = intval($articleInfo['ac_id']);
        if(!in_array($cid, [16,24,26,36,34,39,40,44])) $cid = 16;   //其他的显示国内
        $catrgoryInfo   = $this->articleModel->getCategoryInfo($cid);
        $this->tpl->assign('catgoryInfo', $catrgoryInfo);

        // 模块名称
        $this->tpl->assign('modelName', $catrgoryInfo['ac_name']);

        // 获取文章Tags
        $tagsList       = $this->articleModel->getDataListTags([$articleInfo]);
        $this->tpl->assign('tagsList', $tagsList);

        // 上一页下一页
        $upInfo = $this->articleModel->getDataUp($whereArray);
        $nextInfo = $this->articleModel->getDataNext($whereArray);

        // 获取本周热门
        $heatList = $this->articleModel->getDataHeatList(['ac_id' => $cids], 6);
        $this->tpl->assign('heatList', $heatList);

        // 百科
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));

        $commentList = $this->articleModel->getCommentList($id);   //评论列表
        $this->tpl->assign('commentList', $commentList);

//        echo '<pre>';
//        print_r($articleInfo);
//        exit;
        $this->tpl->assign('articleInfo', $articleInfo);
        $this->tpl->assign('upInfo', $upInfo);
        $this->tpl->assign('nextInfo', $nextInfo);
        $this->tpl->assign('cid', $cid);
        $this->tpl->assign('id', $id);
        $this->tpl->show('Article/detail.html');
    }


}