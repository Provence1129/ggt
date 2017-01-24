<?php
/**
 * @Copyright (C) 2016.
 * @Description Shop
 * @FileName Shop.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Shop;
use \App\Article\ArticleModel;
use \App\Pub\Tips;
use \App\User\UserData;
use \Libs\Comm\From;
use \Libs\Comm\Time;
use \Libs\Frame\Action;
use \Libs\Tag\Db;
use \Libs\Tag\Page;
use \Libs\Tag\Sql;

class Shop extends Action{
    //配置
    public function conf(){
        $this -> userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl = $this -> getTpl();
        $Tpl->assign('modelName', '积分商城');
    }

    private function setdataList(){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        //热销排行榜
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('jf_goods').' WHERE jg_isdel=0 AND jg_issale=1 ORDER BY jg_sale_num DESC LIMIT 8';
        $saleList = $Db -> getData($sql);
        if(isset($saleList[0]) && is_array($saleList[0])){
            foreach ($saleList as $key => $val){
                $val['jg_imgs'] = strlen($val['jg_img']) > 0 ? @json_decode($val['jg_img'], TRUE) : [];
                $val['jg_price'] = sprintf("%.2f", $val['jg_price']/100);
                $saleList[$key] = $val;
            }
        }
        $Tpl -> assign('saleList', $saleList);
        //积分商城帮助
        $ArticleModel = new ArticleModel();
        $shopHelpList = $ArticleModel -> getArticleList(65, 5);    //获取积分商城帮助
        $Tpl -> assign('shopHelpList', $shopHelpList);
    }

    //Main
    public function main(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $whereString = 'jg_isdel=0 AND jg_issale=1';
        //$usId  = intval($this -> userInfo['id']);
        $baseUrl = '/shop/index.php';
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 9);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('jf_goods').' WHERE '.$whereString.' ORDER BY jg_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $val['jg_imgs'] = strlen($val['jg_img']) > 0 ? @json_decode($val['jg_img'], TRUE) : [];
                $val['jg_price'] = sprintf("%.2f", $val['jg_price']/100);
                /*
                $viewUsId = intval($val['us_id']);
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
                $info = $Db->getDataOne($sql);
                $val = array_merge($val, $info);
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$viewUsId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
                $info = $Db->getDataOne($sql);
                $val = array_merge($val, $info);
                $val['url'] = (new Yunjia()) -> getShopUrl($viewUsId);
                */
                $dataList[] = $val;
            }
        }
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 29';
        $topslide = $Db -> getDataOne($sql);
        $slide = json_decode($topslide['image']);
        $imageArray = array();
        if(is_array($slide)){
            foreach (@$slide as $item){
                $array = array();
                $array['image'] = $item->image;
                $array['imageurl'] = $item->imageurl;
                $imageArray[] = $array;
            }
        }

        $Tpl -> assign('topslide', $topslide);
        $Tpl -> assign('slide', $imageArray);
        $pageList = $Page -> getPage($baseUrl);
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $this -> setdataList();
        $Tpl -> show('Shop/main.html');
    }

    //view
    public function view(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $jgId = From::valInt('id');
        if($jgId < 1) Tips::show('数据错误！', 'javascript: history.back();');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('jf_goods').' WHERE jg_id='.$jgId.' AND jg_isdel=0 AND jg_issale=1';
        $goodsInfo = $Db -> getDataOne($sql);
        if(!isset($goodsInfo['jg_id'])) Tips::show('数据错误！', 'javascript: history.back();');
        $goodsInfo['jg_imgs'] = strlen($goodsInfo['jg_img']) > 0 ? @json_decode($goodsInfo['jg_img'], TRUE) : [];
        $goodsInfo['jg_price'] = sprintf("%.2f", $goodsInfo['jg_price']/100);
        $Tpl -> assign('goodsInfo', $goodsInfo);
        $this -> setdataList();
        $Tpl -> show('Shop/view.html');
    }

    //pay
    public function pay(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $num = min(max(From::valInt('num'), 1), 99);    //1-99份
        $jgId = From::valInt('id');
        if($jgId < 1) Tips::show('数据错误！', 'javascript: history.back();');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('jf_goods').' WHERE jg_id='.$jgId.' AND jg_isdel=0 AND jg_issale=1';
        $goodsInfo = $Db -> getDataOne($sql);
        if(!isset($goodsInfo['jg_id'])) Tips::show('数据错误！', 'javascript: history.back();');
        $goodsInfo['jg_imgs'] = strlen($goodsInfo['jg_img']) > 0 ? @json_decode($goodsInfo['jg_img'], TRUE) : [];
        $goodsInfo['jg_price'] = sprintf("%.2f", $goodsInfo['jg_price']/100);
        $Tpl -> assign('goodsInfo', $goodsInfo);
        $Tpl -> assign('nums', $num);
        $Tpl -> assign('money', intval($goodsInfo['jg_money']) * $num);
        $usId  = intval($this -> userInfo['id']);
        if($usId < 1){
            Tips::show('请先登录再兑换！', '/user/signin.php');
        }
        $Tpl -> show('Shop/pay.html');
    }

    //payok
    public function payok(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $num = min(max(From::valInt('num'), 1), 99);    //1-99份
        $jgId = From::valInt('id');
        if($jgId < 1) Tips::show('数据错误！', 'javascript: history.back();');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('jf_goods').' WHERE jg_id='.$jgId.' AND jg_isdel=0 AND jg_issale=1';
        $goodsInfo = $Db -> getDataOne($sql);
        if(!isset($goodsInfo['jg_id'])) Tips::show('数据错误！', 'javascript: history.back();');
        $goodsInfo['jg_imgs'] = strlen($goodsInfo['jg_img']) > 0 ? @json_decode($goodsInfo['jg_img'], TRUE) : [];
        $goodsInfo['jg_price'] = sprintf("%.2f", $goodsInfo['jg_price']/100);
        $Tpl -> assign('goodsInfo', $goodsInfo);
        $Tpl -> assign('nums', $num);
        $usId  = intval($this -> userInfo['id']);
        $jgMoney = intval($goodsInfo['jg_money']);
        $money = $jgMoney * $num;
        $save = From::valInt('save');
        if($save == 1){
            //获取用户当前积分
            $UserData = new UserData();
            if(!$UserData -> accountDec($usId, $money, '兑换:'.$goodsInfo['jg_title'])){
                Tips::show('积分不足，兑换失败！', 'javascript: history.back();');
            }
            $currTime = Time::getTimeStamp();
            $data       = [];
            $data['us_id']                  = $usId;
            $data['jg_id']                  = $jgId;
            $data['jgo_money']              = $jgMoney;
            $data['jgo_isdel']              = 0;
            $data['jgo_first_time']         = $currTime;
            $data['jgo_end_time']           = $currTime;
            $data['jgo_num']                = $num;
            $data['jgo_money_all']          = $money;
            $data['jgo_time']               = $currTime;
            $data['jgo_text']               = From::valTrim('text');
            $data['jgo_status']             = 0;
            $data['au_id']                  = 0;
            $data['jgo_audit_time']         = 0;
            $result = Sql::tag('jf_goods_order', 'GMY') -> addById($data);
            if($result > 0){
            }else{
                Tips::show('兑换失败，请修改正确后提交！', 'javascript: history.back();');
            }
            $Tpl -> assign('result', $result);
            $Tpl -> assign('currTime', $currTime);
        }
        $Tpl -> show('Shop/pay_ok.html');
    }
}