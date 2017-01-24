<?php
/**
 * @Copyright (C) 2016.
 * @Description Ent
 * @FileName Ent.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Ent;
use \App\Index\UserDatas;
use \App\Pub\Common;
use \Libs\Comm\From;
use \Libs\Comm\Http;
use \Libs\Frame\Action;
use \Libs\Frame\Conf;
use \Libs\Frame\Url;
use \Libs\Tag\Db;
use \Libs\Tag\Page;
class Ent extends Action{
    //配置
    public function conf(){
        EntData::checkInfo(EntData::getDomain());   //检测是否可以访问OR是否存在域名
        $Tpl = $this -> getTpl();
        $seo = EntData::getSeo();
        $page = [];
        $page['Title']          = $seo['es_title'] ?? '';
        if(strlen($page['Title']) < 1) $page['Title'] = '港港通国际多式联运门户网';
        $page['Keywords']       = $seo['es_key'] ?? '';
        if(strlen($page['Keywords']) < 1) $page['Keywords'] = '行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布';
        $page['Description']    = $seo['es_desc'] ?? '';
        if(strlen($page['Description']) < 1) $page['Description'] = '国内首家专业性多式联运行业门户网站，集行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布等功能和内容';
        $Tpl -> assign('page', $page);
    }

    //Main
    public function main(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $getCompany['ec_desc'] = mb_substr($getCompany['ec_desc']??'', 0, 400);
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $whereString = 'us_id='.$usId.' AND en_isdel=0';
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_news').' WHERE '.$whereString.' ORDER BY en_last_time DESC LIMIT 5';
        $dataArray = $Db -> getData($sql);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach($dataArray as $key => $val){
//                if(preg_match_all("/<img.+src=\"(.+)\" .+\/>/Um",$val['en_content'],$image)){
//                    $imageList = [];
//                    foreach ($image as $key => $item){
//                        if($key == 0) continue;
//                        foreach ($item as $keyitem => $row){
//                            $imageList[] = $row;
//                        }
//
//                    }
//                }
                //$val['imageList'] = $imageList;
                $val['en_content'] = preg_replace("/<img.+\/>/Um","",$val['en_content']);
                $val['en_content'] = mb_substr($val['en_content'], 0, 200).'...';
                $dataList[] = $val;
            }
        }
        $Tpl -> assign('dataList', $dataList);
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/index.html');
    }

    //企业介绍
    public function about(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/about.html');
    }

    //联系我们
    public function contact(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        $Tpl -> assign('shopUrl', EntData::getShopUrl($usId));
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/contact.html');
    }

    //企业案例
    public function case(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        //list
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 8);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND ec_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('ent_case').' WHERE '.$whereString.' ORDER BY ec_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach($dataArray as $key => $val){
                $val['ec_img'] = $urlRes.ltrim($val['ec_img'], '/');
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(EntData::getLink('', 'case.php'));
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/case.html');
    }

    //企业案例详细
    public function caseView(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $id = From::valInt('id');
        $whereString = 'us_id='.$usId.' AND ec_id='.$id.' AND ec_isdel=0';
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_case').' WHERE '.$whereString;
        $dataInfo = $Db -> getDataOne($sql);
        $Tpl -> assign('dataInfo', $dataInfo);
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_case').' WHERE us_id='.$usId.' AND ec_id<'.$id.' AND ec_isdel=0 ORDER BY ec_id DESC';
        $preInfo = $Db -> getDataOne($sql);
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_case').' WHERE us_id='.$usId.' AND ec_id>'.$id.' AND ec_isdel=0 ORDER BY ec_id ASC';
        $nextInfo = $Db -> getDataOne($sql);
        $Tpl -> assign('preInfo', $preInfo);
        $Tpl -> assign('nextInfo', $nextInfo);
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/case_view.html');
    }

    //企业荣誉
    public function honor(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        //list
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 6);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND eh_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('ent_honor').' WHERE '.$whereString.' ORDER BY eh_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach($dataArray as $key => $val){
                $val['eh_img'] = $urlRes.ltrim($val['eh_img'], '/');
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(EntData::getLink('', 'honor.php'));
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataListA', array_slice($dataList, 0, 3));
        $Tpl -> assign('dataListB', array_slice($dataList, 3, 3));
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/honor.html');
    }

    //资讯新闻
    public function news(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        //list
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 6);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND en_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('ent_news').' WHERE '.$whereString.' ORDER BY en_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach($dataArray as $key => $val){
                $val['en_content'] = preg_replace("/<img.+\/>/Um","",$val['en_content']);
                $val['en_content'] = mb_substr($val['en_content'], 0, 200).'...';
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(EntData::getLink('', 'news.php'));
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/news.html');
    }

    //资讯新闻详细
    public function newsView(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $id = From::valInt('id');
        $whereString = 'us_id='.$usId.' AND en_id='.$id.' AND en_isdel=0';
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_news').' WHERE '.$whereString;
        $dataInfo = $Db -> getDataOne($sql);
        $Tpl -> assign('dataInfo', $dataInfo);
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_news').' WHERE us_id='.$usId.' AND en_id<'.$id.' AND en_isdel=0 ORDER BY en_id DESC';
        $preInfo = $Db -> getDataOne($sql);
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_news').' WHERE us_id='.$usId.' AND en_id>'.$id.' AND en_isdel=0 ORDER BY en_id ASC';
        $nextInfo = $Db -> getDataOne($sql);
        $Tpl -> assign('preInfo', $preInfo);
        $Tpl -> assign('nextInfo', $nextInfo);
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/news_view.html');
    }

    //国际贸易
    public function gjmy(string $action){
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $urlRes = Conf::get('URL.RES'); //资源地址
        $urlWww = Conf::get('URL.WWW'); //主站地址
        $Db = Db::tag('DB.USER', 'GMY');
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 5);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND gd_isdel=0 AND gd_type=2';
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
        $pageList = $Page -> getPage(EntData::getLink('', 'gjmy.php'));
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl -> assign('urlRes', $urlRes);
        $Tpl -> assign('urlWww', $urlWww);
        $Tpl -> show('Ent/gjmy.html');
    }

    //货盘
    public function huopan(string $action){
        $op = From::valTrim('op');
        if($op == 'view'){
            $stype = From::valTrim('stype');
            if(strlen($stype) < 1) $stype = 'multi_zx';
            if($stype == 'land_szh') $stype = 'land_sh';
            $tmpArray = explode('_', $stype);
            $aString = $tmpArray[0].'view';
            $bString = '';
            if(count($tmpArray) > 1) $bString = $tmpArray[1];
            $id = From::valInt('id');
            $url = 'huopan/multi.php?A=huopan-'.$aString.'&stype='.$bString.'&id='.$id;
            Common::toUrl(Conf::get('URL.WWW').$url);    //跳转详细
        }
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        //list
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $type = From::valTrim('type');
        $Tpl -> assign('type', $type);
        switch ($type){
            case 'multi_px':{   //多式联运(拼箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pmp_id as id, pmp_start as start, pmp_end as end, pmp_huowuzhongwen as huowuzhongwen, pmp_type as type, pmp_first_time as first_time, pmp_valid_time as valid_time, pmp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_px').' WHERE us_id='.$usId.' AND pmp_isdel=0 ORDER BY pmp_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'multi_sh':{   //多式联运(散杂货)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pms_id as id, pms_end as end, pms_huowuzhongwen as huowuzhongwen, pms_type as type, pms_first_time as first_time, pms_valid_time as valid_time, pms_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_sh').' WHERE us_id='.$usId.' AND pms_isdel=0 ORDER BY pms_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'railway_zx':{   //铁路(整箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,prz_id as id, prz_start as start, prz_end as end, prz_huowuzhongwen as huowuzhongwen, prz_type as type, prz_first_time as first_time, prz_valid_time as valid_time, prz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_zx').' WHERE us_id='.$usId.' AND prz_isdel=0 ORDER BY prz_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'railway_px':{   //铁路(拼箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,prp_id as id, prp_start as start, prp_end as end, prp_huowuzhongwen as huowuzhongwen, prp_type as type, prp_first_time as first_time, prp_valid_time as valid_time, prp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_px').' WHERE us_id='.$usId.' AND prp_isdel=0 ORDER BY prp_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'railway_sh':{   //铁路(散杂货)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,prs_id as id, prs_end as end, prs_huowuzhongwen as huowuzhongwen, prs_type as type, prs_first_time as first_time, prs_valid_time as valid_time, prs_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_sh').' WHERE us_id='.$usId.' AND prs_isdel=0 ORDER BY prs_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'sea_zx':{   //海运(整箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,psz_id as id, psz_start as start, psz_end as end, psz_huowuzhongwen as huowuzhongwen, psz_first_time as first_time, psz_valid_time as valid_time, psz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_zx').' WHERE us_id='.$usId.' AND psz_isdel=0 ORDER BY psz_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'sea_px':{   //海运(拼箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,psp_id as id, psp_start as start, psp_end as end, psp_huowuzhongwen as huowuzhongwen, psp_type as type, psp_first_time as first_time, psp_valid_time as valid_time, psp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_px').' WHERE us_id='.$usId.' AND psp_isdel=0 ORDER BY psp_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'sea_sh':{   //海运(散杂货)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pss_id as id, pss_end as end, pss_huowuzhongwen as huowuzhongwen, pss_type as type, pss_first_time as first_time, pss_valid_time as valid_time, pss_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_sh').' WHERE us_id='.$usId.' AND pss_isdel=0 ORDER BY pss_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'air':{   //空运
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,par_id as id, par_postion as start, par_end as end, par_huowuzhongwen as huowuzhongwen, par_type as type, par_first_time as first_time, par_valid_time as valid_time, par_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_air').' WHERE us_id='.$usId.' AND par_isdel=0 ORDER BY par_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'land_zx':{   //公路(整箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,plz_id as id, plz_start as start, plz_end as end, plz_huowuzhongwen as huowuzhongwen, plz_type as type, plz_first_time as first_time, plz_valid_time as valid_time, plz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_zx').' WHERE us_id='.$usId.' AND plz_isdel=0 ORDER BY plz_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'land_szh':{   //公路(散杂货)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pls_id as id, pls_end as end, pls_huowuzhongwen as huowuzhongwen, pls_type as type, pls_first_time as first_time, pls_valid_time as valid_time, pls_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_szh').' WHERE us_id='.$usId.' AND pls_isdel=0 ORDER BY pls_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'storage':{   //仓储
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pse_id as id, pse_start as start, pse_huowuzhongwen as huowuzhongwen, pse_first_time as first_time, pse_valid_time as valid_time, pse_ischeck as ischeck, pse_remark as remark FROM '.$Db -> getTableNameAll('pallet_storage').' WHERE us_id='.$usId.' AND pse_isdel=0 ORDER BY pse_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'detect':{   //报关报检
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pdt_id as id, pdt_start as start, pdt_huowuzhongwen as huowuzhongwen, pdt_type as type, pdt_first_time as first_time, pdt_valid_time as valid_time, pdt_ischeck as ischeck, pdt_remark as remark FROM '.$Db -> getTableNameAll('pallet_detect').' WHERE us_id='.$usId.' AND pdt_isdel=0 ORDER BY pdt_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            default:{   //多式联运(整箱) multi_zx
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pmz_id as id, pmz_start as start, pmz_end as end, pmz_huowuzhongwen as huowuzhongwen, pmz_type as type, pmz_first_time as first_time, pmz_valid_time as valid_time, pmz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_zx').' WHERE us_id='.$usId.' AND pmz_isdel=0 ORDER BY pmz_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
        }
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach($dataArray as $key => $val){
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(EntData::getLink('', 'huopan.php').'&type='.$type);
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/huopan.html');
    }

    //箱卡集市
    public function xkjs(string $action){
        $op = From::valTrim('op');
        if($op == 'view'){
            $id = From::valInt('id');
            $url = 'xkjs/index.php?A=detail&id='.$id;
            Common::toUrl(Conf::get('URL.WWW').$url);    //跳转详细
        }
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Db = Db::tag('DB.USER', 'GMY');
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND cb_is_del=0 AND cb_is_onsale=1';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('container_box').' WHERE '.$whereString.' ORDER BY cb_ctime DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            $dataList = $dataArray;
        }
        $pageList = $Page -> getPage(Url::getUrlAction(From::valTrim('A')));
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl -> show('Ent/xkjs.html');
    }

    //运价
    public function yunjia(string $action){
        $op = From::valTrim('op');
        if($op == 'view'){
            $stype = From::valTrim('stype');
            if(strlen($stype) < 1) $stype = 'multi_zx';
            if($stype == 'land_szh') $stype = 'land_sh';
            $tmpArray = explode('_', $stype);
            $aString = $tmpArray[0].'view';
            $bString = '';
            if(count($tmpArray) > 1) $bString = $tmpArray[1];
            $id = From::valInt('id');
            $url = 'yunjia/multi.php?A=yunjia-'.$aString.'&stype='.$bString.'&id='.$id;
            Common::toUrl(Conf::get('URL.WWW').$url);    //跳转详细
        }
        $Tpl = $this -> getTpl();
        $getCompany = EntData::getCompany();        //公司信息
        $Tpl -> assign('getCompany', $getCompany);
        $entInfo = EntData::getEnterprise();        //认证信息
        $Tpl -> assign('entInfo', $entInfo);
        $entInfos = EntData::getEnterpriseAuth();   //授权信息
        $Tpl -> assign('entInfos', $entInfos);
        $baseInfo = EntData::getBasic();   //基本信息
        $Tpl -> assign('baseInfo', $baseInfo);
        $usId = EntData::getUsId();
        //list
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $type = From::valTrim('type');
        $Tpl -> assign('type', $type);
        switch ($type){
            case 'multi_sh':{   //多式联运(大宗散杂)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pms_id as id, pms_end as end, pms_huowuzhongwen as huowuzhongwen, pms_type as type, pms_first_time as first_time, pms_valid_time as valid_time, pms_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_sh').' WHERE us_id='.$usId.' AND pms_isdel=0 ORDER BY pms_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'railway_jzx':{   //铁路(集装箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,trj_id as id, trj_start as start, trj_end as end, trj_tujingquyu as tujingquyu, trj_paytype as paytype, trj_first_time as first_time, trj_valid_time as valid_time, trj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_jzx').' WHERE us_id='.$usId.' AND trj_isdel=0 ORDER BY trj_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'railway_cp':{   //铁路(车皮)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,trz_id as id, trz_start as start, trz_end as end, trz_tujingquyu as tujingquyu, trz_paytype as paytype, trz_first_time as first_time, trz_valid_time as valid_time, trz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_zc').' WHERE us_id='.$usId.' AND trz_isdel=0 ORDER BY trz_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'sea_zx':{   //海运(整箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tsz_id as id, tsz_start as start, tsz_end as end, tsz_first_time as first_time, tsz_valid_time as valid_time, tsz_ischeck as ischeck, tsz_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_zx').' WHERE us_id='.$usId.' AND tsz_isdel=0 ORDER BY tsz_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'sea_px':{   //海运(拼箱)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tsp_id as id, tsp_start as start, tsp_end as end, tsp_first_time as first_time, tsp_valid_time as valid_time, tsp_ischeck as ischeck, tsp_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_px').' WHERE us_id='.$usId.' AND tsp_isdel=0 ORDER BY tsp_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'sea_sh':{   //海运(散杂货)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tsh_id as id, tsh_start as start, tsh_end as end, tsh_first_time as first_time, tsh_valid_time as valid_time, tsh_ischeck as ischeck, tsh_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_sh').' WHERE us_id='.$usId.' AND tsh_isdel=0 ORDER BY tsh_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'air_gn':{   //空运(国内)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tan_id as id, tan_start as start, tan_end as end, tan_hangkong as hangkong, tan_paytype as paytype, tan_first_time as first_time, tan_valid_time as valid_time, tan_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gn').' WHERE us_id='.$usId.' AND tan_isdel=0 ORDER BY tan_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'air_gj':{   //空运(国际)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,taj_id as id, taj_start as start, taj_end as end, taj_hangkong as hangkong, taj_paytype as paytype, taj_first_time as first_time, taj_valid_time as valid_time, taj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gj').' WHERE us_id='.$usId.' AND taj_isdel=0 ORDER BY taj_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'land_jktc':{   //公路(集卡拖车)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tlj_id as id, tlj_start as start, tlj_end as end, tlj_paytype as paytype, tlj_first_time as first_time, tlj_valid_time as valid_time, tlj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_jktc').' WHERE us_id='.$usId.' AND tlj_isdel=0 ORDER BY tlj_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'land_zx':{   //公路(专线)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tlz_id as id, tlz_start as start, tlz_end as end, tlz_paytype as paytype, tlz_first_time as first_time, tlz_valid_time as valid_time, tlz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_zx').' WHERE us_id='.$usId.' AND tlz_isdel=0 ORDER BY tlz_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'land_ld':{   //公路(零担)
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tld_id as id, tld_start as start, tld_end as end, tld_paytype as paytype, tld_first_time as first_time, tld_valid_time as valid_time, tld_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_ld').' WHERE us_id='.$usId.' AND tld_isdel=0 ORDER BY tld_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'storage':{   //仓储
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tst_id as id, tst_type as type, tst_title as title, tst_suozaidi as suozaidi, tst_first_time as first_time, tst_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_storage').' WHERE us_id='.$usId.' AND tst_isdel=0 ORDER BY tst_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            case 'detect':{   //报关报检
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tdt_id as id, tdt_type as type, tdt_title as title, tdt_gangkou as gangkou, tdt_first_time as first_time, tdt_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_detect').' WHERE us_id='.$usId.' AND tdt_isdel=0 ORDER BY tdt_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
            default:{   //多式联运(集装箱) multi_zx
                $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tmj_id as id, tmj_start as start, tmj_end as end, tmj_first_time as first_time, tmj_valid_time as valid_time, tmj_ischeck as ischeck, tmj_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_jzx').' WHERE us_id='.$usId.' AND tmj_isdel=0 ORDER BY tmj_end_time DESC LIMIT '.$limit[0].', '.$limit[1];
                break;
            }
        }
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach($dataArray as $key => $val){
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage(EntData::getLink('', 'yunjia.php').'&type='.$type);
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
        $Tpl -> show('Ent/yunjia.html');
    }
}