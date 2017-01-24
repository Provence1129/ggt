<?php
/**
 * @Copyright (C) 2016.
 * @Description Tariffs
 * @FileName Tariffs.php
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

class Tariffs extends Action{
    private $userInfo = [];     //用户信息
    private $totalNum = 200;    //可以发布总数

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
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_air_gj').' WHERE taj_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_air_gn').' WHERE tan_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_detect').' WHERE tdt_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_land_jktc').' WHERE tlj_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_land_ld').' WHERE tld_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_land_zx').' WHERE tlz_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_multi_jzx').' WHERE tmj_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_multi_zc').' WHERE tmz_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_railway_jzx').' WHERE trj_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_railway_zc').' WHERE trz_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_sea_px').' WHERE tsp_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_sea_sh').' WHERE tsh_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_sea_zx').' WHERE tsz_isdel=0 AND us_id='.$usId, 'num');
        $allowNum += $Db -> getDataInt('SELECT COUNT(*) AS num FROM '.$Db -> getTableNameAll('tariffs_storage').' WHERE tst_isdel=0 AND us_id='.$usId, 'num');
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
        $Tpl->show('User/tariffs.html');
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
        if($type == 'jzx'){  //集装箱
            if($save == 1){
                $data_tmj_end               = From::val('tmj_end');        //卸运站
                $data_tmj_tujingquyu        = From::val('tmj_tujingquyu');        //途经区域
                $data_tmj_n20               = From::val('tmj_n20');        //运输价格20
                $data_tmj_n40gp             = From::val('tmj_n40gp');        //运输价格40GP
                $data_tmj_n40hq             = From::val('tmj_n40hq');        //运输价格40HQ
                $data_tmj_n45hc             = From::val('tmj_n45hc');        //运输价格45HC
                $data_tmj_zaizhong          = @implode(',', From::val('tmj_zaizhong'));        //载重
                $data = [];
                $data['tmj_start']          = From::valTrim('tmj_start');        //起始站
                $data['tmj_paytype']         = @implode(',', From::val('tmj_paytype'));        //付款方式
                $data['tmj_valid_time']     = From::valTrim('tmj_valid_time');        //运价有效日期
                $data['tmj_remark']         = From::valTrim('tmj_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tmj_ischeck']            = 0;
                $data['tmj_check_time']         = 0;
                $data['tmj_isdel']              = 0;
                $data['tmj_first_time']         = $currTime;
                $data['tmj_end_time']           = $currTime;
                $data['tmj_zaizhong']           = $data_tmj_zaizhong;
                $result = FALSE;
                if(count($data_tmj_end) > 0) foreach($data_tmj_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tmj_end']            = $val;
                    $data['tmj_tujingquyu']     = $data_tmj_tujingquyu[$key] ?? '';
                    $data['tmj_n20']            = $data_tmj_n20[$key] ?? '';
                    $data['tmj_n40gp']          = $data_tmj_n40gp[$key] ?? '';
                    $data['tmj_n40hq']          = $data_tmj_n40hq[$key] ?? '';
                    $data['tmj_n45hc']          = $data_tmj_n45hc[$key] ?? '';
                    $tmp = Sql::tag('tariffs_multi_jzx', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_multi_jzx.html');
        }else{  //dzs：大宗散杂
            if($save == 1){
                $data_tmz_end                   = From::val('tmz_end');        //目的站
                $data_tmz_tujingquyu            = From::val('tmz_tujingquyu');        //途经区域
                $data_tmz_yunshujiage           = From::val('tmz_yunshujiage');        //运输价格
                $data_tmz_yunshujiage_danwei    = From::val('tmz_yunshujiage_danwei');        //运输价格单位
                $data_tmz_chepi                 = From::val('tmz_chepi');        //车皮类型
                $data_tmz_chepi_danwei          = From::val('tmz_chepi_danwei');        //车皮类型单位
                $data_tmz_zaizhong              = From::val('tmz_zaizhong');        //载重
                $data_tmz_tiji                  = From::val('tmz_tiji');        //体积
                $data = [];
                $data['tmz_start']              = From::valTrim('tmz_start');        //起始站
                $data['tmz_paytype']            = @implode(',', From::val('tmz_paytype'));        //付款方式
                $data['tmz_valid_time']         = From::valTrim('tmz_valid_time');        //运价有效日期
                $data['tmz_remark']             = From::valTrim('tmz_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tmz_ischeck']            = 0;
                $data['tmz_check_time']         = 0;
                $data['tmz_isdel']              = 0;
                $data['tmz_first_time']         = $currTime;
                $data['tmz_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_tmz_end) > 0) foreach($data_tmz_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tmz_end']            = $val;
                    $data['tmz_tujingquyu']     = $data_tmz_tujingquyu[$key] ?? '';
                    $data['tmz_yunshujiage']    = $data_tmz_yunshujiage[$key] ?? '';
                    $data['tmz_yunshujiage_danwei'] = $data_tmz_yunshujiage_danwei[$key] ?? '';
                    $data['tmz_chepi']          = $data_tmz_chepi[$key] ?? '';
                    $data['tmz_chepi_danwei']   = $data_tmz_chepi_danwei[$key] ?? '';
                    $data['tmz_zaizhong']       = $data_tmz_zaizhong[$key] ?? '';
                    $data['tmz_tiji']           = $data_tmz_tiji[$key] ?? '';
                    $tmp = Sql::tag('tariffs_multi_zc', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_multi_zc.html');
        }
    }

    /**
     * @name typeAir
     * @desciption 揽货空运
     */
    public function typeAir(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $type = From::valTrim('type');
        $save = From::valInt('save');
        if($type == 'gj'){  //国际
            if($save == 1){
                $data_taj_end                   = From::val('taj_end');        //目的地
                $data_taj_min                   = From::val('taj_min');        //min
                $data_taj_n_45                  = From::val('taj_n_45');        //-45
                $data_taj_n45                   = From::val('taj_n45');        //45
                $data_taj_n100                  = From::val('taj_n100');        //100
                $data_taj_n300                  = From::val('taj_n300');        //300
                $data_taj_n500                  = From::val('taj_n500');        //500
                $data_taj_n1000                 = From::val('taj_n1000');        //100
                //$data_taj_hangweek              = From::val('taj_hangweek');        //航班周期
                $data_taj_yundi_time            = From::val('taj_yundi_time');        //运抵时间
                $data_taj_iszhongzhuan          = From::val('taj_iszhongzhuan');        //是否中转
                $data = [];
                $data['taj_start']              = From::valTrim('taj_start');        //起始地
                $data['taj_hangkong']           = From::valTrim('taj_hangkong');        //航空公司
                $data['taj_paytype']            = @implode(',', From::val('taj_paytype'));        //付款方式
                $data['taj_valid_time']         = From::valTrim('taj_valid_time');        //运价有效日期
                $data['taj_remark']             = From::valTrim('taj_remark');        //备注
                $data['taj_hangweek']           = @implode(',', From::val('taj_hangweek'));        //航班周期
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['taj_ischeck']            = 0;
                $data['taj_check_time']         = 0;
                $data['taj_isdel']              = 0;
                $data['taj_first_time']         = $currTime;
                $data['taj_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_taj_end) > 0) foreach($data_taj_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['taj_end']            = $val;
                    $data['taj_min']            = $data_taj_min[$key] ?? '';
                    $data['taj_n_45']           = $data_taj_n_45[$key] ?? '';
                    $data['taj_n45']            = $data_taj_n45[$key] ?? '';
                    $data['taj_n100']           = $data_taj_n100[$key] ?? '';
                    $data['taj_n300']           = $data_taj_n300[$key] ?? '';
                    $data['taj_n500']           = $data_taj_n500[$key] ?? '';
                    $data['taj_n1000']          = $data_taj_n1000[$key] ?? '';
                    //$data['taj_hangweek']       = $data_taj_hangweek[$key] ?? '';
                    $data['taj_yundi_time']     = $data_taj_yundi_time[$key] ?? '';
                    $data['taj_iszhongzhuan']   = $data_taj_iszhongzhuan[$key] ?? '';
                    $tmp = Sql::tag('tariffs_air_gj', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_air_gj.html');
        }else{  //gn：国内
            if($save == 1){
                $data_tan_end                   = From::val('tan_end');        //目的地
                $data_tan_min                   = From::val('tan_min');        //min
                $data_tan_n_45                  = From::val('tan_n_45');        //-45
                $data_tan_n45                   = From::val('tan_n45');        //45
                $data_tan_n100                  = From::val('tan_n100');        //100
                $data_tan_n300                  = From::val('tan_n300');        //300
                $data_tan_n500                  = From::val('tan_n500');        //500
                $data_tan_n1000                 = From::val('tan_n1000');        //100
                //$data_tan_hangweek              = From::val('tan_hangweek');        //航班周期
                $data_tan_yundi_time            = From::val('tan_yundi_time');        //运抵时间
                $data_tan_iszhongzhuan          = From::val('tan_iszhongzhuan');        //是否中转
                $data = [];
                $data['tan_start']              = From::valTrim('tan_start');        //起始地
                $data['tan_hangkong']           = From::valTrim('tan_hangkong');        //航空公司
                $data['tan_paytype']            = @implode(',', From::val('tan_paytype'));        //付款方式
                $data['tan_valid_time']         = From::valTrim('tan_valid_time');        //运价有效日期
                $data['tan_remark']             = From::valTrim('tan_remark');        //备注
                $data['tan_hangweek']           = @implode(',', From::val('tan_hangweek'));        //航班周期
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tan_ischeck']            = 0;
                $data['tan_check_time']         = 0;
                $data['tan_isdel']              = 0;
                $data['tan_first_time']         = $currTime;
                $data['tan_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_tan_end) > 0) foreach($data_tan_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tan_end']            = $val;
                    $data['tan_min']            = $data_tan_min[$key] ?? '';
                    $data['tan_n_45']           = $data_tan_n_45[$key] ?? '';
                    $data['tan_n45']            = $data_tan_n45[$key] ?? '';
                    $data['tan_n100']           = $data_tan_n100[$key] ?? '';
                    $data['tan_n300']           = $data_tan_n300[$key] ?? '';
                    $data['tan_n500']           = $data_tan_n500[$key] ?? '';
                    $data['tan_n1000']          = $data_tan_n1000[$key] ?? '';
                    //$data['tan_hangweek']       = $data_tan_hangweek[$key] ?? '';
                    $data['tan_yundi_time']     = $data_tan_yundi_time[$key] ?? '';
                    $data['tan_iszhongzhuan']   = $data_tan_iszhongzhuan[$key] ?? '';
                    $tmp = Sql::tag('tariffs_air_gn', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_air_gn.html');
        }
    }

    /**
     * @name typeDetect
     * @desciption 揽货报关检测
     */
    public function typeDetect(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $type = From::valTrim('type');
        $save = From::valInt('save');
        if($save == 1){
            $data = [];
            $data['tdt_title']              = From::valTrim('tdt_title');        //标题
            $data['tdt_gangkou']            = From::valTrim('tdt_gangkou');        //报关港口
            $data['tdt_huolei']             = @implode(',', From::val('tdt_huolei'));        //货类
            $data['tdt_fanwei']             = @implode(',', From::val('tdt_fanwei'));        //服务范围
            $data['tdt_baoguanfei']         = From::valTrim('tdt_baoguanfei');        //报关费元/票
            $data['tdt_baoguanfei_remark']  = From::valTrim('tdt_baoguanfei_remark');        //报关费备注
            $data['tdt_baojiandaili']       = From::valTrim('tdt_baojiandaili');        //报检代理费元/票
            $data['tdt_baojiandaili_remark'] = From::valTrim('tdt_baojiandaili_remark');        //报检代理备注
            $data['tdt_baoguanyulu']        = From::valTrim('tdt_baoguanyulu');        //报关预录费元/张
            $data['tdt_baoguanyulu_remark'] = From::valTrim('tdt_baoguanyulu_remark');        //报关预录费备注
            $data['tdt_haiguan']            = From::valTrim('tdt_haiguan');        //海关查验代理费元/票
            $data['tdt_haiguan_remark']     = From::valTrim('tdt_haiguan_remark');        //海关查验代理费备注
            $data['tdt_sanjian']            = From::valTrim('tdt_sanjian');        //三检费用
            $data['tdt_tongguandan']        = From::valTrim('tdt_tongguandan');        //通关单预录费元/张
            $data['tdt_xiangjian20']        = From::valTrim('tdt_xiangjian20');        //箱检费元/20
            $data['tdt_xiangjian40']        = From::valTrim('tdt_xiangjian40');        //箱检费元/40
            $data['tdt_haiguan_chayan']     = From::valTrim('tdt_haiguan_chayan');        //海关查验场地费
            $data['tdt_haiguan_taoxiang']   = From::valTrim('tdt_haiguan_taoxiang');        //海关查验掏箱费
            $data['tdt_dongjian20']         = From::valTrim('tdt_dongjian20');        //动检查验场地费元/20
            $data['tdt_dongjian40']         = From::valTrim('tdt_dongjian40');        //动检查验场地费元/40
            $data['tdt_dongjian_chayan']    = From::valTrim('tdt_dongjian_chayan');        //动检查验掏箱费
            $data['tdt_mian3c']             = From::valTrim('tdt_mian3c');        //免3C申请费元/张
            $data['tdt_mianshuishenqing']   = From::valTrim('tdt_mianshuishenqing');        //免税表申请费元/票
            $data['tdt_mianshuiyulu']       = From::valTrim('tdt_mianshuiyulu');        //免税表预录费张
            $data['tdt_type']               = $type == 'in' ? 2 : 1;
            $currTime = Time::getTimeStamp();
            $data['us_id']                  = intval($this -> userInfo['id']);
            $data['tdt_ischeck']            = 0;
            $data['tdt_check_time']         = 0;
            $data['tdt_isdel']              = 0;
            $data['tdt_first_time']         = $currTime;
            $data['tdt_end_time']           = $currTime;
            $result = Sql::tag('tariffs_detect', 'GMY') -> addById($data);
            if($result){
                (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                Tips::show('新增成功！', Link::getLink('tariffs'));
            }else{
                Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->assign('chengyunren', Common::getChenYunRen());
        $Tpl->assign('gangkou', Common::getGangKou());
        $Tpl->assign('hangkong', Common::getHangKong());
        $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
        if($type == 'in'){  //进口
            $Tpl->show('User/tariffs_detect_in.html');
        }else{  //out：出口
            $Tpl->show('User/tariffs_detect_out.html');
        }
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
        if($type == 'jktc'){  //集卡拖车
            if($save == 1){
                $data_tlj_end                  = From::val('tlj_end');        //目的地
                $data_tlj_gp20                 = From::val('tlj_gp20');        //20GP
                $data_tlj_gp40                 = From::val('tlj_gp40');        //40GP
                $data_tlj_shuangtuo            = From::val('tlj_shuangtuo');        //双拖
                $data_tlj_zaizhong             = From::val('tlj_zaizhong');        //载重

                $data = [];
                $data['tlj_start']              = From::valTrim('tlj_start');        //起始地
                $data['tlj_paytype']            = @implode(',', From::val('tlj_paytype'));        //付款方式
                $data['tlj_valid_time']         = From::valTrim('tlj_valid_time');        //运价有效日期
                $data['tlj_remark']             = From::valTrim('tlj_remark');        //备注
                $data['tlj_huoxing']           = @implode(',', From::val('tlj_huoxing'));        //货型
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tlj_ischeck']            = 0;
                $data['tlj_check_time']         = 0;
                $data['tlj_isdel']              = 0;
                $data['tlj_first_time']         = $currTime;
                $data['tlj_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_tlj_end) > 0) foreach($data_tlj_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tlj_end']            = $val;
                    $data['tlj_gp20']           = $data_tlj_gp20[$key] ?? '';
                    $data['tlj_gp40']           = $data_tlj_gp40[$key] ?? '';
                    $data['tlj_shuangtuo']      = $data_tlj_shuangtuo[$key] ?? '';
                    $data['tlj_zaizhong']       = $data_tlj_zaizhong[$key] ?? '';
                    //$data['tlj_huoxing']        = $data_tlj_huoxing[$key] ?? '';
                    $tmp = Sql::tag('tariffs_land_jktc', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_land_jktc.html');
        }elseif($type == 'zx'){  //专线
            if($save == 1){
                $data_tlz_end                   = From::val('tlz_end');        //目的地
                $data_tlz_tujing                = From::val('tlz_tujing');        //途经区域
                $data_tlz_yunjia                = From::val('tlz_yunjia');        //运输价格
                $data_tlz_yunjia_didian         = From::val('tlz_yunjia_didian');        //运输价格地点
                //$data_tlz_huowu                 = From::val('tlz_huowu');        //货物类型
                $data_tlz_cheliang              = From::val('tlz_cheliang');        //车辆类型
                $data_tlz_zaizhong              = From::val('tlz_zaizhong');        //载重
                $data_tlz_tiji                  = From::val('tlz_tiji');        //体积
                $data = [];
                $data['tlz_start']              = From::valTrim('tlz_start');        //起始地
                $data['tlz_yunjia_type']        = @implode(',', From::val('tlz_yunjia_type'));        //运价类型
                $data['tlz_shangmen']           = @implode(',', From::val('tlz_shangmen'));        //提供上门服务
                $data['tlz_paytype']            = @implode(',', From::val('tlz_paytype'));        //付款方式
                $data['tlz_valid_time']         = From::valTrim('tlz_valid_time');        //运价有效日期
                $data['tlz_remark']             = From::valTrim('tlz_remark');        //备注
                $data['tlz_huowu']              = @implode(',', From::val('tlz_huowu'));        //货型
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tlz_ischeck']            = 0;
                $data['tlz_check_time']         = 0;
                $data['tlz_isdel']              = 0;
                $data['tlz_first_time']         = $currTime;
                $data['tlz_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_tlz_end) > 0) foreach($data_tlz_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tlz_end']            = $val;
                    $data['tlz_tujing']         = $data_tlz_tujing[$key] ?? '';
                    $data['tlz_yunjia']         = $data_tlz_yunjia[$key] ?? '';
                    $data['tlz_yunjia_didian']  = $data_tlz_yunjia_didian[$key] ?? '';
                    //$data['tlz_huowu']          = $data_tlz_huowu[$key] ?? '';
                    $data['tlz_cheliang']       = $data_tlz_cheliang[$key] ?? '';
                    $data['tlz_zaizhong']       = $data_tlz_zaizhong[$key] ?? '';
                    $data['tlz_tiji']           = $data_tlz_tiji[$key] ?? '';
                    $tmp = Sql::tag('tariffs_land_zx', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_land_zx.html');
        }else{  //ld：零担
            if($save == 1){
                $data_tld_end                   = From::val('tld_end');        //目的地
                $data_tld_tujing                = From::val('tld_tujing');        //途经区域
                $data_tld_yunjia                = From::val('tld_yunjia');        //运输价格
                $data_tld_yunjia_didian         = From::val('tld_yunjia_didian');        //运输价格地点
                //$data_tld_huowu                 = From::val('tld_huowu');        //货物类型
                $data_tld_cheliang              = From::val('tld_cheliang');        //车辆类型
                $data_tld_zaizhong              = From::val('tld_zaizhong');        //载重
                $data_tld_tiji                  = From::val('tld_tiji');        //体积
                $data = [];
                $data['tld_start']              = From::valTrim('tld_start');        //起始地
                $data['tld_yunjia_type']        = @implode(',', From::val('tld_yunjia_type'));        //运价类型
                $data['tld_shangmen']           = @implode(',', From::val('tld_shangmen'));        //提供上门服务
                $data['tld_paytype']            = @implode(',', From::val('tld_paytype'));        //付款方式
                $data['tld_valid_time']         = From::valTrim('tld_valid_time');        //运价有效日期
                $data['tld_remark']             = From::valTrim('tld_remark');        //备注
                $data['tld_huowu']              = @implode(',', From::val('tld_huowu'));        //货型
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tld_ischeck']            = 0;
                $data['tld_check_time']         = 0;
                $data['tld_isdel']              = 0;
                $data['tld_first_time']         = $currTime;
                $data['tld_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_tld_end) > 0) foreach($data_tld_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tld_end']            = $val;
                    $data['tld_tujing']         = $data_tld_tujing[$key] ?? '';
                    $data['tld_yunjia']         = $data_tld_yunjia[$key] ?? '';
                    $data['tld_yunjia_didian']  = $data_tld_yunjia_didian[$key] ?? '';
                    //$data['tld_huowu']          = $data_tld_huowu[$key] ?? '';
                    $data['tld_cheliang']       = $data_tld_cheliang[$key] ?? '';
                    $data['tld_zaizhong']       = $data_tld_zaizhong[$key] ?? '';
                    $data['tld_tiji']           = $data_tld_tiji[$key] ?? '';
                    $tmp = Sql::tag('tariffs_land_ld', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_land_ld.html');
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
        if($type == 'jzx'){  //集装箱
            if($save == 1){
                $data_trj_end               = From::val('trj_end');        //卸运站
                $data_trj_tujingquyu        = From::val('trj_tujingquyu');        //途经区域
                $data_trj_n20               = From::val('trj_n20');        //运输价格20
                $data_trj_n40gp             = From::val('trj_n40gp');        //运输价格40GP
                $data_trj_n40hq             = From::val('trj_n40hq');        //运输价格40HQ
                $data_trj_n45hc             = From::val('trj_n45hc');        //运输价格45HC
                $data_trj_zaizhong          = From::val('trj_zaizhong');        //载重
                $data = [];
                $data['trj_start']          = From::valTrim('trj_start');        //起始站
                $data['trj_paytype']         = @implode(',', From::val('trj_paytype'));        //付款方式
                $data['trj_valid_time']     = From::valTrim('trj_valid_time');        //运价有效日期
                $data['trj_remark']         = From::valTrim('trj_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['trj_ischeck']            = 0;
                $data['trj_check_time']         = 0;
                $data['trj_isdel']              = 0;
                $data['trj_first_time']         = $currTime;
                $data['trj_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_trj_end) > 0) foreach($data_trj_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['trj_end']            = $val;
                    $data['trj_tujingquyu']     = $data_trj_tujingquyu[$key] ?? '';
                    $data['trj_n20']            = $data_trj_n20[$key] ?? '';
                    $data['trj_n40gp']          = $data_trj_n40gp[$key] ?? '';
                    $data['trj_n40hq']          = $data_trj_n40hq[$key] ?? '';
                    $data['trj_n45hc']          = $data_trj_n45hc[$key] ?? '';
                    $data['trj_zaizhong']       = $data_trj_zaizhong[$key] ?? '';
                    $tmp = Sql::tag('tariffs_railway_jzx', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_railway_jzx.html');
        }else{  //zc：整车（含大宗散杂货）
            if($save == 1){
                $data_trz_end                   = From::val('trz_end');        //目的站
                $data_trz_tujingquyu            = From::val('trz_tujingquyu');        //途经区域
                $data_trz_yunshujiage           = From::val('trz_yunshujiage');        //运输价格
                $data_trz_yunshujiage_danwei    = From::val('trz_yunshujiage_danwei');        //运输价格单位
                $data_trz_chepi                 = From::val('trz_chepi');        //车皮类型
                $data_trz_chepi_danwei          = From::val('trz_chepi_danwei');        //车皮类型单位
                $data_trz_zaizhong              = From::val('trz_zaizhong');        //载重
                $data_trz_tiji                  = From::val('trz_tiji');        //体积
                $data = [];
                $data['trz_start']              = From::valTrim('trz_start');        //起始站
                $data['trz_paytype']            = @implode(',', From::val('trz_paytype'));        //付款方式
                $data['trz_valid_time']         = From::valTrim('trz_valid_time');        //运价有效日期
                $data['trz_remark']             = From::valTrim('trz_remark');        //备注
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['trz_ischeck']            = 0;
                $data['trz_check_time']         = 0;
                $data['trz_isdel']              = 0;
                $data['trz_first_time']         = $currTime;
                $data['trz_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_trz_end) > 0) foreach($data_trz_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['trz_end']            = $val;
                    $data['trz_tujingquyu']     = $data_trz_tujingquyu[$key] ?? '';
                    $data['trz_yunshujiage']    = $data_trz_yunshujiage[$key] ?? '';
                    $data['trz_yunshujiage_danwei'] = $data_trz_yunshujiage_danwei[$key] ?? '';
                    $data['trz_chepi']          = $data_trz_chepi[$key] ?? '';
                    $data['trz_chepi_danwei']   = $data_trz_chepi_danwei[$key] ?? '';
                    $data['trz_zaizhong']       = $data_trz_zaizhong[$key] ?? '';
                    $data['trz_tiji']           = $data_trz_tiji[$key] ?? '';
                    $tmp = Sql::tag('tariffs_railway_zc', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_railway_zc.html');
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
                $data_tsz_end                = From::val('tsz_end');        //目的地
                $data_tsz_chenyunren         = From::val('tsz_chenyunren');        //承运人
                $data_tsz_n20                = From::val('tsz_n20');        //20
                $data_tsz_n40                = From::val('tsz_n40');        //40
                $data_tsz_n40hq              = From::val('tsz_n40hq');        //40hq
                $data_tsz_n45                = From::val('tsz_n45');        //45
                $data_tsz_fujiafei           = From::val('tsz_fujiafei');        //附加费JSON
                $data_tsz_hangcheng          = From::val('tsz_hangcheng');        //航程
                $data_tsz_zhongzhuan         = From::val('tsz_zhongzhuan');        //中转港
                $data_tsz_yujichuanqi        = From::val('tsz_yujichuanqi');        //预计船期
                $data = [];
                $data['tsz_start']              = From::valTrim('tsz_start');        //起始地
                $data['tsz_xiandun20']          = From::valTrim('tsz_xiandun20');        //限重（吨）20
                $data['tsz_xiandun40']          = From::valTrim('tsz_xiandun40');        //限重（吨）40
                $data['tsz_pinming']            = From::valTrim('tsz_pinming');        //适用品名
                $data['tsz_valid_time']         = From::valTrim('tsz_valid_time');        //运价有效日期
                $data['tsz_remark']             = From::valTrim('tsz_remark');        //备注
                $data['tsz_paytype']            = @implode(',', From::val('tsz_paytype'));        //付款方式
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tsz_ischeck']            = 0;
                $data['tsz_check_time']         = 0;
                $data['tsz_isdel']              = 0;
                $data['tsz_first_time']         = $currTime;
                $data['tsz_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_tsz_end) > 0) foreach($data_tsz_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tsz_end']            = $val;
                    $data['tsz_chenyunren']     = $data_tsz_chenyunren[$key] ?? '';
                    $data['tsz_n20']            = $data_tsz_n20[$key] ?? '';
                    $data['tsz_n40']            = $data_tsz_n40[$key] ?? '';
                    $data['tsz_n40hq']          = $data_tsz_n40hq[$key] ?? '';
                    $data['tsz_n45']            = $data_tsz_n45[$key] ?? '';
                    $data['tsz_fujiafei']       = $data_tsz_fujiafei[$key] ?? '';
                    $data['tsz_hangcheng']      = $data_tsz_hangcheng[$key] ?? '';
                    $data['tsz_zhongzhuan']     = $data_tsz_zhongzhuan[$key] ?? '';
                    $data['tsz_yujichuanqi']    = $data_tsz_yujichuanqi[$key] ?? '';
                    $tmp = Sql::tag('tariffs_sea_zx', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_sea_zx.html');
        }elseif($type == 'px'){  //集装箱拼箱
            if($save == 1){
                $data_tsp_end                = From::val('tsp_end');        //目的地
                $data_tsp_chenyunren         = From::val('tsp_chenyunren');        //承运人
                $data_tsp_meilifang          = From::val('tsp_meilifang');        //每立方米
                $data_tsp_meidun             = From::val('tsp_meidun');        //每吨
                $data_tsp_fujiafei           = From::val('tsp_fujiafei');        //附加费JSON
                $data_tsp_hangcheng          = From::val('tsp_hangcheng');        //航程
                $data_tsp_zhongzhuan         = From::val('tsp_zhongzhuan');        //中转港
                $data_tsp_yujichuanqi        = From::val('tsp_yujichuanqi');        //预计船期
                $data = [];
                $data['tsp_start']              = From::valTrim('tsp_start');        //起始地
                $data['tsp_valid_time']         = From::valTrim('tsp_valid_time');        //运价有效日期
                $data['tsp_remark']             = From::valTrim('tsp_remark');        //备注
                $data['tsp_paytype']            = @implode(',', From::val('tsp_paytype'));        //付款方式
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tsp_ischeck']            = 0;
                $data['tsp_check_time']         = 0;
                $data['tsp_isdel']              = 0;
                $data['tsp_first_time']         = $currTime;
                $data['tsp_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_tsp_end) > 0) foreach($data_tsp_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tsp_end']            = $val;
                    $data['tsp_chenyunren']     = $data_tsp_chenyunren[$key] ?? '';
                    $data['tsp_meilifang']      = $data_tsp_meilifang[$key] ?? '';
                    $data['tsp_meidun']         = $data_tsp_meidun[$key] ?? '';
                    $data['tsp_fujiafei']       = $data_tsp_fujiafei[$key] ?? '';
                    $data['tsp_hangcheng']      = $data_tsp_hangcheng[$key] ?? '';
                    $data['tsp_zhongzhuan']     = $data_tsp_zhongzhuan[$key] ?? '';
                    $data['tsp_yujichuanqi']    = $data_tsp_yujichuanqi[$key] ?? '';
                    $tmp = Sql::tag('tariffs_sea_px', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_sea_px.html');
        }else{  //ph：大宗散杂
            if($save == 1){
                $data_tsh_end               = From::val('tsh_end');        //卸运港
                $data_tsh_yunjia            = From::val('tsh_yunjia');        //运价
                $data_tsh_shouzaicangwei    = From::val('tsh_shouzaicangwei');        //受载仓位
                $data_tsh_chuanming         = From::val('tsh_chuanming');        //船名
                $data_tsh_chuanji           = From::val('tsh_chuanji');        //船籍
                $data_tsh_jianzaoriqi       = From::val('tsh_jianzaoriqi');        //建造日期
                $data_tsh_zaizhong          = From::val('tsh_zaizhong');        //载重
                $data_tsh_shouzairiqi       = From::val('tsh_shouzairiqi');        //截止日期
                $data = [];
                $data['tsh_start']          = From::valTrim('tsh_start');        //起始港
                $data['tsh_zuchuan']        = From::valTrim('tsh_zuchuan');        //租船类型
                $data['tsh_jiezai']         = @implode(',', From::val('tsh_jiezai'));        //接载货类
                $data['tsh_valid_time']     = From::valTrim('tsh_valid_time');        //运价有效日期
                $data['tsh_remark']         = From::valTrim('tsh_remark');        //备注
                $data['tsh_paytype']        = @implode(',', From::val('tsh_paytype'));        //付款方式
                $data['tsh_chuanbo']        = @implode(',', From::val('tsh_chuanbo'));        //船舶类型
                $data['tsh_yunshu']        = @implode(',', From::val('tsh_yunshu'));        //运输条款
                $currTime = Time::getTimeStamp();
                $data['us_id']                  = intval($this -> userInfo['id']);
                $data['tsh_ischeck']            = 0;
                $data['tsh_check_time']         = 0;
                $data['tsh_isdel']              = 0;
                $data['tsh_first_time']         = $currTime;
                $data['tsh_end_time']           = $currTime;
                $result = FALSE;
                if(count($data_tsh_end) > 0) foreach($data_tsh_end as $key => $val){
                    $val = trim($val);
                    if(strlen($val) < 1) continue;
                    $data['tsh_end']            = $val;
                    $data['tsh_yunjia']         = $data_tsh_yunjia[$key] ?? '';
                    $data['tsh_shouzaicangwei'] = $data_tsh_shouzaicangwei[$key] ?? '';
                    $data['tsh_chuanming']      = $data_tsh_chuanming[$key] ?? '';
                    $data['tsh_chuanji']        = $data_tsh_chuanji[$key] ?? '';
                    $data['tsh_jianzaoriqi']    = $data_tsh_jianzaoriqi[$key] ?? '';
                    $data['tsh_zaizhong']       = $data_tsh_zaizhong[$key] ?? '';
                    $data['tsh_shouzairiqi']    = $data_tsh_shouzairiqi[$key] ?? '';
                    $tmp = Sql::tag('tariffs_sea_sh', 'GMY') -> addById($data);
                    $result = $result || $tmp;
                }
                if($result){
                    (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                    Tips::show('新增完成！', Link::getLink('tariffs'));
                }else{
                    Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
                }
            }
            $Tpl->assign('chengyunren', Common::getChenYunRen());
            $Tpl->assign('gangkou', Common::getGangKou());
            $Tpl->assign('hangkong', Common::getHangKong());
            $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
            $Tpl->show('User/tariffs_sea_ph.html');
        }
    }

    /**
     * @name typeStorage
     * @desciption 揽货仓储
     */
    public function typeStorage(string $action){
        if($this -> getAllowNum() < 1) Tips::show('已达到发布数上限，请联系平台客服!', 'javascript: history.back();');
        $Tpl = $this->getTpl();
        $type = From::valTrim('type');
        $save = From::valInt('save');
        if($save == 1){
            $data = [];
            $data['tst_title']              = From::valTrim('tst_title');        //标题
            $data['tst_suozaidi']           = From::valTrim('tst_suozaidi');        //仓储所在地
            $data['tst_xiangxidizhi']       = From::valTrim('tst_xiangxidizhi');        //仓储详细地址
            $data['tst_cangkuleibie']       = From::valTrim('tst_cangkuleibie');        //仓储类别
            $data['tst_kufangmianji']       = From::valTrim('tst_kufangmianji');        //库房面积
            $data['tst_kufangmianji_danwei'] = From::valTrim('tst_kufangmianji_danwei');        //库房面积单位
            $data['tst_kufangcenggao']      = From::valTrim('tst_kufangcenggao');        //库层层高
            $data['tst_duichangmianji']     = From::valTrim('tst_duichangmianji');        //堆场面积平方米
            $data['tst_bangongmianji']      = From::valTrim('tst_bangongmianji');        //办公面积平方米
            $data['tst_kufangcengshu']      = From::valTrim('tst_kufangcengshu');        //库房层数
            $data['tst_xiaofangdengji']     = From::valTrim('tst_xiaofangdengji');        //消防等级
            $data['tst_cangkujiegou']       = From::valTrim('tst_cangkujiegou');        //仓库结构
            $data['tst_cangkudimian']       = @implode(',', From::val('tst_cangkudimian'));        //仓库地面
            $data['tst_cangkudimian_cz']    = From::valTrim('tst_cangkudimian_cz');        //仓库地面每平米承重(吨)
            $data['tst_fanghuoanbao']       = @implode(',', From::val('tst_fanghuoanbao'));        //防火安保
            $data['tst_ithuanjing']         = @implode(',', From::val('tst_ithuanjing'));        //IT 环境
            $data['tst_kufangguanli']       = @implode(',', From::val('tst_kufangguanli'));        //库存管理
            $data['tst_fuwuxuanxiang']      = @implode(',', From::val('tst_fuwuxuanxiang'));        //服务选项
            $data['tst_banyunshebei']       = @implode(',', From::val('tst_banyunshebei'));        //搬运设备
            $data['tst_tupian']             = From::valTrim('tst_tupian');        //图片上传
            $data['tst_cangkumiaoshu']      = From::valTrim('tst_cangkumiaoshu');        //仓库描述
            $data['tst_qishi_time']         = From::valTrim('tst_qishi_time');        //起始时间
            $data['tst_jieshu_time']        = From::valTrim('tst_jieshu_time');        //结束时间
            $data['tst_jizhuangxiang20']    = From::valTrim('tst_jizhuangxiang20');        //集装箱拆装费元/20英尺
            $data['tst_jizhuangxiang40']    = From::valTrim('tst_jizhuangxiang40');        //集装箱拆装费元/40英尺
            $data['tst_keyongmianji']       = From::valTrim('tst_keyongmianji');        //可用面积
            $data['tst_keyongdanwei']       = From::valTrim('tst_keyongdanwei');        //可用面积单位
            $data['tst_zhuangxie']          = From::valTrim('tst_zhuangxie');        //装卸费元
            $data['tst_zhuangxiedanwei']    = From::valTrim('tst_zhuangxiedanwei');        //装卸费元/单位
            $data['tst_cangchubaojia']      = From::valTrim('tst_cangchubaojia');        //仓储报价元
            $data['tst_cangchubaojia_danwei'] = From::valTrim('tst_cangchubaojia_danwei');      //仓储报价元/单位
            $data['tst_cangchubaojia_time'] = From::valTrim('tst_cangchubaojia_time');        //仓储报价元/时间
            $data['tst_zuidizuping_time']    = From::valTrim('tst_zuidizuping_time');        //最低租赁期限
            $data['tst_type']               = $type == 'ziyou' ? 1 : 2;
            $currTime = Time::getTimeStamp();
            $data['us_id']                  = intval($this -> userInfo['id']);
            $data['tst_ischeck']            = 0;
            $data['tst_check_time']         = 0;
            $data['tst_isdel']              = 0;
            $data['tst_first_time']         = $currTime;
            $data['tst_end_time']           = $currTime;
            $result = Sql::tag('tariffs_storage', 'GMY') -> addById($data);
            if($result){
                (new UserData()) -> addSetGgt('YUNJIA_REL', '发布运价获得');
                Tips::show('新增成功！', Link::getLink('tariffs'));
            }else{
                Tips::show('新增失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl->assign('chengyunren', Common::getChenYunRen());
        $Tpl->assign('gangkou', Common::getGangKou());
        $Tpl->assign('hangkong', Common::getHangKong());
        $Tpl->assign('jichang', Common::getJiChang());             $Tpl->assign('area', Common::getArea());
        if($type == 'ziyou'){  //自有
            $Tpl->show('User/tariffs_storage_ziyou.html');
        }else{  //zuyong：租用
            $Tpl->show('User/tariffs_storage_zuyong.html');
        }
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
                    if($stype == 'zc'){   //车皮
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND trz_id='.$id.' AND trz_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_railway_zc').' SET trz_isdel=1, trz_end_time='.$currTime.$whereString;
                    }else{  //集装箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND trj_id='.$id.' AND trj_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_railway_jzx').' SET trj_isdel=1, trj_end_time='.$currTime.$whereString;
                    }
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('tariffs').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                $tplHtml = 'User/tariffs_manage_railway.html';
                break;
            }
            case 'sea':{    //海运:
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if($stype == 'px'){ //拼箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsp_id='.$id.' AND tsp_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_sea_px').' SET tsp_isdel=1, tsp_end_time='.$currTime.$whereString;
                    }else if($stype == 'sh'){   //散杂货
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsh_id='.$id.' AND tsh_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_sea_sh').' SET tsh_isdel=1, tsh_end_time='.$currTime.$whereString;
                    }else{  //整箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tsz_id='.$id.' AND tsz_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_sea_zx').' SET tsz_isdel=1, tsz_end_time='.$currTime.$whereString;
                    }
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('tariffs').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                $tplHtml = 'User/tariffs_manage_sea.html';
                break;
            }
            case 'air':{    //空运
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if($stype == 'gn'){   //国内
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tan_id='.$id.' AND tan_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_air_gn').' SET tan_isdel=1, tan_end_time='.$currTime.$whereString;
                    }else{  //国际
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND taj_id='.$id.' AND taj_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_air_gj').' SET taj_isdel=1, taj_end_time='.$currTime.$whereString;
                    }
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('tariffs').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                }
                if($stype == 'gn'){   //国内
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tan_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tan_id as id, tan_start as start, tan_end as end, tan_hangkong as hangkong, tan_paytype as paytype, tan_first_time as first_time, tan_valid_time as valid_time, tan_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gn').$whereString.' ORDER BY tan_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //国际
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND taj_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,taj_id as id, taj_start as start, taj_end as end, taj_hangkong as hangkong, taj_paytype as paytype, taj_first_time as first_time, taj_valid_time as valid_time, taj_ischeck as ischeck FROM '.$Db -> getTableNameAll('tariffs_air_gj').$whereString.' ORDER BY taj_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                $tplHtml = 'User/tariffs_manage_air.html';
                break;
            }
            case 'land':{    //公路运输
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if($stype == 'zx'){ //专线
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tlz_id='.$id.' AND tlz_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_land_zx').' SET tlz_isdel=1, tlz_end_time='.$currTime.$whereString;
                    }else if($stype == 'ld'){   //零担
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tld_id='.$id.' AND tld_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_land_ld').' SET tld_isdel=1, tld_end_time='.$currTime.$whereString;
                    }else{  //集卡拖车
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tlj_id='.$id.' AND tlj_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_land_jktc').' SET tlj_isdel=1, tlj_end_time='.$currTime.$whereString;
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
                $tplHtml = 'User/tariffs_manage_land.html';
                break;
            }
            case 'storage':{    //仓储
                if($stype == 'zuyong'){
                    $whereString .= ($whereString == ''?'':' AND ').'tst_type=2';
                }else{
                    $whereString .= ($whereString == ''?'':' AND ').'tst_type=1';
                }
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tst_id='.$id.' AND tst_isdel=0';
                    $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_storage').' SET tst_isdel=1, tst_end_time='.$currTime.$whereString;
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('storage').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                $tplHtml = 'User/tariffs_manage_storage.html';
                break;
            }
            case 'detect':{    //报关报检
                if($stype == 'out'){
                    $whereString .= ($whereString == ''?'':' AND ').'tdt_type=1';
                }else{
                    $whereString .= ($whereString == ''?'':' AND ').'tdt_type=2';
                }
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tdt_id='.$id.' AND tdt_isdel=0';
                    $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_detect').' SET tdt_isdel=1, tdt_end_time='.$currTime.$whereString;
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('tariffs').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                $tplHtml = 'User/tariffs_manage_detect.html';
                break;
            }
            default:{   //multi-多式联运
                $op       = From::valTrim('op');
                if($op == 'del'){
                    $id = From::valInt('id');
                    if($id < 1) Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    if($stype == 'dzs'){   //大宗散货
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tmz_id='.$id.' AND tmz_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_multi_zc').' SET tmz_isdel=1, tmz_end_time='.$currTime.$whereString;
                    }else{  //集装箱
                        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tmj_id='.$id.' AND tmj_isdel=0';
                        $sql = 'UPDATE '.$Db -> getTableNameAll('tariffs_multi_jzx').' SET tmj_isdel=1, tmj_end_time='.$currTime.$whereString;
                    }
                    if($Db -> getDataNum($sql) > 0){
                        Tips::show('删除成功！', Link::getLink('tariffs').'?A='.From::valTrim('A').'&type='.$type.'&stype='.$stype);
                    }else{
                        Tips::show('删除失败，请稍后重试！', 'javascript: history.back();');
                    }
                }
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
                }
                if($stype == 'dzs'){   //大宗散货
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tmz_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tmz_id as id, tmz_start as start, tmz_end as end, tmz_first_time as first_time, tmz_valid_time as valid_time, tmz_ischeck as ischeck, tmz_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_zc').$whereString.' ORDER BY tmz_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }else{  //集装箱
                    if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString.' AND tmj_isdel=0';
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS *,tmj_id as id, tmj_start as start, tmj_end as end, tmj_first_time as first_time, tmj_valid_time as valid_time, tmj_ischeck as ischeck, tmj_paytype as paytype FROM '.$Db -> getTableNameAll('tariffs_multi_jzx').$whereString.' ORDER BY tmj_id DESC LIMIT '.$limit[0].', '.$limit[1];
                }
                $tplHtml = 'User/tariffs_manage.html';
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
        $Tpl->show('User/tariffs_recv.html');
    }

    /**
     * @name send
     * @desciption 我的询单
     */
    public function send(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/tariffs_send.html');
    }
}