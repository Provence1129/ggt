<?php
/**
 * @Copyright (C) 2016.
 * @Description Box
 * @FileName Box.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;
use \App\Pub\Common;
use App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use Libs\Comm\Valid;
use \Libs\Comm\Time;
use \Libs\Frame\Action;
use \Libs\Frame\Url;
use \App\Auth\MyAuth;
use \Libs\Comm\File;
use \Libs\Load;
use \Libs\Plugins\Checkcode\Checkcode;
use \Libs\Tag\Page;
use \Libs\Tag\Db;
use \Libs\Tag\Sql;
use App\Index\Gjmy;

class Box extends Action
{
    public $tpl;
    public $BoxData;
    public $userid;
    public $userInfo;
    const SUCCESS   = "success";    // 成功
    const FAIL      = "fail";       // 失败
    const ISDEL     = 0;            // 删除

    //配置
    public function conf()
    {
        $this->tpl = $this -> getTpl();

        $this->userid   = $_SESSION['TOKEN']['INFO']['id'];
        $this->userInfo = $_SESSION['TOKEN']['INFO'];

        $this->BoxData = new BoxData();

        $page = [];
        $page['Title']          = '港港通国际多式联运门户网';
        $page['Keywords']       = '行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布';
        $page['Description']    = '国内首家专业性多式联运行业门户网站，集行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布等功能和内容';
        $this->tpl -> assign('page', $page);
    }

    /**
     * @name main
     * @desciption 箱卡集市
     */
    public function main(string $action)
    {
        $this->tpl->show('User/box_main.html');
    }


    /**
     * @name sendpurcha
     * @desciption 询价管理
     */
    public function sendpurcha(string $action)
    {
        $this->tpl->show('User/box_sendpurcha.html');
    }


    /**
     * @name purchamore
     * @desciption 询价详情
     */
    public function purchamore()
    {
        $this->tpl->show('User/box_purchamore.html');
    }


    /**
     * @name orderpurcha
     * @desciption 订单管理
     */
    public function orderpurcha(string $action)
    {
        $this->tpl->show('User/box_orderpurcha.html');
    }


    /**
     * @name tuoche
     * @desciption 拖车信息管理
     */
    public function tuoche()
    {
        // 条件处理
        $whereString = "us_id=".$this->userid;

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));

        $list = $this->BoxData->getTuocheList($whereString, $Page);

        // 获取车型
        $carModels = [];
        $carModelsTemp = $this->BoxData->getBoxCarModels();
        foreach($carModelsTemp as $key=>$val){
            $carModels[$val['bcm_id']] = $val;
        }
        $this->tpl->assign('carModels', $carModels);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('box').'?A=box-tuoche');
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('list', $list);

        $this->tpl->show('User/box_tuoche.html');
    }


    /**
     * 删除拖车信息
     */
    public function deltuoche()
    {
        $id      = From::valInt('id');
        if(empty($id)) Tips::show('条件错误', Link::getLink('box').'?A=box-tuoche');

        // 条件处理
        $whereArray = [];
        if (!empty($id)) $whereArray['bx_id'] = $id;
        $result = $this->BoxData->delTuoche($whereArray);

        // 跳转处理
        if(static::SUCCESS == $result){
            Tips::show('操作成功', Link::getLink('box').'?A=box-tuoche');
        }
        Tips::show('操作失败', Link::getLink('box').'?A=box-tuoche');
    }


    /**
     * @name addtuoche
     * @desciption 添加拖车信息
     */
    public function addtuoche()
    {
        // 获取ID参数
        $id         = From::valInt('id');

        if($id){
            $tempID = $id;
        }else{
            $tempID = str_replace('.', '', microtime(true) . rand(0, 999));
        }
        $this->tpl->assign('tempID', $tempID);

        // 获取拖车地址
        $tuoche = $this->BoxData->getBoxAddress();
        $this->tpl->assign('tuoche', $tuoche);
        $city =  $this->BoxData->getCity();
        $this->tpl->assign('city', $city);
        // 获取车型
        $carModels = $this->BoxData->getBoxCarModels();
        $this->tpl->assign('carModels', $carModels);

        // 获取拖车详细信息
        $info   = $this->BoxData->getTuocheInfo($id);
        $this->tpl->assign('info', $info);


        $this->tpl->show('User/box_addtuoche.html');
    }

    /**
     * 保存拖车信息
     */
    public function addtuoche_save()
    {
        $bx_id              = From::valInt('bx_id');
        $tuoche_id          = From::valInt('tuoche_id');
        $bx_address         = From::post('bx_address');
        $bx_route           = From::post('bx_route');
        $bx_description     = From::post('bx_description');
        $bx_freight         = From::post('bx_freight');
        $bx_car_models      = From::valInt('car_models');
        $bx_departure       = From::valInt('bx_departure');
        $bx_arrival         = From::valInt('bx_arrival');
        $port_inout         = From::valInt('port_inout');
        $bx_order           = From::valInt('bx_order');
        if($port_inout == 1){
            $bx_departure = $bx_arrival = 0;
        }

        if(empty($bx_address) || empty($bx_route) || empty($bx_car_models)){
            Tips::show('保存失败', Link::getLink('box').'?A=box-tuoche');
        }

        // 保存拖车信息
        $result = $this->BoxData->saveTuoche([
            'bx_id' => $bx_id,
            'tuoche_id' => $tuoche_id,
            'bx_address' => $bx_address,
            'bx_route' => $bx_route,
            'bx_description' => $bx_description,
            'bx_freight' => $bx_freight,
            'bx_departure' => $bx_departure,
            'bx_arrival' => $bx_arrival,
            'bx_car_models' => $bx_car_models,
            'bx_order' => $bx_order >= 1 ? $bx_order : 50
        ]);

        if(static::SUCCESS == $result){
            Tips::show('保存成功', Link::getLink('box').'?A=box-tuoche');
        }
        Tips::show('保存失败', Link::getLink('box').'?A=box-tuoche');

    }




    /**
     * 获取拖车信息
     * @param string $action
     */
    public function getdriver(string $action)
    {
        $id = From::valInt('id');
        if($id){
            $list = $this->BoxData->getTuocheDriverlist($id);
            $this->tpl->assign('list', $list);
        }
        $this->tpl->show('User/box_getdriver.html');
    }

    /**
     * @name manage
     * @desciption 添加司机
     */
    public function adddriver(string $action)
    {
        $id = From::valInt('id');
        $tuoche_id = From::valPost('tuoche_id');
        if($id){
            $info = $this->BoxData->getTuocheDriverInfo($id);
            $this->tpl->assign('info', $info);
        }
        $this->tpl->show('User/box_adddriver.html');
    }

    /**
     * @name manage
     * @desciption 保存司机
     */
    public function savedriver(string $action)
    {
        $bd_id      = From::valInt('bd_id');
        $tuoche_id  = From::valPost('tuoche_id');
        $bd_name    = From::valPost('bd_name');
        $bd_mobie   = From::valPost('bd_mobie');
        $bd_head_img   = From::valPost('bd_head_img');
        $bx_order   = From::valPost('bx_order');

        if(!empty($bd_name) && !empty($bd_mobie))
        {
            // 保存拖车信息
            $result = $this->BoxData->saveTuocheDriver([
                'bd_id'         => $bd_id,
                'bx_id'         => $tuoche_id,
                'bd_name'       => $bd_name,
                'bd_head_img'   => $bd_head_img,
                'bd_mobie'      => $bd_mobie,
                'bx_order'      => $bx_order >= 1 ? $bx_order : 50
            ]);
        }

        // 返回JSON
        echo json_encode(['status' => $result], JSON_UNESCAPED_UNICODE);
        exit;

    }

    /**
     * @name manage
     * @desciption 删除司机
     */
    public function deldriver(string $action)
    {
        $id     = From::valInt('id');
        $result = null;
        if($id){
            $result = $this->BoxData->delTuocheDriver(['bd_id'=>$id]);
        }

        // 返回JSON
        echo json_encode(['status' => $result], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * @name manage
     * @desciption 上传头像
     */
    public function driverupimg(string $action)
    {
        $allowAnnx = ['rar', 'zip', 'doc', 'jpg', 'png'];  //允许上传类型
        if(isset($_FILES['bd_head_img']) && isset($_FILES['bd_head_img']['tmp_name']) && strlen($_FILES['bd_head_img']['tmp_name']) > 0)
        {
            $localUrl = $_FILES['bd_head_img']['tmp_name'];
            $annx = '';
            $pos = strrpos($_FILES['bd_head_img']['name'], '.');
            if($pos > 0) $annx = strtolower(substr($_FILES['bd_head_img']['name'], $pos+1));
            if(!in_array($annx, $allowAnnx)){
                Tips::show('不允许的文件格式！', 'javascript: history.back();');
            }
            $newUrl = Load::getUrlRoot();
            $photoUrl = 'Static/data/BoxDriver/'.md5($_SESSION['TOKEN']['INFO']['id'].microtime(true).rand(1000, 9999)).'.'.$annx;
            File::writeString($newUrl.$photoUrl, File::getContent($localUrl));
            $photo = $photoUrl;

            // 返回JSON
            $json = [
                'url'       => $photo,
                'status'    => static::SUCCESS
            ];

            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }

    }





    ###########################################
    /**
     * 获取拖车信息
     * @param string $action
     */
    public function getcar(string $action)
    {
        $id = From::valInt('id');
        if($id){
            $list = $this->BoxData->getTuocheCarlist($id);
            $this->tpl->assign('list', $list);
        }
        $this->tpl->show('User/box_getcar.html');
    }

    /**
     * @name manage
     * @desciption 添加司机
     */
    public function addcar(string $action)
    {
        $id = From::valInt('id');
        if($id){
            $info = $this->BoxData->getTuocheCarInfo($id);
            $this->tpl->assign('info', $info);
        }

        $this->tpl->show('User/box_addcar.html');
    }

    /**
     * @name manage
     * @desciption 保存司机
     */
    public function savecar(string $action)
    {
        $bc_id          = From::valInt('bc_id');
        $tuoche_id      = From::valPost('tuoche_id');
        $bc_models      = From::valPost('bc_models');
        $bc_carnum      = From::valPost('bc_carnum');
        $bc_brand       = From::valPost('bc_brand');
        $bc_number      = From::valInt('bc_number');
        $bc_source      = From::valPost('bc_source');
        $bc_head_img    = From::valPost('bc_head_img');
        $bc_order       = From::valInt('bc_order');

        $bc_number      = !empty($bc_number) ? $bc_number : 1;


        if(!empty($bc_models) && !empty($bc_number))
        {
            // 保存拖车信息
            $result = $this->BoxData->saveTuocheCar([
                'bc_id'         => $bc_id,
                'bx_id'         => $tuoche_id,
                'bc_models'     => $bc_models,
                'bc_carnum'     => $bc_carnum,
                'bc_brand'      => $bc_brand,
                'bc_number'     => $bc_number,
                'bc_source'     => $bc_source,
                'bc_head_img'   => $bc_head_img,
                'bc_order'      => $bc_order >= 1 ? $bc_order : 50
            ]);
        }

        // 返回JSON
        echo json_encode(['status' => $result], JSON_UNESCAPED_UNICODE);
        exit;

    }

    /**
     * @name manage
     * @desciption 删除司机
     */
    public function delcar(string $action)
    {
        $id     = From::valInt('id');
        $result = null;
        if($id){
            $result = $this->BoxData->delTuocheCar(['bd_id'=>$id]);
        }

        // 返回JSON
        echo json_encode(['status' => $result], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * @name manage
     * @desciption 上传头像
     */
    public function carupimg(string $action)
    {
        $allowAnnx = ['rar', 'zip', 'doc', 'jpg', 'png'];  //允许上传类型
        if(isset($_FILES['bc_head_img']) && isset($_FILES['bc_head_img']['tmp_name']) && strlen($_FILES['bc_head_img']['tmp_name']) > 0)
        {
            $localUrl = $_FILES['bc_head_img']['tmp_name'];
            $annx = '';
            $pos = strrpos($_FILES['bc_head_img']['name'], '.');
            if($pos > 0) $annx = strtolower(substr($_FILES['bc_head_img']['name'], $pos+1));
            if(!in_array($annx, $allowAnnx)){
                Tips::show('不允许的文件格式！', 'javascript: history.back();');
            }
            $newUrl = Load::getUrlRoot();
            $photoUrl = 'Static/data/BoxCar/'.md5($_SESSION['TOKEN']['INFO']['id'].microtime(true).rand(1000, 9999)).'.'.$annx;
            File::writeString($newUrl.$photoUrl, File::getContent($localUrl));
            $photo = $photoUrl;

            // 返回JSON
            $json = [
                'url'       => $photo,
                'status'    => static::SUCCESS
            ];

            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }

    }





    /**
     * @name manage
     * @desciption 箱卡管理
     */
    public function manage(string $action)
    {
        // 条件处理
        $whereString = "us_id=".$this->userid;
        //if (!empty($title)) $whereString .= " AND bk_title='{$title}'";

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $Page -> setParam('size', 2);

        //$list = $this->baikeData->getList($whereString, $Page);
        $list = $this->BoxData->getContainerList($whereString,$Page);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('box').'?A=box-manage');
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('containerList', $list);

        $this->tpl->show('User/box_manage.html');
    }


    /**
     * @name releasecontainer
     * @desciption 集装箱发布
     */
    public function releasecontainer(string $action)
    {
        //获取集装箱类型
        $containerType = $this->BoxData->getContainerType();
        //获取集装箱结构
        $containerStructure = $this->BoxData->getContainerStructure();
        //获取集装箱材料
        $containerMaterial = $this->BoxData->getContainerMaterial();
        //获取集装箱型号
        $containerModelnumber = $this->BoxData->getContainerModelnumber();
        // 获取省份
        $containerCity = $this->BoxData->getCity();

        if(isset($_GET['id'])&&!empty($_GET['id'])){
            $id = intval($_GET['id']);
            $goodsInfo = $this->BoxData->getContainerInfo($id);
            $this->tpl->assign("goodsInfo",$goodsInfo);
        }


        $this->tpl->assign("containerType",$containerType);
        $this->tpl->assign("containerStructure",$containerStructure);
        $this->tpl->assign("containerMaterial",$containerMaterial);
        $this->tpl->assign("containerModelnumber",$containerModelnumber);
        $this->tpl->assign("containerCity", $containerCity);
        $this->tpl->show('User/box_releasecontainer.html');
    }

    /**
     * @name releasecontainer
     * @desciption 集装箱发布
     */
    public function releasecontainer_save()
    {
        $cb_is_new           = From::valInt('cb_is_new');
        $cb_title            = From::post('cb_title');
        $cb_sell_point       = From::post('cb_sell_point');
        $cb_number           = From::valInt('cb_number');
        $cb_market_price     = From::valInt('cb_market_price')*100;//产品价格*100 单位：分
        $ca_code             = From::valInt('ca_code');
        $cb_address          = From::post('cb_address');
        $ct_id               = From::valInt('ct_id');
        $cm_id               = From::valInt('cm_id');
        $gcm_id              = From::valInt('gcm_id');
        $cs_id               = From::valInt('cs_id');
        $cb_deadweight       = From::post('cb_deadweight');
        $cb_loadweight       = From::post('cb_loadweight');
        $cb_inner_volume     = From::post('cb_inner_volume');
        $cb_outer_volume     = From::post('cb_outer_volume');
        $cb_inner_size       = From::post('cb_inner_size');
        $cb_outer_size       = From::post('cb_outer_size');
        $cb_photo            = From::post('cb_photo');
        $cbi_introduction    = From::post('cbi_introduction');
        $cb_id               = From::valInt('cb_id');

        $cb_photo = implode(',',$cb_photo);

        if(empty($cb_title) || empty($cb_number) || empty($cb_address)){
            Tips::show('保存失败', Link::getLink('box').'?A=box-tuoche');
        }

        // 保存数据到数据库
        $result = $this->BoxData->saveData([
            'cb_id'               =>$cb_id,
            'cb_is_new'          => $cb_is_new,
            'cb_title'           => $cb_title,
            'cb_market_price'   => $cb_market_price,
            'cb_number'          => $cb_number,
            'ca_code'           => $ca_code,
            'cb_address'         => $cb_address,
            'cb_sell_point'     => $cb_sell_point,
            'ct_id'              => $ct_id,
            'cm_id'              => $cm_id,
            'gcm_id'             => $gcm_id,
            'cs_id'              => $cs_id,
            'cb_deadweight'    => $cb_deadweight,
            'cb_loadweight'    => $cb_loadweight,
            'cb_inner_volume'  => $cb_inner_volume,
            'cb_outer_volume'  => $cb_outer_volume,
            'cb_inner_size'    => $cb_inner_size,
            'cb_outer_size'    => $cb_outer_size,
            'cb_photo'          => $cb_photo,
            'cbi_introduction' => $cbi_introduction
        ]);

        if(static::SUCCESS == $result){
            Tips::show('保存成功', Link::getLink('box').'?A=box-manage');
        }
        Tips::show('保存失败', Link::getLink('box').'?A=box-manage');
    }

    /**
     * 集装箱删除
     */
    public function releasecontainer_del()
    {
        $id      = From::valInt('id');
        if(empty($id)) Tips::show('条件错误', Link::getLink('box').'?A=box-manage');

        // 条件处理
        $whereArray = [];
        if (!empty($id)) $whereArray['cb_id'] = $id;
        $result = $this->BoxData->delData($whereArray);

        // 跳转处理
        if(static::SUCCESS == $result){
            Tips::show('操作成功', Link::getLink('box').'?A=box-manage');
        }
        Tips::show('操作失败', Link::getLink('box').'?A=box-manage');
    }

    /**
     * 上传集装箱图片
     * @param string $action
     */
    public function upimg(string $action)
    {
        $allowAnnx = ['rar', 'zip', 'doc', 'jpg', 'png'];  //允许上传类型
        if(isset($_FILES['cb_photo']) && isset($_FILES['cb_photo']['tmp_name']) && strlen($_FILES['cb_photo']['tmp_name']) > 0)
        {
            $localUrl = $_FILES['cb_photo']['tmp_name'];
            $annx = '';
            $pos = strrpos($_FILES['cb_photo']['name'], '.');
            if($pos > 0) $annx = strtolower(substr($_FILES['cb_photo']['name'], $pos+1));
            if(!in_array($annx, $allowAnnx)){
                Tips::show('不允许的文件格式！', 'javascript: history.back();');
            }
            $newUrl = Load::getUrlRoot();
            $photoUrl = 'Static/data/BoxPhoto/'.md5($_SESSION['TOKEN']['INFO']['id'].microtime(true).rand(1000, 9999)).'.'.$annx;
            File::writeString($newUrl.$photoUrl, File::getContent($localUrl));
            $photo = $photoUrl;

            // 返回JSON
            $json = [
                'url'       => $photo,
                'status'    => static::SUCCESS
            ];

            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * 集装箱下架
     */
    public function releasecontainer_ChangeSaleStatus()
    {
        $id      =  From::valInt('id');
        $status      = intval(From::get('status')); //1：出售   0：下架

        if(empty($id)) Tips::show('条件错误', Link::getLink('box').'?A=box-manage');
        if($status!=0 && $status!=1) Tips::show('操作错误', Link::getLink('box').'?A=box-manage');

        // 条件处理
        $whereArray = [];
        if (!empty($id)) $whereArray['cb_id'] = $id;
        $result = $this->BoxData->containerOnSaleStatus($whereArray,$status);

        // 跳转处理
        if(static::SUCCESS == $result){
            Tips::show('操作成功', Link::getLink('box').'?A=box-manage');
        }
        Tips::show('操作失败', Link::getLink('box').'?A=box-manage');
    }








    /**
     * @name recvconsult
     * @desciption 供应商询价（集装箱/拖车）
     */
    public function recvconsult(string $action)
    {
        $type = From::valInt('type');
        $type = !empty($type) ? $type : 1;
        $this->tpl->assign('type', $type);
        $au_id  = $_SESSION['TOKEN']['INFO']['id'];

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 8);

        $list = $this->BoxData->getTuocheConsult($whereString = "au_id={$au_id} AND bc_type={$type}", $Page);
        $pageList = $Page->getPage(Link::getLink('box') . '?A=box-recvconsult&type=' . $type);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('list', $list);
        $this->tpl->show('User/box_recvconsult.html');
    }

    /**
     * @name recvconsult
     * @desciption 供应商订单（集装箱/拖车）
     */
    public function recvorder(string $action)
    {
        $Gjmy = new Gjmy();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'au_id='.$usId.' AND co_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('container_order').' WHERE '.$whereString.' ORDER BY co_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val) {
                $val['co_price'] = sprintf("%.2f", $val['co_price']);
                $val['co_total'] = sprintf("%.2f", $val['co_total']);
                $val['co_fare'] = sprintf("%.2f", $val['co_fare']);
                $val['co_offers'] = sprintf("%.2f", $val['co_offers']);

                // 获取集装箱详细信息
                $info = $this->BoxData->getContainerInfo(intval($val['ss_id']));
                $cinfo = $Gjmy->getEnterprise(intval($info['us_id']));
                $dataList[] = array_merge($val, $info, $cinfo);
            }
        }
        $pageList = $Page -> getPage(Link::getLink('box').'?A=box-recvorder');
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('dataList', $dataList);
        $this->tpl->assign('action', 'recvorder');
        $this->tpl->show('User/box_recvorder.html');
    }


    /**
     * 修改订单状态
     * @param string $action
     */
    public function recvordercheak(string $action)
    {
        $orderid = From::valInt('orderid');
        $status = From::valInt('status');
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'UPDATE ' . $Db->getTableNameAll('container_order') . ' SET co_result=' . $status . ' WHERE co_id=' . $orderid . ' AND co_isdel=0';
        $result = $Db->getDataNum($sql);
        echo json_encode(['status' => $result], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 查看订单详情
     * @param string $action
     */
    public function recvordermor(string $action)
    {
        $Gjmy = new Gjmy();
        $Db = Db::tag('DB.USER', 'GMY');
        $id = From::valInt('id');

        // 获取集装箱型号
        $type = $this->BoxData->getContainerType();
        $this->tpl->assign('type', $type);

        // 获取集装箱结构
        $structure = $this->BoxData->getContainerStructure();
        $this->tpl->assign('structure', $structure);

        // 获取集装箱材料
        $material = $this->BoxData->getContainerMaterial();
        $this->tpl->assign('material', $material);

        // 获取订单详细信息
        $whereString = 'co_id='.$id.' AND co_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('container_order').' WHERE '.$whereString.' ORDER BY co_last_time DESC';
        $orderinfo = $Db -> getDataOne($sql);
        $orderinfo['co_price'] = sprintf("%.2f", $orderinfo['co_price']);
        $orderinfo['co_total'] = sprintf("%.2f", $orderinfo['co_total']);
        $orderinfo['co_fare'] = sprintf("%.2f", $orderinfo['co_fare']);
        $orderinfo['co_offers'] = sprintf("%.2f", $orderinfo['co_offers']);

        // 获取集装箱详细信息
        $info = $this->BoxData->getContainerInfo(intval($orderinfo['ss_id']));
        $userBasic = $Gjmy -> getBasic(intval($orderinfo['us_id']));
        $userEnt   = $Gjmy->getEnterprise(intval($orderinfo['us_id']));
        $dataInfo['user']  = array_merge($userBasic, $userEnt);

        $adminBasic = $Gjmy -> getBasic(intval($orderinfo['au_id']));
        $adminEnt   = $Gjmy->getEnterprise(intval($orderinfo['au_id']));
        $dataInfo['admin']  = array_merge($adminBasic, $adminEnt);

        $dataInfo = array_merge($dataInfo, $orderinfo, $info);

//        echo '<pre>';
//        print_r($dataInfo);
//        exit;

        $this->tpl->assign('dataInfo', $dataInfo);
        $this->tpl->show('User/box_recvordermor.html');
    }


    ##############################################################################################################
    ##############################################################################################################
    ##############################################################################################################
    ##############################################################################################################


    /**
     * @name recvconsult
     * @desciption 采购询价（集装箱/拖车）
     */
    public function sendconsult(string $action)
    {
        $type = From::valInt('type');
        $type = !empty($type) ? $type : 1;
        $this->tpl->assign('type', $type);
        $us_id  = $_SESSION['TOKEN']['INFO']['id'];

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        $Page->setParam('size', 8);

        $list = $this->BoxData->getTuocheConsult($whereString = "us_id={$us_id} AND bc_type={$type}", $Page);
        $pageList = $Page->getPage(Link::getLink('box') . '?A=box-sendconsult&type=' . $type);
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('list', $list);
        $this->tpl->show('User/box_sendconsult.html');
    }

    /**
     * @name recvconsult
     * @desciption 采购订单（集装箱/拖车）
     */
    public function sendorder(string $action)
    {
        $Gjmy = new Gjmy();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND co_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('container_order').' WHERE '.$whereString.' ORDER BY co_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val) {
                $val['co_price'] = sprintf("%.2f", $val['co_price']);
                $val['co_total'] = sprintf("%.2f", $val['co_total']);
                $val['co_fare'] = sprintf("%.2f", $val['co_fare']);
                $val['co_offers'] = sprintf("%.2f", $val['co_offers']);

                // 获取集装箱详细信息
                $info = $this->BoxData->getContainerInfo(intval($val['ss_id']));
                $cinfo = $Gjmy->getEnterprise(intval($info['us_id']));
                $dataList[] = array_merge($val, $info, $cinfo);
            }
        }
        $pageList = $Page -> getPage(Link::getLink('box').'?A=box-recvorder');
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('dataList', $dataList);
        $this->tpl->assign('action', 'sendorder');
        $this->tpl->show('User/box_sendorder.html');
    }

    /**
     * 拖车订单(采购)
     * @param string $action
     */
    public function tuochesendorder(string $action)
    {
        $Gjmy = new Gjmy();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND or_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('box_order').' WHERE '.$whereString.' ORDER BY or_create_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val) {
                // 获取集装箱详细信息
                $info = $this->BoxData->getTuocheInfo(intval($val['ss_id']));
                $userBasic = $Gjmy -> getBasic(intval($info['us_id']));
                $userEnt   = $Gjmy->getEnterprise(intval($info['us_id']));
                $dataInfo['user']  = array_merge($userBasic, $userEnt);
                $dataList[] = array_merge($val, $info, $dataInfo);
            }
        }

        $pageList = $Page -> getPage(Link::getLink('box').'?A=box-tuochesendorder');
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('dataList', $dataList);
        $this->tpl->assign('action', 'tuochesendorder');
        $this->tpl->show('User/box_tuochesendorder.html');
    }


    /**
     * 拖车订单(供应商)
     * @param string $action
     */
    public function tuocherecvorder(string $action)
    {
        $Gjmy = new Gjmy();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'au_id='.$usId.' AND or_isdel=0';
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('box_order').' WHERE '.$whereString.' ORDER BY or_create_time DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val) {
                // 获取集装箱详细信息
                $info = $this->BoxData->getTuocheInfo(intval($val['ss_id']));
                $userBasic = $Gjmy -> getBasic(intval($info['us_id']));
                $userEnt   = $Gjmy->getEnterprise(intval($info['us_id']));
                $dataInfo['user']  = array_merge($userBasic, $userEnt);
                $dataList[] = array_merge($val, $info, $dataInfo);
            }
        }

