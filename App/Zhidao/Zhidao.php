<?php
/**
 * @Copyright (C) 2016.
 * @Description Zhidao
 * @FileName Zhidao.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Zhidao;

use \Libs\Frame\Action;
use \App\User\MyZhidaoData;
use App\Article\ArticleModel;
use Libs\Frame\Conf;
use \Libs\Tag\Page;
use \Libs\Comm\From;
use \Libs\Frame\Url;
use \App\Pub\Tips;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use Libs\Tag\Db;
use App\Index\Gjmy;

class Zhidao extends Action
{
    public $tpl;
    public $zhidaoData;
    public $userid;
    private $Db         = NULL;         //数据库对象
    const SUCCESS   = "success";    // 成功
    const FAIL      = "fail";       // 失败
    const ISDEL     = 0;            // 删除


    //配置
    public function conf()
    {
        $this->tpl = $this -> getTpl();
        $this -> Db         = Db::tag('DB.USER', 'GMY');

        $this->zhidaoData = new MyZhidaoData();

        $this->userid   = $_SESSION['TOKEN']['INFO']['id'];
        $this->tpl->assign('userid', $this->userid);
    }

    /**
     * @name main
     * @desciption 知道
     */
    public function main(string $action)
    {
        $Db = Db::tag('DB.USER', 'GMY');

        // 多联知道日报 zd_order 排序
        $daylist = $this->zhidaoData->getList("zd_status=0 AND zd_thumb_img != ''", [0, 4], 'zd_order DESC');
        $this->tpl->assign('daylist', $daylist);


        // 热力波
        $hitslist = $this->zhidaoData->getList("zd_status=0 AND zd_thumb_img != ''", [0, 10], 'zd_hits DESC');
        $this->tpl->assign('hitslist', $hitslist);

        // 推荐头条
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('zhidao').' WHERE zd_iscommend=1 AND zd_thumb_img != "" AND zd_isdel=0';
        $commend = $Db->getDataOne($sql);
        if(!empty($commend)) $commend['zd_thumb_img'] = $this->zhidaoData->getThumbImgUrl($commend['zd_thumb_img']);
        $this->tpl->assign('commend', $commend);

        // 中间推荐
        $timelist = $this->zhidaoData->getList("zd_status=0 AND zd_thumb_img != ''", [0, 10], 'zd_last_time DESC');
        $this->tpl->assign('timelist', $timelist);

        // 公告 ( 资讯增加一个公告栏目，在这里显示)
        $ArticleModel = new ArticleModel();
        $noticeList = $ArticleModel -> getArticleList(64, 6);
        $this->tpl->assign('noticeList', $noticeList);

        // 等你来回答(每日一题)
        $dayInfo = $this->zhidaoData->getInfo('zd_status=0 ORDER BY zd_last_time DESC');
        $this->tpl->assign('dayInfo', $dayInfo);

        // 每日一题右侧( 最新发布公告 )
        $newlist = $this->zhidaoData->getList("zd_status=0", [0, 5], 'zd_id DESC');
        $this->tpl->assign('newlist', $newlist);

        // 获取问题总数
        $sql = 'SELECT count(zd_id) as num FROM '.$Db->getTableNameAll('ggt_zhidao').' WHERE zd_isdel=0';
        $total = $Db->getDataOne($sql);
        $this->tpl->assign('total', $total);

        // 等待回答总数
        $sql = 'SELECT count(zd_id) as num FROM '.$Db->getTableNameAll('ggt_zhidao').' WHERE zd_isdel=0 AND zd_status=0';
        $waitTotal = $Db->getDataOne($sql);
        $this->tpl->assign('waitTotal', $waitTotal);

        // 行业专家在线 ( 待定 )
        $num = 5;
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('zhuanjia').' WHERE zj_isdel=0 group by zt_id ORDER BY zj_order ASC, zj_hits DESC, zj_id DESC LIMIT '.$num;
        $zjList = $Db -> getData($sql);
        $this->tpl->assign('zjList', $zjList);

        $this->tpl->show('Zhidao/main.html');
    }

    /**
     * @name main
     * @desciption 知道
     */
    public function lists(string $action)
    {
        // 获取知道列表
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $zhidaoList = $this->zhidaoData->getListPage('',$Page);
        $pageList       = $Page -> getPage(Link::getLink('zhidao'));

        $this->tpl->assign('zhidaoList', $zhidaoList);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->show('Zhidao/lists.html');
    }

    /**
     * @name main
     * @desciption 知道
     */
    public function view(string $action)
    {
        $id     = intval(From::val('id'));
        //获取知道详情
        $zhidaoInfo = $this->zhidaoData->getZhidaoInfo("zd_id={$id}");

        //回答问题表单提交
        if($_POST){
            if(!$this->userid)
                Tips::show('您没有权限进行此操作，请先登录！', Link::getLink('signin'));
            $id = intval(From::valTrim('articleId'));
            $data['zd_id'] = $id;
            $data['us_id'] = $this->userid;
            $data['za_content'] = From::valTrim('content');// $_POST['content'];
            if($this->zhidaoData->saveWaitanswer($data)){
                Tips::show('操作成功！',Link::getLink('zhidao-view').'?A=zhidao-view&id='.$id);
            }
        }
//        $Page = Page::tag('Admin', 'PLST');
//        $Page->setParam('currPage', max(From::valInt('pg'), 1));
//        $Page->setParam('size', 1);
//        $answerList = $this->zhidaoData->getZhidaoAnswerList("zd_id={$id}",$Page);
//        $pageList       = $Page -> getPage(Link::getLink('zhidao'));
//        $this->tpl->assign('pageList', $pageList);
        //获取所有答案
        /*$answerList = $this->zhidaoData->getZhidaoAnswerAll("zd_id={$id}");
        $answerList = $this->zhidaoData->setZhidaoViewInc("zd_id={$id}");*/

        //获取热门问题(右侧其他问题)
        $hitslist = $this->zhidaoData->getList("zd_status=0 AND zd_thumb_img != ''", [0, 10], 'zd_hits DESC');
        $this->tpl->assign('hitslist', $hitslist);

        $answerList = $this->getAnswerList($id);   //知道回答列表
        $this->tpl->assign('answerList', $answerList);

        $this->tpl->assign('answerList', $answerList);
        $this->tpl->assign('zhidaoInfo', $zhidaoInfo);
        $this->tpl->show('Zhidao/view.html');
    }



    /**
     * @name getAnswerList
     * @desction 获取知道回答列表
     * @param int $zdId
     * @return array
     */
    public function getAnswerList(int $zdId):array{
        if($zdId < 1) return [];
        $dataList = [];
        $sql = 'SELECT * FROM '.$this -> Db -> getTableNameAll('zhidao_answer').' WHERE zd_id='.$zdId.' AND za_status IN (0, 1) ORDER BY za_best DESC, za_id DESC';
        $list = $this -> Db -> getData($sql);
        $urlRes = Conf::get('URL.RES');
        if(count($list) > 0) foreach ($list as $key => $val){
            $viewUsId = intval($val['us_id']);
            $sql = 'SELECT * FROM '.$this -> Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0';
            $info = $this -> Db->getDataOne($sql);
            if(isset($info['ui_photo'])){
                if(strlen(trim($info['ui_photo'])) < 3){
                    $info['photo'] = '';
                }else{
                    $info['photo'] = $urlRes.ltrim($info['ui_photo'], '/');
                }
                $val = array_merge($val, $info);
            }
            $val['za_content'] = htmlentities($val['za_content']);
            $dataList[] = $val;
        }
        return $dataList;
    }
}