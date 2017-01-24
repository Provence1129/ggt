<?php
/**
 * @Copyright (C) 2016.
 * @Description Bid
 * @FileName Bid.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;
use \App\Pub\Common;
use \App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Frame\Action;
use \Libs\Frame\Url;
use \App\Auth\MyAuth;
use Libs\Load;
use \Libs\Plugins\Checkcode\Checkcode;
use \Libs\Tag\Db;
use \Libs\Tag\Page;
use \Libs\Tag\Sql;

class Bid extends Action{
    //配置
    public function conf(){
        $Tpl = $this -> getTpl();
        $page = [];
        $page['Title']          = '港港通国际多式联运门户网';
        $page['Keywords']       = '行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布';
        $page['Description']    = '国内首家专业性多式联运行业门户网站，集行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布等功能和内容';
        $Tpl -> assign('page', $page);
        $this -> userInfo = $_SESSION['TOKEN']['INFO'];
    }

    /**
     * @name main
     * @desciption 项目竞标
     */
    public function main(string $action){
        $this -> manage($action);
    }

    /**
     * @name manage
     * @desciption 招标公告管理
     */
    public function manage(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        $op = From::valTrim('op');
        $id = From::valTrim('td_id');
        if($op == 'edit'){  //编辑
            $save = From::valInt('save');
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('tender').' WHERE us_id='.$usId.' AND td_id='.$id.' AND td_isdel=0';
            $info = $Db->getDataOne($sql);
            if(!isset($info['td_file'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            if($save == 1){
                $data       = [];
                $data['us_id']                 = $usId;
                $data['td_isdel']              = 0;
                $data['td_first_time']         = $currTime;
                $data['td_last_time']          = $currTime;
                $data['td_title']              = From::valTrim('td_title');
                $data['td_content']            = From::valTrim('td_content');
                $data['td_concat']             = From::valTrim('td_concat');
                $data['td_concat_phone']       = From::valTrim('td_concat_phone');
                $data['td_addr_sheng']         = From::valTrim('td_addr_sheng');
                $data['td_addr_shi']           = From::valTrim('td_addr_shi');
                $data['td_addr_xian']          = From::valTrim('td_addr_xian');
                $data['td_addr_more']          = From::valTrim('td_addr_more');
                $data['td_end_time']           = max(intval(strtotime(From::valTrim('td_end_time'))), $currTime);
                $data['td_remark']             = From::valTrim('td_remark');
                $allowType = ['7z', 'zip', 'rar', 'doc', 'docx', 'ppt', 'xls', 'xlsx'];
                if(isset($_FILES['td_file']) && strlen($_FILES['td_file']['name']) > 0){
                    $data['td_file']               = '';
                    $val = trim($_FILES['td_file']['name']);
                    $annx = strtolower(substr($val, strrpos($val, '.')+1));
                    if(!in_array($annx, $allowType)) Tips::show('失败，请修改标书文件后提交！', 'javascript: history.back();');
                    $tmpName = $_FILES['td_file']['tmp_name'];
                    $newName = 'Static/data/tender/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
                    $descImg = Load::getUrlRoot().$newName;
                    @mkdir(dirname($descImg), 0777);
                    if(move_uploaded_file($tmpName, $descImg)){
                        $data['td_file']        = $newName;
                    }
                }
                $result = Sql::tag('tender', 'GMY') -> setBy($data, ['us_id' => $usId, 'td_id' => $id, 'td_isdel' => 0]);
                if($result > 0){
                    Tips::show('修改标书完成！', Link::getLink('bid').'?A=bid-manage');
                }else{
                    Tips::show('修改标书失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl -> assign('info', $info);
            $Tpl->show('User/bid_release_edit.html');
        }else if($op == 'del'){  //删除
            $sql = 'UPDATE '.$Db -> getTableNameAll('tender').' SET td_isdel=1, td_last_time='.$currTime.' WHERE us_id='.$usId.' AND td_id='.$id.' AND td_isdel=0';
            $Db->getDataNum($sql);
            Tips::show('删除成功!', Link::getLink('bid').'?A=bid-manage');
        }
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND td_isdel=0';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('tender').' WHERE '.$whereString.' ORDER BY td_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(Link::getLink('bid').'?A=bid-manage');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/bid_manage.html');
    }

    /**
     * @name release
     * @desciption 发布招标公告
     */
    public function release(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $save = From::valInt('save');
        if($save == 1){
            $data       = [];
            $data['us_id']                 = $usId;
            $data['td_isdel']              = 0;
            $data['td_first_time']         = $currTime;
            $data['td_last_time']          = $currTime;
            $data['td_title']              = From::valTrim('td_title');
            $data['td_content']            = From::valTrim('td_content');
            $data['td_concat']             = From::valTrim('td_concat');
            $data['td_concat_phone']       = From::valTrim('td_concat_phone');
            $data['td_addr_sheng']         = From::valTrim('td_addr_sheng');
            $data['td_addr_shi']           = From::valTrim('td_addr_shi');
            $data['td_addr_xian']          = From::valTrim('td_addr_xian');
            $data['td_addr_more']          = From::valTrim('td_addr_more');
            $data['td_end_time']           = max(intval(strtotime(From::valTrim('td_end_time'))), $currTime);
            $data['td_remark']             = From::valTrim('td_remark');
            $data['td_file']               = '';
            $allowType = ['7z', 'zip', 'rar', 'doc', 'docx', 'ppt', 'xls', 'xlsx'];
            if(isset($_FILES['td_file']) && strlen($_FILES['td_file']['name']) > 0){
                $val = trim($_FILES['td_file']['name']);
                $annx = strtolower(substr($val, strrpos($val, '.')+1));
                if(!in_array($annx, $allowType)) Tips::show('失败，请修改标书文件后提交！', 'javascript: history.back();');
                $tmpName = $_FILES['td_file']['tmp_name'];
                $newName = 'Static/data/tender/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
                $descImg = Load::getUrlRoot().$newName;
                @mkdir(dirname($descImg), 0777);
                if(move_uploaded_file($tmpName, $descImg)){
                    $data['td_file']        = $newName;
                }
            }
            $result = Sql::tag('tender', 'GMY') -> addById($data);
            if($result > 0){
                Tips::show('发表标书完成！', Link::getLink('bid').'?A=bid-manage');
            }else{
                Tips::show('发表标书失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->show('User/bid_release.html');
    }
}