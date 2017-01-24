<?php
/**
 * @Copyright (C) 2016.
 * @Description Gtb
 * @FileName Gtb.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use App\Pub\Tips;
use Libs\Comm\From;
use Libs\Comm\Time;
use \Libs\Frame\Action;
use \Libs\Frame\Conf;
use \Libs\Tag\Db;
use Libs\Tag\Page;

class Gtb extends Action{
    //配置
    public function conf(){
        $Tpl = $this -> getTpl();
        $Tpl->assign('modelName', '港通宝');
    }

    //MAIN
    public function main(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('Gtb/index.html');
    }

    //投资发布
    public function fbrz(string $action){
        $Tpl = $this -> getTpl();
        $save = From::valInt('save');
        $gtzId = From::valInt('gtz_id');
        $Db = Db::tag('DB.USER', 'GMY');
        if($save == 1 && $gtzId > 0){
            $company = From::valTrim('company');
            $concat = From::valTrim('concat');
            $phones = From::valTrim('phones');
            $qwdate = From::valTrim('qwdate');
            $desc   = From::valTrim('desc');
            $currTime = Time::getTimeStamp();
            $usId = $Db -> getDataInt('SELECT us_id FROM '.$Db -> getTableNameAll('gtb_touzi').' WHERE gtz_isdel=0 AND gtz_id='.$gtzId, 'us_id');
            if($usId < 1) Tips::show('投递项目失败，请正确操作提交！', 'javascript: history.back();');
            $sql = 'INSERT INTO '.$Db -> getTableNameAll('gtb_touzi_record').' SET gtz_id='.$gtzId.', us_id='.$usId.', tr_company=\''.addslashes($company).'\', tr_concat=\''.addslashes($concat).'\', tr_phones=\''.addslashes($phones).'\', tr_qwdate=\''.addslashes($qwdate).'\', tr_desc=\''.addslashes($desc).'\', tr_isdel=0, tr_first_time='.$currTime.', tr_last_time='.$currTime;
            $Db -> getDataNum($sql);
            Tips::show('投递项目成功！', '/gtb/fbrz.php');
        }
        $Db = Db::tag('DB.USER', 'GMY');
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 4);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'gtz_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gtb_touzi').' WHERE '.$whereString.' ORDER BY gtz_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $userInfo = $Db -> getDataOne('SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id='.intval($val['us_id'].' AND ui_isdel=0'));
                $entInfo = $Db -> getDataOne('SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id='.intval($val['us_id'].' AND ent_isdel=0'));
                $dataList[] = array_merge($val, $userInfo, $entInfo);
            }
        }
        $pageList = $Page -> getPage('/gtb/fbrz.php');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        //推荐
        $recommList = $Db -> getData('SELECT * FROM '.$Db -> getTableNameAll('gtb_touzi').' WHERE gtz_isdel=0 ORDER BY gtz_isrecommend DESC, gtz_id DESC LIMIT 2');
        $Tpl -> assign('recommList', $recommList);
        $Tpl -> show('Gtb/fbrz.html');
    }

    //我要融资
    public function wyrz(string $action){
        $Tpl = $this -> getTpl();
        $save = From::valInt('save');
        $grzId = From::valInt('grz_id');
        $Db = Db::tag('DB.USER', 'GMY');
        if($save == 1 && $grzId > 0){
            $company = From::valTrim('company');
            $concat = From::valTrim('concat');
            $phones = From::valTrim('phones');
            $qwdate = From::valTrim('qwdate');
            $desc   = From::valTrim('desc');
            $currTime = Time::getTimeStamp();
            $usId = $Db -> getDataInt('SELECT us_id FROM '.$Db -> getTableNameAll('gtb_rongzi').' WHERE grz_isdel=0 AND grz_id='.$grzId, 'us_id');
            if($usId < 1) Tips::show('约谈失败，请正确操作提交！', 'javascript: history.back();');
            $sql = 'INSERT INTO '.$Db -> getTableNameAll('gtb_rongzi_record').' SET grz_id='.$grzId.', us_id='.$usId.', rr_company=\''.addslashes($company).'\', rr_concat=\''.addslashes($concat).'\', rr_phones=\''.addslashes($phones).'\', rr_qwdate=\''.addslashes($qwdate).'\', rr_desc=\''.addslashes($desc).'\', rr_isdel=0, rr_first_time='.$currTime.', rr_last_time='.$currTime;
            $Db -> getDataNum($sql);
            Tips::show('约谈项目成功！', '/gtb/wyrz.php');
        }
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 4);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'grz_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gtb_rongzi').' WHERE '.$whereString.' ORDER BY grz_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $userInfo = $Db -> getDataOne('SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id='.intval($val['us_id'].' AND ui_isdel=0'));
                $entInfo = $Db -> getDataOne('SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id='.intval($val['us_id'].' AND ent_isdel=0'));
                $dataList[] = array_merge($val, $userInfo, $entInfo);
            }
        }
        $pageList = $Page -> getPage('/gtb/wyrz.php');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        //推荐
        $recommList = $Db -> getData('SELECT * FROM '.$Db -> getTableNameAll('gtb_rongzi').' WHERE grz_isdel=0 ORDER BY grz_isrecommend DESC, grz_id DESC LIMIT 2');
        $Tpl -> assign('recommList', $recommList);
        $Tpl -> show('Gtb/wyrz.html');
    }

    //在线投保
    public function zxtb(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('Gtb/zxtb.html');
    }
}