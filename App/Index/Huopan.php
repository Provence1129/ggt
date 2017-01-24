<?php
/**
 * @Copyright (C) 2016.
 * @Description Huopan
 * @FileName Huopan.php
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
class Huopan extends Action{
    //配置
    public function conf(){
        $Tpl = $this -> getTpl();
        $Tpl->assign('modelName', '货盘中心');
    }

    //Main
    public function main(string $action){
        $Tpl = $this -> getTpl();
        //list
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $size = 6;
        $multi_px = []; //多式联运(拼箱)
        $sql = 'SELECT *,pmp_id as id, pmp_start as start, pmp_end as end, pmp_huowuzhongwen as huowuzhongwen, pmp_type as type, pmp_first_time as first_time, pmp_valid_time as valid_time, pmp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_px').' WHERE  pmp_isdel=0 ORDER BY pmp_end_time DESC LIMIT '.$size;
        $multi_px = $Db -> getData($sql);
        $Tpl -> assign('multi_px', $multi_px);

        $multi_sh = [];   //多式联运(散杂货)
        $sql = 'SELECT *,pms_id as id, pms_end as end, pms_huowuzhongwen as huowuzhongwen, pms_type as type, pms_first_time as first_time, pms_valid_time as valid_time, pms_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_sh').' WHERE  pms_isdel=0 ORDER BY pms_end_time DESC LIMIT '.$size;
        $multi_sh = $Db -> getData($sql);
        $Tpl -> assign('multi_sh', $multi_sh);

        $railway_zx = [];   //铁路(整箱)
        $sql = 'SELECT *,prz_id as id, prz_start as start, prz_end as end, prz_huowuzhongwen as huowuzhongwen, prz_type as type, prz_first_time as first_time, prz_valid_time as valid_time, prz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_zx').' WHERE  prz_isdel=0 ORDER BY prz_end_time DESC LIMIT '.$size;
        $railway_zx = $Db -> getData($sql);
        $Tpl -> assign('railway_zx', $railway_zx);

        $railway_px = [];   //铁路(拼箱)
        $sql = 'SELECT *,prp_id as id, prp_start as start, prp_end as end, prp_huowuzhongwen as huowuzhongwen, prp_type as type, prp_first_time as first_time, prp_valid_time as valid_time, prp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_px').' WHERE  prp_isdel=0 ORDER BY prp_end_time DESC LIMIT '.$size;
        $railway_px = $Db -> getData($sql);
        $Tpl -> assign('railway_px', $railway_px);

        $railway_sh = [];   //铁路(散杂货)
        $sql = 'SELECT *,prs_id as id, prs_end as end, prs_huowuzhongwen as huowuzhongwen, prs_type as type, prs_first_time as first_time, prs_valid_time as valid_time, prs_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_sh').' WHERE  prs_isdel=0 ORDER BY prs_end_time DESC LIMIT '.$size;
        $railway_sh = $Db -> getData($sql);
        $Tpl -> assign('railway_sh', $railway_sh);

        $sea_zx = [];   //海运(整箱)
        $sql = 'SELECT *,psz_id as id, psz_start as start, psz_end as end, psz_huowuzhongwen as huowuzhongwen, psz_first_time as first_time, psz_valid_time as valid_time, psz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_zx').' WHERE  psz_isdel=0 ORDER BY psz_end_time DESC LIMIT '.$size;
        $sea_zx = $Db -> getData($sql);
        $Tpl -> assign('sea_zx', $sea_zx);

        $sea_px = [];   //海运(拼箱)
        $sql = 'SELECT *,psp_id as id, psp_start as start, psp_end as end, psp_huowuzhongwen as huowuzhongwen, psp_type as type, psp_first_time as first_time, psp_valid_time as valid_time, psp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_px').' WHERE  psp_isdel=0 ORDER BY psp_end_time DESC LIMIT '.$size;
        $sea_px = $Db -> getData($sql);
        $Tpl -> assign('sea_px', $sea_px);

        $sea_sh = [];   //海运(散杂货)
        $sql = 'SELECT *,pss_id as id, pss_start as start, pss_end as end, pss_huowuzhongwen as huowuzhongwen, pss_type as type, pss_first_time as first_time, pss_valid_time as valid_time, pss_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_sh').' WHERE  pss_isdel=0 ORDER BY pss_end_time DESC LIMIT '.$size;
        $sea_sh = $Db -> getData($sql);
        $Tpl -> assign('sea_sh', $sea_sh);

        $air = [];  //空运
        $sql = 'SELECT *,par_id as id, par_postion as start, par_end as end, par_huowuzhongwen as huowuzhongwen, par_type as type, par_first_time as first_time, par_valid_time as valid_time, par_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_air').' WHERE  par_isdel=0 ORDER BY par_end_time DESC LIMIT '.$size;
        $air = $Db -> getData($sql);
        $Tpl -> assign('air', $air);

        $land_zx = [];   //公路(整箱)
        $sql = 'SELECT *,plz_id as id, plz_start as start, plz_end as end, plz_huowuzhongwen as huowuzhongwen, plz_type as type, plz_first_time as first_time, plz_valid_time as valid_time, plz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_zx').' WHERE  plz_isdel=0 ORDER BY plz_end_time DESC LIMIT '.$size;
        $land_zx = $Db -> getData($sql);
        $Tpl -> assign('land_zx', $land_zx);

        $land_szh = [];   //公路(散杂货)
        $sql = 'SELECT *,pls_id as id, pls_end as end, pls_huowuzhongwen as huowuzhongwen, pls_type as type, pls_first_time as first_time, pls_valid_time as valid_time, pls_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_szh').' WHERE  pls_isdel=0 ORDER BY pls_end_time DESC LIMIT '.$size;
        $land_szh = $Db -> getData($sql);
        $Tpl -> assign('land_szh', $land_szh);

        $storage = [];  //仓储
        $sql = 'SELECT *,pse_id as id, pse_start as start, pse_huowuzhongwen as huowuzhongwen, pse_first_time as first_time, pse_valid_time as valid_time, pse_ischeck as ischeck, pse_remark as remark FROM '.$Db -> getTableNameAll('pallet_storage').' WHERE  pse_isdel=0 ORDER BY pse_end_time DESC LIMIT '.$size;
        $storage = $Db -> getData($sql);
        $Tpl -> assign('storage', $storage);

        $detect = [];   //报关报检
        $sql = 'SELECT *,pdt_id as id, pdt_start as start, pdt_huowuzhongwen as huowuzhongwen, pdt_type as type, pdt_first_time as first_time, pdt_valid_time as valid_time, pdt_ischeck as ischeck, pdt_remark as remark FROM '.$Db -> getTableNameAll('pallet_detect').' WHERE  pdt_isdel=0 ORDER BY pdt_end_time DESC LIMIT '.$size;
        $detect = $Db -> getData($sql);
        $Tpl -> assign('detect', $detect);

        $multi_zx = []; //多式联运(整箱)
        $sql = 'SELECT *,pmz_id as id, pmz_start as start, pmz_end as end, pmz_huowuzhongwen as huowuzhongwen, pmz_type as type, pmz_first_time as first_time, pmz_valid_time as valid_time, pmz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_zx').' WHERE  pmz_isdel=0 ORDER BY pmz_end_time DESC LIMIT '.$size;
        $multi_zx = $Db -> getData($sql);
        $Tpl -> assign('multi_zx', $multi_zx);
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
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 10';
        $rightbanner = $Db -> getDataOne($sql);
        $Tpl -> assign('rightbanner', $rightbanner);
        //国内资讯
        $ArticleModel = new ArticleModel();
        $articleList = $ArticleModel -> getArticleList(16, 5);    //获取国内咨询下的10条信息
        $Tpl -> assign('articleList', $articleList);
        $Tpl -> show('Huopan/index.html');
    }

    //air
    public function air(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('air', $stype);
        $Tpl -> show('Huopan/air.html');
    }

    //detect
    public function detect(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('detect', $stype);
        $Tpl -> show('Huopan/detect.html');
    }

    //land
    public function land(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('land', $stype);
        $Tpl -> show('Huopan/land.html');
    }

    //multi
    public function multi(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('multi', $stype);
        $Tpl -> show('Huopan/multi.html');
    }

    //railway
    public function railway(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('railway', $stype);
        $Tpl -> show('Huopan/railway.html');
    }

    //sea
    public function sea(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('sea', $stype);
        $Tpl -> show('Huopan/sea.html');
    }

    //storage
    public function storage(string $action){
        $Tpl = $this -> getTpl();
        $stype = From::valTrim('stype');    //子类
        $this -> getListData('storage', $stype);
        $Tpl -> show('Huopan/storage.html');
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
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'prp_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'prs_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'prz_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'prp_start like \'%'.addslashes($start).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            //$whereString .= ($whereString == ''?'':' AND ').'prs_start like \'%'.addslashes($start).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'prz_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'prp_end like \'%'.addslashes($end).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'prs_end like \'%'.addslashes($end).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'prz_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'prp_ischeck like \''.addslashes($check).'\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'prs_ischeck like \''.addslashes($check).'\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'prz_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if($stype == 'px'){ //拼箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prp_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,prp_id as id, prp_start as start, prp_end as end, prp_huowuzhongwen as huowuzhongwen, prp_type as type, prp_first_time as first_time, prp_valid_time as valid_time, prp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_px').$whereString.' ORDER BY prp_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else if($stype == 'sh'){   //散杂货
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prs_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,prs_id as id, prs_end as end, prs_huowuzhongwen as huowuzhongwen, prs_type as type, prs_first_time as first_time, prs_valid_time as valid_time, prs_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_sh').$whereString.' ORDER BY prs_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //整箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,prz_id as id, prz_start as start, prz_end as end, prz_huowuzhongwen as huowuzhongwen, prz_type as type, prz_first_time as first_time, prz_valid_time as valid_time, prz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_zx').$whereString.' ORDER BY prz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                break;
            }
            case 'sea':{    //海运:
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'psp_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pss_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'psz_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'psp_start like \'%'.addslashes($start).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            //$whereString .= ($whereString == ''?'':' AND ').'pss_start like \'%'.addslashes($start).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'psz_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'psp_end like \'%'.addslashes($end).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pss_end like \'%'.addslashes($end).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'psz_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'psp_ischeck like \''.addslashes($check).'\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pss_ischeck like \''.addslashes($check).'\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'psz_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if($stype == 'px'){ //拼箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND psp_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,psp_id as id, psp_start as start, psp_end as end, psp_huowuzhongwen as huowuzhongwen, psp_type as type, psp_first_time as first_time, psp_valid_time as valid_time, psp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_px').$whereString.' ORDER BY psp_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else if($stype == 'sh'){   //散杂货
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pss_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pss_id as id, pss_start as start, pss_end as end, pss_huowuzhongwen as huowuzhongwen, pss_type as type, pss_first_time as first_time, pss_valid_time as valid_time, pss_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_sh').$whereString.' ORDER BY pss_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //整箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND psz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,psz_id as id, psz_start as start, psz_end as end, psz_huowuzhongwen as huowuzhongwen, psz_first_time as first_time, psz_valid_time as valid_time, psz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_zx').$whereString.' ORDER BY psz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                break;
            }
            case 'air':{    //空运
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'par_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'par_postion like \'%'.addslashes($start).'%\'';
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'par_end like \'%'.addslashes($end).'%\'';
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'par_ischeck like \''.addslashes($check).'\'';
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND par_isdel=0';
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,par_id as id, par_postion as start, par_end as end, par_huowuzhongwen as huowuzhongwen, par_type as type, par_first_time as first_time, par_valid_time as valid_time, par_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_air').$whereString.' ORDER BY par_id DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'land':{    //公路运输
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pls_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'plz_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'sh'){   //散杂货
                            //$whereString .= ($whereString == ''?'':' AND ').'pls_start like \'%'.addslashes($start).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'plz_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pls_end like \'%'.addslashes($end).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'plz_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pls_ischeck like \''.addslashes($check).'\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'plz_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if($stype == 'sh'){   //散杂货
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pls_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pls_id as id, pls_end as end, pls_huowuzhongwen as huowuzhongwen, pls_type as type, pls_first_time as first_time, pls_valid_time as valid_time, pls_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_szh').$whereString.' ORDER BY pls_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //整箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND plz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,plz_id as id, plz_start as start, plz_end as end, plz_huowuzhongwen as huowuzhongwen, plz_type as type, plz_first_time as first_time, plz_valid_time as valid_time, plz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_zx').$whereString.' ORDER BY plz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                break;
            }
            case 'storage':{    //仓储
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'pse_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'pse_start like \'%'.addslashes($start).'%\'';
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        //$whereString .= ($whereString == ''?'':' AND ').'pse_end like \'%'.addslashes($end).'%\'';
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'pse_ischeck like \''.addslashes($check).'\'';
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pse_isdel=0';
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pse_id as id, pse_start as start, pse_huowuzhongwen as huowuzhongwen, pse_first_time as first_time, pse_valid_time as valid_time, pse_ischeck as ischeck, pse_remark as remark FROM '.$Db -> getTableNameAll('pallet_storage').$whereString.' ORDER BY pse_id DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'detect':{    //报关报检
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'pdt_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'pdt_start like \'%'.addslashes($start).'%\'';
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        //$whereString .= ($whereString == ''?'':' AND ').'pdt_end like \'%'.addslashes($end).'%\'';
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        $whereString .= ($whereString == ''?'':' AND ').'pdt_ischeck like \''.addslashes($check).'\'';
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pdt_isdel=0';
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pdt_id as id, pdt_start as start, pdt_huowuzhongwen as huowuzhongwen, pdt_type as type, pdt_first_time as first_time, pdt_valid_time as valid_time, pdt_ischeck as ischeck, pdt_remark as remark FROM '.$Db -> getTableNameAll('pallet_detect').$whereString.' ORDER BY pdt_id DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            default:{   //multi-多式联运
                $save       = From::valTrim('save');
                if($save == 1){
                    $Page -> setQuery('save', $save);
                    $name = From::valTrim('name');
                    if(strlen($name) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'pmp_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pms_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'pmz_huowuzhongwen like \'%'.addslashes($name).'%\'';
                        }
                        $Page -> setQuery('name', $name);
                        $Tpl -> assign('name', $name);
                    }
                    $start = From::valTrim('start');
                    if(strlen($start) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'pmp_start like \'%'.addslashes($start).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            //$whereString .= ($whereString == ''?'':' AND ').'pms_start like \'%'.addslashes($start).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'pmz_start like \'%'.addslashes($start).'%\'';
                        }
                        $Page -> setQuery('start', $start);
                        $Tpl -> assign('start', $start);
                    }
                    $end = From::valTrim('end');
                    if(strlen($end) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'pmp_end like \'%'.addslashes($end).'%\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pms_end like \'%'.addslashes($end).'%\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'pmz_end like \'%'.addslashes($end).'%\'';
                        }
                        $Page -> setQuery('end', $end);
                        $Tpl -> assign('end', $end);
                    }
                    $check = From::valTrim('check');
                    if(strlen($check) > 0){
                        if($stype == 'px'){ //拼箱
                            $whereString .= ($whereString == ''?'':' AND ').'pmp_ischeck like \''.addslashes($check).'\'';
                        }else if($stype == 'sh'){   //散杂货
                            $whereString .= ($whereString == ''?'':' AND ').'pms_ischeck like \''.addslashes($check).'\'';
                        }else{  //整箱
                            $whereString .= ($whereString == ''?'':' AND ').'pmz_ischeck like \''.addslashes($check).'\'';
                        }
                        $Page -> setQuery('check', $check);
                        $Tpl -> assign('check', $check);
                    }
                }
                if($stype == 'px'){ //拼箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pmp_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pmp_id as id, pmp_start as start, pmp_end as end, pmp_huowuzhongwen as huowuzhongwen, pmp_type as type, pmp_first_time as first_time, pmp_valid_time as valid_time, pmp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_px').$whereString.' ORDER BY pmp_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else if($stype == 'sh'){   //散杂货
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pms_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pms_id as id, pms_end as end, pms_huowuzhongwen as huowuzhongwen, pms_type as type, pms_first_time as first_time, pms_valid_time as valid_time, pms_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_sh').$whereString.' ORDER BY pms_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //整箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pmz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pmz_id as id, pmz_start as start, pmz_end as end, pmz_huowuzhongwen as huowuzhongwen, pmz_type as type, pmz_first_time as first_time, pmz_valid_time as valid_time, pmz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_zx').$whereString.' ORDER BY pmz_id DESC LIMIT '.$limit[0].', '.$limit[1];
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
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 10';
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
                if($stype == 'px'){ //拼箱
                    $dataType = '拼箱';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_railway_px.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prp_isdel=0 AND prp_id='.$id;
                    $sql = 'SELECT *,prp_id as id, prp_start as start, prp_end as end, prp_huowuzhongwen as huowuzhongwen, prp_type as type, prp_first_time as first_time, prp_valid_time as valid_time, prp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_px').$whereString;
                }else if($stype == 'sh'){   //散杂货
                    $dataType = '散杂货';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_railway_sh.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prs_isdel=0 AND prs_id='.$id;
                    $sql = 'SELECT *,prs_id as id, prs_end as end, prs_huowuzhongwen as huowuzhongwen, prs_type as type, prs_first_time as first_time, prs_valid_time as valid_time, prs_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_sh').$whereString;
                }else{  //整箱
                    $dataType = '整箱';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_railway_zx.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prz_isdel=0 AND prz_id='.$id;
                    $sql = 'SELECT *,prz_id as id, prz_start as start, prz_end as end, prz_huowuzhongwen as huowuzhongwen, prz_type as type, prz_first_time as first_time, prz_valid_time as valid_time, prz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_zx').$whereString;
                }
                break;
            }
            case 'sea':{    //海运:
                if($stype == 'px'){ //拼箱
                    $dataType = '拼箱';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_sea_px.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND psp_isdel=0 AND psp_id='.$id;
                    $sql = 'SELECT *,psp_id as id, psp_start as start, psp_end as end, psp_huowuzhongwen as huowuzhongwen, psp_type as type, psp_first_time as first_time, psp_valid_time as valid_time, psp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_px').$whereString;
                }else if($stype == 'sh'){   //散杂货
                    $dataType = '散杂货';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_sea_ph.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pss_isdel=0 AND pss_id='.$id;
                    $sql = 'SELECT *,pss_id as id, pss_end as end, pss_huowuzhongwen as huowuzhongwen, pss_type as type, pss_first_time as first_time, pss_valid_time as valid_time, pss_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_sh').$whereString;
                }else{  //整箱
                    $dataType = '整箱';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_sea_zx.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND psz_isdel=0 AND psz_id='.$id;
                    $sql = 'SELECT *,psz_id as id, psz_start as start, psz_end as end, psz_huowuzhongwen as huowuzhongwen, psz_first_time as first_time, psz_valid_time as valid_time, psz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_zx').$whereString;
                }
                break;
            }
            case 'air':{    //空运
                $Tpl -> assign('tplHtml', 'Huopan/pallet_air.html');
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND par_isdel=0 AND par_id='.$id;
                $sql = 'SELECT *,par_id as id, par_postion as start, par_end as end, par_huowuzhongwen as huowuzhongwen, par_type as type, par_first_time as first_time, par_valid_time as valid_time, par_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_air').$whereString;
                break;
            }
            case 'land':{    //公路运输
                if($stype == 'sh'){   //散杂货
                    $dataType = '散杂货';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_land_szh.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pls_isdel=0 AND pls_id='.$id;
                    $sql = 'SELECT *,pls_id as id, pls_end as end, pls_huowuzhongwen as huowuzhongwen, pls_type as type, pls_first_time as first_time, pls_valid_time as valid_time, pls_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_szh').$whereString;
                }else{  //整箱
                    $dataType = '整箱';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_land_zx.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND plz_isdel=0 AND plz_id='.$id;
                    $sql = 'SELECT *,plz_id as id, plz_start as start, plz_end as end, plz_huowuzhongwen as huowuzhongwen, plz_type as type, plz_first_time as first_time, plz_valid_time as valid_time, plz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_zx').$whereString;
                }
                break;
            }
            case 'storage':{    //仓储
                $Tpl -> assign('tplHtml', 'Huopan/pallet_storage.html');
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pse_isdel=0 AND pse_id='.$id;
                $sql = 'SELECT *,pse_id as id, pse_start as start, pse_huowuzhongwen as huowuzhongwen, pse_first_time as first_time, pse_valid_time as valid_time, pse_ischeck as ischeck, pse_remark as remark FROM '.$Db -> getTableNameAll('pallet_storage').$whereString;
                break;
            }
            case 'detect':{    //报关报检
                $Tpl -> assign('tplHtml', 'Huopan/pallet_detect.html');
                if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pdt_isdel=0 AND pdt_id='.$id;
                $sql = 'SELECT *,pdt_id as id, pdt_start as start, pdt_huowuzhongwen as huowuzhongwen, pdt_type as type, pdt_first_time as first_time, pdt_valid_time as valid_time, pdt_ischeck as ischeck, pdt_remark as remark FROM '.$Db -> getTableNameAll('pallet_detect').$whereString;
                break;
            }
            default:{   //multi-多式联运
                if($stype == 'px'){ //拼箱
                    $dataType = '拼箱';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_multi_px.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pmp_isdel=0 AND pmp_id='.$id;
                    $sql = 'SELECT *,pmp_id as id, pmp_start as start, pmp_end as end, pmp_huowuzhongwen as huowuzhongwen, pmp_type as type, pmp_first_time as first_time, pmp_valid_time as valid_time, pmp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_px').$whereString;
                }else if($stype == 'sh'){   //散杂货
                    $dataType = '散杂货';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_multi_sh.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pms_isdel=0 AND pms_id='.$id;
                    $sql = 'SELECT *,pms_id as id, pms_end as end, pms_huowuzhongwen as huowuzhongwen, pms_type as type, pms_first_time as first_time, pms_valid_time as valid_time, pms_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_sh').$whereString;
                }else{  //整箱
                    $dataType = '整箱';
                    $Tpl -> assign('tplHtml', 'Huopan/pallet_multi_zx.html');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pmz_isdel=0 AND pmz_id='.$id;
                    $sql = 'SELECT *,pmz_id as id, pmz_start as start, pmz_end as end, pmz_huowuzhongwen as huowuzhongwen, pmz_type as type, pmz_first_time as first_time, pmz_valid_time as valid_time, pmz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_zx').$whereString;
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
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('daili').' WHERE 1 ORDER BY id DESC LIMIT 10';
        $dailiList = $Db->getData($sql);
        $Tpl -> assign('dailiList', $dailiList);
        if(isset($_SESSION['TOKEN']) && $_SESSION['TOKEN']['UID'] > 0){
            $Tpl -> show('Huopan/viewsignin.html');
        }else{
            $Tpl -> show('Huopan/viewsignout.html');
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