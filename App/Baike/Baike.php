<?php
/**
 * @Copyright (C) 2016.
 * @Description Baike
 * @FileName Baike.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Baike;
use \Libs\Frame\Action;
use \App\User\MyBaikeData;
use \Libs\Tag\Page;
use \Libs\Comm\From;
use \Libs\Frame\Url;
use \App\Pub\Tips;
use \Libs\Comm\File;
use \Libs\Load;
use \App\Pub\Link;
use Libs\Tag\Db;
use App\Index\Gjmy;

class Baike extends Action{
    //配置
    public function conf(){
        $this->baikeData = new MyBaikeData();
        $this->tpl = $this -> getTpl();

        //获取百科分类
        $categoryList = $this->baikeData->getCategoryList();
        $this->tpl->assign('categoryList', $categoryList);

        // 获取词条数据
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT count(bk_id) as baikeCount,count(distinct us_id) as userCount  FROM '.$Db->getTableNameAll('ggt_baike').' WHERE bk_isdel=0';
        $total = $Db->getDataOne($sql);
        $this->tpl->assign('total', $total);

    }

    /**
     * @name main
     * @desciption 百科
     */
    public function main(string $action)
    {
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));

        //获取 热点 百科列表
        $Page->setParam('size', 5);
        $baikeList = $this->baikeData->getList("bk_status!=2 AND bk_thumb_img != ''", $Page, 'bk_hits DESC');

        //获取百科分类
        $categoryList = $this->baikeData->getCategoryList();

        // 获取最新发布百科的用户信息
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT distinct a.us_id,b.ui_name,b.ui_photo,b.ui_mobile FROM '.$Db->getTableNameAll('ggt_baike').' as a LEFT JOIN  '.$Db->getTableNameAll('ggt_user_info').' AS b ON a.us_id=b.us_id WHERE a.bk_isdel=0 ORDER BY a.bk_id DESC LIMIT 10';
        $userListResult = $Db->getData($sql);
        foreach($userListResult as $key=>$val){
            $val['ui_mobile'] = substr_replace($val['ui_mobile'],'****',3,4);
            $val['ui_name'] = (!empty($val['ui_name'])) ? $val['ui_name'] : $val['ui_mobile'];
            $userList[] = $val;
        }
        $this->tpl->assign('userList', $userList);

        // 名人百科
        $Page->setParam('size', 9);
        $mingrenList = $this->baikeData->getList("bk_status!=2 AND bc_id=16", $Page, 'bk_hits DESC');
        $this->tpl->assign('mingrenList', $mingrenList);

        // 企业百科
        $Page->setParam('size', 9);
        $qiyeList = $this->baikeData->getList("bk_status!=2 AND bc_id=17", $Page, 'bk_hits DESC');
        $this->tpl->assign('qiyeList', $qiyeList);

        $this->tpl->assign('categoryList', $categoryList);
        $this->tpl->assign('baikeList', $baikeList);
        $this->tpl->assign('id', 0);

        //百科中间图片
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('banner').' where id = 30';
        $centerBanner = $Db -> getDataOne($sql);
        $this->tpl-> assign('centerBanner', $centerBanner);
        $this->tpl->show('Baike/main.html');
    }

    /**
     * @name main
     * @desciption 百科列表
     */
    public function lists()
    {
        $id     = intval(From::val('id'));
        $kw     = trim(From::val('kw'));

        $Page = Page::tag('Admin', 'PLST');
        $whereString = '';
        if(!empty($id)){
            $whereString .= ' AND bc_id='.$id;
            $Page->setQuery('id', $id);
        }

        if(!empty($kw)){
            $whereString .= " AND bk_title like '%{$kw}%'";
            $Page->setQuery('kw', $kw);
        }
        $whereString    = ltrim($whereString, ' AND ');

        // 获取知道列表
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 8);
        $baikeList = $this->baikeData->getList($whereString, $Page);
        $pageList = $Page -> getPage(Link::getLink('baike'));
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('baikeList', $baikeList);

        // 分类详情
        $cateInfo = $this->baikeData->getCategoryInfo($id);
        $this->tpl->assign('cateInfo', $cateInfo);

        $this->tpl->assign('cateId', $id);
        $this->tpl->assign('id', $id);
        $this->tpl->assign('kw', $kw);
        $this->tpl->show('Baike/lists.html');
    }

    /**
     * @name main
     * @desciption 百科详情
     */
    public function view()
    {
        $id = intval(From::val('id'));
        $baikeInfo = $this->baikeData->getBaikeInfo($id);
        $this->tpl->assign('baikeInfo', $baikeInfo);

        // 更新点击次数
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'UPDATE ' . $Db->getTableNameAll('baike') . ' SET bk_hits=bk_hits+1 WHERE bk_id=' . $id . ' AND bk_isdel=0';
        $Db->getDataNum($sql);

        // 获取用户信息
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$baikeInfo['us_id'].'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
        $userInfo = $Db->getDataOne($sql);
        $this->tpl->assign('userInfo', $userInfo);

        $this->tpl -> show('Baike/view.html');
    }
}