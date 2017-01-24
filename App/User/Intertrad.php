<?php
/**
 * @Copyright (C) 2016.
 * @Description Intertrad
 * @FileName Intertrad.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;
use \App\Pub\Common;
use \App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use \Libs\Comm\Valid;
use \Libs\Comm\Time;
use \Libs\Frame\Action;
use \Libs\Frame\Url;
use \App\Auth\MyAuth;
use Libs\Load;
use \Libs\Plugins\Checkcode\Checkcode;
use \Libs\Tag\Db;
use \Libs\Tag\Page;
use \Libs\Tag\Sql;

class Intertrad extends Action{
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
     * @desciption 国际贸易
     */
    public function main(string $action){
        $this -> releaseproduct($action);
    }

    /**
     * @name manageproduct
     * @desciption 产品管理
     */
    public function manageproduct(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        $op = From::valTrim('op');
        $id = From::valTrim('gd_id');
        if($op == 'edit'){  //编辑
            $save = From::valInt('save');
            //分类列表
            $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
            $listClass = $Db->getData($sql);
            if(count($listClass) > 0){
                foreach($listClass as $key => $val){
                    $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid='.intval($val['gc_id']).' ORDER BY gc_sort ASC, gc_id ASC';
                    $listsClass = $Db->getData($sql);
                    $listClass[$key]['list'] = $listsClass;
                    if(count($listsClass) > 0){
                        foreach($listsClass as $keys => $vals){
                            $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid='.intval($vals['gc_id']).' ORDER BY gc_sort ASC, gc_id ASC';
                            $listssClass = $Db->getData($sql);
                            $listClass[$key]['list'][$keys]['list'] = $listssClass;
                        }
                    }
                }
            }
            $Tpl -> assign('listClass', $listClass);
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE us_id='.$usId.' AND gd_id='.$id.' AND gd_isdel=0';
            $info = $Db->getDataOne($sql);
            if(!isset($info['gd_img'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
            $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
            $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
            if($save == 1){
                $data       = [];
                $data['gd_last_time']          = $currTime;
                $data['gc_id']                 = From::valInt('gc_id');
                $data['gd_title']              = From::valTrim('gd_title');
                $data['gd_maidian']            = From::valTrim('gd_maidian');
                $data['gd_keyword']            = From::valTrim('gd_keyword');
                $data['gd_price']              = From::valTrim('gd_price');
                $data['gd_num']                = From::valInt('gd_num');
                $data['gd_num_min']            = From::valInt('gd_num_min');
                $data['gd_send_date']          = From::valTrim('gd_send_date');
                $data['gd_end_date']           = max(intval(strtotime(From::valTrim('gd_end_date'))), $currTime);
                $data['gd_text']               = From::val('gd_text', FALSE);
                $data['gd_img']                = '';
                $data['gd_area']               = From::valTrim('gd_area');
                $data['gd_unit']               = From::valTrim('gd_unit');
                $data['gd_pingpai']            = From::valTrim('gd_pingpai');
                $data['gd_xinghao']            = From::valTrim('gd_xinghao');
                $data['gd_isjiagong']          = intval(From::valTrim('gd_isjiagong'));
                $data['gd_iskucun']            = intval(From::valTrim('gd_iskucun'));
                $data['gd_attr']               = '';
                $listImg = $info['gd_imgs'];
                $allowType = ['jpg', 'png', 'gif', 'jpeg', 'bmp'];
                if(isset($_FILES['gd_img']) && count($_FILES['gd_img']['name']) > 0) foreach($_FILES['gd_img']['name'] as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $annx = strtolower(substr($val, strrpos($val, '.')+1));
                    if(!in_array($annx, $allowType)) continue;  //不允许类型
                    $tmpName = $_FILES['gd_img']['tmp_name'][$key];
                    $newName = 'Static/data/gjmy/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
                    $descImg = Load::getUrlRoot().$newName;
                    @mkdir(dirname($descImg), 0777);
                    if(move_uploaded_file($tmpName, $descImg)){
                        $listImg[$key] = $newName;
                    }
                }
                $data['gd_img']                = json_encode($listImg);
                $result = Sql::tag('gjmy_data', 'GMY') -> setBy($data, ['us_id' => $usId, 'gd_id' => $id, 'gd_isdel' => 0]);
                if($result > 0){
                    Tips::show('修改成功！', Link::getLink('intertrad').'?A=intertrad-manageproduct');
                }else{
                    Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl -> assign('info', $info);
            $Tpl->show('User/intertrad_releaseproduct_edit.html');
        }else if($op == 'del'){  //删除
            $sql = 'UPDATE '.$Db -> getTableNameAll('gjmy_data').' SET gd_isdel=1, gd_last_time='.$currTime.' WHERE us_id='.$usId.' AND gd_id='.$id.' AND gd_isdel=0';
            $Db->getDataNum($sql);
            Tips::show('成功!', Link::getLink('intertrad').'?A=intertrad-manageproduct');
        }
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 6);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND gd_isdel=0 AND gd_type=2';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE '.$whereString.' ORDER BY gd_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $val['gd_imgs'] = strlen($val['gd_img']) > 0 ? @json_decode($val['gd_img'], TRUE) : [];
                $val['gd_attrs'] = strlen($val['gd_attr']) > 0 ? @json_decode($val['gd_attr'], TRUE) : [];
                $val['gd_price'] = sprintf("%.2f", $val['gd_price']);
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(Link::getLink('intertrad').'?A=intertrad-manageproduct');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/intertrad_manageproduct.html');
    }

    /**
     * @name releaseproduct
     * @desciption 产品发布
     */
    public function releaseproduct(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $save = From::valInt('save');
        //分类列表
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
        $listClass = $Db->getData($sql);
        if(count($listClass) > 0){
            foreach($listClass as $key => $val){
                $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid='.intval($val['gc_id']).' ORDER BY gc_sort ASC, gc_id ASC';
                $listsClass = $Db->getData($sql);
                $listClass[$key]['list'] = $listsClass;
                if(count($listsClass) > 0){
                    foreach($listsClass as $keys => $vals){
                        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid='.intval($vals['gc_id']).' ORDER BY gc_sort ASC, gc_id ASC';
                        $listssClass = $Db->getData($sql);
                        $listClass[$key]['list'][$keys]['list'] = $listssClass;
                    }
                }
            }
        }
        $Tpl -> assign('listClass', $listClass);
        if($save == 1){
            $data       = [];
            $data['us_id']                 = $usId;
            $data['gd_isdel']              = 0;
            $data['gd_type']               = 2;
            $data['gd_frist_time']         = $currTime;
            $data['gd_last_time']          = $currTime;
            $data['gc_id']                 = From::valInt('gc_id');
            $data['gd_title']              = From::valTrim('gd_title');
            $data['gd_maidian']            = From::valTrim('gd_maidian');
            $data['gd_keyword']            = From::valTrim('gd_keyword');
            $data['gd_price']              = From::valTrim('gd_price');
            $data['gd_num']                = From::valInt('gd_num');
            $data['gd_num_min']            = From::valInt('gd_num_min');
            $data['gd_send_date']          = From::valTrim('gd_send_date');
            $data['gd_end_date']           = max(intval(strtotime(From::valTrim('gd_end_date'))), $currTime);
            $data['gd_text']               = From::val('gd_text', FALSE);
            $data['gd_img']                = '';
            $data['gd_area']               = From::valTrim('gd_area');
            $data['gd_unit']               = From::valTrim('gd_unit');
            $data['gd_pingpai']            = From::valTrim('gd_pingpai');
            $data['gd_xinghao']            = From::valTrim('gd_xinghao');
            $data['gd_isjiagong']          = intval(From::valTrim('gd_isjiagong'));
            $data['gd_iskucun']            = intval(From::valTrim('gd_iskucun'));
            $data['gd_attr']               = '';
            $listImg = [];
            $allowType = ['jpg', 'png', 'gif', 'jpeg', 'bmp'];
            if(isset($_FILES['gd_img']) && count($_FILES['gd_img']['name']) > 0) foreach($_FILES['gd_img']['name'] as $key => $val){
                $val = trim($val);
                if(strlen($val) < 1) continue;
                $annx = strtolower(substr($val, strrpos($val, '.')+1));
                if(!in_array($annx, $allowType)) continue;  //不允许类型
                $tmpName = $_FILES['gd_img']['tmp_name'][$key];
                $newName = 'Static/data/gjmy/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
                $descImg = Load::getUrlRoot().$newName;
                @mkdir(dirname($descImg), 0777);
                if(move_uploaded_file($tmpName, $descImg)){
                    $listImg[] = $newName;
                }
            }
            $data['gd_img']                = json_encode($listImg);
            $result = Sql::tag('gjmy_data', 'GMY') -> addById($data);
            if($result > 0){
                Tips::show('新增完成！', Link::getLink('intertrad').'?A=intertrad-manageproduct');
            }else{
                Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->show('User/intertrad_releaseproduct.html');
    }

    /**
     * @name managepurcha
     * @desciption 采购需求管理
     */
    public function managepurcha(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        $op = From::valTrim('op');
        $id = From::valTrim('gd_id');
        if($op == 'edit'){  //编辑
            $save = From::valInt('save');
            //分类列表
            $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
            $listClass = $Db->getData($sql);
            if(count($listClass) > 0){
                foreach($listClass as $key => $val){
                    $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid='.intval($val['gc_id']).' ORDER BY gc_sort ASC, gc_id ASC';
                    $listsClass = $Db->getData($sql);
                    $listClass[$key]['list'] = $listsClass;
                    if(count($listsClass) > 0){
                        foreach($listsClass as $keys => $vals){
                            $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid='.intval($vals['gc_id']).' ORDER BY gc_sort ASC, gc_id ASC';
                            $listssClass = $Db->getData($sql);
                            $listClass[$key]['list'][$keys]['list'] = $listssClass;
                        }
                    }
                }
            }
            $Tpl -> assign('listClass', $listClass);
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE us_id='.$usId.' AND gd_id='.$id.' AND gd_isdel=0';
            $info = $Db->getDataOne($sql);
            if(!isset($info['gd_img'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
            $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
            $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
            if($save == 1){
                $data       = [];
                $data['gd_last_time']          = $currTime;
                $data['gc_id']                 = From::valInt('gc_id');
                $data['gd_title']              = From::valTrim('gd_title');
                //$data['gd_maidian']            = From::valTrim('gd_maidian');
                //$data['gd_keyword']            = From::valTrim('gd_keyword');
                //$data['gd_price']              = From::valTrim('gd_price');
                //$data['gd_num']                = From::valInt('gd_num');
                $data['gd_num_min']            = From::valInt('gd_num_min');
                //$data['gd_send_date']          = From::valTrim('gd_send_date');
                $data['gd_end_date']           = max(intval(strtotime(From::valTrim('gd_end_date'))), $currTime);
                $data['gd_text']               = From::val('gd_text', FALSE);
                $data['gd_img']                = '';
                //$data['gd_area']               = From::valTrim('gd_area');
                //$data['gd_unit']               = From::valTrim('gd_unit');
                //$data['gd_pingpai']            = From::valTrim('gd_pingpai');
                //$data['gd_xinghao']            = From::valTrim('gd_xinghao');
                //$data['gd_isjiagong']          = From::valTrim('gd_isjiagong');
                //$data['gd_iskucun']            = From::valTrim('gd_iskucun');
                $data['gd_attr']               = '';
                $listImg = $info['gd_imgs'];
                $allowType = ['jpg', 'png', 'gif', 'jpeg', 'bmp'];
                if(isset($_FILES['gd_img']) && count($_FILES['gd_img']['name']) > 0) foreach($_FILES['gd_img']['name'] as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $annx = strtolower(substr($val, strrpos($val, '.')+1));
                    if(!in_array($annx, $allowType)) continue;  //不允许类型
                    $tmpName = $_FILES['gd_img']['tmp_name'][$key];
                    $newName = 'Static/data/gjmy/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
                    $descImg = Load::getUrlRoot().$newName;
                    @mkdir(dirname($descImg), 0777);
                    if(move_uploaded_file($tmpName, $descImg)){
                        $listImg[$key] = $newName;
                    }
                }
                $data['gd_img']                = json_encode($listImg);
                $result = Sql::tag('gjmy_data', 'GMY') -> setBy($data, ['us_id' => $usId, 'gd_id' => $id, 'gd_isdel' => 0]);
                if($result > 0){
                    Tips::show('修改成功！', Link::getLink('intertrad').'?A=intertrad-managepurcha');
                }else{
                    Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl -> assign('info', $info);
            $Tpl->show('User/intertrad_releasepurcha_edit.html');
        }else if($op == 'del'){  //删除
            $sql = 'UPDATE '.$Db -> getTableNameAll('gjmy_data').' SET gd_isdel=1, gd_last_time='.$currTime.' WHERE us_id='.$usId.' AND gd_id='.$id.' AND gd_isdel=0';
            $Db->getDataNum($sql);
            Tips::show('成功!', Link::getLink('intertrad').'?A=intertrad-managepurcha');
        }
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 6);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND gd_isdel=0 AND gd_type=1';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE '.$whereString.' ORDER BY gd_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $val['gd_imgs'] = strlen($val['gd_img']) > 0 ? @json_decode($val['gd_img'], TRUE) : [];
                $val['gd_attrs'] = strlen($val['gd_attr']) > 0 ? @json_decode($val['gd_attr'], TRUE) : [];
                $val['gd_price'] = sprintf("%.2f", $val['gd_price']);
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(Link::getLink('intertrad').'?A=intertrad-managepurcha');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/intertrad_managepurcha.html');
    }

    /**
     * @name releasepurcha
     * @desciption 采购需求发布
     */
    public function releasepurcha(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $save = From::valInt('save');
        //分类列表
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
        $listClass = $Db->getData($sql);
        if(count($listClass) > 0){
            foreach($listClass as $key => $val){
                $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid='.intval($val['gc_id']).' ORDER BY gc_sort ASC, gc_id ASC';
                $listsClass = $Db->getData($sql);
                $listClass[$key]['list'] = $listsClass;
                if(count($listsClass) > 0){
                    foreach($listsClass as $keys => $vals){
                        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid='.intval($vals['gc_id']).' ORDER BY gc_sort ASC, gc_id ASC';
                        $listssClass = $Db->getData($sql);
                        $listClass[$key]['list'][$keys]['list'] = $listssClass;
                    }
                }
            }
        }
        $Tpl -> assign('listClass', $listClass);
        if($save == 1){
            $data       = [];
            $data['us_id']                 = $usId;
            $data['gd_isdel']              = 0;
            $data['gd_type']               = 1;
            $data['gd_frist_time']         = $currTime;
            $data['gd_last_time']          = $currTime;
            $data['gc_id']                 = From::valInt('gc_id');
            $data['gd_title']              = From::valTrim('gd_title');
            //$data['gd_maidian']            = From::valTrim('gd_maidian');
            //$data['gd_keyword']            = From::valTrim('gd_keyword');
            //$data['gd_price']              = From::valTrim('gd_price');
            //$data['gd_num']                = From::valInt('gd_num');
            $data['gd_num_min']            = From::valInt('gd_num_min');
            //$data['gd_send_date']          = From::valTrim('gd_send_date');
            $data['gd_end_date']           = max(intval(strtotime(From::valTrim('gd_end_date'))), $currTime);
            $data['gd_text']               = From::val('gd_text', FALSE);
            $data['gd_img']                = '';
            //$data['gd_area']               = From::valTrim('gd_area');
            //$data['gd_unit']               = From::valTrim('gd_unit');
            //$data['gd_pingpai']            = From::valTrim('gd_pingpai');
            //$data['gd_xinghao']            = From::valTrim('gd_xinghao');
            //$data['gd_isjiagong']          = From::valTrim('gd_isjiagong');
            //$data['gd_iskucun']            = From::valTrim('gd_iskucun');
            $data['gd_attr']               = '';
            $listImg = [];
            $allowType = ['jpg', 'png', 'gif', 'jpeg', 'bmp'];
            if(isset($_FILES['gd_img']) && count($_FILES['gd_img']['name']) > 0) foreach($_FILES['gd_img']['name'] as $key => $val){
                $val = trim($val);
                if(strlen($val) < 1) continue;
                $annx = strtolower(substr($val, strrpos($val, '.')+1));
                if(!in_array($annx, $allowType)) continue;  //不允许类型
                $tmpName = $_FILES['gd_img']['tmp_name'][$key];
                $newName = 'Static/data/gjmy/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
                $descImg = Load::getUrlRoot().$newName;
                @mkdir(dirname($descImg), 0777);
                if(move_uploaded_file($tmpName, $descImg)){
                    $listImg[] = $newName;
                }
            }
            $data['gd_img']                = json_encode($listImg);
            $result = Sql::tag('gjmy_data', 'GMY') -> addById($data);
            if($result > 0){
                Tips::show('新增完成！', Link::getLink('intertrad').'?A=intertrad-managepurcha');
            }else{
                Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->show('User/intertrad_releasepurcha.html');
    }

    /**
     * @name recv
     * @desciption 我的咨询
     */
    public function recv(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $op = From::valTrim('op');
        $gx_id = intval(From::valInt('gx_id'));
        if($op == 'del'){  //删除
            $sql = 'UPDATE '.$Db -> getTableNameAll('gjmy_xunjia').' SET gx_isdel=1, gx_last_time='.$currTime.' WHERE us_id_in='.$usId.' AND gx_id='.$gx_id.' AND gx_isdel=0';
            $Db->getDataNum($sql);
            Tips::show('成功!', Link::getLink('intertrad').'?A=intertrad-recv');
        }
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = '(us_id_in='.$usId.' OR us_id_out='.$usId.') AND gx_isdel=0';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gjmy_xunjia').' WHERE '.$whereString.' ORDER BY gx_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id='.$val['gd_id'].' AND gd_isdel=0';
                $info = $Db->getDataOne($sql);
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$val['us_id_out'].'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
                $seoInfo = $Db->getDataOne($sql);
                $dataList[] = array_merge($val, $info, $seoInfo);
            }
        }
        $pageList = $Page -> getPage(Link::getLink('intertrad').'?A=intertrad-recv');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/intertrad_recv.html');
    }

    /**
     * @name recvhf
     * @desciption 我的咨询回复
     */
    public function recvhf(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $gd_id = intval(From::valInt('gd_id'));
        $gx_id = intval(From::valInt('gx_id'));
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id='.$gd_id.' AND gd_isdel=0';
        $info = $Db->getDataOne($sql);
        $musId  = intval($info['us_id']);
        $Tpl -> assign('info', $info);
        $save = From::valInt('save');
        if($save == 1){
            $data       = [];
            $data['gx_pid']                = $gx_id;
            $data['us_id_in']              = $usId;
            $data['us_id_out']             = $musId;
            $data['gd_id']                 = $gd_id;
            $data['gx_isdel']              = 0;
            $data['gx_status']             = 0;
            $data['gx_frist_time']         = $currTime;
            $data['gx_last_time']          = $currTime;
            $data['gx_text']               = From::valTrim('gx_text');
            $result = Sql::tag('gjmy_xunjia', 'GMY') -> addById($data);
            if($result > 0){
                $Db->getDataNum('UPDATE'.$Db -> getTableNameAll('gjmy_xunjia').' SET gx_status=3, gx_last_time='.$currTime.' WHERE gx_id=\''.$gx_id.'\' AND gx_isdel=0');
                Tips::show('发表完成！', Link::getLink('intertrad').'?A=intertrad-recv');
            }else{
                Tips::show('发表失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Db->getDataNum('UPDATE'.$Db -> getTableNameAll('gjmy_xunjia').' SET gx_status=1, gx_last_time='.$currTime.' WHERE gx_id=\''.$gx_id.'\' AND gx_isdel=0');
        $Tpl->show('User/intertrad_recv_hf.html');
    }

    /**
     * @name order
     * @desciption 我的订单
     */
    public function order(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = '(us_id_in='.$usId.' OR us_id_out='.$usId.') AND go_isdel=0';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gjmy_order').' WHERE '.$whereString.' ORDER BY go_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $val['go_price'] = sprintf("%.2f", $val['go_price']);
                $val['go_total'] = sprintf("%.2f", $val['go_total']);
                $val['go_fare'] = sprintf("%.2f", $val['go_fare']);
                $val['go_offers'] = sprintf("%.2f", $val['go_offers']);
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id=\''.$val['gd_id'].'\' AND gd_isdel=0';
                $gdInfo = $Db->getDataOne($sql);
                $dataList[] = array_merge($val, $gdInfo);
            }
        }
        $pageList = $Page -> getPage(Link::getLink('intertrad').'?A=intertrad-order');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/intertrad_order.html');
    }

    /**
     * @name orderview
     * @desciption 我的订单查看
     */
    public function orderview(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $goId = From::valInt('go_id');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_order').' WHERE (us_id_in='.$usId.' OR us_id_out='.$usId.') AND go_id='.$goId.' AND go_isdel=0';
        $oinfo = $Db->getDataOne($sql);
        if(!isset($oinfo['gd_id'])) Tips::show('订单查看失败！', 'javascript: history.back();');
        $id = intval($oinfo['gd_id']);
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id='.$id.' AND gd_isdel=0';
        $info = $Db->getDataOne($sql);
        if(!isset($info['gd_img'])) Tips::show('订单查看失败！', 'javascript: history.back();');
        $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
        $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
        $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
        $info = array_merge($info, $oinfo);
        $info['go_price'] = sprintf("%.2f", $info['go_price']);
        $info['go_total'] = sprintf("%.2f", $info['go_total']);
        $info['go_fare'] = sprintf("%.2f", $info['go_fare']);
        $info['go_offers'] = sprintf("%.2f", $info['go_offers']);
        $Tpl -> assign('info', $info);
        $Tpl->show('User/intertrad_order_view.html');
    }

    /**
     * @name shoporder
     * @desciption 我的积分商城订单
     */
    public function shoporder(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND jgo_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('jf_goods_order').' WHERE '.$whereString.' ORDER BY jgo_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('jf_goods').' WHERE jg_id=\''.$val['jg_id'].'\' AND jg_isdel=0';
                $jgInfo = $Db->getDataOne($sql);
                $dataList[] = array_merge($val, $jgInfo);
            }
        }
        $pageList = $Page -> getPage(Link::getLink('intertrad').'?A=intertrad-shoporder');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/intertrad_shoporder.html');
    }
}