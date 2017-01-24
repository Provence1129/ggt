<?php
/**
 * @Copyright (C) 2016.
 * @Description Box
 * @FileName Box.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Box;

use App\Article\ArticleModel;
use App\Index\Gjmy;
use App\Index\Huopan;
use App\Index\UserDatas;
use \Libs\Frame\Action;
use \Libs\Comm\From;
use Libs\Comm\Time;
use \App\Pub\Tips;
use \Libs\Frame\Url;
use \Libs\Comm\File;
use \Libs\Load;
use Libs\Tag\Db;
use \Libs\Tag\Page;
use Libs\Tag\Sql;
use \App\Pub\Link;

class Box extends Action
{
    public $tpl;
    public $boxModel;
    public $entData;
    const SUCCESS   = "success";    // 成功
    const FAIL      = "fail";       // 失败
    const ISDEL     = 0;            // 删除
    private $userInfo = [];         //当前用户信息

    //配置
    public function conf(){
        $this->tpl = $this -> getTpl();

        // 获取栏目分类列表
        // 实例化Model
        $this->boxModel = new BoxModel();
        $this->userInfo = $_SESSION['TOKEN']['INFO'];

        $this->tpl->assign('modelName', '箱卡集市');
    }


    /**
     * 箱卡集市首页
     * @param  string $action [description]
     * @return [type]         [description]
     */
    public function index(string $action)
    {
        $whereString = "";
        $cm_id = From::valInt('m');
        $this->tpl->assign('cm_id', $cm_id);

        $code = From::valInt('code');
        $this->tpl->assign('code', $code);

        // 获取集装箱型号
        $model = $this->boxModel->getContainerModelnumber();
        $this->tpl->assign('model', $model);

        // 获取省份
        $city = $this->boxModel->getCity();
        $this->tpl->assign('city', $city);

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 6);

        if (!empty($cm_id)){
            $whereString .= " AND cm_id={$cm_id}";
            $Page->setQuery('m', $cm_id);
        }

        if(!empty($code)){
            $whereString .= " AND ca_code={$code}";
            $Page->setQuery('code', $code);
        }
        $whereString = ltrim($whereString, ' AND');

        // 获取集装箱列表
        $list = $this->boxModel->getContainerList($whereString,$Page);
        $this->tpl->assign('containerList', $list);


        // 获取拖车信息
        $whereString = '';
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 7);
        $tuochelist = $this->boxModel->getTuocheList($whereString, $Page);
        $this->tpl->assign('tuochelist', $tuochelist);

        // 获取车型
        $carModels = $this->boxModel->getBoxCarModels();
        $this->tpl->assign('carModels', $carModels);
        $topbanner = $this->boxModel->getBannerData(12);
        $this->tpl->assign('topbanner', $topbanner);
        $leftbanner = $this->boxModel->getBannerData(13);
        $this->tpl->assign('leftbanner', $leftbanner);
        $footerbanner = $this->boxModel->getBannerData(14);
        $this->tpl->assign('footerbanner', $footerbanner);
        // 获取拖车地址
        $tuoche = $this->boxModel->getBoxAddress();
        $this->tpl->assign('departure', $tuoche['departure']);
        $this->tpl->assign('arrival', $tuoche['arrival']);

        // 获取拖车任务信息
        $whereString = '';
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 7);
        $tuocheTaskList = $this->boxModel->getTuocheTaskList($whereString, $Page);
        $this->tpl->assign('tuocheTaskList', $tuocheTaskList);

        $gongluyunshu = $this->boxModel->getTuoCheZhongXin();
        $this->tpl->assign('gongluyunshu', $gongluyunshu);
        //热门
        $ArticleModel = new ArticleModel();
        //最新资讯 最新的时间的
        //政策解读 ac_id=29
        //行业分析 ac_id=39
        //一带一路 ac_id=47
        $num = 6;
        $this->tpl -> assign('newsNewList', $ArticleModel -> getArticleList(0, $num));
        $this->tpl -> assign('newsZcjdList', $ArticleModel -> getArticleList(29, $num));
        $this->tpl -> assign('newsDataList', $ArticleModel -> getArticleList(39, $num));
        $this->tpl -> assign('newsYdylList', $ArticleModel -> getArticleList(47, $num));
        $this->tpl->show('Box/index.html');
    }


    /**
     * 箱卡集市列表页
     * @param string $action
     */
    public function lists(string $action)
    {
        // 条件处理
        $whereString = "";
        // 材质
        $gcm_id = From::valInt('ms');
        // 结构
        $cs_id = From::valInt('s');
        // 类型
        $ct_id = From::valInt('t');
        // 箱型
        $cm_id = From::valInt('m');
        // 排序
        $order = trim(From::valGet('order'));
        $this->tpl->assign('order', $order);
        // 城市
        $city = From::valInt('city');

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        //$Page->setParam('size', 2);
        if (!empty($gcm_id)){
            $whereString .= " AND gcm_id={$gcm_id}";
            $Page->setQuery('ms', $gcm_id);
        }
        if (!empty($cs_id)){
            $whereString .= " AND cs_id={$cs_id}";
            $Page->setQuery('s', $cs_id);
        }
        if (!empty($ct_id)){
            $whereString .= " AND ct_id={$ct_id}";
            $Page->setQuery('t', $ct_id);
        }
        if (!empty($cm_id)){
            $whereString .= " AND cm_id={$cm_id}";
            $Page->setQuery('m', $cm_id);
        }

        $orderString = "cb_id DESC";
        if (!empty($order)) {
            switch ($order) {
                case 'time':
                    $orderString = " cb_ctime DESC";
                    break;

                case 'price':
                    $orderString = "cb_market_price DESC";
                    break;

            }
            $Page->setQuery('order', $order);
        }

        if(!empty($city)){
            $whereString .= " AND ca_code={$city}";
            $Page->setQuery('city', $city);
        }

        $whereString = ltrim($whereString, ' AND');

        // 获取集装箱型号
        $type = $this->boxModel->getContainerType();
        $this->tpl->assign('type', $type);

        // 获取集装箱型号
        $model = $this->boxModel->getContainerModelnumber();
        $this->tpl->assign('model', $model);

        // 获取集装箱结构
        $structure = $this->boxModel->getContainerStructure();
        $this->tpl->assign('structure', $structure);

        // 获取集装箱材料
        $material = $this->boxModel->getContainerMaterial();
        $this->tpl->assign('material', $material);

        // 获取集装箱材料
        $material = $this->boxModel->getContainerMaterial();
        $this->tpl->assign('material', $material);

        // 获取省份
        $city = $this->boxModel->getCity();
        $this->tpl->assign('city', $city);

        // 获取集装箱列表
        $list = $this->boxModel->getContainerList($whereString,$Page, $orderString);

        $topbanner = $this->boxModel->getBannerData(27);
        $this->tpl->assign('topbanner', $topbanner);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('xkjs').'?A=lists');
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('containerList', $list);

        $this->tpl->show('Box/lists.html');
    }

    /**
     * 箱卡集市详情页
     * @param string $action
     */
    public function detail(string $action)
    {
        $id = From::valInt('id');
        if($id){
            // 获取集装箱型号
            $type = $this->boxModel->getContainerType();
            $this->tpl->assign('type', $type);

            // 获取集装箱结构
            $structure = $this->boxModel->getContainerStructure();
            $this->tpl->assign('structure', $structure);

            // 获取集装箱材料
            $material = $this->boxModel->getContainerMaterial();
            $this->tpl->assign('material', $material);

            // 获取集装箱详细信息
            $info = $this->boxModel->getContainerInfo($id);
//            echo '<pre>';
//            print_r($info);
//            exit;
            $this->tpl->assign('info', $info);
        }
        //商家评分信息
        $Db = Db::tag('DB.USER', 'GMY');
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
            $entInfo['url'] = (new Huopan()) -> getShopUrl($viewUsId);
            $this->tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($viewUsId));   //评分
        }
        $this->tpl -> assign('entInfo', $entInfo);
        $this->tpl->show('Box/detail.html');
    }

    /**
     * 拖车
     * @param string $action
     */
    public function tuochemain(string $action)
    {
        $whereString = '';
        // 类别
        $bx_car_models = From::valInt('m');
        // 出发地
        $bx_departure = From::valInt('departure');
        // 目的地
        $bx_arrival = From::valInt('arrival');

        // 获取车型
        $carModels = $this->boxModel->getBoxCarModels();
        $this->tpl->assign('carModels', $carModels);

        // 获取拖车地址
        $tuoche = $this->boxModel->getBoxAddress();
        $this->tpl->assign('departure', $tuoche['departure']);
        $this->tpl->assign('arrival', $tuoche['arrival']);

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        //Page -> setParam('size', 2);
        if (!empty($bx_car_models)){
            $whereString .= " AND bx_car_models={$bx_car_models}";
            $Page->setQuery('m', $bx_car_models);
        }
        if (!empty($bx_departure)){
            $whereString .= " AND bx_departure={$bx_departure}";
            $Page->setQuery('departure', $bx_departure);
        }
        if (!empty($bx_arrival)){
            $whereString .= " AND bx_arrival={$bx_arrival}";
            $Page->setQuery('arrival', $bx_arrival);
        }
        $whereString = ltrim($whereString, ' AND');

        $list = $this->boxModel->getTuocheList($whereString, $Page);
        $this->tpl->assign('list', $list);

        $Page->setParam('size', 10);
        $hotlist = $this->boxModel->getTuocheList($whereString, $Page, "bx_click DESC,bx_id DESC");
        $this->tpl->assign('hotlist', $hotlist);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('box').'?A=box-tuochemain');
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->show('Box/tuochemain.html');
    }


    /**
     * 拖车详情页
     * @param string $action
     */
    public function tuochedetail(string $action)
    {
        $id         = From::valInt('id');

        // 获取拖车地址
        $tuoche = $this->boxModel->getBoxAddress();
        $this->tpl->assign('departure', $tuoche['departure']);
        $this->tpl->assign('arrival', $tuoche['arrival']);

        // 获取车型
        $carModels = $this->boxModel->getBoxCarModels();
        $this->tpl->assign('carModels', $carModels);

        // 获取司机信息
        $driver = $this->boxModel->getTuocheDriverlist($id);
        $this->tpl->assign('driver', $driver);

        // 获取车辆信息
        $car = $this->boxModel->getTuocheCarlist($id);
        $this->tpl->assign('car', $car);
        $this->tpl->assign('carCount', count($car));

        // 获取拖车详细信息
        $info   = $this->boxModel->getTuocheInfo($id);
        $this->tpl->assign('info', $info);

        // 获取公司信息
        $Gjmy = new Gjmy();
        $userBasic = $Gjmy->getBasic(intval($info['us_id']));
        $userEnt = $Gjmy->getEnterprise(intval($info['us_id']));
        $userEnt['reviewNum'] = (new UserDatas())->getReviewNum(intval($info['us_id']));   //评分
        $userEnt['contractYear'] = (date('Y', time())-date('Y', intval($userBasic['ui_first_time'])))+1;
        $entUser   = array_merge($userBasic, $userEnt);
        $this->tpl->assign('entUser', $entUser);

//        echo '<pre>';
//        print_r($entUser);
//        exit;

        // 更新点击次数
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'UPDATE ' . $Db->getTableNameAll('box') . ' SET bx_click=bx_click+1 WHERE bx_id=' . $id . ' AND bx_isdel=0';
        $Db->getDataNum($sql);

//        echo '<pre>';
//        print_r($tuoche);
//        print_r($carModels);
//        print_r($info);
//        print_r($driver);
//        print_r($car);
//        exit;

        $this->tpl->show('Box/tuochedetail.html');
    }

    /**
     * 拖车咨询
     * @param string $action
     */
    public function tuocheconsult(string $action)
    {
        $ss_id          = intval(From::valInt('id'));
        $au_id          = intval(From::valPost('userid'));
        $bc_type        = intval(From::valInt('bc_type'));
        $bc_count       = From::valPost('bc_count');
        $bc_name        = trim(From::valPost('bc_name'));
        $bc_phone       = trim(From::valPost('bc_phone'));
        $bc_description = trim(From::valPost('bc_description'));
        $bc_title       = trim(From::valPost('bc_title'));
        $bc_freight     = trim(From::valPost('bc_freight'));

        $result = $this->boxModel->saveConsult([
            'au_id'             => $au_id,
            'us_id'             => $_SESSION['TOKEN']['INFO']['id'],
            'bc_type'          => $bc_type,
            'ss_id'             => $ss_id,
            'bc_count'          => $bc_count,
            'bc_name'           => $bc_name,
            'bc_phone'          => $bc_phone,
            'bc_description'    => $bc_description,
            'bc_title'          => $bc_title,
            'bc_freight'        => $bc_freight
        ]);

        echo json_encode(['status' => $result], JSON_UNESCAPED_UNICODE);
        exit;
    }


    /**
     * 订单
     * @param string $action
     */
    public function order(string $action)
    {
        $id = From::valInt('id');
        if($id){
            // 获取集装箱型号
            $type = $this->boxModel->getContainerType();
            $this->tpl->assign('type', $type);

            // 获取集装箱型号
            $model = $this->boxModel->getContainerModelnumber();
            $this->tpl->assign('model', $model);

            // 获取集装箱结构
            $structure = $this->boxModel->getContainerStructure();
            $this->tpl->assign('structure', $structure);

            // 获取集装箱材料
            $material = $this->boxModel->getContainerMaterial();
            $this->tpl->assign('material', $material);

            // 获取集装箱详细信息
            $info = $this->boxModel->getContainerInfo($id);
            $this->tpl->assign('info', $info);

            //买卖家信息
            $Gjmy = new Gjmy();
            $usId = intval($info['us_id']);
            $this->tpl -> assign('uinfom', $Gjmy -> getBasic($usId));
            $this->tpl -> assign('cinfom', $Gjmy -> getEnterprise($usId));
            $usId  = intval($this -> userInfo['id']);
            if($usId < 1){
                Tips::show('请先登录再购买！', '/user/signin.php');
            }
            $this->tpl -> assign('uinfo', $Gjmy -> getBasic($usId));
            $this->tpl -> assign('cinfo', $Gjmy -> getEnterprise($usId));

            $this->tpl->show('Box/order.html');
        }else{
            die("参数错误");
        }
    }


    /**
     * 保存箱卡集市订单信息
     * @param string $action
     */
    public function ordersave(string $action)
    {
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $id = From::valInt('id');

        // 获取集装箱详细信息
        $info = $this->boxModel->getContainerInfo($id);
        $this->tpl->assign('info', $info);

        //这里需要开发接收数据并写入订单信息
        $Gjmy = new Gjmy();
        $usIdm = $usId = intval($info['us_id']);
        $Tpl -> assign('uinfom', $Gjmy -> getBasic($usId));
        $Tpl -> assign('cinfom', $Gjmy -> getEnterprise($usId));
        $usId  = intval($this -> userInfo['id']);
        $Tpl -> assign('uinfo', $Gjmy -> getBasic($usId));
        $Tpl -> assign('cinfo', $Gjmy -> getEnterprise($usId));
        $save = From::valInt('save');
        if($save == 1){
            $goNum = From::valInt('co_num');
            $goFare = floatval(From::valTrim('co_fare'));
            $goText = From::valTrim('co_text');
            $price = floatval($info['cb_market_price']/100);
            $currTime = Time::getTimeStamp();
            $data       = [];
            $data['us_id']                 = $usId;
            $data['au_id']                 = $usIdm;
            $data['ss_id']                 = $id;
            $data['co_isdel']              = 0;
            $data['co_frist_time']         = $currTime;
            $data['co_last_time']          = $currTime;
            $data['co_num']                = $goNum;
            $data['co_price']              = strval($price);
            $data['co_total']              = strval($goNum*$price+$goFare);
            $data['co_fare']               = strval($goFare);
            $data['co_offers']             = 0;
            $data['co_text']               = $goText;
            $data['co_remark']             = From::valTrim('remark');
            $time_year = From::valInt('time_year');
            $time_month = From::valInt('time_month');
            $time_day = From::valInt('time_day');
            if($time_year < 2016 || $time_year > 2116 || $time_month < 1 || $time_month > 12 || $time_day < 1 || $time_day > 31){
                $data['co_pay_time']          = $currTime+86400*30;
            }else{
                $time_set = intval(strtotime($time_year.'-'.$time_month.'-'.$time_day.' 23:59:59'));
                $time_set = $time_set < $currTime ? $currTime+86400*30 : $time_set;
                $data['co_pay_time']          = $time_set;
            }
            $data['co_result']              = 0;
            $result = Sql::tag('container_order', 'GMY') -> addById($data);
            if($result > 0){
            }else{
                Tips::show('下单失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $Tpl -> assign('co_total', $data['co_total']);
        $Tpl -> assign('co_day', max(ceil(($data['co_pay_time'] - $currTime) / 86400), 1));
        $Tpl -> show('Box/buypay.html');
    }



    ####################################################################
    ####################################################################
    ####################################################################


    /**
     * 订单
     * @param string $action
     */
    public function tuocheorder(string $action)
    {
        $id = From::valInt('id');
        if($id){
            // 获取拖车地址
            $tuoche = $this->boxModel->getBoxAddress();
            $this->tpl->assign('departure', $tuoche['departure']);
            $this->tpl->assign('arrival', $tuoche['arrival']);

            // 获取车型
            $carModels = $this->boxModel->getBoxCarModels();
            $this->tpl->assign('carModels', $carModels);

            // 获取司机信息
            $driver = $this->boxModel->getTuocheDriverlist($id);
            $this->tpl->assign('driver', $driver);

            // 获取车辆信息
            $car = $this->boxModel->getTuocheCarlist($id);
            $this->tpl->assign('car', $car);
            $this->tpl->assign('carCount', count($car));

            // 获取拖车详细信息
            $info   = $this->boxModel->getTuocheInfo($id);
            $this->tpl->assign('info', $info);

            $basic          = $this->boxModel->getBasic(intval($info['us_id']));
            $enterprise     = $this->boxModel->getEnterprise(intval($info['us_id']));

//            echo '<pre>';
//            print_r($info);
//            print_r($basic);
//            print_r($enterprise);
//            exit;

            $this->tpl->assign('basic', $basic);
            $this->tpl->assign('enterprise', $enterprise);
            $this->tpl->show('Box/tuocheorder.html');
        }else{
            die("参数错误");
        }
    }


    /**
     * 保存订单信息
     * @param string $action
     */
    public function tuocherdersave(string $action)
    {
        $ss_id          = intval(From::valInt('ss_id'));
        $au_id          = intval(From::valPost('au_id'));
        $or_num         = intval(From::valPost('or_num'));
        $or_pay_money   = intval(From::valPost('or_pay_money'));
        $or_name        = trim(From::valPost('or_name'));
        $or_mobile      = trim(From::valPost('or_mobile'));
        $or_phone       = trim(From::valPost('or_phone'));
        $or_company     = trim(From::valPost('or_company'));
        $or_address     = trim(From::valPost('or_address'));
        $or_send_time   = strtotime(trim(From::valPost('or_send_time')));

        $or_pay_type    = trim(From::valPost('or_pay_type'));
        $or_pay_time    = ($or_pay_type == 2) ? strtotime(trim(From::valPost('or_pay_time'))) : 0;

        $result = $this->boxModel->saveTuocheOrder([
            'ss_id'            => $ss_id,
            'au_id'             => $au_id,
            'us_id'             => $_SESSION['TOKEN']['INFO']['id'],
            'or_num'           => $or_num,
            'or_pay_money'     => $or_pay_money,
            'or_name'          => $or_name,
            'or_mobile'        => $or_mobile,
            'or_phone'         => $or_phone,
            'or_company'       => $or_company,
            'or_address'       => $or_address,
            'or_send_time'     => $or_send_time,
            'or_pay_type'      => $or_pay_type,
            'or_pay_time'      => $or_pay_time
        ]);

        if($result == static::SUCCESS){
            Tips::show('保存成功', Link::getLink('tuoche')."?A=tuochedetail&id={$ss_id}");
            exit;
        }
        Tips::show('保存失败', Link::getLink('tuoche')."?A=tuochedetail&id={$ss_id}");

    }

}