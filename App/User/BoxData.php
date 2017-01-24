<?php
/**
 * @Copyright (C) 2016.
 * @Description MyZhidao
 * @FileName MyZhidao.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types = 1);//strict
namespace App\User;

use \App\Pub\Common;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Tag\Db;
use \Libs\Comm\Net;
use \Libs\Tag\Page;
use \App\Auth\MyAuth;
use \Libs\Frame\Conf;


class BoxData {
    public $userid;
    private $Db         = NULL;         //数据库对象
    const SUCCESS       = "success";    // 成功
    const FAIL          = "fail";       // 失败
    const ISDEL         = 0;            // 删除

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.USER', 'GMY');

        $this->userid       = $_SESSION['TOKEN']['INFO']['id'];
    }

    /**
     * 获取集装箱类型
     */
    public function getContainerType()
    {
        // 获取数据
        $sql    = 'SELECT ct_id,ct_title FROM '.$this -> Db -> getTableNameAll('container_type').' WHERE ct_is_show= 0 ORDER BY ct_sort DESC, ct_id ASC';
        $results = $this -> Db -> getData($sql);
        return $results;
    }

    /**
     * 获取集装箱结构
     */
    public function getContainerStructure()
    {
        // 获取数据
        $sql    = 'SELECT cs_id,cs_title FROM '.$this -> Db -> getTableNameAll('container_structure').' WHERE cs_is_show= 0 ORDER BY cs_sort DESC, cs_id ASC';
        $results = $this -> Db -> getData($sql);
        return $results;
    }

    /**
     * 获取集装箱材料
     */
    public function getContainerMaterial()
    {
        // 获取数据
        $sql    = 'SELECT gcm_id,gcm_title FROM '.$this -> Db -> getTableNameAll('container_material').' WHERE gcm_is_show= 0 ORDER BY gcm_sort DESC, gcm_id ASC';
        $results = $this -> Db -> getData($sql);
        return $results;
    }

    /**
     * 获取集装箱型号
     */
    public function getContainerModelnumber()
    {
        // 获取数据
        $sql = 'SELECT cm_id,cm_title FROM ' . $this->Db->getTableNameAll('container_modelnumber') . ' WHERE cm_is_show= 0 ORDER BY cm_sort DESC, cm_id ASC';
        $results = $this->Db->getData($sql);
        return $results;
    }

    /**
     * 获取省份
     */
    public function getCity()
    {
        $results = $this->Db->getData('SELECT ca_code,ca_name FROM ' . $this->Db->getTableNameAll('data_area') . ' WHERE ca_code like \'1001___0000\'');
        return $results;
    }


    /**
     * 获取集装箱列表
     */
    public function getContainerList(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'cb_is_del= 0';

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$this -> Db -> getTableNameAll('container_box').' WHERE '.$whereString.' ORDER BY cb_id DESC LIMIT '.$limit[0].', '.$limit[1];

        $result = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 数据处理
        foreach($result as $k =>$v){
            if($v['cb_photo'])
            $result[$k]['cb_photo']= explode(',',$v['cb_photo']);
        }
        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * 获取集装箱详情
     */
    public function getContainerInfo(int $id)
    {
        $sql    = 'SELECT  a.*,b.cbi_introduction FROM '.$this -> Db -> getTableNameAll('container_box').' as a left join '.$this -> Db -> getTableNameAll('container_box_info').' as b on a.cb_id = b.cb_id WHERE  a.cb_id= '.$id;
        $result = $this -> Db -> getData($sql);
        // 数据处理
        foreach($result as $k =>$v){
            if (isset($v['cb_photo'])){
                $photo      = '';
                $photoTemp  = explode(',', $v['cb_photo']);
                foreach($photoTemp as $val){
                    $photo[] = $this->getThumbImgUrl($val);
                }

                // 默认头像
                if(empty($photo[0])){
                    $photo[0] = $this->getThumbImgUrl('images/default.jpg');
                }
                $result[$k]['cb_photo'] = $photo;
            }

//            if($v['cb_photo'])
//            $result[$k]['cb_photo']= explode(',',$v['cb_photo']);
        }
        return isset($result[0]) && !empty($result[0]) ? $result[0] : [];
    }

    public function deleteDate(int $id)
    {
        $sql = 'DELETE FROM ' . $this->Db->getTableNameAll('container_box') . ' WHERE cb_id = '.$id;
    }


    /**
     * @name signout
     * @desciption 保存知道数据
     */
    public function saveData(array $data = [])
    {
        $cb_id = !empty($data['cb_id'])? intval($data['cb_id']) : 0;
        if(empty($cb_id)) {
            $sql = 'INSERT INTO ' . $this->Db->getTableNameAll('container_box') . ' SET us_id='.$this->userid.',cb_title=\'' . addslashes($data[cb_title]) .'\',cb_market_price=\'' . $data[cb_market_price] . '\',
                cb_is_new=' . $data[cb_is_new] . ',cb_number=' . $data[cb_number] . ', cb_sell_point=\'' . $data[cb_sell_point] . '\', ca_code='.$data['ca_code'].', cb_address=\'' . $data[cb_address] . '\',gcm_id=' . $data[gcm_id] . ',ct_id=' . $data[ct_id] . ',cm_id=' . $data[cm_id] . ',cb_deadweight=\'' . $data[cb_deadweight] . '\',cb_loadweight=\'' . $data[cb_loadweight] . '\',cb_inner_volume=\'' . $data[cb_inner_volume] . '\',cb_outer_volume=\'' . $data[cb_outer_volume] . '\',cb_inner_size=\'' . $data[cb_inner_size] . '\',cb_outer_size=\'' . $data[cb_outer_size] . '\',cs_id=' . $data[cs_id] . ',cb_photo=\'' . $data[cb_photo] . '\',cb_is_onsale= 1,cb_ctime='.time();

            $result_id = $this->Db->getDataId($sql);

            if($result_id){
                $sqlInfo        = 'INSERT INTO ' . $this->Db->getTableNameAll('container_box_info') . ' SET cb_id=' . $result_id . ', cbi_introduction=\'' . addslashes($data[cbi_introduction]) .'\'';
                $resultInfo     = $this->Db->getDataId($sqlInfo);
            }
        }else{
            $sql = 'UPDATE  ' . $this->Db->getTableNameAll('container_box') . ' SET cb_title=\'' . addslashes($data[cb_title]) .'\',cb_market_price=\'' . $data[cb_market_price] . '\',
                cb_is_new=' . $data[cb_is_new] . ',cb_number=' . $data[cb_number] . ', cb_sell_point=\'' . $data[cb_sell_point] . '\', ca_code='.$data['ca_code'].', cb_address=\'' . $data[cb_address] . '\',gcm_id=' . $data[gcm_id] . ',ct_id=' . $data[ct_id] . ',cm_id=' . $data[cm_id] . ',cb_deadweight=\'' . $data[cb_deadweight] . '\',cb_loadweight=\'' . $data[cb_loadweight] . '\',cb_inner_volume=\'' . $data[cb_inner_volume] . '\',cb_outer_volume=\'' . $data[cb_outer_volume] . '\',cb_inner_size=\'' . $data[cb_inner_size] . '\',cb_outer_size=\'' . $data[cb_outer_size] . '\',cs_id=' . $data[cs_id] . ',cb_photo=\'' . $data[cb_photo] . '\' WHERE cb_id='.$cb_id.'';

            $result_id      = $this->Db->getDataNum($sql);

            if($cb_id){
                $sqlInfo        = 'UPDATE ' . $this->Db->getTableNameAll('container_box_info') . ' SET cbi_introduction=\'' . addslashes($data[cbi_introduction]) .'\' WHERE cb_id='.$cb_id;
                $resultInfo     = $this->Db->getDataNum($sqlInfo);
            }
        }

        // 返回结果
        if($resultInfo || $result_id){
            return static::SUCCESS;
        }
        return static::FAIL;
    }

    /**
     * 删除
     * @param array $whereArray
     * @return string
     */
    public function delData(array $whereArray = [])
    {
        if(!isset($whereArray['cb_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'us_id='.$this->userid.' AND cb_is_del= 0';
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 标题
                case 'cb_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'cb_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('container_box').' SET cb_is_del=1 WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }

    /**
     * 集装箱上下架
     * @param array $whereArray
     * @param int $status
     * @return string
     */
    public function containerOnSaleStatus(array $whereArray = [],int $status)
    {
        if(!isset($whereArray['cb_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'us_id='.$this->userid;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 标题
                case 'cb_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'cb_id = '.intval($val);
                    break;
                }
            }
        }

        // 集装箱上下架
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('container_box').' SET cb_is_onsale='.$status.' WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }


    /**
     * 获取拖车地址信息
     * @param string $where
     */
    public function getBoxAddress(String $whereString='', bool $isGroup = true)
    {
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'ba_isdel='.static::ISDEL;
        $sql    = "SELECT ba_id,ba_name,ba_type,ba_order FROM ".$this -> Db -> getTableNameAll('box_address')." WHERE {$whereString} ORDER BY ba_order DESC, ba_id ASC";
        $results = $this -> Db -> getData($sql);

        if(true === $isGroup && is_array($results)){
            $resultTmp = [];
            foreach($results as $key=>$val){
                if($val['ba_type'] == 1){
                    // 出发地
                    $resultTmp['departure'][] = $val;
                }else{
                    // 目的地
                    $resultTmp['arrival'][] = $val;
                }
            }

            $results = $resultTmp;
            unset($resultTmp);
        }

        return $results;
    }


    /**
     * 获取车型
     * @param String $whereString
     * @return mixed
     */
    public function getBoxCarModels(String $whereString='')
    {
        $whereString = (empty($whereString) ? $whereString : rtrim($whereString, 'AND') . ' AND ') . 'bcm_isdel=' . static::ISDEL;
        $sql = "SELECT bcm_id,bcm_name,bcm_order FROM " . $this->Db->getTableNameAll('box_car_models') . " WHERE {$whereString} ORDER BY bcm_order DESC, bcm_id ASC";
        $results = $this->Db->getData($sql);
        return $results;
    }


    /**
     * 获取详细信息
     */
    public function getTuocheInfo(int $bx_id = 0)
    {
        // 获取百科
        $sql    = 'SELECT bx_id,us_id,bx_address,bx_route,bx_freight,bx_description,bx_departure,bx_arrival,bx_car_models,bx_order,bx_release_ip,bx_update_time';
        $sql    .= ' FROM '.$this -> Db -> getTableNameAll('box').' WHERE bx_id='.$bx_id.' AND bx_isdel='.static::ISDEL;
        $result = $this->Db->getDataOne($sql);
        return $result;
    }


    /**
     * 获取拖车列表
     * @param string $whereString
     * @param Page $Page
     * @return array
     */
    public function getTuocheList(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'bx_isdel='.static::ISDEL;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS bx_id,us_id,bx_address,bx_route,bx_freight,bx_description,bx_departure,bx_arrival,bx_car_models,bx_order,bx_release_ip,bx_update_time';
        $sql    .= ' FROM '.$this -> Db -> getTableNameAll('box').' WHERE '.$whereString.' ORDER BY bx_id DESC LIMIT '.$limit[0].', '.$limit[1];
        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 数据处理
        foreach($results as $val){
            $val['bx_release_ip'] = Net::longIp(intval($val['bx_release_ip']));
            $result[] = $val;
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }


    /**
     * 保存拖车信息
     * @param array $data
     */
    public function saveTuoche(array $data = [])
    {
        $bx_release_ip      = Net::getIpLong();
        $currTime           = Time::getTimeStamp();
        $bx_freight         = isset($data['bx_freight']) ? $data['bx_freight'] : '面议';
        $bx_id              = isset($data['bx_id']) && !empty($data['bx_id']) ? $data['bx_id'] : 0;

        if(empty($bx_id)) {
            $sql = 'INSERT INTO '.$this->Db->getTableNameAll('box') .' SET
            us_id=' . $this->userid . ',
            bx_address=\'' . addslashes($data['bx_address']) .'\',
            bx_route=\'' . addslashes($data['bx_route']) .'\',
            bx_description=\'' . addslashes($data['bx_description']) . '\',
            bx_freight = \''.$bx_freight.'\',
            bx_departure=' . $data['bx_departure'] . ',
            bx_arrival='.$data['bx_arrival'].',
            bx_car_models=' . $data['bx_car_models'] . ',
            bx_order=' . $data['bx_order'] . ',
            bx_release_ip=' . $bx_release_ip . ',
            bx_create_time=' . $currTime . ',
            bx_update_time=' . $currTime . ',
            bx_isdel=' . static::ISDEL;
            $result_id = $this->Db->getDataId($sql);

            $tuoche_id = isset($data['tuoche_id']) ? $data['tuoche_id'] : 0;
            if($tuoche_id){
                // 更新司机
                $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('box_driver').' SET bx_id='.$result_id.' WHERE bd_isdel=0 AND bx_id='.$tuoche_id;
                $this -> Db -> getDataNum($sql);

                // 更新拖车
                $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('box_car').' SET bx_id='.$result_id.' WHERE bc_isdel=0 AND bx_id='.$tuoche_id;
                $this -> Db -> getDataNum($sql);
            }


        }else{
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('box').' SET
            bx_address=\'' . addslashes($data['bx_address']) .'\',
            bx_route=\'' . addslashes($data['bx_route']) .'\',
            bx_description=\'' . addslashes($data['bx_description']) . '\',
            bx_freight = \''.$bx_freight.'\',
            bx_departure=' . $data['bx_departure'] . ',
            bx_arrival='.$data['bx_arrival'].',
            bx_car_models=' . $data['bx_car_models'] . ',
            bx_order=' . $data['bx_order'] . ',
            bx_update_time=' . $currTime . ' WHERE bx_id='.$bx_id.' AND us_id='.$this->userid.' AND bx_isdel='.static::ISDEL;
            $result_id      = $this->Db->getDataNum($sql);
        }

        // 返回结果
        if($result_id){
            return static::SUCCESS;
        }
        return static::FAIL;
    }


    /**
     * 删除拖车信息
     * @param array $whereArray
     * @return string
     */
    public function delTuoche(array $whereArray = [])
    {
        if(!isset($whereArray['bx_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'us_id='.$this->userid.' AND bx_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 标题
                case 'bx_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'bx_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除附件
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('box').' SET bx_isdel=1 WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }


    /**
     * 获取拖车信息
     * @param int $id
     * @return array
     */
    public function getTuocheDriverlist(int $id)
    {
        $result     = [];
        $sql        = 'SELECT  bd_id,bx_id,bd_name,bd_head_img,bd_mobie,bd_order FROM '.$this -> Db -> getTableNameAll('box_driver').' WHERE bx_id= '.$id.' AND bd_isdel='.static::ISDEL;
        $resultTemp = $this -> Db -> getData($sql);
        foreach($resultTemp as $key=>$val){
            if(!empty($val)) {
                $val['bd_head_img_source'] = $val['bd_head_img'];
                $val['bd_head_img'] = !empty($val['bd_head_img']) ? $this->getThumbImgUrl($val['bd_head_img']) : '';
                $result[] = $val;
            }
        }
        return is_array($result) ? $result : [];
    }


    /**
     * 获取司机详细信息
     * @param $id
     * @return array|void
     */
    public function getTuocheDriverInfo($id)
    {
        $result     = [];
        $sql        = 'SELECT  bd_id,bx_id,bd_name,bd_head_img,bd_mobie,bd_order FROM '.$this -> Db -> getTableNameAll('box_driver').' WHERE bd_id= '.$id.' AND bd_isdel='.static::ISDEL;
        $result     = $this -> Db -> getData($sql);
        $result[0]['bd_head_img_source'] = isset($result[0]['bd_head_img']) && !empty($result[0]['bd_head_img']) ? $result[0]['bd_head_img'] : '';
        $result[0]['bd_head_img'] = isset($result[0]['bd_head_img']) && !empty($result[0]['bd_head_img']) ? $this->getThumbImgUrl($result[0]['bd_head_img']) : '';
        return isset($result[0]) && !empty($result[0]) ? $result[0] : [];
    }


    /**
     * 删除拖车信息
     * @param $id
     */
    public function delTuocheDriver($whereArray)
    {
        if(!isset($whereArray['bd_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'bd_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                case 'bd_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'bd_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除附件
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('box_driver').' SET bd_isdel=1 WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }

    /**
     * 保存拖车司机
     * @param $data
     * @return string
     */
    public function saveTuocheDriver($data)
    {
        $bx_release_ip      = Net::getIpLong();
        $currTime           = Time::getTimeStamp();
        $bd_order           = isset($data['bd_order']) ? intval($data['bd_order']) : 50;
        $bx_id              = isset($data['bx_id']) ? trim($data['bx_id']) : 0;
        $bd_id              = isset($data['bd_id']) ? intval($data['bd_id']) : 0;

        if(empty($bd_id)) {
            $sql = 'INSERT INTO '.$this->Db->getTableNameAll('box_driver') .' SET
            bx_id=' . $bx_id . ',
            bd_name=\'' . addslashes($data['bd_name']) .'\',
            bd_head_img=\'' . addslashes($data['bd_head_img']) .'\',
            bd_mobie=\'' . addslashes($data['bd_mobie']) . '\',
            bd_order=' . $bd_order . ',
            bd_create_time=' . $currTime . ',
            bd_update_time=' . $currTime . ',
            bd_isdel=' . static::ISDEL;
            $result_id = $this->Db->getDataId($sql);

        }else{
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('box_driver').' SET
            bd_name=\'' . addslashes($data['bd_name']) .'\',
            bd_head_img=\'' . addslashes($data['bd_head_img']) .'\',
            bd_mobie=\'' . addslashes($data['bd_mobie']) . '\',
            bd_order=' . $bd_order . ',
            bd_update_time=' . $currTime . ' WHERE bd_id='.$bd_id.' AND bx_id='.$bx_id.' AND bd_isdel='.static::ISDEL;
            $result_id      = $this->Db->getDataNum($sql);
        }

        // 返回结果
        if($result_id){
            return static::SUCCESS;
        }
        return static::FAIL;
    }



######################################################################\

    public function getTuocheCarModels()
    {
        $result     = [];
        $sql        = 'SELECT  bcm_id,bcm_name,bcm_order FROM '.$this -> Db -> getTableNameAll('box_car_models').' WHERE bcm_isdel='.static::ISDEL;
        $result     = $this -> Db -> getData($sql);
        return isset($result) && !empty($result) ? $result : [];
    }

    /**
     * 获取拖车信息
     * @param int $id
     * @return array
     */
    public function getTuocheCarlist(int $id)
    {
        $result     = [];
        $sql        = 'SELECT  bc_id,bx_id,bc_models,bc_carnum,bc_brand,bc_number,bc_source,bc_head_img,bc_order FROM '.$this -> Db -> getTableNameAll('box_car').' WHERE bx_id= '.$id.' AND bc_isdel='.static::ISDEL;
        $resultTemp = $this -> Db -> getData($sql);
        foreach($resultTemp as $key=>$val){
            if(!empty($val)){
                $val['bc_head_img_source'] = $val['bc_head_img'];
                $val['bc_head_img'] = !empty($val['bc_head_img']) ? $this->getThumbImgUrl($val['bc_head_img']) : '';
                $result[] = $val;
            }
        }
        return is_array($result) ? $result : [];
    }


    /**
     * 获取司机详细信息
     * @param $id
     * @return array|void
     */
    public function getTuocheCarInfo($id)
    {
        $result     = [];
        $sql        = 'SELECT  bc_id,bx_id,bc_models,bc_carnum,bc_brand,bc_number,bc_source,bc_head_img,bc_order FROM '.$this -> Db -> getTableNameAll('box_car').' WHERE bc_id= '.$id.' AND bc_isdel='.static::ISDEL;
        $result     = $this -> Db -> getData($sql);
        $result[0]['bc_head_img_source'] = isset($result[0]['bc_head_img']) && !empty($result[0]['bc_head_img']) ? $result[0]['bc_head_img'] : '';
        $result[0]['bc_head_img'] = isset($result[0]['bc_head_img']) && !empty($result[0]['bc_head_img']) ? $this->getThumbImgUrl($result[0]['bc_head_img']) : '';
        return isset($result[0]) && !empty($result[0]) ? $result[0] : [];
    }


    /**
     * 删除拖车信息
     * @param $id
     */
    public function delTuocheCar($whereArray)
    {
        if(!isset($whereArray['bc_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'bc_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                case 'bc_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'bc_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除附件
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('box_car').' SET bc_isdel=1 WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }

    /**
     * 保存拖车司机
     * @param $data
     * @return string
     */
    public function saveTuocheCar($data)
    {
        $bx_release_ip      = Net::getIpLong();
        $currTime           = Time::getTimeStamp();
        $bc_order           = isset($data['bc_order']) ? intval($data['bc_order']) : 50;
        $bx_id              = isset($data['bx_id']) ? trim($data['bx_id']) : 0;
        $bc_id              = isset($data['bc_id']) ? intval($data['bc_id']) : 0;

        if(empty($bc_id)) {
            $sql = 'INSERT INTO '.$this->Db->getTableNameAll('box_car') .' SET
            bx_id=' . $bx_id . ',
            bc_models=\'' . addslashes($data['bc_models']) .'\',
            bc_carnum=\'' . $data['bc_carnum'] .'\',
            bc_brand=\'' . addslashes($data['bc_brand']) . '\',
            bc_number=' . $data['bc_number'] . ',
            bc_source=\'' . addslashes($data['bc_source']) . '\',
            bc_head_img=\'' . $data['bc_head_img'] . '\',
            bc_order=' . $bc_order . ',
            bc_create_time=' . $currTime . ',
            bc_update_time=' . $currTime . ',
            bc_isdel=' . static::ISDEL;
            $result_id = $this->Db->getDataId($sql);

        }else{
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('box_car').' SET
            bc_models=\'' . addslashes($data['bc_models']) .'\',
            bc_carnum=\'' . $data['bc_carnum'] .'\',
            bc_brand=\'' . addslashes($data['bc_brand']) . '\',
            bc_number=' . $data['bc_number'] . ',
            bc_source=\'' . addslashes($data['bc_source']) . '\',
            bc_head_img=\'' . $data['bc_head_img'] . '\',
            bc_order=' . $bc_order . ',
            bc_update_time=' . $currTime . ' WHERE bc_id='.$bc_id.' AND bx_id='.$bx_id.' AND bc_isdel='.static::ISDEL;
            $result_id      = $this->Db->getDataNum($sql);
        }

        // 返回结果
        if($result_id){
            return static::SUCCESS;
        }
        return static::FAIL;
    }


    /**
     * 获取咨询信息
     * @param int $id
     * @return array
     */
    public function getTuocheConsult(string $where='', Page $Page)
    {
        $limit  = $Page -> getLimit();
        $where = (empty($where) ? '' : rtrim($where, 'AND') . ' AND ') . 'bc_isdel='.static::ISDEL;
        $sql        = 'SELECT SQL_CALC_FOUND_ROWS bc_id,au_id,us_id,bc_type,bc_title,bc_freight,bc_count,bc_description,bc_name,bc_phone,ss_id,bc_create_time,bc_isdel FROM '.$this -> Db -> getTableNameAll('box_consult').' WHERE '.$where.' ORDER BY bc_id DESC '.' LIMIT '.$limit[0].', '.$limit[1];
        $result = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        return is_array($result) ? $result : [];
    }





    /**
     * 获取图片地址
     * @param string $url
     */
    public function getThumbImgUrl(string $url='')
    {
        if(empty($url)) return '';

        $host = Conf::get('URL.RES');

        $url        = ltrim($url, '/');
        $url        = str_replace('Static/data/', '', $url);
        $hostUrl    = rtrim($host, '/').'/';
        return $hostUrl.$url;
    }

}