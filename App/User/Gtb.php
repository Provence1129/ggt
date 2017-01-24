<?php
/**
 * @Copyright (C) 2016.
 * @Description Gtb
 * @FileName Gtb.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;
use \App\Pub\Common;
use App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use Libs\Comm\Time;
use Libs\Comm\Valid;
use \Libs\Frame\Action;
use \Libs\Frame\Url;
use \App\Auth\MyAuth;
use \Libs\Plugins\Checkcode\Checkcode;
use Libs\Tag\Db;
use Libs\Tag\Page;
use Libs\Tag\Sql;

class Gtb extends Action{
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
     * @desciption 港通宝
     */
    public function main(string $action){
        $this->financingRelease($action);
    }

    /**
     * @name insurance
     * @desciption 我的保险
     */
    public function insurance(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/gtb_insurance.html');
    }

    /**
     * @name financingRelease
     * @desciption 融资管理发布
     */
    public function financingRelease(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $save = From::valInt('save');
        if($save == 1){
            $data       = [];
            $data['us_id']                 = $usId;
            $data['grz_isdel']              = 0;
            $data['grz_first_time']         = $currTime;
            $data['grz_last_time']          = $currTime;
            $data['grz_project']            = From::valTrim('grz_project');
            $data['grz_desc']               = From::valTrim('grz_desc');
            $data['grz_hangye']             = From::valTrim('grz_hangye');
            $data['grz_fangshi']            = From::valTrim('grz_fangshi');
            $data['grz_area_sheng']         = From::valTrim('grz_area_sheng');
            $data['grz_area_shi']           = From::valTrim('grz_area_shi');
            $data['grz_area_xian']          = From::valTrim('grz_area_xian');
            $data['grz_money']              = From::valTrim('grz_money');
            $data['grz_phone']              = From::valTrim('grz_phone');
            $data['grz_rzxq']               = From::valTrim('grz_rzxq');
            /**
            $data['grz_file']               = '';
            $allowType = ['7z', 'zip', 'rar', 'doc', 'docx', 'ppt', 'xls', 'xlsx'];
            if(isset($_FILES['grz_file']) && strlen($_FILES['grz_file']['name']) > 0){
            $val = trim($_FILES['td_file']['name']);
            $annx = strtolower(substr($val, strrpos($val, '.')+1));
            if(!in_array($annx, $allowType)) Tips::show('失败，请修改投资信息后提交！', 'javascript: history.back();');
            $tmpName = $_FILES['td_file']['tmp_name'];
            $newName = 'Static/data/tender/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
            $descImg = Load::getUrlRoot().$newName;
            @mkdir(dirname($descImg), 0777);
            if(move_uploaded_file($tmpName, $descImg)){
            $data['grz_file']        = $newName;
            }
            }
             */
            $result = Sql::tag('gtb_rongzi', 'GMY') -> addById($data);
            if($result > 0){
                Tips::show('发表融资信息成功！', Link::getLink('gtb').'?A=gtb-financing');
            }else{
                Tips::show('发表融资信息失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->show('User/gtb_financingfb.html');
    }

    /**
     * @name financing
     * @desciption 融资管理
     */
    public function financing(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        $op = From::valTrim('op');
        $id = From::valInt('id');
        if($op == 'edit'){  //编辑
            $save = From::valInt('save');
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gtb_rongzi').' WHERE us_id='.$usId.' AND grz_id='.$id.' AND grz_isdel=0';
            $info = $Db->getDataOne($sql);
            if(!isset($info['grz_id'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            if($save == 1){
                $data       = [];
                $data['grz_isdel']              = 0;
                $data['grz_first_time']         = $currTime;
                $data['grz_last_time']          = $currTime;
                $data['grz_project']            = From::valTrim('grz_project');
                $data['grz_desc']               = From::valTrim('grz_desc');
                $data['grz_hangye']             = From::valTrim('grz_hangye');
                $data['grz_fangshi']            = From::valTrim('grz_fangshi');
                $data['grz_area_sheng']         = From::valTrim('grz_area_sheng');
                $data['grz_area_shi']           = From::valTrim('grz_area_shi');
                $data['grz_area_xian']          = From::valTrim('grz_area_xian');
                $data['grz_money']              = From::valTrim('grz_money');
                $data['grz_phone']              = From::valTrim('grz_phone');
                $data['grz_rzxq']               = From::valTrim('grz_rzxq');
                /**
                $allowType = ['7z', 'zip', 'rar', 'doc', 'docx', 'ppt', 'xls', 'xlsx'];
                if(isset($_FILES['grz_file']) && strlen($_FILES['grz_file']['name']) > 0){
                $data['grz_file']               = '';
                $val = trim($_FILES['grz_file']['name']);
                $annx = strtolower(substr($val, strrpos($val, '.')+1));
                if(!in_array($annx, $allowType)) Tips::show('失败，请修改标书文件后提交！', 'javascript: history.back();');
                $tmpName = $_FILES['grz_file']['tmp_name'];
                $newName = 'Static/data/tender/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
                $descImg = Load::getUrlRoot().$newName;
                @mkdir(dirname($descImg), 0777);
                if(move_uploaded_file($tmpName, $descImg)){
                $data['grz_file']        = $newName;
                }
                }
                 */
                $result = Sql::tag('gtb_rongzi', 'GMY') -> setBy($data, ['us_id' => $usId, 'grz_id' => $id, 'grz_isdel' => 0]);
                if($result > 0){
                    Tips::show('修改融资信息成功！', Link::getLink('gtb').'?A=gtb-financing');
                }else{
                    Tips::show('修改融资信息失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl -> assign('info', $info);
            $Tpl->show('User/gtb_financingfb_edit.html');
        }else if($op == 'del'){  //删除
            $sql = 'UPDATE '.$Db -> getTableNameAll('gtb_rongzi').' SET grz_isdel=1, grz_last_time='.$currTime.' WHERE us_id='.$usId.' AND grz_id='.$id.' AND grz_isdel=0';
            $Db->getDataNum($sql);
            Tips::show('删除成功!', Link::getLink('gtb').'?A=gtb-financing');
        }
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND grz_isdel=0';
        $save       = From::valTrim('save');
        if($save == 1) {
            $Page -> setQuery('save', $save);
            $grz_project = From::valTrim('grz_project');
            if(strlen($grz_project) > 0){
                $whereString .= ($whereString==''?'':' AND ').'grz_project LIKE \'%'.$grz_project.'%\'';
                $Page -> setQuery('grz_project', $grz_project);
                $Tpl -> assign('grz_project', $grz_project);
            }
            $startTime = From::valTrim('start_time');
            if(strlen($startTime) > 0){
                $whereString .= ($whereString==''?'':' AND ').'grz_first_time>='.max(intval(strtotime($startTime.' 00:00:00')), 0);
                $Page -> setQuery('start_time', $startTime);
                $Tpl -> assign('start_time', $startTime);
            }
            $endTime = From::valTrim('end_time');
            if(strlen($endTime) > 0){
                $whereString .= ($whereString==''?'':' AND ').'grz_first_time<='.max(intval(strtotime($endTime.' 23:59:59')), 0);
                $Page -> setQuery('end_time', $endTime);
                $Tpl -> assign('end_time', $endTime);
            }
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gtb_rongzi').' WHERE '.$whereString.' ORDER BY grz_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(Link::getLink('gtb').'?A=gtb-financing');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/gtb_financing.html');
    }

    /**
     * @name financingrecord
     * @desciption 融资约谈管理
     */
    public function financingrecord(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND rr_isdel=0';
        $save       = From::valTrim('save');
        if($save == 1) {
            $Page -> setQuery('save', $save);
            $grz_name = From::valTrim('grz_name');
            if(strlen($grz_name) > 0){
                $whereString .= ($whereString==''?'':' AND ').'rr_company LIKE \'%'.$grz_name.'%\'';
                $Page -> setQuery('grz_name', $grz_name);
                $Tpl -> assign('grz_name', $grz_name);
            }
            $startTime = From::valTrim('start_time');
            if(strlen($startTime) > 0){
                $whereString .= ($whereString==''?'':' AND ').'rr_first_time>='.max(intval(strtotime($startTime.' 00:00:00')), 0);
                $Page -> setQuery('start_time', $startTime);
                $Tpl -> assign('start_time', $startTime);
            }
            $endTime = From::valTrim('end_time');
            if(strlen($endTime) > 0){
                $whereString .= ($whereString==''?'':' AND ').'rr_first_time<='.max(intval(strtotime($endTime.' 23:59:59')), 0);
                $Page -> setQuery('end_time', $endTime);
                $Tpl -> assign('end_time', $endTime);
            }
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gtb_rongzi_record').' WHERE '.$whereString.' ORDER BY rr_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(Link::getLink('gtb').'?A=gtb-financingrecord');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/gtb_financing_record.html');
    }

    /**
     * @name investmentRelease
     * @desciption 投资管理发布
     */
    public function investmentRelease(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $save = From::valInt('save');
        if($save == 1){
            $data       = [];
            $data['us_id']                 = $usId;
            $data['gtz_isdel']              = 0;
            $data['gtz_first_time']         = $currTime;
            $data['gtz_last_time']          = $currTime;
            $data['gtz_project']            = From::valTrim('gtz_project');
            $data['gtz_desc']               = From::valTrim('gtz_desc');
            $data['gtz_hangye']             = From::valTrim('gtz_hangye');
            $data['gtz_fangshi']            = From::valTrim('gtz_fangshi');
            $data['gtz_area_sheng']         = From::valTrim('gtz_area_sheng');
            $data['gtz_area_shi']           = From::valTrim('gtz_area_shi');
            $data['gtz_area_xian']          = From::valTrim('gtz_area_xian');
            $data['gtz_money']              = From::valTrim('gtz_money');
            $data['gtz_phone']              = From::valTrim('gtz_phone');
            $data['gtz_type']               = From::valTrim('gtz_type');
            $data['gtz_like']               = From::valTrim('gtz_like');
            $data['gtz_jieduan']            = From::valTrim('gtz_jieduan');
            $data['gtz_name']               = From::valTrim('gtz_name');
            $data['gtz_concat']             = From::valTrim('gtz_concat');
            /**
            $data['gtz_file']               = '';
            $allowType = ['7z', 'zip', 'rar', 'doc', 'docx', 'ppt', 'xls', 'xlsx'];
            if(isset($_FILES['gtz_file']) && strlen($_FILES['gtz_file']['name']) > 0){
            $val = trim($_FILES['td_file']['name']);
            $annx = strtolower(substr($val, strrpos($val, '.')+1));
            if(!in_array($annx, $allowType)) Tips::show('失败，请修改投资信息后提交！', 'javascript: history.back();');
            $tmpName = $_FILES['td_file']['tmp_name'];
            $newName = 'Static/data/tender/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
            $descImg = Load::getUrlRoot().$newName;
            @mkdir(dirname($descImg), 0777);
            if(move_uploaded_file($tmpName, $descImg)){
            $data['gtz_file']        = $newName;
            }
            }
             */
            $result = Sql::tag('gtb_touzi', 'GMY') -> addById($data);
            if($result > 0){
                Tips::show('发表投资信息成功！', Link::getLink('gtb').'?A=gtb-investment');
            }else{
                Tips::show('发表投资信息失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->show('User/gtb_investmentfb.html');
    }

    /**
     * @name investment
     * @desciption 投资管理
     */
    public function investment(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        $op = From::valTrim('op');
        $id = From::valInt('id');
        if($op == 'edit'){  //编辑
            $save = From::valInt('save');
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gtb_touzi').' WHERE us_id='.$usId.' AND gtz_id='.$id.' AND gtz_isdel=0';
            $info = $Db->getDataOne($sql);
            if(!isset($info['gtz_id'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            if($save == 1){
                $data       = [];
                $data['gtz_isdel']              = 0;
                $data['gtz_first_time']         = $currTime;
                $data['gtz_last_time']          = $currTime;
                $data['gtz_project']            = From::valTrim('gtz_project');
                $data['gtz_desc']               = From::valTrim('gtz_desc');
                $data['gtz_hangye']             = From::valTrim('gtz_hangye');
                $data['gtz_fangshi']            = From::valTrim('gtz_fangshi');
                $data['gtz_area_sheng']         = From::valTrim('gtz_area_sheng');
                $data['gtz_area_shi']           = From::valTrim('gtz_area_shi');
                $data['gtz_area_xian']          = From::valTrim('gtz_area_xian');
                $data['gtz_money']              = From::valTrim('gtz_money');
                $data['gtz_phone']              = From::valTrim('gtz_phone');
                $data['gtz_type']               = From::valTrim('gtz_type');
                $data['gtz_like']               = From::valTrim('gtz_like');
                $data['gtz_jieduan']            = From::valTrim('gtz_jieduan');
                $data['gtz_name']               = From::valTrim('gtz_name');
                $data['gtz_concat']             = From::valTrim('gtz_concat');
                /**
                $allowType = ['7z', 'zip', 'rar', 'doc', 'docx', 'ppt', 'xls', 'xlsx'];
                if(isset($_FILES['gtz_file']) && strlen($_FILES['gtz_file']['name']) > 0){
                $data['gtz_file']               = '';
                $val = trim($_FILES['gtz_file']['name']);
                $annx = strtolower(substr($val, strrpos($val, '.')+1));
                if(!in_array($annx, $allowType)) Tips::show('失败，请修改标书文件后提交！', 'javascript: history.back();');
                $tmpName = $_FILES['gtz_file']['tmp_name'];
                $newName = 'Static/data/tender/'.date('Ymd').'/'.md5($tmpName.$currTime.mt_rand(10000, 99999)).'.'.$annx;
                $descImg = Load::getUrlRoot().$newName;
                @mkdir(dirname($descImg), 0777);
                if(move_uploaded_file($tmpName, $descImg)){
                $data['gtz_file']        = $newName;
                }
                }
                 */
                $result = Sql::tag('gtb_touzi', 'GMY') -> setBy($data, ['us_id' => $usId, 'gtz_id' => $id, 'gtz_isdel' => 0]);
                if($result > 0){
                    Tips::show('修改投资信息成功！', Link::getLink('gtb').'?A=gtb-investment');
                }else{
                    Tips::show('修改投资信息失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl -> assign('info', $info);
            $Tpl->show('User/gtb_investmentfb_edit.html');
        }else if($op == 'del'){  //删除
            $sql = 'UPDATE '.$Db -> getTableNameAll('gtb_touzi').' SET gtz_isdel=1, gtz_last_time='.$currTime.' WHERE us_id='.$usId.' AND gtz_id='.$id.' AND gtz_isdel=0';
            $Db->getDataNum($sql);
            Tips::show('删除成功!', Link::getLink('gtb').'?A=gtb-investment');
        }
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND gtz_isdel=0';
        $save       = From::valTrim('save');
        if($save == 1) {
            $Page -> setQuery('save', $save);
            $gtz_project = From::valTrim('gtz_project');
            if(strlen($gtz_project) > 0){
                $whereString .= ($whereString==''?'':' AND ').'gtz_project LIKE \'%'.$gtz_project.'%\'';
                $Page -> setQuery('gtz_project', $gtz_project);
                $Tpl -> assign('gtz_project', $gtz_project);
            }
            $startTime = From::valTrim('start_time');
            if(strlen($startTime) > 0){
                $whereString .= ($whereString==''?'':' AND ').'gtz_first_time>='.max(intval(strtotime($startTime.' 00:00:00')), 0);
                $Page -> setQuery('start_time', $startTime);
                $Tpl -> assign('start_time', $startTime);
            }
            $endTime = From::valTrim('end_time');
            if(strlen($endTime) > 0){
                $whereString .= ($whereString==''?'':' AND ').'gtz_first_time<='.max(intval(strtotime($endTime.' 23:59:59')), 0);
                $Page -> setQuery('end_time', $endTime);
                $Tpl -> assign('end_time', $endTime);
            }
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gtb_touzi').' WHERE '.$whereString.' ORDER BY gtz_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(Link::getLink('gtb').'?A=gtb-investment');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/gtb_investment.html');
    }

    /**
     * @name investmentrecord
     * @desciption 投资管理
     */
    public function investmentrecord(string $action){
        $Tpl = $this->getTpl();
        $currTime   = Time::getTimeStamp();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND tr_isdel=0';
        $save       = From::valTrim('save');
        if($save == 1) {
            $Page -> setQuery('save', $save);
            $gtz_name = From::valTrim('gtz_name');
            if(strlen($gtz_name) > 0){
                $whereString .= ($whereString==''?'':' AND ').'tr_company LIKE \'%'.$gtz_name.'%\'';
                $Page -> setQuery('gtz_name', $gtz_name);
                $Tpl -> assign('gtz_name', $gtz_name);
            }
            $startTime = From::valTrim('start_time');
            if(strlen($startTime) > 0){
                $whereString .= ($whereString==''?'':' AND ').'tr_first_time>='.max(intval(strtotime($startTime.' 00:00:00')), 0);
                $Page -> setQuery('start_time', $startTime);
                $Tpl -> assign('start_time', $startTime);
            }
            $endTime = From::valTrim('end_time');
            if(strlen($endTime) > 0){
                $whereString .= ($whereString==''?'':' AND ').'tr_first_time<='.max(intval(strtotime($endTime.' 23:59:59')), 0);
                $Page -> setQuery('end_time', $endTime);
                $Tpl -> assign('end_time', $endTime);
            }
        }
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('gtb_touzi_record').' WHERE '.$whereString.' ORDER BY tr_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $val['tr_desc'] = preg_replace("/<br[^>]*>/i", '', $val['tr_desc']);
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(Link::getLink('gtb').'?A=gtb-investmentrecord');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show('User/gtb_investment_record.html');
    }
}