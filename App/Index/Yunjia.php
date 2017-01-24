<?php
/**
 * @Copyright (C) 2016.
 * @Description Yunjia
 * @FileName Yunjia.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Index;
use \App\Article\ArticleModel;
use \Libs\Comm\From;
use \Libs\Comm\Http;
use \Libs\Frame\Action;
use \Libs\Tag\Db;
use \Libs\Frame\Conf;
use \Libs\Tag\Page;
class Yunjia extends Action{
    //配置
    public function conf(){
        $Tpl = $this -> getTpl();
        $Tpl->assign('modelName', '运价中心');
    }

    //Main
    public function main(string $action){
        $Tpl = $this -> getTpl();
        //list
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $size = 6;

        $railway_zc = [];   //铁路(车皮)
        $sql = 'SELECT *,trz_id as id, trz_start as start, trz_end as end, trz_tujingquyu as tujingquyu, trz_paytype as paytype, trz_first_time as first_time, trz_valid_time as valid_time, trz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_zc').' WHERE  trz_isdel=0 ORDER BY trz_end_time DESC LIMIT '.$size;
        $railway_zc = $Db -> getData($sql);
        $Tpl -> assign('railway_zc', $railway_zc);

        $railway_jzx = [];   //铁路(集装箱)
        $sql = 'SELECT *,trj_id as id, trj_start as start, trj_end as end, trj_tujingquyu as tujingquyu, trj_paytype as paytype, trj_first_time as first_time, trj_valid_time as valid_time, trj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_jzx').' WHERE  trj_isdel=0 ORDER BY trj_end_time DESC LIMIT '.$size;
        $railway_jzx = $Db -> getData($sql);
        $Tpl -> assign('railway_jzx', $railway_jzx);

        $sea_zx = [];   //海运(整箱)
        $sql = 'SELECT *,tsz_id as id, tsz_start as start, tsz_end as end, tsz_first_time as first_time, tsz_valid_time as valid_time, tsz_ischeck as ischeck, tsz_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_zx').' WHERE  tsz_isdel=0 ORDER BY tsz_end_time DESC LIMIT '.$size;
        $sea_zx = $Db -> getData($sql);
        $Tpl -> assign('sea_zx', $sea_zx);

        $sea_px = [];   //海运(拼箱)
        $sql = 'SELECT *,tsp_id as id, tsp_start as start, tsp_end as end, tsp_first_time as first_time, tsp_valid_time as valid_time, tsp_ischeck as ischeck, tsp_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_px').' WHERE  tsp_isdel=0 ORDER BY tsp_end_time DESC LIMIT '.$size;
        $sea_px = $Db -> getData($sql);
        $Tpl -> assign('sea_px', $sea_px);

        $sea_sh = [];   //海运(散杂货)
        $sql = 'SELECT *,tsh_id as id, tsh_start as start, tsh_end as end, tsh_first_time as first_time, tsh_valid_time as valid_time, tsh_ischeck as ischeck, tsh_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_sh').' WHERE  tsh_isdel=0 ORDER BY tsh_end_time DESC LIMIT '.$size;
        $sea_sh = $Db -> getData($sql);
        $Tpl -> assign('sea_sh', $sea_sh);

        $air_gn = [];  //空运(国内)
        $sql = 'SELECT *,tan_id as id, tan_start as start, tan_end as end, tan_hangkong as hangkong, tan_paytype as paytype, tan_first_time as first_time, tan_valid_time as valid_time, tan_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gn').' WHERE  tan_isdel=0 ORDER BY tan_end_time DESC LIMIT '.$size;
        $air_gn = $Db -> getData($sql);
        $Tpl -> assign('air_gn', $air_gn);

        $air_gj = [];  //空运(国际)
        $sql = 'SELECT *,taj_id as id, taj_start as start, taj_end as end, taj_hangkong as hangkong, taj_paytype as paytype, taj_first_time as first_time, taj_valid_time as valid_time, taj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gj').' WHERE  taj_isdel=0 ORDER BY taj_end_time DESC LIMIT '.$size;
        $air_gj = $Db -> getData($sql);
        $Tpl -> assign('air_gj', $air_gj);

        $land_zx = [];   //公路(专线)
        $sql = 'SELECT *,tlz_id as id, tlz_start as start, tlz_end as end, tlz_paytype as paytype, tlz_first_time as first_time, tlz_valid_time as valid_time, tlz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_zx').' WHERE  tlz_isdel=0 ORDER BY tlz_end_time DESC LIMIT '.$size;
        $land_zx = $Db -> getData($sql);
        $Tpl -> assign('land_zx', $land_zx);

        $land_ld = [];   //公路(零担)
        $sql = 'SELECT *,tld_id as id, tld_start as start, tld_end as end, tld_paytype as paytype, tld_first_time as first_time, tld_valid_time as valid_time, tld_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_ld').' WHERE  tld_isdel=0 ORDER BY tld_end_time DESC LIMIT '.$size;
        $land_ld = $Db -> getData($sql);
        $Tpl -> assign('land_ld', $land_ld);

        $land_jktc = [];   //公路(集卡拖车)
        $sql = 'SELECT *,tlj_id as id, tlj_start as start, tlj_end as end, tlj_paytype as paytype, tlj_first_time as first_time, tlj_valid_time as valid_time, tlj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_jktc').' WHERE  tlj_isdel=0 ORDER BY tlj_end_time DESC LIMIT '.$size;
        $land_jktc = $Db -> getData($sql);
        $Tpl -> assign('land_jktc', $land_jktc);

        $storage = [];  //仓储
        $sql = 'SELECT *,tst_id as id, tst_type as type, tst_title as title, tst_suozaidi as suozaidi, tst_first_time as first_time, tst_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_storage').' WHERE  tst_isdel=0 ORDER BY tst_end_time DESC LIMIT '.$size;
        $storage = $Db -> getData($sql);
        $Tpl -> assign('storage', $storage);

        $detect = [];   //报关报检
        $sql = 'SELECT *,tdt_id as id, tdt_type as type, tdt_title as title, tdt_gangkou as gangkou, tdt_first_time as first_time, tdt_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_detect').' WHERE  tdt_isdel=0 ORDER BY tdt_end_time DESC LIMIT '.$size;
        $detect = $Db -> getData($sql);
        $Tpl -> assign('detect', $detect);

        $multi_dzs = []; //多式联运(大宗散货)
        $sql = 'SELECT *,tmz_id as id, tmz_start as start, tmz_end as end, tmz_first_time as first_time, tmz_valid_time as valid_time, tmz_ischeck as ischeck, tmz_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_zc').' WHERE  tmz_isdel=0 ORDER BY tmz_end_time DESC LIMIT '.$size;
        $multi_dzs = $Db -> getData($sql);
        $Tpl -> assign('multi_dzs', $multi_dzs);

        $multi_jzx = []; //多式联运(集装箱)
        $sql = 'SELECT *,tmj_id as id, tmj_start as start, tmj_end as end, tmj_first_time as first_time, tmj_valid_time as valid_time, tmj_ischeck as ischeck, tmj_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_jzx').' WHERE  tmj_isdel=0 ORDER BY tmj_end_time DESC LIMIT '.$size;
        $multi_jzx = $Db -> getData($sql);
        $Tpl -> assign('multi_jzx', $multi_jzx);
        //代理
        $sql = 'SELECT country,name,web FROM '.$Db -> getTableNameAll('daili').' LIMIT 5';
        $dailiList = $Db->getData($sql);
        $Tpl -> assign('dailiList', $dailiList);
        //班列信息
        $sql = 'SELECT id, originating, terminal FROM '.$Db -> getTableNameAll('trains_information').' WHERE 1 ORDER BY id DESC LIMIT 5';
        $trainsList = $Db->getData($sql);
        $Tpl -> assign('trainsList', $trainsList);
        //箱卡集市
        $sql = 'SELECT cb_id, cb_title, cb_market_price/100 AS cb_market_price FROM '.$Db -> getTableNameAll('container_box').' WHERE cb_is_del=0 AND cb_is_onsale=1 ORDER BY cb_ctime DESC LIMIT 5';
        $containerList = $Db->getData($sql);
        $Tpl -> assign('containerList', $containerList);

        //运价中心内页右侧图片
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 8';
        $rightbanner = $Db -> getDataOne($sql);
        $Tpl -> assign('rightbanner', $rightbanner);
        //国内资讯
        $ArticleModel = new ArticleModel();
        $articleList = $ArticleModel -> getArticleList(16, 5);    //获取国内咨询下的10条信息
        $Tpl -> assign('articleList', $articleList);
        $Tpl -> show('Yunjia/index.html');
    }

    //air
    public function air(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('air', $stype);
        $Tpl -> show('Yunjia/air.html');
    }

    //detect
    public function detect(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('detect', $stype);
        $Tpl -> show('Yunjia/detect.html');
    }

    //land
    public function land(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('land', $stype);
        $Tpl -> show('Yunjia/land.html');
    }

    //multi
    public function multi(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('multi', $stype);
        $Tpl -> show('Yunjia/multi.html');
    }

    //railway
    public function railway(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('railway', $stype);
        $Tpl -> show('Yunjia/railway.html');
    }

    //sea
    public function sea(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('sea', $stype);
        $Tpl -> show('Yunjia/sea.html');
    }

    //storage
    public function storage(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('storage', $stype);
        $Tpl -> show('Yunjia/storage.html');
    }


    /**
     * @name getListData
     * @desciption 列表
     */
    public function getListData(string $type, string $stype){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = '1';
        $Tpl -> assign('stype', $stype);
        $Page -> setQuery('stype', $stype);
        switch ($type){
            case 'railway':{    //铁路
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        if($stype == 'zc'){   //车皮
                            $whereString .= ($whereString == ''?'':' AND ').'trz_tujingquyu like \'%'.addslashes($name).'%\'';
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'trj_tujingquyu like \'%'.addslashes($name).'%\'';
                        }
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'zc'){   //车皮
                            $whereString .= ($whereString == ''?'':' AND ').'trz_start like \'%'.addslashes($start).'%\'';
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'trj_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'zc'){   //车皮
                            $whereString .= ($whereString == ''?'':' AND ').'trz_end like \'%'.addslashes($end).'%\'';
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'trj_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $startTime = From::valTrim('start_time');
                    if(strlen($startTime) > 0){
                        if($stype == 'zc'){   //车皮
                            $whereString .= ($whereString == ''?'':' AND ').'trz_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'trj_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }
                        $Page -> setQuery('start_time', $startTime);
                        $Tpl -> assign('start_time', $startTime);
                    }
                    $endTime = From::valTrim('end_time');
                    if(strlen($endTime) > 0){
                        if($stype == 'zc'){   //车皮
                            $whereString .= ($whereString == ''?'':' AND ').'trz_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'trj_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }
                        $Page -> setQuery('end_time', $endTime);
                        $Tpl -> assign('end_time', $endTime);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'zc'){   //车皮
                            $whereString .= ($whereString == ''?'':' AND ').'trz_ischeck like \''.addslashes($check).'\'';
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'trj_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if($stype == 'zc'){   //车皮
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND trz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,trz_id as id, trz_start as start, trz_end as end, trz_tujingquyu as tujingquyu, trz_paytype as paytype, trz_first_time as first_time, trz_valid_time as valid_time, trz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_zc').$whereString.' ORDER BY trz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //集装箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND trj_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,trj_id as id, trj_start as start, trj_end as end, trj_tujingquyu as tujingquyu, trj_paytype as paytype, trj_first_time as first_time, trj_valid_time as valid_time, trj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_jzx').$whereString.' ORDER BY trj_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                break;
            }
            case 'sea':{    //海运
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsp_start like \'%'.addslashes($start).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'tsh_start like \'%'.addslashes($start).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsz_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsp_end like \'%'.addslashes($end).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'tsh_end like \'%'.addslashes($end).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsz_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsp_ischeck like \''.addslashes($check).'\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'tsh_ischeck like \''.addslashes($check).'\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsz_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                    $startTime = From::valTrim('start_time');
                    if(strlen($startTime) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsp_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'tsh_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsz_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }
                        $Page -> setQuery('start_time', $startTime);
                        $Tpl -> assign('start_time', $startTime);
                    }
                    $endTime = From::valTrim('end_time');
                    if(strlen($endTime) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsp_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'tsh_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'tsz_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }
                        $Page -> setQuery('end_time', $endTime);
                        $Tpl -> assign('end_time', $endTime);
                    }
                }
                if($stype == 'px'){ //拼箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsp_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tsp_id as id, tsp_start as start, tsp_end as end, tsp_first_time as first_time, tsp_valid_time as valid_time, tsp_ischeck as ischeck, tsp_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_px').$whereString.' ORDER BY tsp_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else if($stype == 'sh'){   //散杂货
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsh_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tsh_id as id, tsh_start as start, tsh_end as end, tsh_first_time as first_time, tsh_valid_time as valid_time, tsh_ischeck as ischeck, tsh_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_sh').$whereString.' ORDER BY tsh_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //整箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tsz_id as id, tsz_start as start, tsz_end as end, tsz_first_time as first_time, tsz_valid_time as valid_time, tsz_ischeck as ischeck, tsz_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_zx').$whereString.' ORDER BY tsz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                break;
            }
            case 'air':{    //空运
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        if($stype == 'gn'){   //国内
                            $whereString .= ($whereString == ''?'':' AND ').'tan_hangkong like \'%'.addslashes($name).'%\'';
                        }else{  //国际
                            $whereString .= ($whereString == ''?'':' AND ').'taj_hangkong like \'%'.addslashes($name).'%\'';
                        }
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'gn'){   //国内
                            $whereString .= ($whereString == ''?'':' AND ').'tan_start like \'%'.addslashes($start).'%\'';
                        }else{  //国际
                            $whereString .= ($whereString == ''?'':' AND ').'taj_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'gn'){   //国内
                            $whereString .= ($whereString == ''?'':' AND ').'tan_end like \'%'.addslashes($end).'%\'';
                        }else{  //国际
                            $whereString .= ($whereString == ''?'':' AND ').'taj_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'gn'){   //国内
                            $whereString .= ($whereString == ''?'':' AND ').'tan_ischeck like \''.addslashes($check).'\'';
                        }else{  //国际
                            $whereString .= ($whereString == ''?'':' AND ').'taj_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                    $startTime = From::valTrim('start_time');
                    if(strlen($startTime) > 0){
                        if($stype == 'gn'){   //国内
                            $whereString .= ($whereString == ''?'':' AND ').'tan_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }else{  //国际
                            $whereString .= ($whereString == ''?'':' AND ').'taj_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }
                        $Page -> setQuery('start_time', $startTime);
                        $Tpl -> assign('start_time', $startTime);
                    }
                    $endTime = From::valTrim('end_time');
                    if(strlen($endTime) > 0){
                        if($stype == 'gn'){   //国内
                            $whereString .= ($whereString == ''?'':' AND ').'tan_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }else{  //国际
                            $whereString .= ($whereString == ''?'':' AND ').'taj_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }
                        $Page -> setQuery('end_time', $endTime);
                        $Tpl -> assign('end_time', $endTime);
                    }
                }
                if($stype == 'gn'){   //国内
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tan_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tan_id as id, tan_start as start, tan_end as end, tan_hangkong as hangkong, tan_paytype as paytype, tan_first_time as first_time, tan_valid_time as valid_time, tan_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gn').$whereString.' ORDER BY tan_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //国际
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND taj_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,taj_id as id, taj_start as start, taj_end as end, taj_hangkong as hangkong, taj_paytype as paytype, taj_first_time as first_time, taj_valid_time as valid_time, taj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gj').$whereString.' ORDER BY taj_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                break;
            }
            case 'land':{    //公路运输
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'zx'){ //专线
                            $whereString .= ($whereString == ''?'':' AND ').'tlz_start like \'%'.addslashes($start).'%\'';
                        }else if($stype == 'ld'){   //零担
                            $whereString .= ($whereString == ''?'':' AND ').'tld_start like \'%'.addslashes($start).'%\'';
                        }else{  //集卡拖车
                            $whereString .= ($whereString == ''?'':' AND ').'tlj_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'zx'){ //专线
                            $whereString .= ($whereString == ''?'':' AND ').'tlz_end like \'%'.addslashes($end).'%\'';
                        }else if($stype == 'ld'){   //零担
                            $whereString .= ($whereString == ''?'':' AND ').'tld_end like \'%'.addslashes($end).'%\'';
                        }else{  //集卡拖车
                            $whereString .= ($whereString == ''?'':' AND ').'tlj_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'zx'){ //专线
                            $whereString .= ($whereString == ''?'':' AND ').'tlz_ischeck like \''.addslashes($check).'\'';
                        }else if($stype == 'ld'){   //零担
                            $whereString .= ($whereString == ''?'':' AND ').'tld_ischeck like \''.addslashes($check).'\'';
                        }else{  //集卡拖车
                            $whereString .= ($whereString == ''?'':' AND ').'tlj_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                    $startTime = From::valTrim('start_time');
                    if(strlen($startTime) > 0){
                        if($stype == 'zx'){ //专线
                            $whereString .= ($whereString == ''?'':' AND ').'tlz_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }else if($stype == 'ld'){   //零担
                            $whereString .= ($whereString == ''?'':' AND ').'tld_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }else{  //集卡拖车
                            $whereString .= ($whereString == ''?'':' AND ').'tlj_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }
                        $Page -> setQuery('start_time', $startTime);
                        $Tpl -> assign('start_time', $startTime);
                    }
                    $endTime = From::valTrim('end_time');
                    if(strlen($endTime) > 0){
                        if($stype == 'zx'){ //专线
                            $whereString .= ($whereString == ''?'':' AND ').'tlz_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }else if($stype == 'ld'){   //零担
                            $whereString .= ($whereString == ''?'':' AND ').'tld_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }else{  //集卡拖车
                            $whereString .= ($whereString == ''?'':' AND ').'tlj_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }
                        $Page -> setQuery('end_time', $endTime);
                        $Tpl -> assign('end_time', $endTime);
                    }
                }
                if($stype == 'zx'){ //专线
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tlz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tlz_id as id, tlz_start as start, tlz_end as end, tlz_paytype as paytype, tlz_first_time as first_time, tlz_valid_time as valid_time, tlz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_zx').$whereString.' ORDER BY tlz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else if($stype == 'ld'){   //零担
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tld_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tld_id as id, tld_start as start, tld_end as end, tld_paytype as paytype, tld_first_time as first_time, tld_valid_time as valid_time, tld_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_ld').$whereString.' ORDER BY tld_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //集卡拖车
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tlj_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tlj_id as id, tlj_start as start, tlj_end as end, tlj_paytype as paytype, tlj_first_time as first_time, tlj_valid_time as valid_time, tlj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_jktc').$whereString.' ORDER BY tlj_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                break;
            }
            case 'storage':{    //仓储
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'tst_title like \'%'.addslashes($name).'%\'';
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'tst_suozaidi like \'%'.addslashes($start).'%\'';
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'tst_ischeck like \''.addslashes($check).'\'';
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tst_isdel=0';
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tst_id as id, tst_type as type, tst_title as title, tst_suozaidi as suozaidi, tst_first_time as first_time, tst_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_storage').$whereString.' ORDER BY tst_id DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'detect':{    //报关报检
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'tdt_title like \'%'.addslashes($name).'%\'';
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'tdt_gangkou like \'%'.addslashes($start).'%\'';
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'tdt_ischeck like \''.addslashes($check).'\'';
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tdt_isdel=0';
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tdt_id as id, tdt_type as type, tdt_title as title, tdt_gangkou as gangkou, tdt_first_time as first_time, tdt_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_detect').$whereString.' ORDER BY tdt_id DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            default:{   //multi-多式联运
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'dzs'){   //大宗散货
                            $whereString .= ($whereString == ''?'':' AND ').'tmz_start like \'%'.addslashes($start).'%\'';
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'tmj_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'dzs'){   //大宗散货
                            $whereString .= ($whereString == ''?'':' AND ').'tmz_end like \'%'.addslashes($end).'%\'';
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'tmj_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'dzs'){   //大宗散货
                            $whereString .= ($whereString == ''?'':' AND ').'tmz_ischeck like \''.addslashes($check).'\'';
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'tmj_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'dzs'){   //大宗散货
                            $whereString .= ($whereString == ''?'':' AND ').'tmz_start like \'%'.addslashes($start).'%\'';
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'tmj_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $startTime = From::valTrim('start_time');
                    if(strlen($startTime) > 0){
                        if($stype == 'dzs'){   //大宗散货
                            $whereString .= ($whereString == ''?'':' AND ').'tmz_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'tmj_first_time>='.intval(strtotime($startTime.' 00:00:00'));
                        }
                        $Page -> setQuery('start_time', $startTime);
                        $Tpl -> assign('start_time', $startTime);
                    }
                    $endTime = From::valTrim('end_time');
                    if(strlen($endTime) > 0){
                        if($stype == 'dzs'){   //大宗散货
                            $whereString .= ($whereString == ''?'':' AND ').'tmz_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }else{  //集装箱
                            $whereString .= ($whereString == ''?'':' AND ').'tmj_first_time<='.intval(strtotime($endTime.' 23:59:59'));
                        }
                        $Page -> setQuery('end_time', $endTime);
                        $Tpl -> assign('end_time', $endTime);
                    }
                }
                if($stype == 'dzs'){   //大宗散货
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tmz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tmz_id as id, tmz_start as start, tmz_end as end, tmz_first_time as first_time, tmz_valid_time as valid_time, tmz_ischeck as ischeck, tmz_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_zc').$whereString.' ORDER BY tmz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //集装箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tmj_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tmj_id as id, tmj_start as start, tmj_end as end, tmj_first_time as first_time, tmj_valid_time as valid_time, tmj_ischeck as ischeck, tmj_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_jzx').$whereString.' ORDER BY tmj_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                break;
            }
        }
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            $dataList = $dataArray;
        }
        $pageList = $Page -> getPage(Http::getHttpSelf());
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        //代理
        $sql = 'SELECT id, country, name FROM '.$Db -> getTableNameAll('daili').' LIMIT 5';
        $dailiList = $Db->getData($sql);
        $Tpl -> assign('dailiList', $dailiList);
        //班列信息
        $sql = 'SELECT id, originating, terminal FROM '.$Db -> getTableNameAll('trains_information').' WHERE 1 ORDER BY id DESC LIMIT 5';
        $trainsList = $Db->getData($sql);
        $Tpl -> assign('trainsList', $trainsList);
        //箱卡集市
        $sql = 'SELECT cb_id, cb_title, cb_market_price/100 AS cb_market_price FROM '.$Db -> getTableNameAll('container_box').' WHERE cb_is_del=0 AND cb_is_onsale=1 ORDER BY cb_ctime DESC LIMIT 5';
        $containerList = $Db->getData($sql);
        $Tpl -> assign('containerList', $containerList);

        //运价中心内页右侧图片
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 8';
        $rightbanner = $Db -> getDataOne($sql);
        $Tpl -> assign('rightbanner', $rightbanner);
        //国内资讯
        $ArticleModel = new ArticleModel();
        $articleList = $ArticleModel -> getArticleList(16, 5);    //获取国内咨询下的10条信息
        $Tpl -> assign('articleList', $articleList);
    }

    //airview
    public function airview(string $action){
        $stype = From::valTrim('stype');    //子类
        $this -> getInfoData('air', $stype);
    }

    //detectview
    public function detectview(string $action){
        $stype = From::valTrim('stype');    //子类
        $this -> getInfoData('detect', $stype);
    }

    //landview
    public function landview(string $action){
        $stype = From::valTrim('stype');    //子类
        $this -> getInfoData('land', $stype);
    }

    //multiview
    public function multiview(string $action){
        $stype = From::valTrim('stype');    //子类
        $this -> getInfoData('multi', $stype);
    }

    //railwayview
    public function railwayview(string $action){
        $stype = From::valTrim('stype');    //子类
        $this -> getInfoData('railway', $stype);
    }

    //seaview
    public function seaview(string $action){
        $stype = From::valTrim('stype');    //子类
        $this -> getInfoData('sea', $stype);
    }

    //storageview
    public function storageview(string $action){
        $stype = From::valTrim('stype');    //子类
        $this -> getInfoData('storage', $stype);
    }

    /**
     * @name getInfoData
     * @desciption 详细
     */
    public function getInfoData(string $type, string $stype){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $whereString = '1';
        $Tpl -> assign('stype', $stype);
        $id = From::valInt('id');
        $Tpl -> assign('id', $id);
        switch ($type){
            case 'railway':{    //铁路
                if($stype == 'zc'){   //车皮
                    $dataType = '车皮';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_railway_zc.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND trz_isdel=0 AND trz_id='.$id;
                    $sql = 'SELECT *,trz_id as id, trz_start as start, trz_end as end, trz_tujingquyu as tujingquyu, trz_paytype as paytype, trz_first_time as first_time, trz_valid_time as valid_time, trz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_zc').$whereString;
                }else{  //集装箱
                    $dataType = '集装箱';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_railway_jzx.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND trj_isdel=0 AND trj_id='.$id;
                    $sql = 'SELECT *,trj_id as id, trj_start as start, trj_end as end, trj_tujingquyu as tujingquyu, trj_paytype as paytype, trj_first_time as first_time, trj_valid_time as valid_time, trj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_jzx').$whereString;
                }
                break;
            }
            case 'sea':{    //海运
                if($stype == 'px'){ //拼箱
                    $dataType = '拼箱';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_sea_px.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsp_isdel=0 AND tsp_id='.$id;
                    $sql = 'SELECT *,tsp_id as id, tsp_start as start, tsp_end as end, tsp_first_time as first_time, tsp_valid_time as valid_time, tsp_ischeck as ischeck, tsp_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_px').$whereString;
                }else if($stype == 'sh'){   //散杂货
                    $dataType = '散杂货';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_sea_ph.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsh_isdel=0 AND tsh_id='.$id;
                    $sql = 'SELECT *,tsh_id as id, tsh_start as start, tsh_end as end, tsh_first_time as first_time, tsh_valid_time as valid_time, tsh_ischeck as ischeck, tsh_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_sh').$whereString;
                }else{  //整箱
                    $dataType = '整箱';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_sea_zx.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsz_isdel=0 AND tsz_id='.$id;
                    $sql = 'SELECT *,tsz_id as id, tsz_start as start, tsz_end as end, tsz_first_time as first_time, tsz_valid_time as valid_time, tsz_ischeck as ischeck, tsz_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_zx').$whereString;
                }
                break;
            }
            case 'air':{    //空运
                if($stype == 'gn'){   //国内
                    $dataType = '国内';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_air_gn.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tan_isdel=0 AND tan_id='.$id;
                    $sql = 'SELECT *,tan_id as id, tan_start as start, tan_end as end, tan_hangkong as hangkong, tan_paytype as paytype, tan_first_time as first_time, tan_valid_time as valid_time, tan_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gn').$whereString;
                }else{  //国际
                    $dataType = '国际';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_air_gj.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND taj_isdel=0 AND taj_id='.$id;
                    $sql = 'SELECT *,taj_id as id, taj_start as start, taj_end as end, taj_hangkong as hangkong, taj_paytype as paytype, taj_first_time as first_time, taj_valid_time as valid_time, taj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gj').$whereString;
                }
                break;
            }
            case 'land':{    //公路运输
                if($stype == 'zx'){ //专线
                    $dataType = '专线';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_land_zx.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tlz_isdel=0 AND tlz_id='.$id;
                    $sql = 'SELECT *,tlz_id as id, tlz_start as start, tlz_end as end, tlz_paytype as paytype, tlz_first_time as first_time, tlz_valid_time as valid_time, tlz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_zx').$whereString;
                }else if($stype == 'ld'){   //零担
                    $dataType = '零担';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_land_ld.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tld_isdel=0 AND tld_id='.$id;
                    $sql = 'SELECT *,tld_id as id, tld_start as start, tld_end as end, tld_paytype as paytype, tld_first_time as first_time, tld_valid_time as valid_time, tld_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_ld').$whereString;
                }else{  //集卡拖车
                    $dataType = '集卡拖车';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_land_jktc.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tlj_isdel=0 AND tlj_id='.$id;
                    $sql = 'SELECT *,tlj_id as id, tlj_start as start, tlj_end as end, tlj_paytype as paytype, tlj_first_time as first_time, tlj_valid_time as valid_time, tlj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_jktc').$whereString;
                }
                break;
            }
            case 'storage':{    //仓储
                $dataType = '仓储';
                $Tpl -> assign('tplHtml', 'Yunjia/tariffs_storage.html');
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tst_isdel=0 AND tst_id='.$id;
                $sql = 'SELECT *,tst_id as id, tst_type as type, tst_title as title, tst_suozaidi as suozaidi, tst_first_time as first_time, tst_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_storage').$whereString;
                break;
            }
            case 'detect':{    //报关报检
                $dataType = '报关报检';
                $Tpl -> assign('tplHtml', 'Yunjia/tariffs_detect.html');
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tdt_isdel=0 AND tdt_id='.$id;
                $sql = 'SELECT *,tdt_id as id, tdt_type as type, tdt_title as title, tdt_gangkou as gangkou, tdt_first_time as first_time, tdt_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_detect').$whereString;
                break;
            }
            default:{   //multi-多式联运
                if($stype == 'dzs'){   //大宗散货
                    $dataType = '大宗散货';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_multi_zc.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tmz_isdel=0 AND tmz_id='.$id;
                    $sql = 'SELECT *,tmz_id as id, tmz_start as start, tmz_end as end, tmz_first_time as first_time, tmz_valid_time as valid_time, tmz_ischeck as ischeck, tmz_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_zc').$whereString;
                }else{  //集装箱
                    $dataType = '集装箱';
                    $Tpl -> assign('tplHtml', 'Yunjia/tariffs_multi_jzx.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tmj_isdel=0 AND tmj_id='.$id;
                    $sql = 'SELECT *,tmj_id as id, tmj_start as start, tmj_end as end, tmj_first_time as first_time, tmj_valid_time as valid_time, tmj_ischeck as ischeck, tmj_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_jzx').$whereString;
                }
                break;
            }
        }
        $dataInfo = $Db -> getDataOne($sql);
        $viewUsId = intval($dataInfo['us_id'] ?? 0);
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
            $entInfo['url'] = $this -> getShopUrl($viewUsId);
            $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($viewUsId));   //评分
        }
        $Tpl -> assign('dataType', $dataType);
        $Tpl -> assign('entInfo', $entInfo);
        $Tpl -> assign('dataInfo', $dataInfo);
        //代理
        $sql = 'SELECT id, country, name FROM '.$Db -> getTableNameAll('daili').' LIMIT 10';
        $dailiList = $Db->getData($sql);
        $Tpl -> assign('dailiList', $dailiList);
        if(isset($_SESSION['TOKEN']) && $_SESSION['TOKEN']['UID'] > 0){
            $Tpl -> show('Yunjia/viewsignin.html');
        }else{
            $Tpl -> show('Yunjia/viewsignout.html');
        }
    }

    /**
     * @name getShopUrl
     * @desciption 获取商铺地址
     * @return string
     */
    public function getShopUrl(int $usId){
        $url = '';
        $Db = Db::tag('DB.USER', 'GMY');
        //开通商铺信息
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $esInfo = $Db->getDataOne($sql);
        $isEs = intval($esInfo['es_id'] ?? 0) > 0 ? 1 : 0;
        if($isEs == 0) return $url;
        $es_status = intval($esInfo['es_status']) == 1 ? 1 : 0; //是否已禁止[1-是,0-否]禁止将无法访问
        if($es_status == 1) return $url;
        $domain = trim($esInfo['es_domain']);
        $domain = preg_replace("/[^a-z\d\-]+/i", '', $domain);  //只允许字母数字和中线
        if(strlen($domain) < 1) return $url;
        if(Conf::get('Ent.isDomain') == 1){ //域名模式
            $pubDomain = Conf::get('Ent.domain');
            if(strlen($pubDomain) < 1) return $url;
            $isHttps = Conf::get('Ent.isHttps');
            $url = ($isHttps ? 'https://' : 'http://').$domain.'.'.ltrim(trim($pubDomain), '.');
        }else{
            $url = Http::getHttpDomain(TRUE).'ent/index.php?domain='.urlencode($domain);
        }
        return $url;
    }
}