//        echo '<pre>';
//        print_r($dataList);
//        exit;

        $pageList = $Page -> getPage(Link::getLink('box').'?A=box-tuochesendorder');
        $this->tpl->assign('pageList', $pageList);
        $this->tpl->assign('dataList', $dataList);
        $this->tpl->assign('action', 'tuocherecvorder');
        $this->tpl->show('User/box_tuocherecvorder.html');
    }



    /**
     * 修改订单状态
     * @param string $action
     */
    public function tuocherecvordercheak(string $action)
    {
        $orderid = From::valInt('orderid');
        $status = From::valInt('status');
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'UPDATE ' . $Db->getTableNameAll('box_order') . ' SET or_status=' . $status . ' WHERE or_id=' . $orderid . ' AND or_isdel=0';
        $result = $Db->getDataNum($sql);
        echo json_encode(['status' => $result], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 查看订单详情
     * @param string $action
     */
    public function tuocherecvordermor(string $action)
    {
        $Gjmy = new Gjmy();
        $Db = Db::tag('DB.USER', 'GMY');
        $id = From::valInt('id');
        $currTime   = Time::getTimeStamp();
        $usId  = intval($this -> userInfo['id']);
        $whereString = 'or_id='.$id.' AND or_isdel=0';
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('box_order').' WHERE '.$whereString.' ORDER BY or_create_time DESC';
        $orderinfo = $Db -> getDataOne($sql);

        if(isset($orderinfo) && is_array($orderinfo)){
            // 获取拖车地址
            $tuoche = $this->BoxData->getBoxAddress();
            $this->tpl->assign('departure', $tuoche['departure']);
            $this->tpl->assign('arrival', $tuoche['arrival']);

            // 获取车型
            $carModels = $this->BoxData->getBoxCarModels();
            $this->tpl->assign('carModels', $carModels);

            // 获取司机信息
            $driver = $this->BoxData->getTuocheDriverlist(intval($orderinfo['ss_id']));
            $this->tpl->assign('driver', $driver);

            // 获取车辆信息
            $car = $this->BoxData->getTuocheCarlist(intval($orderinfo['ss_id']));
            $this->tpl->assign('car', $car);

            // 获取集装箱详细信息
            $info = $this->BoxData->getTuocheInfo(intval($orderinfo['ss_id']));

            $userBasic = $Gjmy -> getBasic(intval($orderinfo['us_id']));
            $userEnt   = $Gjmy->getEnterprise(intval($orderinfo['us_id']));
            $dataInfo['user']  = array_merge($userBasic, $userEnt);

            $adminBasic = $Gjmy -> getBasic(intval($orderinfo['au_id']));
            $adminEnt   = $Gjmy->getEnterprise(intval($orderinfo['au_id']));
            $dataInfo['admin']  = array_merge($adminBasic, $adminEnt);

            $dataInfo = array_merge($orderinfo, $info, $dataInfo);
        }

//        echo '<pre>';
//        print_r($dataInfo);
//        exit;

        $this->tpl->assign('dataInfo', $dataInfo);
        $this->tpl->show('User/box_tuocherecvordermor.html');
    }

}