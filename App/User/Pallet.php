<?php
/**
 * @Copyright (C) 2016.
 * @Description Pallet
 * @FileName Pallet.php
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
use \Libs\Tag\Sql;
use \Libs\Tag\Db;
use \Libs\Tag\Page;
use \Libs\Frame\Url;

class Pallet extends Action{
    private $userInfo = [];     //用户信息
    private $totalNum = 100;    //可以发布总数

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
     * @name getAllowNum
     * @desciption 允许发布数
     * @return int
     */
    public function getAllowNum():int{
        $allowNum = 0;
        $userInfo = $this -> userInfo;
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_air').' WHERE par_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_detect').' WHERE pdt_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_land_szh').' WHERE pls_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_land_zx').' WHERE plz_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_multi_px').' WHERE pmp_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_multi_sh').' WHERE pms_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_multi_zx').' WHERE pmz_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_railway_px').' WHERE prp_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_railway_sh').' WHERE prs_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_railway_zx').' WHERE prz_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_sea_px').' WHERE psp_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_sea_sh').' WHERE pss_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_sea_zx').' WHERE psz_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('pallet_storage').' WHERE pse_isdel=0 AND us_id='.$usId, 'num');
        return $this -> totalNum - $allowNum;
    }

    /**
     * @name main
     * @desciption 主页
     */
    public function main(string $action){
        $Tpl = $this->getTpl();
        $Tpl->assign('totalNum', $this -> totalNum);
        $Tpl->assign('allowNum', $this -> getAllowNum());
        $Tpl->show('User/pallet.html');
    }

    /**
     * @name typeMulti
     * @desciption 多式联运
     */
    public function typeMulti(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $type = From::valTrim('type');
        $save = From::valInt('save');
        if($type == 'zx'){  //整箱
            if($save == 1){
                $data = [];
                $data['pmz_start']              = From::valTrim('pmz_start');        //起始地
                $data['pmz_end']                = From::valTrim('pmz_end');        //目的地
                $data['pmz_huowuzhongwen']      = From::valTrim('pmz_huowuzhongwen');        //货物中文名称
                $data['pmz_type']               = From::valTrim('pmz_type');        //进出口
                $data['pmz_shuxing']            = @implode(',', From::val('pmz_shuxing'));        //货物属性
                $data['pmz_shangmen']           = From::valTrim('pmz_shangmen');        //需要上门取货
                $data['pmz_paytype']            = From::valTrim('pmz_paytype');        //付款方式
                $data['pmz_zouhuo_riqi']        = From::valTrim('pmz_zouhuo_riqi');        //走货日期
                $data['pmz_valid_time']         = From::valTrim('pmz_valid_time');        //报价截止日期
                $data['pmz_remark']             = From::valTrim('pmz_remark');        //备注
                $data['pmz_xiangxing']          = From::valTrim('pmz_xiangxing');        //箱型/箱量
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['pmz_ischeck']            = 0;
                $data['pmz_check_time']         = 0;
                $data['pmz_isdel']              = 0;
                $data['pmz_first_time']         = $currTime;
                $data['pmz_end_time']           = $currTime;
                $result = Sql::tag('pallet_multi_zx', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_multi_zx.html');
        }elseif($type == 'px'){  //拼箱
            if($save == 1){
                $data = [];
                $data['pmp_start']              = From::valTrim('pmp_start');        //起始地
                $data['pmp_end']                = From::valTrim('pmp_end');        //目的地
                $data['pmp_huowuzhongwen']      = From::valTrim('pmp_huowuzhongwen');        //货物中文名称
                $data['pmp_zhonglei']           = From::valTrim('pmp_zhonglei');        //货物种类
                $data['pmp_type']               = From::valTrim('pmp_type');        //进出口
                $data['pmp_shuxing']            = @implode(',', From::val('pmp_shuxing'));        //货物属性
                $data['pmp_zhongliang']         = From::valTrim('pmp_zhongliang');        //总重量吨
                $data['pmp_tiji']               = From::valTrim('pmp_tiji');        //总体积立方米
                $data['pmp_quhuo']              = From::valTrim('pmp_quhuo');        //需要上门取货[1-是,2-否]
                $data['pmp_chuhuoliang_month']  = From::valTrim('pmp_chuhuoliang_month');        //月出货量说明
                $data['pmp_paytype']            = From::valTrim('pmp_paytype');        //付款方式
                $data['pmp_zouhuo_riqi']        = From::valTrim('pmp_zouhuo_riqi');        //走货日期
                $data['pmp_valid_time']         = From::valTrim('pmp_valid_time');        //报价截止日期
                $data['pmp_remark']             = From::valTrim('pmp_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['pmp_ischeck']            = 0;
                $data['pmp_check_time']         = 0;
                $data['pmp_isdel']              = 0;
                $data['pmp_first_time']         = $currTime;
                $data['pmp_end_time']           = $currTime;
                $result = Sql::tag('pallet_multi_px', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_multi_px.html');
        }else{  //sh：散杂货
            if($save == 1){
                $data = [];
                $data['pms_start']              = From::valTrim('pms_start');        //起始地
                $data['pms_end']                = From::valTrim('pms_end');        //目的地
                $data['pms_huowuzhongwen']      = From::valTrim('pms_huowuzhongwen');        //货物中文名称
                $data['pms_type']               = From::valTrim('pms_type');        //进出口
                $data['pms_zhonglei']           = From::valTrim('pms_zhonglei');        //货物种类
                $data['pms_shuxing']            = @implode(',', From::val('pms_shuxing'));        //货物属性
                $data['pms_jianshu_one']        = From::valTrim('pms_jianshu_one');        //货量件数1
                $data['pms_tiji']               = From::valTrim('pms_tiji');        //货量单件体积
                $data['pms_jianshu_two']        = From::valTrim('pms_jianshu_two');        //货量件数2
                $data['pms_dun']                = From::valTrim('pms_dun');        //货量毛重吨
                $data['pms_dantiji']           = From::valTrim('pms_dantiji');        //单件体积
                $data['pms_maozhong']           = From::valTrim('pms_maozhong');        //毛重
                $data['pms_zhuangxie']          = From::valTrim('pms_zhuangxie');        //装卸条款
                $data['pms_dajian']             = From::valTrim('pms_dajian');        //是否超大件[1-是,2-否]
                $data['pms_chuhuoliang_month']  = From::valTrim('pms_chuhuoliang_month');        //月出货量说明
                $data['pms_valid_time']         = From::valTrim('pms_valid_time');        //报价截止日期
                $data['pms_remark']             = From::valTrim('pms_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['pms_ischeck']            = 0;
                $data['pms_check_time']         = 0;
                $data['pms_isdel']              = 0;
                $data['pms_first_time']         = $currTime;
                $data['pms_end_time']           = $currTime;
                $result = Sql::tag('pallet_multi_sh', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_multi_sh.html');
        }
    }

    /**
     * @name typeAir
     * @desciption 揽货空运
     */
    public function typeAir(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $save = From::valInt('save');
        if($save == 1){
            $data = [];
            $data['par_postion']            = From::valTrim('par_postion');        //货物所在地
            $data['par_start']              = From::valTrim('par_start');        //起始地
            $data['par_end']                = From::valTrim('par_end');        //目的地
            $data['par_huowuzhongwen']      = From::valTrim('par_huowuzhongwen');        //货物中文名称
            $data['par_type']               = From::valTrim('par_type');        //进出口
            $data['par_zhonglei']           = From::valTrim('par_zhonglei');        //货物种类
            $data['par_shuxing']            = @implode(',', From::val('par_shuxing'));        //货物属性
            $data['par_chicun']             = From::valTrim('par_chicun');        //货量尺寸
            $data['par_zhongliang']         = From::valTrim('par_zhongliang');        //	货量重量
            $data['par_jizhuangxiang']      = From::valTrim('par_jizhuangxiang');        //货量集装箱
            $data['par_chuhuoliang_month']  = From::valTrim('par_chuhuoliang_month');        //月出货量说明
            $data['par_valid_time']         = From::valTrim('par_valid_time');        //报价截止日期
            $data['par_remark']             = From::valTrim('par_remark');        //备注
            $currTime = Time::getTimeStamp();
            $data['us_id']                  = intval($this -> userInfo['id']);
            $data['par_ischeck']            = 0;
            $data['par_check_time']         = 0;
            $data['par_isdel']              = 0;
            $data['par_first_time']         = $currTime;
            $data['par_end_time']           = $currTime;
            $result = Sql::tag('pallet_air', 'GMY') -> addById($data);
            if($result){
                (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                Tips::show('新增完成！', Link::getLink('pallet'));
            }else{
                Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->assign('chengyunren', Common::getChenYunRen());
        $Tpl->assign('gangkou', Common::getGangKou());
        $Tpl->assign('hangkong', Common::getHangKong());
        $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
        $Tpl->show('User/pallet_air.html');
    }

    /**
     * @name typeDetect
     * @desciption 揽货报关检测
     */
    public function typeDetect(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $save = From::valInt('save');
        if($save == 1){
            $data = [];
            $data['pdt_start']              = From::valTrim('pdt_start');        //操作口岸
            $data['pdt_type']               = From::valTrim('pdt_type');        //进出口[1-出口,2-进口]
            $data['pdt_huowuzhongwen']      = From::valTrim('pdt_huowuzhongwen');        //货物中文名称
            $data['pdt_zhonglei']           = From::valTrim('pdt_zhonglei');        //货物种类
            $data['pdt_shuxing']            = @implode(',', From::val('pdt_shuxing'));        //货物属性
            $data['pdt_fuwuxiangmu']        = From::valTrim('pdt_fuwuxiangmu');        //服务项目
            $data['pdt_valid_time']         = From::valTrim('pdt_valid_time');        //报价截止日期
            $data['pdt_remark']             = From::valTrim('pdt_remark');        //备注
            $currTime = Time::getTimeStamp();
            $data['us_id']                  = intval($this -> userInfo['id']);
            $data['pdt_ischeck']            = 0;
            $data['pdt_check_time']         = 0;
            $data['pdt_isdel']              = 0;
            $data['pdt_first_time']         = $currTime;
            $data['pdt_end_time']           = $currTime;
            $result = Sql::tag('pallet_detect', 'GMY') -> addById($data);
            if($result){
                (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                Tips::show('新增完成！', Link::getLink('pallet'));
            }else{
                Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->assign('chengyunren', Common::getChenYunRen());
        $Tpl->assign('gangkou', Common::getGangKou());
        $Tpl->assign('hangkong', Common::getHangKong());
        $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
        $Tpl->show('User/pallet_detect.html');
    }

    /**
     * @name typeLand
     * @desciption 揽货公路运输
     */
    public function typeLand(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $type = From::valTrim('type');
        $save = From::valInt('save');
        if($type == 'zx'){  //整箱
            if($save == 1){
                $data = [];
                $data['plz_start']              = From::valTrim('plz_start');        //起始地
                $data['plz_end']                = From::valTrim('plz_end');        //目的地
                $data['plz_huowuzhongwen']      = From::valTrim('plz_huowuzhongwen');        //货物中文名称
                $data['plz_type']               = From::valTrim('plz_type');        //进出口
                $data['plz_shuxing']            = @implode(',', From::val('plz_shuxing'));        //货物属性
                $data['plz_xiangxing']          = From::valTrim('plz_xiangxing');        //箱型/箱量
                $data['plz_chuhuoliang_month']  = From::valTrim('plz_chuhuoliang_month');        //月出货量说明
                $data['plz_shangmen']           = From::valTrim('plz_shangmen');        //需要上门取货
                $data['plz_paytype']            = From::valTrim('plz_paytype');        //付款方式
                $data['plz_zouhuo_riqi']        = From::valTrim('plz_zouhuo_riqi');        //需要上门取货
                $data['plz_valid_time']         = From::valTrim('plz_valid_time');        //报价截止日期
                $data['plz_remark']             = From::valTrim('plz_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['plz_ischeck']            = 0;
                $data['plz_check_time']         = 0;
                $data['plz_isdel']              = 0;
                $data['plz_first_time']         = $currTime;
                $data['plz_end_time']           = $currTime;
                $result = Sql::tag('pallet_land_zx', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_land_zx.html');
        }else{  //szh：散杂货
            if($save == 1){
                $data = [];
                $data['pls_start']              = From::valTrim('pls_start');        //起始地
                $data['pls_end']                = From::valTrim('pls_end');        //目的地
                $data['pls_huowuzhongwen']      = From::valTrim('pls_huowuzhongwen');        //货物中文名称
                $data['pls_type']               = From::valTrim('pls_type');        //进出口
                $data['pls_shuxing']            = @implode(',', From::val('pls_shuxing'));        //货物属性
                $data['pls_jianshu_one']          = From::valTrim('pls_jianshu_one');        //货量件数1
                $data['pls_tiji']          = From::valTrim('pls_tiji');        //货量单件体积
                $data['pls_jianshu_two']          = From::valTrim('pls_jianshu_two');        //货量件数2
                $data['pls_dun']           = From::valTrim('pls_dun');        //货量毛重吨
                $data['pls_dantiji']           = From::valTrim('pls_dantiji');        //单件体积
                $data['pls_maozhong']           = From::valTrim('pls_maozhong');        //毛重
                $data['pls_zhuangxie']            = From::valTrim('pls_zhuangxie');        //装卸条款
                $data['pls_dajian']        = From::valTrim('pls_dajian');        //是否超大件
                $data['pls_chuhuoliang_month']  = From::valTrim('pls_chuhuoliang_month');        //月出货量说明
                $data['pls_valid_time']         = From::valTrim('pls_valid_time');        //报价截止日期
                $data['pls_remark']             = From::valTrim('pls_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['pls_ischeck']            = 0;
                $data['pls_check_time']         = 0;
                $data['pls_isdel']              = 0;
                $data['pls_first_time']         = $currTime;
                $data['pls_end_time']           = $currTime;
                $result = Sql::tag('pallet_land_szh', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_land_szh.html');
        }
    }

    /**
     * @name typeRailway
     * @desciption 揽货铁运
     */
    public function typeRailway(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $type = From::valTrim('type');
        $save = From::valInt('save');
        if($type == 'zx'){  //整箱
            if($save == 1){
                $data = [];
                $data['prz_start']              = From::valTrim('prz_start');        //起始地
                $data['prz_end']                = From::valTrim('prz_end');        //目的地
                $data['prz_huowuzhongwen']      = From::valTrim('prz_huowuzhongwen');        //货物中文名称
                $data['prz_type']               = From::valTrim('prz_type');        //进出口
                $data['prz_shuxing']            = @implode(',', From::val('prz_shuxing'));        //货物属性
                $data['prz_xiangxing']          = From::valTrim('prz_xiangxing');        //箱型/箱量
                $data['prz_chuhuoliang_month']  = From::valTrim('prz_chuhuoliang_month');        //月出货量说明
                $data['prz_shangmen']           = From::valTrim('prz_shangmen');        //需要上门取货
                $data['prz_paytype']            = From::valTrim('prz_paytype');        //付款方式
                $data['prz_zouhuo_riqi']        = From::valTrim('prz_zouhuo_riqi');        //需要上门取货
                $data['prz_valid_time']         = From::valTrim('prz_valid_time');        //报价截止日期
                $data['prz_remark']             = From::valTrim('prz_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['prz_ischeck']            = 0;
                $data['prz_check_time']         = 0;
                $data['prz_isdel']              = 0;
                $data['prz_first_time']         = $currTime;
                $data['prz_end_time']           = $currTime;
                $result = Sql::tag('pallet_railway_zx', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_railway_zx.html');
        }elseif($type == 'px'){  //拼箱
            if($save == 1){
                $data = [];
                $data['prp_start']              = From::valTrim('prp_start');        //起始地
                $data['prp_end']                = From::valTrim('prp_end');        //目的地
                $data['prp_huowuzhongwen']      = From::valTrim('prp_huowuzhongwen');        //货物中文名称
                $data['prp_zhonglei']           = From::valTrim('prp_zhonglei');        //货物种类
                $data['prp_type']               = From::valTrim('prp_type');        //进出口
                $data['prp_shuxing']            = @implode(',', From::val('prp_shuxing'));        //货物属性
                $data['prp_zhongliang']         = From::valTrim('prp_zhongliang');        //总重量吨
                $data['prp_tiji']               = From::valTrim('prp_tiji');        //总体积立方米
                $data['prp_quhuo']              = From::valTrim('prp_quhuo');        //需要上门取货[1-是,2-否]
                $data['prp_chuhuoliang_month']  = From::valTrim('prp_chuhuoliang_month');        //月出货量说明
                $data['prp_paytype']            = From::valTrim('prp_paytype');        //付款方式
                $data['prp_zouhuo_riqi']        = From::valTrim('prp_zouhuo_riqi');        //走货日期
                $data['prp_valid_time']         = From::valTrim('prp_valid_time');        //报价截止日期
                $data['prp_remark']             = From::valTrim('prp_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['prp_ischeck']            = 0;
                $data['prp_check_time']         = 0;
                $data['prp_isdel']              = 0;
                $data['prp_first_time']         = $currTime;
                $data['prp_end_time']           = $currTime;
                $result = Sql::tag('pallet_railway_px', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_railway_px.html');
        }else{  //sh：散杂货
            if($save == 1){
                $data = [];
                $data['prs_start']              = From::valTrim('prs_start');        //起始地
                $data['prs_end']                = From::valTrim('prs_end');        //目的地
                $data['prs_huowuzhongwen']      = From::valTrim('prs_huowuzhongwen');        //货物中文名称
                $data['prs_type']               = From::valTrim('prs_type');        //进出口
                $data['prs_zhonglei']           = From::valTrim('prs_zhonglei');        //货物种类
                $data['prs_shuxing']            = @implode(',', From::val('prs_shuxing'));        //货物属性
                $data['prs_jianshu_one']        = From::valTrim('prs_jianshu_one');        //货量件数1
                $data['prs_tiji']               = From::valTrim('prs_tiji');        //货量单件体积
                $data['prs_jianshu_two']        = From::valTrim('prs_jianshu_two');        //货量件数2
                $data['prs_dun']                = From::valTrim('prs_dun');        //货量毛重吨
                $data['prs_dantiji']           = From::valTrim('prs_dantiji');        //单件体积
                $data['prs_maozhong']           = From::valTrim('prs_maozhong');        //毛重
                $data['prs_zhuangxie']          = From::valTrim('prs_zhuangxie');        //装卸条款
                $data['prs_dajian']             = From::valTrim('prs_dajian');        //是否超大件[1-是,2-否]
                $data['prs_valid_time']         = From::valTrim('prs_valid_time');        //报价截止日期
                $data['prs_remark']             = From::valTrim('prs_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['prs_ischeck']            = 0;
                $data['prs_check_time']         = 0;
                $data['prs_isdel']              = 0;
                $data['prs_first_time']         = $currTime;
                $data['prs_end_time']           = $currTime;
                $result = Sql::tag('pallet_railway_sh', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_railway_sh.html');
        }
    }

    /**
     * @name typeSea
     * @desciption 揽货海运
     */
    public function typeSea(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $type = From::valTrim('type');
        $save = From::valInt('save');
        if($type == 'zx'){  //集装箱整箱
            if($save == 1){
                $data = [];
                $data['psz_tiaokuan']           = From::valTrim('psz_tiaokuan');        //运输条款
                $data['psz_start']              = From::valTrim('psz_start');        //起始地
                $data['psz_end']                = From::valTrim('psz_end');        //目的地
                $data['psz_huowuzhongwen']      = From::valTrim('psz_huowuzhongwen');        //货物中文名称
                $data['psz_zhonglei']           = From::valTrim('psz_zhonglei');        //货物种类
                $data['psz_xiangxing']          = From::valTrim('psz_xiangxing');        //箱型/箱量
                $data['psz_shuxing']            = @implode(',', From::val('psz_shuxing'));        //货物属性
                $data['psz_zouhuo_riqi']        = From::valTrim('psz_zouhuo_riqi');        //走货日期
                $data['psz_chuhuoliang_month']  = From::valTrim('psz_chuhuoliang_month');        //月出货量说明
                $data['psz_valid_time']         = From::valTrim('psz_valid_time');        //报价截止日期
                $data['psz_remark']             = From::valTrim('psz_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['psz_ischeck']            = 0;
                $data['psz_check_time']         = 0;
                $data['psz_isdel']              = 0;
                $data['psz_first_time']         = $currTime;
                $data['psz_end_time']           = $currTime;
                $result = Sql::tag('pallet_sea_zx', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_sea_zx.html');
        }elseif($type == 'px'){  //集装箱拼箱
            if($save == 1){
                $data = [];
                $data['psp_tiaokuan']           = From::valTrim('psp_tiaokuan');        //运输条款
                $data['psp_start']              = From::valTrim('psp_start');        //起始地
                $data['psp_end']                = From::valTrim('psp_end');        //目的地
                $data['psp_huowuzhongwen']      = From::valTrim('psp_huowuzhongwen');        //货物中文名称
                $data['psp_zhonglei']           = From::valTrim('psp_zhonglei');        //货物种类
                $data['psp_type']               = From::valTrim('psp_type');        //进出口
                $data['psp_shuxing']            = @implode(',', From::val('psp_shuxing'));        //货物属性
                $data['psp_zhongliang']         = From::valTrim('psp_zhongliang');        //总重量吨
                $data['psp_tiji']               = From::valTrim('psp_tiji');        //总体积立方米
                $data['psp_quhuo']              = From::valTrim('psp_quhuo');        //需要上门取货[1-是,2-否]
                $data['psp_chuhuoliang_month']  = From::valTrim('psp_chuhuoliang_month');        //月出货量说明
                $data['psp_paytype']            = From::valTrim('psp_paytype');        //付款方式
                $data['psp_zouhuo_riqi']        = From::valTrim('psp_zouhuo_riqi');        //走货日期
                $data['psp_valid_time']         = From::valTrim('psp_valid_time');        //报价截止日期
                $data['psp_remark']             = From::valTrim('psp_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['psp_ischeck']            = 0;
                $data['psp_check_time']         = 0;
                $data['psp_isdel']              = 0;
                $data['psp_first_time']         = $currTime;
                $data['psp_end_time']           = $currTime;
                $result = Sql::tag('pallet_sea_px', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_sea_px.html');
        }else{  //sh：大宗散杂
            if($save == 1){
                $data = [];
                $data['pss_tiaokuan']           = From::valTrim('pss_tiaokuan');        //运输条款
                $data['pss_start']              = From::valTrim('pss_start');        //起始地
                $data['pss_end']                = From::valTrim('pss_end');        //目的地
                $data['pss_huowuzhongwen']      = From::valTrim('pss_huowuzhongwen');        //货物中文名称
                $data['pss_zhonglei']           = From::valTrim('pss_zhonglei');        //货物种类
                $data['pss_type']               = From::valTrim('pss_type');        //进出口
                $data['pss_shuxing']            = @implode(',', From::val('pss_shuxing'));        //货物属性
                $data['pss_jianshu_one']        = From::valTrim('pss_jianshu_one');        //货量件数1
                $data['pss_tiji']               = From::valTrim('pss_tiji');        //货量单件体积
                $data['pss_jianshu_two']        = From::valTrim('pss_jianshu_two');        //货量件数2
                $data['pss_dun']                = From::valTrim('pss_dun');        //货量毛重吨
                $data['pss_dantiji']           = From::valTrim('pss_dantiji');        //单件体积
                $data['pss_maozhong']           = From::valTrim('pss_maozhong');        //毛重
                $data['pss_zhuangxie']          = From::valTrim('pss_zhuangxie');        //装卸条款
                $data['pss_dajian']             = From::valTrim('pss_dajian');        //是否超大件[1-是,2-否]
                $data['pss_chuhuoliang_month']  = From::valTrim('pss_chuhuoliang_month');        //月出货量说明
                $data['pss_zouhuo_riqi']        = From::valTrim('pss_zouhuo_riqi');        //走货日期
                $data['pss_valid_time']         = From::valTrim('pss_valid_time');        //报价截止日期
                $data['pss_remark']             = From::valTrim('pss_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['pss_ischeck']            = 0;
                $data['pss_check_time']         = 0;
                $data['pss_isdel']              = 0;
                $data['pss_first_time']         = $currTime;
                $data['pss_end_time']           = $currTime;
                $result = Sql::tag('pallet_sea_sh', 'GMY') -> addById($data);
                if($result){
                    (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                    Tips::show('新增完成！', Link::getLink('pallet'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/pallet_sea_ph.html');
        }
    }

    /**
     * @name typeStorage
     * @desciption 揽货仓储
     */
    public function typeStorage(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $save = From::valInt('save');
        if($save == 1){
            $data = [];
            $data['pse_start']              = From::valTrim('pse_start');        //操作口岸
            $data['pse_huowuzhongwen']      = From::valTrim('pse_huowuzhongwen');        //货物中文名称
            $data['pse_zhonglei']           = From::valTrim('pse_zhonglei');        //货物种类
            $data['pse_shuxing']            = @implode(',', From::val('pse_shuxing'));        //货物属性
            $data['pse_leixing']            = From::valTrim('pse_leixing');        //仓储类型
            $data['pse_zhouqi_start']       = From::valTrim('pse_zhouqi_start');        //仓储周期start
            $data['pse_zhouqi_end']         = From::valTrim('pse_zhouqi_end');        //仓储周期end
            $data['pse_valid_time']         = From::valTrim('pse_valid_time');        //报价截止日期
            $data['pse_remark']             = From::valTrim('pse_remark');        //备注
            $currTime = Time::getTimeStamp();
            $data['us_id']                  = intval($this -> userInfo['id']);
            $data['pse_ischeck']            = 0;
            $data['pse_check_time']         = 0;
            $data['pse_isdel']              = 0;
            $data['pse_first_time']         = $currTime;
            $data['pse_end_time']           = $currTime;
            $result = Sql::tag('pallet_storage', 'GMY') -> addById($data);
            if($result){
                (new UserData()) -> addSetGgt('HUOPAN_REL', '发布货盘获得');
                Tips::show('新增完成！', Link::getLink('pallet'));
            }else{
                Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->assign('chengyunren', Common::getChenYunRen());
        $Tpl->assign('gangkou', Common::getGangKou());
        $Tpl->assign('hangkong', Common::getHangKong());
        $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
        $Tpl->show('User/pallet_storage.html');
    }

    /**
     * @name manage
     * @desciption 管理
     */
    public function manage(string $action){
        $Tpl = $this->getTpl();
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $userInfo = $this -> userInfo;
        $usId = $userInfo['id'];
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId;
        $type = From::valTrim('type');
        $stype = From::valTrim('stype');    //子类
        $Tpl -> assign('type', $type);
        $Tpl -> assign('stype', $stype);
        $Page -> setQuery('type', $type);
        $Page -> setQuery('stype', $stype);
        switch ($type){
            case 'railway':{    //铁路
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if($stype == 'px'){ //拼箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prp_id='.$id.' AND prp_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_railway_px').' SET prp_isdel=1, prp_end_time='.$currTime.$whereString;
                    }else if($stype == 'sh'){   //散杂货
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prs_id='.$id.' AND prs_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_railway_sh').' SET prs_isdel=1, prs_end_time='.$currTime.$whereString;
                    }else{  //整箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prz_id='.$id.' AND prz_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_railway_zx').' SET prz_isdel=1, prz_end_time='.$currTime.$whereString;
                    }
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('pallet').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                            $whereString .= ($whereString == ''?'':' AND ').'prs_start like \'%'.addslashes($start).'%\'';
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
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,prs_id as id, prs_start as start, prs_end as end, prs_huowuzhongwen as huowuzhongwen, prs_type as type, prs_first_time as first_time, prs_valid_time as valid_time, prs_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_sh').$whereString.' ORDER BY prs_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //整箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND prz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,prz_id as id, prz_start as start, prz_end as end, prz_huowuzhongwen as huowuzhongwen, prz_type as type, prz_first_time as first_time, prz_valid_time as valid_time, prz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_railway_zx').$whereString.' ORDER BY prz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                $tplHtml = 'User/pallet_manage_railway.html';
                break;
            }
            case 'sea':{    //海运:
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if($stype == 'px'){ //拼箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND psp_id='.$id.' AND psp_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_sea_px').' SET psp_isdel=1, psp_end_time='.$currTime.$whereString;
                    }else if($stype == 'sh'){   //散杂货
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pss_id='.$id.' AND pss_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_sea_sh').' SET pss_isdel=1, pss_end_time='.$currTime.$whereString;
                    }else{  //整箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND psz_id='.$id.' AND psz_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_sea_zx').' SET psz_isdel=1, psz_end_time='.$currTime.$whereString;
                    }
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('pallet').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pss_id as id, pss_end as end, pss_huowuzhongwen as huowuzhongwen, pss_type as type, pss_first_time as first_time, pss_valid_time as valid_time, pss_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_sh').$whereString.' ORDER BY pss_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //整箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND psz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,psz_id as id, psz_start as start, psz_end as end, psz_huowuzhongwen as huowuzhongwen, psz_first_time as first_time, psz_valid_time as valid_time, psz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_sea_zx').$whereString.' ORDER BY psz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                $tplHtml = 'User/pallet_manage_sea.html';
                break;
            }
            case 'air':{    //空运
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND par_id='.$id.' AND par_isdel=0';
                    $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_air').' SET par_isdel=1, par_end_time='.$currTime.$whereString;
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('pallet').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                $tplHtml = 'User/pallet_manage_air.html';
                break;
            }
            case 'land':{    //公路运输
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if($stype == 'sh'){   //散杂货
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pls_id='.$id.' AND pls_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_land_szh').' SET pls_isdel=1, pls_end_time='.$currTime.$whereString;
                    }else{  //整箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND plz_id='.$id.' AND plz_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_land_zx').' SET plz_isdel=1, plz_end_time='.$currTime.$whereString;
                    }
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('pallet').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                $tplHtml = 'User/pallet_manage_land.html';
                break;
            }
            case 'storage':{    //仓储
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pse_id='.$id.' AND pse_isdel=0';
                    $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_storage').' SET pse_isdel=1, pse_end_time='.$currTime.$whereString;
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('pallet').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                $tplHtml = 'User/pallet_manage_storage.html';
                break;
            }
            case 'detect':{    //报关报检
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pdt_id='.$id.' AND pdt_isdel=0';
                    $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_detect').' SET pdt_isdel=1, pdt_end_time='.$currTime.$whereString;
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('pallet').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                $tplHtml = 'User/pallet_manage_detect.html';
                break;
            }
            default:{   //multi-多式联运
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if($stype == 'px'){ //拼箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pmp_id='.$id.' AND pmp_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_multi_px').' SET pmp_isdel=1, pmp_end_time='.$currTime.$whereString;
                    }else if($stype == 'sh'){   //散杂货
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pms_id='.$id.' AND pms_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_multi_sh').' SET pms_isdel=1, pms_end_time='.$currTime.$whereString;
                    }else{  //整箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pmz_id='.$id.' AND pmz_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('pallet_multi_zx').' SET pmz_isdel=1, pmz_end_time='.$currTime.$whereString;
                    }
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('pallet').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                            $whereString .= ($whereString == ''?'':' AND ').'pms_start like \'%'.addslashes($start).'%\'';
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
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pms_id as id, pms_start as start, pms_end as end, pms_huowuzhongwen as huowuzhongwen, pms_type as type, pms_first_time as first_time, pms_valid_time as valid_time, pms_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_sh').$whereString.' ORDER BY pms_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //整箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND pmz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,pmz_id as id, pmz_start as start, pmz_end as end, pmz_huowuzhongwen as huowuzhongwen, pmz_type as type, pmz_first_time as first_time, pmz_valid_time as valid_time, pmz_ischeck as ischeck FROM '.$Db -> getTableNameAll('pallet_multi_zx').$whereString.' ORDER BY pmz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                $tplHtml = 'User/pallet_manage.html';
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
        $pageList = $Page -> getPage(Url::getUrlAction(From::valTrim('A')));
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        $Tpl->show($tplHtml);
    }

    /**
     * @name recv
     * @desciption 收到的询单
     */
    public function recv(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/pallet_recv.html');
    }

    /**
     * @name send
     * @desciption 我的询单
     */
    public function send(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/pallet_send.html');
    }
}