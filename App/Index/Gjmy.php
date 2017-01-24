<?php
/**
 * @Copyright (C) 2016.
 * @Description Tools
 * @FileName Tools.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use App\Pub\Link;
use App\Pub\Tips;
use Libs\Comm\From;
use Libs\Comm\Time;
use \Libs\Frame\Action;
use \Libs\Frame\Conf;
use \Libs\Tag\Db;
use Libs\Tag\Page;
use Libs\Tag\Sql;

class Gjmy extends Action{
    private $userInfo  = [];
    //配置
    public function conf(){
        $this -> userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl = $this -> getTpl();
        $Tpl->assign('modelName', '国际贸易');
    }

    //国际贸易
    public function main(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
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
        //求购
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_data').' WHERE gd_isdel=0 AND gd_type=1 ORDER BY gd_last_time DESC, gd_id DESC LIMIT 3';
        $qgList = $Db->getData($sql);
        $qglistArray = array();
        foreach ($qgList as $key =>$item){
                $showtime = (int)$item['gd_last_time'];
                $time = date("Y-m-d",$showtime);
                $now = date("Y-m-d",time());
                if($time == $now){
                    $item['time'] = date("H:i:s",$showtime);
                }else{
                    $item['time'] = date("Y-m-d",$showtime);
                }
            $qglistArray[] = $item;
        }
        $qgList = $qglistArray;
        $sql = 'SELECT COUNT(*) as num FROM '.$Db->getTableNameAll('gjmy_data').' WHERE gd_isdel=0 AND gd_type=1';
        $qgNum = $Db->getDataInt($sql, 'num');
        //供应
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_data').' WHERE gd_isdel=0 AND gd_type=2 ORDER BY gd_last_time DESC, gd_id DESC LIMIT 3';
        $gyList = $Db->getData($sql);
        $qglistArray = array();
        foreach ($gyList as $key =>$item){
            $showtime = (int)$item['gd_last_time'];
            $time = date("Y-m-d",$showtime);
            $now = date("Y-m-d",time());
            if($time == $now){
                $item['time'] = date("H:i:s",$showtime);
            }else{
                $item['time'] = date("Y-m-d",$showtime);
            }
            $qglistArray[] = $item;
        }
        $gyList = $qglistArray;
        $sql = 'SELECT COUNT(*) as num FROM '.$Db->getTableNameAll('gjmy_data').' WHERE gd_isdel=0 AND gd_type=2';
        $gyNum = $Db->getDataInt($sql, 'num');

        //国际贸易板块首页轮播图
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 11';
        $topslide = $Db -> getDataOne($sql);

        $sql    = 'SELECT  distinct a.ar_id,a.au_id,a.ar_title,a.ar_keywords,a.ar_description,a.ar_hits,a.ar_heart,a.ar_comments,a.ar_source,a.ar_status,a.ar_order,a.ar_thumb_img,a.ar_first_time,a.ar_last_time FROM '.$Db -> getTableNameAll('article').'as  a LEFT JOIN  '.$Db -> getTableNameAll('article_category_relation').' b ON a.ar_id=b.ar_id WHERE a.ar_iscommend=0 and a.ar_isdel=0 AND a.ar_status=1 AND b.ac_id in (43) ORDER BY a.ar_order ASC, a.ar_id DESC LIMIT 0, 3';
        $articleList = $Db -> getData($sql);
        $Tpl -> assign('articleList', $articleList);
        $slide = @json_decode($topslide['image'], TRUE);
        $imageArray = array();
        if(is_array($slide) && count($slide) > 0) foreach ($slide as $item){
            $array = array();
            if(is_array($item)){
                $array['image'] = $item['image'] ?? '';
                $array['imageurl'] = $item['imageurl'] ?? '';
            }else{
                $array['image'] = $item;
                $array['imageurl'] = $item;
            }
            $imageArray[] = $array;
        }
        $Tpl -> assign('slide', $imageArray);
        $Tpl -> assign('topslide', $topslide);

        $Tpl -> assign('qgList', $qgList);
        $Tpl -> assign('qgNum', $qgNum);
        $Tpl -> assign('gyList', $gyList);
        $Tpl -> assign('gyNum', $gyNum);
        $Tpl -> show('Gjmy/index.html');
    }

    //供应
    public function gongying(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
        $listClass = $Db->getData($sql);
        $Tpl -> assign('listClass', $listClass);
        $baseUrl = '/guojimaoyi/gongying.php?filter=1';
        $whereString = 'gd_isdel=0 AND gd_type=2';
        $gcId = From::valInt('gcid');
        if($gcId > 0){
            $whereString .= ' AND gc_id=\''.$gcId.'\'';
            $baseUrl .= '&gcid='.$gcId;
        }
        $baseUrlsf = $baseUrlpp = $baseUrlxh = $baseUrljg = $baseUrl;
        $shengfen = From::valTrim('shengfen');
        if(strlen($shengfen) > 0){
            $Tpl -> assign('shengfen', $shengfen);
            $baseUrlpp .= (strpos($baseUrlpp, '?') ? '&':'?').'shengfen='.$shengfen;
            $baseUrlxh .= (strpos($baseUrlxh, '?') ? '&':'?').'shengfen='.$shengfen;
            $baseUrljg .= (strpos($baseUrljg, '?') ? '&':'?').'shengfen='.$shengfen;
            $usIdList = [];
            $sql = 'SELECT us_id FROM '.$Db -> getTableNameAll('enterprise').' WHERE ent_addr_sheng=\''.$shengfen.'\'';
            $usList = $Db->getData($sql);
            if(count($usList) > 0) foreach ($usList as $val){
                $usIdList[] = intval($val['us_id']);
            }
            if(count($usIdList) > 0){
                $usIdList = array_unique($usIdList);
                $whereString .= ' AND us_id IN ('.implode(',', $usIdList).')';
            }else{
                $whereString .= ' AND 1=0'; //必须查询不到
            }
        }
        $pingpai = From::valTrim('pingpai');
        if(strlen($pingpai) > 0){
            $Tpl -> assign('pingpai', $pingpai);
            $baseUrlsf .= (strpos($baseUrlsf, '?') ? '&':'?').'pingpai='.$pingpai;
            $baseUrlxh .= (strpos($baseUrlxh, '?') ? '&':'?').'pingpai='.$pingpai;
            $baseUrljg .= (strpos($baseUrljg, '?') ? '&':'?').'pingpai='.$pingpai;
            $whereString .= ' AND gd_pingpai=\''.$pingpai.'\'';
        }
        $xinghao = From::valTrim('xinghao');
        if(strlen($xinghao) > 0){
            $Tpl -> assign('xinghao', $xinghao);
            $baseUrlsf .= (strpos($baseUrlsf, '?') ? '&':'?').'xinghao='.$xinghao;
            $baseUrlpp .= (strpos($baseUrlpp, '?') ? '&':'?').'xinghao='.$xinghao;
            $baseUrljg .= (strpos($baseUrljg, '?') ? '&':'?').'xinghao='.$xinghao;
            $whereString .= ' AND gd_xinghao=\''.$xinghao.'\'';
        }
        $isjiagong = From::valTrim('isjiagong');
        if(strlen($isjiagong) > 0){
            $Tpl -> assign('isjiagong', $isjiagong);
            $baseUrlsf .= (strpos($baseUrlsf, '?') ? '&':'?').'isjiagong='.$isjiagong;
            $baseUrlpp .= (strpos($baseUrlpp, '?') ? '&':'?').'isjiagong='.$isjiagong;
            $baseUrlxh .= (strpos($baseUrlxh, '?') ? '&':'?').'isjiagong='.$isjiagong;
            $whereString .= ' AND gd_isjiagong=\''.$isjiagong.'\'';
        }
        $Tpl -> assign('baseUrlsf', $baseUrlsf);
        $Tpl -> assign('baseUrlpp', $baseUrlpp);
        $Tpl -> assign('baseUrlxh', $baseUrlxh);
        $Tpl -> assign('baseUrljg', $baseUrljg);
        $usId  = intval($this -> userInfo['id']);
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 20);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
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
                $viewUsId = intval($val['us_id']);
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
                $info = $Db->getDataOne($sql);
                $val = array_merge($val, $info);
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$viewUsId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
                $info = $Db->getDataOne($sql);
                $val = array_merge($val, $info);
                $val['url'] = (new Yunjia()) -> getShopUrl($viewUsId);
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage($baseUrl);
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        //产品推荐
        $tjinfoList = [];
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_isdel=0 AND gd_type=2 ORDER BY gd_isrecommend DESc, gd_id DESC LIMIT 4';
        $infoList = $Db->getData($sql);
        if(count($infoList) > 0) foreach ($infoList as $info){
            if(!isset($info['gd_img'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
            $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
            $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
            $tjinfoList[] = $info;
        }
        $Tpl -> assign('tjinfoList', $tjinfoList);
        $currUrl = $pageList['baseUrl'];
        $Tpl -> assign('currUrl', $currUrl);
        //省份
        $list = $Db->getData('SELECT ca_name as name FROM '.$Db -> getTableNameAll('data_area').' WHERE ca_code like \'1001___0000\'');
        $Tpl -> assign('shengfenlist', $list);
        //品牌
        $list = $Db->getData('SELECT gd_pingpai as name, COUNT(gd_id) as num FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_type=2 AND gd_isdel=0 AND gd_pingpai!=\'\' GROUP BY gd_pingpai ORDER BY num DESC LIMIT 30');
        $Tpl -> assign('pingpailist', $list);
        //型号
        $list = $Db->getData('SELECT gd_xinghao as name, COUNT(gd_id) as num FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_type=2 AND gd_isdel=0 AND gd_xinghao!=\'\' GROUP BY gd_xinghao ORDER BY num DESC LIMIT 30');
        $Tpl -> assign('xinghaolist', $list);

        $Tpl -> show('Gjmy/lists.html');
    }

    //求购
    public function qiugou(string $action)
    {
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM ' . $Db->getTableNameAll('gjmy_class') . ' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
        $listClass = $Db->getData($sql);
        $Tpl->assign('listClass', $listClass);
        $usId = intval($this->userInfo['id']);
        $Page = Page::tag('ent', 'PLST');
        $Page->setParam('size', 20);
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page->getLimit();
        $whereString = 'gd_isdel=0 AND gd_type=1';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $Db->getTableNameAll('gjmy_data') . ' WHERE ' . $whereString . ' ORDER BY gd_last_time DESC LIMIT ' . $limit[0] . ', ' . $limit[1];
        $dataArray = $Db->getData($sql);
        $totalNum = $Db->getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page->setParam('totalNum', $totalNum);
        $dataList = [];
        if (isset($dataArray[0]) && is_array($dataArray[0])) {
            foreach ($dataArray as $key => $val) {
                $val['gd_imgs'] = strlen($val['gd_img']) > 0 ? @json_decode($val['gd_img'], TRUE) : [];
                $val['gd_attrs'] = strlen($val['gd_attr']) > 0 ? @json_decode($val['gd_attr'], TRUE) : [];
                $val['gd_price'] = sprintf("%.2f", $val['gd_price']);
                $dataList[] = $val;
            }
        }
        $pageList = $Page->getPage(Link::getLink('intertrad') . '?A=intertrad-managepurcha');
        $Tpl->assign('pageList', $pageList);
        $Tpl->assign('dataList', $dataList);
        //产品推荐
        $infoList = [];
        $sql = 'SELECT * FROM ' . $Db->getTableNameAll('gjmy_data') . ' WHERE gd_isdel=0 AND gd_type=1 ORDER BY gd_isrecommend DESc, gd_id DESC LIMIT 4';
        $infoLists = $Db->getData($sql);
        if(count($infoLists) > 0) foreach ($infoLists as $info){
            if (!isset($info['gd_img'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
            $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
            $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
            $infoList[] = $info;
        }
        $Tpl -> assign('infoList', $infoList);
        $Tpl -> show('Gjmy/qiugou.html');
    }

    //列表筛选
    public function lists(string $action){
        $this -> gongying($action);
    }

    //详细
    public function view(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
        $listClass = $Db->getData($sql);
        $Tpl -> assign('listClass', $listClass);
        $id = From::valInt('id');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id='.$id.' AND gd_isdel=0';
        $info = $Db->getDataOne($sql);
        if(!isset($info['gd_img'])) Tips::show('数据错误！', 'javascript: history.back();');
        $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
        $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
        $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
        $Tpl -> assign('info', $info);
        $usId = intval($info['us_id']);
        $Tpl -> assign('uinfom', $this -> getBasic($usId));
        $Tpl -> assign('cinfom', $this -> getEnterprise($usId));
        $usId  = intval($this -> userInfo['id']);
        $Tpl -> assign('uinfo', $this -> getBasic($usId));
        $Tpl -> assign('cinfo', $this -> getEnterprise($usId));
        $viewUsId = intval($info['us_id'] ?? 0);
        $entInfo = [];
        if($viewUsId > 0){
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
            $info = $Db->getDataOne($sql);
            $entInfo = array_merge($entInfo, $info);
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_company').' WHERE us_id=\''.$viewUsId.'\' AND ec_isdel=0 ORDER BY ec_last_time DESC';
            $info = $Db->getDataOne($sql);
            $entInfo = array_merge($entInfo, $info);
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$viewUsId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
            $info = $Db->getDataOne($sql);
            $entInfo = array_merge($entInfo, $info);
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise_shouquan').' WHERE us_id=\''.$viewUsId.'\' AND ents_isdel=0 ORDER BY ents_last_time DESC';
            $info = $Db->getDataOne($sql);
            $entInfo = array_merge($entInfo, $info);
            $entInfo['url'] = (new Yunjia()) -> getShopUrl($viewUsId);
            $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($viewUsId));   //评分
        }
        $Tpl -> assign('entInfo', $entInfo);
        //点评记录
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_review').' WHERE er_gd_id=\''.$id.'\' AND er_isdel=0 ORDER BY er_first_time DESC LIMIT 100';
        $reviewList = $Db->getData($sql);
        $numAll = 0;
        $numOne = 0;
        $numTwo = 0;
        $numThree = 0;
        if(count($reviewList) > 0)foreach ($reviewList as $key => $val){
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
            $info = $Db->getDataOne($sql);
            $reviewList[$key]['userInfo'] = $info;
            $xingnum = intval(ceil($val['er_avg_scope']/2));
            $reviewList[$key]['xingNum'] = $xingnum;
            $reviewList[$key]['xingString'] = $this -> getXing($xingnum);
            $xingType = ($xingnum >= 4 ? 1 : ($xingnum==3?2:3));
            $reviewList[$key]['xingType'] = $xingType;
            ++$numAll;
            if($xingType == 1){
                ++$numOne;
            }elseif($xingType ==2){
                ++$numTwo;
            }else{
                ++$numThree;
            }
        }
        $Tpl -> assign('numAll', $numAll);
        $Tpl -> assign('numOne', $numOne);
        $Tpl -> assign('numTwo', $numTwo);
        $Tpl -> assign('numThree', $numThree);
        $Tpl -> assign('reviewList', $reviewList);
        $Tpl -> show('Gjmy/view.html');
    }

    //详细
    public function viewcg(string $action){
        $Tpl = $this -> getTpl();
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $save = From::valInt('save');
        if($save == 1){
            $usId  = intval($this -> userInfo['id']);
            if($usId < 1) Tips::show('请先登录后提交报价！', '/user/signin.php');
            $gd_id = intval(From::valInt('gd_id'));
            $gx_id = 0;
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id='.$gd_id.' AND gd_isdel=0';
            $info = $Db->getDataOne($sql);
            $musId  = intval($info['us_id']);
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
                Tips::show('报价成功！', '/guojimaoyi/viewcg.php?id='.$gd_id);
            }else{
                Tips::show('报价失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
        $listClass = $Db->getData($sql);
        $Tpl -> assign('listClass', $listClass);
        $id = From::valInt('id');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id='.$id.' AND gd_isdel=0';
        $info = $Db->getDataOne($sql);
        if(!isset($info['gd_img'])) Tips::show('数据错误！', 'javascript: history.back();');
        $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
        $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
        $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
        $diffTimeInt = max($info['gd_end_date'] - $currTime, 0);
        $info['day'] = $diffTimeInt < 1 ? 0 : ceil($diffTimeInt/86400);
        $Tpl -> assign('info', $info);
        $bj = From::valInt('bj');
        $Tpl -> assign('bj', $bj);
        $usId = intval($info['us_id']);
        $Tpl -> assign('uinfom', $this -> getBasic($usId));
        $Tpl -> assign('cinfom', $this -> getEnterprise($usId));
        $usId  = intval($this -> userInfo['id']);
        $Tpl -> assign('uinfo', $this -> getBasic($usId));
        $Tpl -> assign('cinfo', $this -> getEnterprise($usId));
        $viewUsId = intval($info['us_id'] ?? 0);
        $entInfo = [];
        if($viewUsId > 0){
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
            $info = $Db->getDataOne($sql);
            $entInfo = array_merge($entInfo, $info);
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_company').' WHERE us_id=\''.$viewUsId.'\' AND ec_isdel=0 ORDER BY ec_last_time DESC';
            $info = $Db->getDataOne($sql);
            $entInfo = array_merge($entInfo, $info);
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$viewUsId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
            $info = $Db->getDataOne($sql);
            $entInfo = array_merge($entInfo, $info);
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise_shouquan').' WHERE us_id=\''.$viewUsId.'\' AND ents_isdel=0 ORDER BY ents_last_time DESC';
            $info = $Db->getDataOne($sql);
            $entInfo = array_merge($entInfo, $info);
            $entInfo['url'] = (new Yunjia()) -> getShopUrl($viewUsId);
            $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($viewUsId));   //评分
        }
        $Tpl -> assign('entInfo', $entInfo);
        //点评记录
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_review').' WHERE er_gd_id=\''.$id.'\' AND er_isdel=0 ORDER BY er_first_time DESC LIMIT 100';
        $reviewList = $Db->getData($sql);
        $numAll = 0;
        $numOne = 0;
        $numTwo = 0;
        $numThree = 0;
        if(count($reviewList) > 0)foreach ($reviewList as $key => $val){
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
            $info = $Db->getDataOne($sql);
            $reviewList[$key]['userInfo'] = $info;
            $xingnum = intval(ceil($val['er_avg_scope']/2));
            $reviewList[$key]['xingNum'] = $xingnum;
            $reviewList[$key]['xingString'] = $this -> getXing($xingnum);
            $xingType = ($xingnum >= 4 ? 1 : ($xingnum==3?2:3));
            $reviewList[$key]['xingType'] = $xingType;
            ++$numAll;
            if($xingType == 1){
                ++$numOne;
            }elseif($xingType ==2){
                ++$numTwo;
            }else{
                ++$numThree;
            }
        }
        $Tpl -> assign('numAll', $numAll);
        $Tpl -> assign('numOne', $numOne);
        $Tpl -> assign('numTwo', $numTwo);
        $Tpl -> assign('numThree', $numThree);
        $Tpl -> assign('reviewList', $reviewList);
        //其他求购信息列表
        $whereString = 'gd_isdel=0 AND gd_type=1';
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE '.$whereString.' ORDER BY gd_last_time DESC LIMIT 5';
        $dataArray = $Db -> getData($sql);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $val['gd_imgs'] = strlen($val['gd_img']) > 0 ? @json_decode($val['gd_img'], TRUE) : [];
                $val['gd_attrs'] = strlen($val['gd_attr']) > 0 ? @json_decode($val['gd_attr'], TRUE) : [];
                $val['gd_price'] = sprintf("%.2f", $val['gd_price']);
                $viewUsId = intval($val['us_id']);
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
                $info = $Db->getDataOne($sql);
                $dataList[] = array_merge($val, $info);
            }
        }
        $Tpl -> assign('dataList', $dataList);
        //产品推荐
        $tjinfoList = [];
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_isdel=0 AND gd_type=1 ORDER BY gd_isrecommend DESc, gd_id DESC LIMIT 4';
        $infoList = $Db->getData($sql);
        if(count($infoList) > 0) foreach ($infoList as $info){
            if(!isset($info['gd_img'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
            $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
            $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
            $tjinfoList[] = $info;
        }
        $Tpl -> assign('tjinfoList', $tjinfoList);
        $Tpl -> show('Gjmy/qiugou_more.html');
    }

    public function getXing(int $num):string{
        $strings = '';
        if($num >= 1){
            $strings .= '<i class="iconfont icon-star-solid text-danger"></i>';
        }else{
            $strings .= '<i class="iconfont icon-star-solid text-gray"></i>';
        }
        if($num >= 2){
            $strings .= '<i class="iconfont icon-star-solid text-danger"></i>';
        }else{
            $strings .= '<i class="iconfont icon-star-solid text-gray"></i>';
        }
        if($num >= 3){
            $strings .= '<i class="iconfont icon-star-solid text-danger"></i>';
        }else{
            $strings .= '<i class="iconfont icon-star-solid text-gray"></i>';
        }
        if($num >= 4){
            $strings .= '<i class="iconfont icon-star-solid text-danger"></i>';
        }else{
            $strings .= '<i class="iconfont icon-star-solid text-gray"></i>';
        }
        if($num >= 5){
            $strings .= '<i class="iconfont icon-star-solid text-danger"></i>';
        }else{
            $strings .= '<i class="iconfont icon-star-solid text-gray"></i>';
        }
        return $strings;
    }

    //购买
    public function buy(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
        $listClass = $Db->getData($sql);
        $Tpl -> assign('listClass', $listClass);
        $id = From::valInt('id');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id='.$id.' AND gd_isdel=0';
        $info = $Db->getDataOne($sql);
        if(!isset($info['gd_img'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
        $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
        $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
        $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
        $Tpl -> assign('info', $info);
        $usId = intval($info['us_id']);
        $Tpl -> assign('uinfom', $this -> getBasic($usId));
        $Tpl -> assign('cinfom', $this -> getEnterprise($usId));
        $usId  = intval($this -> userInfo['id']);
        if($usId < 1){
            Tips::show('请先登录再购买！', '/user/signin.php');
        }
        $Tpl -> assign('uinfo', $this -> getBasic($usId));
        $Tpl -> assign('cinfo', $this -> getEnterprise($usId));
        $Tpl -> show('Gjmy/buy.html');
    }

    //购买支付
    public function buypay(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('gjmy_class').' WHERE gc_isdel=0 AND gc_pid=0 ORDER BY gc_sort ASC, gc_id ASC';
        $listClass = $Db->getData($sql);
        $Tpl -> assign('listClass', $listClass);
        $id = From::valInt('id');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('gjmy_data').' WHERE gd_id='.$id.' AND gd_isdel=0';
        $info = $Db->getDataOne($sql);
        if(!isset($info['gd_img'])) Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
        $info['gd_imgs'] = strlen($info['gd_img']) > 0 ? @json_decode($info['gd_img'], TRUE) : [];
        $info['gd_attrs'] = strlen($info['gd_attr']) > 0 ? @json_decode($info['gd_attr'], TRUE) : [];
        $info['gd_price'] = sprintf("%.2f", $info['gd_price']);
        $Tpl -> assign('info', $info);
        $usIdm = $usId = intval($info['us_id']);
        $Tpl -> assign('uinfom', $this -> getBasic($usId));
        $Tpl -> assign('cinfom', $this -> getEnterprise($usId));
        $usId  = intval($this -> userInfo['id']);
        $Tpl -> assign('uinfo', $this -> getBasic($usId));
        $Tpl -> assign('cinfo', $this -> getEnterprise($usId));
        $save = From::valInt('save');
        if($save == 1){
            $goNum = From::valInt('go_num');
            $goFare = floatval(From::valTrim('go_fare'));
            $goText = From::valTrim('go_text');
            $price = floatval($info['gd_price']);
            $currTime = Time::getTimeStamp();
            $data       = [];
            $data['us_id_in']              = $usId;
            $data['us_id_out']             = $usIdm;
            $data['gd_id']                 = $id;
            $data['go_isdel']              = 0;
            $data['go_frist_time']         = $currTime;
            $data['go_last_time']          = $currTime;
            $data['go_num']                = $goNum;
            $data['go_price']              = strval($price);
            $data['go_total']              = strval($goNum*$price+$goFare);
            $data['go_fare']               = strval($goFare);
            $data['go_offers']             = 0;
            $data['go_text']               = $goText;
            $data['go_remark']             = From::valTrim('remark');
            $time_year = From::valInt('time_year');
            $time_month = From::valInt('time_month');
            $time_day = From::valInt('time_day');
            if($time_year < 2016 || $time_year > 2116 || $time_month < 1 || $time_month > 12 || $time_day < 1 || $time_day > 31){
                $data['go_pay_time']          = $currTime+86400*30;
            }else{
                $time_set = intval(strtotime($time_year.'-'.$time_month.'-'.$time_day.' 23:59:59'));
                $time_set = $time_set < $currTime ? $currTime+86400*30 : $time_set;
                $data['go_pay_time']          = $time_set;
            }
            $data['go_result']              = 0;
            $result = Sql::tag('gjmy_order', 'GMY') -> addById($data);
            if($result > 0){
            }else{
                Tips::show('下单失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl -> assign('go_total', $data['go_total']);
        $Tpl -> assign('go_day', max(ceil(($data['go_pay_time'] - $currTime) / 86400), 1));
        $Tpl -> show('Gjmy/buypay.html');
    }

    /**
     * @name getBasic
     * @desciption 获取基本信息
     * @return array
     */
    public static function getBasic(int $usId):array{
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$usId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
        $seoInfo = $Db->getDataOne($sql);
        return $seoInfo;
    }

    /**
     * @name getEnterprise
     * @desciption 获取企业认证信息
     * @return array
     */
    public static function getEnterprise(int $usId):array{
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
        $seoInfo = $Db->getDataOne($sql);
        return $seoInfo;
    }
}