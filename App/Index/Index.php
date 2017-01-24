<?php
/**
 * @Copyright (C) 2016.
 * @Description Index
 * @FileName Index.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use App\Article\ArticleModel;
use \Libs\Frame\Action;
use \Libs\Frame\Conf;
use \Libs\Tag\Db;
class Index extends Action{
    //配置
    public function conf(){
    }

    //Main
    public function main(string $action){
        $Tpl = $this -> getTpl();
        //运价中心
        $Db = Db::tag('DB.USER', 'GMY');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $size = 7;
        //铁路(车皮)
        $sql = 'SELECT *,trz_id as id, trz_start as start, trz_end as end, trz_tujingquyu as tujingquyu, trz_paytype as paytype, trz_first_time as first_time, trz_valid_time as valid_time, trz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_zc').' WHERE  trz_isdel=0 ORDER BY trz_end_time DESC LIMIT '.$size;
        $tariffs_railway_zc = $Db -> getData($sql);
        $Tpl -> assign('tariffs_railway_zc', $tariffs_railway_zc);
        //铁路(集装箱)
        $sql = 'SELECT *,trj_id as id, trj_start as start, trj_end as end, trj_tujingquyu as tujingquyu, trj_paytype as paytype, trj_first_time as first_time, trj_valid_time as valid_time, trj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_railway_jzx').' WHERE  trj_isdel=0 ORDER BY trj_end_time DESC LIMIT '.$size;
        $tariffs_railway_jzx = $Db -> getData($sql);
        $Tpl -> assign('tariffs_railway_jzx', $tariffs_railway_jzx);
        //海运(整箱)
        $sql = 'SELECT *,tsz_id as id, tsz_start as start, tsz_end as end, tsz_first_time as first_time, tsz_valid_time as valid_time, tsz_ischeck as ischeck, tsz_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_zx').' WHERE  tsz_isdel=0 ORDER BY tsz_end_time DESC LIMIT '.$size;
        $tariffs_sea_zx = $Db -> getData($sql);
        $Tpl -> assign('tariffs_sea_zx', $tariffs_sea_zx);
        //海运(拼箱)
        $sql = 'SELECT *,tsp_id as id, tsp_start as start, tsp_end as end, tsp_first_time as first_time, tsp_valid_time as valid_time, tsp_ischeck as ischeck, tsp_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_px').' WHERE  tsp_isdel=0 ORDER BY tsp_end_time DESC LIMIT '.$size;
        $tariffs_sea_px = $Db -> getData($sql);
        $Tpl -> assign('tariffs_sea_px', $tariffs_sea_px);
        //海运(散杂货)
        $sql = 'SELECT *,tsh_id as id, tsh_start as start, tsh_end as end, tsh_first_time as first_time, tsh_valid_time as valid_time, tsh_ischeck as ischeck, tsh_remark as remark FROM '.$Db -> getTableNameAll('tariffs_sea_sh').' WHERE  tsh_isdel=0 ORDER BY tsh_end_time DESC LIMIT '.$size;
        $tariffs_sea_sh = $Db -> getData($sql);
        $Tpl -> assign('tariffs_sea_sh', $tariffs_sea_sh);
        //空运(国内)
        $sql = 'SELECT *,tan_id as id, tan_start as start, tan_end as end, tan_hangkong as hangkong, tan_paytype as paytype, tan_first_time as first_time, tan_valid_time as valid_time, tan_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gn').' WHERE  tan_isdel=0 ORDER BY tan_end_time DESC LIMIT '.$size;
        $tariffs_air_gn = $Db -> getData($sql);
        $Tpl -> assign('tariffs_air_gn', $tariffs_air_gn);
        //空运(国际)
        $sql = 'SELECT *,taj_id as id, taj_start as start, taj_end as end, taj_hangkong as hangkong, taj_paytype as paytype, taj_first_time as first_time, taj_valid_time as valid_time, taj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gj').' WHERE  taj_isdel=0 ORDER BY taj_end_time DESC LIMIT '.$size;
        $tariffs_air_gj = $Db -> getData($sql);
        $Tpl -> assign('tariffs_air_gj', $tariffs_air_gj);
        //公路(专线)
        $sql = 'SELECT *,tlz_id as id, tlz_start as start, tlz_end as end, tlz_paytype as paytype, tlz_first_time as first_time, tlz_valid_time as valid_time, tlz_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_zx').' WHERE  tlz_isdel=0 ORDER BY tlz_end_time DESC LIMIT '.$size;
        $tariffs_land_zx = $Db -> getData($sql);
        $Tpl -> assign('tariffs_land_zx', $tariffs_land_zx);
        //公路(零担)
        $sql = 'SELECT *,tld_id as id, tld_start as start, tld_end as end, tld_paytype as paytype, tld_first_time as first_time, tld_valid_time as valid_time, tld_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_ld').' WHERE  tld_isdel=0 ORDER BY tld_end_time DESC LIMIT '.$size;
        $tariffs_land_ld = $Db -> getData($sql);
        $Tpl -> assign('tariffs_land_ld', $tariffs_land_ld);
        //公路(集卡拖车)
        $sql = 'SELECT *,tlj_id as id, tlj_start as start, tlj_end as end, tlj_paytype as paytype, tlj_first_time as first_time, tlj_valid_time as valid_time, tlj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_land_jktc').' WHERE  tlj_isdel=0 ORDER BY tlj_end_time DESC LIMIT '.$size;
        $tariffs_land_jktc = $Db -> getData($sql);
        $Tpl -> assign('tariffs_land_jktc', $tariffs_land_jktc);
        //仓储
        $sql = 'SELECT *,tst_id as id, tst_type as type, tst_title as title, tst_suozaidi as suozaidi, tst_first_time as first_time, tst_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_storage').' WHERE  tst_isdel=0 ORDER BY tst_end_time DESC LIMIT '.$size;
        $tariffs_storage = $Db -> getData($sql);
        $Tpl -> assign('tariffs_storage', $tariffs_storage);
        //报关报检
        $sql = 'SELECT *,tdt_id as id, tdt_type as type, tdt_title as title, tdt_gangkou as gangkou, tdt_first_time as first_time, tdt_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_detect').' WHERE  tdt_isdel=0 ORDER BY tdt_end_time DESC LIMIT '.$size;
        $tariffs_detect = $Db -> getData($sql);
        $Tpl -> assign('tariffs_detect', $tariffs_detect);
        //多式联运(大宗散货)
        $sql = 'SELECT *,tmz_id as id, tmz_start as start, tmz_end as end, tmz_first_time as first_time, tmz_valid_time as valid_time, tmz_ischeck as ischeck, tmz_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_zc').' WHERE  tmz_isdel=0 ORDER BY tmz_end_time DESC LIMIT '.$size;
        $tariffs_multi_dzs = $Db -> getData($sql);
        $Tpl -> assign('tariffs_multi_dzs', $tariffs_multi_dzs);
        //多式联运(集装箱)
        $sql = 'SELECT *,tmj_id as id, tmj_start as start, tmj_end as end, tmj_first_time as first_time, tmj_valid_time as valid_time, tmj_ischeck as ischeck, tmj_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_jzx').' WHERE  tmj_isdel=0 ORDER BY tmj_end_time DESC LIMIT '.$size;
        $tariffs_multi_jzx = $Db -> getData($sql);
        $Tpl -> assign('tariffs_multi_jzx', $tariffs_multi_jzx);
        //货盘中心
        //多式联运(拼箱)
        $sql = 'SELECT *,pmp_id as id, pmp_start as start, pmp_end as end, pmp_huowuzhongwen as huowuzhongwen, pmp_type as type, pmp_first_time as first_time, pmp_valid_time as valid_time, pmp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_px').' WHERE  pmp_isdel=0 ORDER BY pmp_end_time DESC LIMIT '.$size;
        $pallet_multi_px = $Db -> getData($sql);
        $Tpl -> assign('pallet_multi_px', $pallet_multi_px);
        //多式联运(散杂货)
        $sql = 'SELECT *,pms_id as id, pms_end as end, pms_huowuzhongwen as huowuzhongwen, pms_type as type, pms_first_time as first_time, pms_valid_time as valid_time, pms_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_sh').' WHERE  pms_isdel=0 ORDER BY pms_end_time DESC LIMIT '.$size;
        $pallet_multi_sh = $Db -> getData($sql);
        $Tpl -> assign('pallet_multi_sh', $pallet_multi_sh);
        //铁路(整箱)
        $sql = 'SELECT *,prz_id as id, prz_start as start, prz_end as end, prz_huowuzhongwen as huowuzhongwen, prz_type as type, prz_first_time as first_time, prz_valid_time as valid_time, prz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_zx').' WHERE  prz_isdel=0 ORDER BY prz_end_time DESC LIMIT '.$size;
        $pallet_railway_zx = $Db -> getData($sql);
        $Tpl -> assign('pallet_railway_zx', $pallet_railway_zx);
        //铁路(拼箱)
        $sql = 'SELECT *,prp_id as id, prp_start as start, prp_end as end, prp_huowuzhongwen as huowuzhongwen, prp_type as type, prp_first_time as first_time, prp_valid_time as valid_time, prp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_px').' WHERE  prp_isdel=0 ORDER BY prp_end_time DESC LIMIT '.$size;
        $pallet_railway_px = $Db -> getData($sql);
        $Tpl -> assign('pallet_railway_px', $pallet_railway_px);
        //铁路(散杂货)
        $sql = 'SELECT *,prs_id as id, prs_end as end, prs_huowuzhongwen as huowuzhongwen, prs_type as type, prs_first_time as first_time, prs_valid_time as valid_time, prs_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_sh').' WHERE  prs_isdel=0 ORDER BY prs_end_time DESC LIMIT '.$size;
        $pallet_railway_sh = $Db -> getData($sql);
        $Tpl -> assign('pallet_railway_sh', $pallet_railway_sh);
        //海运(整箱)
        $sql = 'SELECT *,psz_id as id, psz_start as start, psz_end as end, psz_huowuzhongwen as huowuzhongwen, psz_first_time as first_time, psz_valid_time as valid_time, psz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_zx').' WHERE  psz_isdel=0 ORDER BY psz_end_time DESC LIMIT '.$size;
        $pallet_sea_zx = $Db -> getData($sql);
        $Tpl -> assign('pallet_sea_zx', $pallet_sea_zx);
        //海运(拼箱)
        $sql = 'SELECT *,psp_id as id, psp_start as start, psp_end as end, psp_huowuzhongwen as huowuzhongwen, psp_type as type, psp_first_time as first_time, psp_valid_time as valid_time, psp_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_px').' WHERE  psp_isdel=0 ORDER BY psp_end_time DESC LIMIT '.$size;
        $pallet_sea_px = $Db -> getData($sql);
        $Tpl -> assign('pallet_sea_px', $pallet_sea_px);
        //海运(散杂货)
        $sql = 'SELECT *,pss_id as id, pss_end as end, pss_huowuzhongwen as huowuzhongwen, pss_type as type, pss_first_time as first_time, pss_valid_time as valid_time, pss_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_sh').' WHERE  pss_isdel=0 ORDER BY pss_end_time DESC LIMIT '.$size;
        $pallet_sea_sh = $Db -> getData($sql);
        $Tpl -> assign('pallet_sea_sh', $pallet_sea_sh);
        //空运
        $sql = 'SELECT *,par_id as id, par_postion as start, par_end as end, par_huowuzhongwen as huowuzhongwen, par_type as type, par_first_time as first_time, par_valid_time as valid_time, par_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_air').' WHERE  par_isdel=0 ORDER BY par_end_time DESC LIMIT '.$size;
        $pallet_air = $Db -> getData($sql);
        $Tpl -> assign('pallet_air', $pallet_air);
        //公路(整箱)
        $sql = 'SELECT *,plz_id as id, plz_start as start, plz_end as end, plz_huowuzhongwen as huowuzhongwen, plz_type as type, plz_first_time as first_time, plz_valid_time as valid_time, plz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_zx').' WHERE  plz_isdel=0 ORDER BY plz_end_time DESC LIMIT '.$size;
        $pallet_land_zx = $Db -> getData($sql);
        $Tpl -> assign('pallet_land_zx', $pallet_land_zx);
        //公路(散杂货)
        $sql = 'SELECT *,pls_id as id, pls_end as end, pls_huowuzhongwen as huowuzhongwen, pls_type as type, pls_first_time as first_time, pls_valid_time as valid_time, pls_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_land_szh').' WHERE  pls_isdel=0 ORDER BY pls_end_time DESC LIMIT '.$size;
        $pallet_land_szh = $Db -> getData($sql);
        $Tpl -> assign('pallet_land_szh', $pallet_land_szh);
        //仓储
        $sql = 'SELECT *,pse_id as id, pse_start as start, pse_huowuzhongwen as huowuzhongwen, pse_first_time as first_time, pse_valid_time as valid_time, pse_ischeck as ischeck, pse_remark as remark FROM '.$Db -> getTableNameAll('pallet_storage').' WHERE  pse_isdel=0 ORDER BY pse_end_time DESC LIMIT '.$size;
        $pallet_storage = $Db -> getData($sql);
        $Tpl -> assign('pallet_storage', $pallet_storage);
        //报关报检
        $sql = 'SELECT *,pdt_id as id, pdt_start as start, pdt_huowuzhongwen as huowuzhongwen, pdt_type as type, pdt_first_time as first_time, pdt_valid_time as valid_time, pdt_ischeck as ischeck, pdt_remark as remark FROM '.$Db -> getTableNameAll('pallet_detect').' WHERE  pdt_isdel=0 ORDER BY pdt_end_time DESC LIMIT '.$size;
        $pallet_detect = $Db -> getData($sql);
        $Tpl -> assign('pallet_detect', $pallet_detect);
        //多式联运(整箱)
        $sql = 'SELECT *,pmz_id as id, pmz_start as start, pmz_end as end, pmz_huowuzhongwen as huowuzhongwen, pmz_type as type, pmz_first_time as first_time, pmz_valid_time as valid_time, pmz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_zx').' WHERE  pmz_isdel=0 ORDER BY pmz_end_time DESC LIMIT '.$size;
        $pallet_multi_zx = $Db -> getData($sql);
        $Tpl -> assign('pallet_multi_zx', $pallet_multi_zx);

        //班列总数
        $sql = 'SELECT COUNT(id) as count FROM '.$Db -> getTableNameAll('trains_category');
        $banlieCount = $Db -> getDataOne($sql);
        $Tpl -> assign('banlieCount', $banlieCount);

        //班列推荐
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('trains_information').' ORDER BY recommond DESC, id DESC limit 0,8';
        $trainsInformation = $Db -> getData($sql);
        $Tpl -> assign('trainsInformation', $trainsInformation);

        //国外代理总数
        $sql = 'SELECT COUNT(id) as count FROM '.$Db -> getTableNameAll('daili');
        $dailiCount = $Db -> getDataOne($sql);
        $Tpl -> assign('dailiCount', $dailiCount);

        $sql = "select country,name,web from ".$Db -> getTableNameAll('daili'). " LIMIT 0,4";
        $daili = $Db -> getData($sql);
        $Tpl -> assign('daili', $daili);

        //首页顶部横幅图片
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 1 and isshow = 1';
        $topbanner = $Db -> getDataOne($sql);
        $Tpl -> assign('topbanner', $topbanner);

        //首页中间横幅图片
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 3';
        $centerbanner = $Db -> getDataOne($sql);
        $Tpl -> assign('centerbanner', $centerbanner);

        //首页中间横幅图片
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 4';
        $centerrightbanner = $Db -> getDataOne($sql);
        $Tpl -> assign('centerrightbanner', $centerrightbanner);
        //首页底部横幅图片
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 5';
        $footerbanner = $Db -> getDataOne($sql);
        $Tpl -> assign('footerbanner', $footerbanner);


        //首页导航下面的轮播图
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 2';
        $topslide = $Db -> getDataOne($sql);
        $slide = json_decode($topslide['image']);
        $imageArray = array();
        foreach ($slide as $item){
            $array = array();
            $array['image'] = $item->image;
            $array['imageurl'] = $item->imageurl;
            $imageArray[] = $array;
        }
        $Tpl -> assign('slide', $imageArray);
        $Tpl -> assign('topslide', $topslide);

        //首页热点下面
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 24';
        $redianbanner = $Db -> getDataOne($sql);
        $Tpl -> assign('redianbanner', $redianbanner);
        //首页一带一路广告图
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 28';
        $yiluyidaibanner = $Db -> getDataOne($sql);
        $Tpl -> assign('yiluyidaibanner', $yiluyidaibanner);

//        echo '<pre>';
//        print_r($yiluyidaibanner);
//        exit;

        //首页右下面热门展会
        $zhanhuiBannerArray = array();
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 25';
        $zhanhuibanner = $Db -> getDataOne($sql);
        $zhanhuiBannerArray[] = $zhanhuibanner;
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 26';
        $zhanhuibanner = $Db -> getDataOne($sql);
        $zhanhuiBannerArray[] = $zhanhuibanner;
        $Tpl -> assign('zhanhuiBanner', $zhanhuiBannerArray);

        //首页中间右侧优秀企业推荐
        $suppliersBaner = array();
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 6';
        $topslide = $Db -> getDataOne($sql);
        $suppliersBaner[] = $topslide;
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 17';
        $topslide = $Db -> getDataOne($sql);
        $suppliersBaner[] = $topslide;
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 18';
        $topslide = $Db -> getDataOne($sql);
        $suppliersBaner[] = $topslide;
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 19';
        $topslide = $Db -> getDataOne($sql);
        $suppliersBaner[] = $topslide;
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 20';
        $topslide = $Db -> getDataOne($sql);
        $suppliersBaner[] = $topslide;
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 21';
        $topslide = $Db -> getDataOne($sql);
        $suppliersBaner[] = $topslide;
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 22';
        $topslide = $Db -> getDataOne($sql);
        $suppliersBaner[] = $topslide;
        $sql = 'SELECT banner.* FROM '.$Db -> getTableNameAll('banner').' as  banner where banner.id = 23';
        $topslide = $Db -> getDataOne($sql);
        $suppliersBaner[] = $topslide;
        $Tpl -> assign('suppliersBaner', $suppliersBaner);
        $ArticleModel = new ArticleModel();
        //最新资讯 ac_id=16国内
        //政策解读 ac_id=29
        //行业分析 ac_id=39
        //一带一路 ac_id=47
        $num = 4;
        $Tpl -> assign('newsNewList', $ArticleModel -> getArticleList(16, $num));
        $Tpl -> assign('newsZcjdList', $ArticleModel -> getArticleList(29, $num));
        $Tpl -> assign('newsDataList', $ArticleModel -> getArticleList(39, $num));
        $Tpl -> assign('newsYdylList', $ArticleModel -> getArticleList(47, $num));
        //专家在线
        $num = 5;
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('zhuanjia').' WHERE zj_isdel=0 group by zt_id ORDER BY zj_order ASC, zj_hits DESC, zj_id DESC LIMIT '.$num;
        $zjList = $Db -> getData($sql);
        foreach ($zjList as $key => $val){
            $ztId = intval($val['zt_id']);
            $sql = 'SELECT zt_name FROM '.$Db -> getTableNameAll('zhuanjia_type').' WHERE zt_isdel=0 AND zt_id='.$ztId;
            $ztInfo = $Db -> getDataOne($sql);
            if(isset($ztInfo['zt_name'])) $zjList[$key]['zt_name'] = trim($ztInfo['zt_name']);
        }
        $Tpl -> assign('zjList', $zjList);
        //多联知道
        $num = 3;
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('zhidao').' WHERE zd_isdel=0 AND zd_status=0 ORDER BY zd_hits DESC, zd_answer DESC, zd_id DESC LIMIT '.$num;
        $zdList = $Db -> getData($sql);
        foreach ($zdList as $key => $val){
            $zdKeywords = trim($val['zd_keywords']);
            $keyTitle = '';
            if(strlen($zdKeywords) > 0){
                foreach (explode(',', $zdKeywords) as $keyString){
                    $keyString = trim($keyString);
                    if(strlen($keyString) > 0){
                        $keyTitle = mb_substr($keyString, 0, 4);
                        break;
                    }
                }
            }
            $zdList[$key]['keyTitle'] = $keyTitle;
        }
        $Tpl -> assign('zdList', $zdList);
        //热点
        $sql = 'SELECT ar.* FROM '.$Db -> getTableNameAll('article').' as ar INNER JOIN '.$Db -> getTableNameAll('article_category_relation').' as acr on acr.ar_id = ar.ar_id WHERE acr.ac_id = 66 and  ar.ar_isdel=0 AND ar.ar_status=1 ORDER BY ar.ar_last_time DESC';
        $newInfo = $Db -> getDataOne($sql);
        if($newInfo['ar_id'] > 0){
            $sql = 'SELECT ac_id FROM '.$Db -> getTableNameAll('article_category_relation').' WHERE ar_id='.$newInfo['ar_id'];
            $newacInfo = $Db -> getDataOne($sql);
            $newInfo['ac_id'] = intval($newacInfo['ac_id']);
            $mbLength = mb_strlen($newInfo['ar_description']);
            if($mbLength > 50) $newInfo['ar_description'] = mb_substr($newInfo['ar_description'], 0, 50).'...';
            $Tpl -> assign('newInfo', $newInfo);
        }
        //一带一路 ac_id=47
        $ydylListInfo = $ArticleModel -> getArticleImgList(47, 1);
        $Tpl -> assign('ydylInfo', $ydylListInfo[0] ?? []);
        //港口口岸-59, 铁路口岸-60, 公路口岸-61, 空港口岸-62
        $sql = 'SELECT count(*) as num FROM '.$Db -> getTableNameAll('kouan');
        $kouanNum = $Db -> getDataInt($sql, 'num');
        $Tpl -> assign('kouanNum', $kouanNum);
        $num = 4;
        //国内重点口岸
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('kouan').' WHERE region like \'%中国%\' ORDER BY recommend_type DESC, ordering DESC, browse DESC, id DESC LIMIT '.$num;
        $kouanGnList = $Db -> getData($sql);
        $Tpl -> assign('kouanGnList', $kouanGnList);
        //国际重点口岸
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('kouan').' WHERE region not like \'%中国%\' ORDER BY recommend_type DESC, ordering DESC, browse DESC, id DESC LIMIT '.$num;
        $kouanGjList = $Db -> getData($sql);
        $Tpl -> assign('kouanGjList', $kouanGjList);
        $Tpl -> show('Index/index.html');
    }
    
    public function data(string $action){
        $Db = Db::tag('DB.USER', 'GMY');
        //获取承运人
        $sql = 'SELECT dcyr_name_en, dcyr_name_zh FROM '.$Db -> getTableNameAll('data_chengyunren').' GROUP BY dcyr_name_zh ORDER BY dcyr_id ASC';
        $getChenYunRen = $Db -> getData($sql);
        $getChenYunRenData = [];
        if(count($getChenYunRen) > 0) foreach($getChenYunRen as $val){
            $getChenYunRenData[] = ['val' => $val['dcyr_name_zh'], 'text' => $val['dcyr_name_zh'].'('.$val['dcyr_name_en'].')'];
        }
        //获取港口
        $sql = 'SELECT dgk_name_en, dgk_name_zh, dgk_name_country FROM '.$Db -> getTableNameAll('data_gangkou').'  GROUP BY dgk_name_zh ORDER BY dgk_id ASC';
        $getGangKou = $Db -> getData($sql);
        $getGangKouData = [];
        if(count($getGangKou) > 0) foreach($getGangKou as $val){
            $getGangKouData[] = ['val' => $val['dgk_name_zh'], 'text' => $val['dgk_name_country']];
        }
        //获取航空公司
        $sql = 'SELECT dhk_name_en, dhk_name_zh FROM '.$Db -> getTableNameAll('data_hangkong').'  GROUP BY dhk_name_zh ORDER BY dhk_id ASC';
        $getHangKong = $Db -> getData($sql);
        $getHangKongData = [];
        if(count($getHangKong) > 0) foreach($getHangKong as $val){
            $getHangKongData[] = ['val' => $val['dhk_name_zh'], 'text' => $val['dhk_name_zh'].'('.$val['dhk_name_en'].')'];
        }
        //获取机场
        $sql = 'SELECT djc_name_en, djc_name_zh FROM '.$Db -> getTableNameAll('data_jichang').'  GROUP BY djc_name_zh ORDER BY djc_id ASC';
        $getJiChang = $Db -> getData($sql);
        $getJiChangData = [];
        if(count($getJiChang) > 0) foreach($getJiChang as $val){
            $getJiChangData[] = ['val' => $val['djc_name_zh'], 'text' => $val['djc_name_zh'].'('.$val['djc_name_en'].')'];
        }
        //获取地区
        $sql = 'SELECT ca_name, IF(ca_code < 10100000000, \'中国\', ca_name_tradi) as ca_name_tradi, ca_name_en FROM '.$Db -> getTableNameAll('data_area').'  GROUP BY ca_name ORDER BY ca_code ASC';
        $getArea = $Db -> getData($sql);
        $getAreaData = [];
        if(count($getArea) > 0) foreach($getArea as $val){
            $getAreaData[] = ['val' => $val['ca_name'], 'text' => $val['ca_name_tradi'].'('.$val['ca_name_en'].')'];
        }
        //输出
        $cacheTime = 86400;
        $dateGmt = gmdate('D, d M Y H:i:s', time() + $cacheTime).' GMT';
        header('Content-type: application/x-javascript');
        header('Expires: '.$dateGmt);
        header('Pragma: cache');
        header('Cache-Control: max-age='.$cacheTime);
        $outPrint = '';
        $outPrint .= 'var DataChenYunRen = '.json_encode($getChenYunRenData).';'."\r\n";
        $outPrint .= 'var DataGangKou = '.json_encode($getGangKouData).';'."\r\n";
        $outPrint .= 'var DataHangKong = '.json_encode($getHangKongData).';'."\r\n";
        $outPrint .= 'var DataJiChang = '.json_encode($getJiChangData).';'."\r\n";
        $outPrint .= 'var DataArea = '.json_encode($getAreaData).';'."\r\n";
        die($outPrint);
    }
}