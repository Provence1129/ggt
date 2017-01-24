<?php
/**
 * @Copyright (C) 2016.
 * @Description MyZhidao
 * @FileName MyZhidao.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types = 1);//strict
namespace App\Box;

use App\Ent\EntData;
use \App\Pub\Common;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Tag\Db;
use \Libs\Comm\Net;
use \Libs\Tag\Page;
use \App\Auth\MyAuth;
use \Libs\Frame\Conf;


class BoxModel
{
    public $userid;
    private $Db = NULL;         //数据库对象
    const SUCCESS = "success";    // 成功
    const FAIL = "fail";       // 失败
    const ISDEL = 0;            // 删除

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct()
    {
        $this->Db = Db::tag('DB.USER', 'GMY');

        $this->userid = $_SESSION['TOKEN']['INFO']['id'];
    }


    /**
     * 获取集装箱详情
     */
    public function getContainerInfo(int $id)
    {
        $sql    = 'SELECT  a.*,b.cbi_introduction FROM '.$this -> Db -> getTableNameAll('container_box').' as a left join '.$this -> Db -> getTableNameAll('container_box_info').' as b on a.cb_id = b.cb_id WHERE  a.cb_id= '.$id;
        $result = $this -> Db -> getData($sql);
        // 数据处理
        foreach($result as $k =>$v) {
            if (isset($v['cb_photo'])){
                $photo = '';
                $photoTemp = explode(',', $v['cb_photo']);
                foreach ($photoTemp as $val) {
                    $photo[] = $this->getThumbImgUrl($val);
                }

                // 默认头像
                if (empty($photo[0])) {
                    $photo[0] = $this->getThumbImgUrl('images/default.jpg');
                }
                $result[$k]['cb_photo'] = $photo;
            }
        }
        return isset($result[0]) && !empty($result[0]) ? $result[0] : [];
    }

    public function getBannerData(int $id){
        $sql = 'SELECT banner.* FROM '.$this->Db -> getTableNameAll('banner').' as  banner where banner.id = '.$id;
        $topbanner = $this->Db -> getDataOne($sql);
        return $topbanner;
    }
    /**
     * 获取集装箱列表
     */
    public function getContainerList(string $whereString = '', Page $Page, $order = "cb_id DESC")
    {
        $limit = $Page->getLimit();

        // 条件处理
        $whereString = (empty($whereString) ? $whereString : rtrim($whereString, 'AND') . ' AND ') . 'cb_is_del= 0';

        // 获取数据
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $this->Db->getTableNameAll('container_box') . ' WHERE ' . $whereString . ' ORDER BY '.$order.' LIMIT ' . $limit[0] . ', ' . $limit[1];
        $result = $this->Db->getData($sql);
        $totalNum = $this->Db->getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page->setParam('totalNum', $totalNum);

        // 数据处理
        foreach ($result as $k => $v) {
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
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }


    /**
     * 获取集装箱类型
     */
    public function getContainerType()
    {
        // 获取数据
        $results = [];
        $sql    = 'SELECT ct_id,ct_title FROM '.$this -> Db -> getTableNameAll('container_type').' WHERE ct_is_show= 0 ORDER BY ct_sort DESC, ct_id ASC';
        $resultsTemp = $this -> Db -> getData($sql);
        foreach($resultsTemp as $key=>$val){
            $results[$val['ct_id']] = $val;
        }
        return $results;
    }

    /**
     * 获取集装箱结构
     */
    public function getContainerStructure()
    {
        // 获取数据
        $results = [];
        $sql    = 'SELECT cs_id,cs_title FROM '.$this -> Db -> getTableNameAll('container_structure').' WHERE cs_is_show= 0 ORDER BY cs_sort DESC, cs_id ASC';
        $resultsTemp = $this -> Db -> getData($sql);
        foreach($resultsTemp as $key=>$val){
            $results[$val['cs_id']] = $val;
        }
        return $results;
    }

    /**
     * 获取集装箱材料
     */
    public function getContainerMaterial()
    {
        // 获取数据
        $results = [];
        $sql    = 'SELECT gcm_id,gcm_title FROM '.$this -> Db -> getTableNameAll('container_material').' WHERE gcm_is_show= 0 ORDER BY gcm_sort DESC, gcm_id ASC';
        $resultsTemp = $this -> Db -> getData($sql);
        foreach($resultsTemp as $key=>$val){
            $results[$val['gcm_id']] = $val;
        }
        return $results;
    }

    /**
     * 获取集装箱型号
     */
    public function getContainerModelnumber()
    {
        // 获取数据
        $results = [];
        $sql    = 'SELECT cm_id,cm_title FROM '.$this -> Db -> getTableNameAll('container_modelnumber').' WHERE cm_is_show= 0 ORDER BY cm_sort DESC, cm_id ASC';
        $resultsTemp = $this -> Db -> getData($sql);
        foreach($resultsTemp as $key=>$val){
            $results[$val['cm_id']] = $val;
        }
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
     * 获取拖车列表
     * @param string $whereString
     * @param Page $Page
     * @return array
     */
    public function getTuocheList(string $whereString = '', Page $Page, $order="bx_id DESC")
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'bx_isdel='.static::ISDEL;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS bx_id,us_id,bx_address,bx_route,bx_freight,bx_description,bx_click,bx_departure,bx_arrival,bx_car_models,bx_order,bx_release_ip,bx_update_time';
        $sql    .= ' FROM '.$this -> Db -> getTableNameAll('box').' WHERE '.$whereString.' ORDER BY '.$order.' LIMIT '.$limit[0].', '.$limit[1];

        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 数据处理
        foreach($results as $val){
            $val['bx_enterprise'] = $this->getEnterpriseBox($val['us_id']);
            $val['bx_release_ip'] = Net::longIp(intval($val['bx_release_ip']));
            $result[] = $val;
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }


    /**
     * 获取拖车任务列表
     * @param string $whereString
     * @param Page $Page
     * @return array
     */
    public function getTuocheTaskList(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'cb_is_del='.static::ISDEL;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$this -> Db -> getTableNameAll('container_box').' WHERE '.$whereString.' ORDER BY cb_id DESC LIMIT '.$limit[0].', '.$limit[1];

        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 数据处理
        foreach($results as $val){
            $val['cb_market_price'] = sprintf("%.2f", $val['cb_market_price']/100);
            if(mb_strlen($val['cb_title']) > 22) $val['cb_title'] = mb_substr($val['cb_title'], 0, 22).'...';
            $result[] = $val;
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * @name getTuoCheZhongXin 拖车任务中心
     * @return array
     */
    public function getTuoCheZhongXin(){
        $sql = 'SELECT *,plz_id as id, plz_start as start, plz_end as end, plz_huowuzhongwen as huowuzhongwen, plz_type as type, plz_first_time as first_time, plz_valid_time as valid_time, plz_ischeck as ischeck FROM '.$this->Db  -> getTableNameAll('pallet_land_zx').' WHERE  plz_isdel=0 ORDER BY plz_end_time DESC LIMIT 7';
        $land_zx = $this->Db -> getData($sql);
        return isset($land_zx[0]) && !empty($land_zx[0]) ? $land_zx : [];
    }

    /**
     * @name getEnterprise
     * @desciption 获取企业认证信息
     * @return array
     */
    public function getEnterpriseBox($usId = 0){
        $sql = 'SELECT * FROM '.$this->Db->getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
        $seoInfo = $this->Db->getDataOne($sql);
        return isset($seoInfo['ent_name']) && !empty($seoInfo['ent_name']) ? $seoInfo['ent_name'] : '--';
    }



    /**
     * 获取车型
     * @param String $whereString
     * @return mixed
     */
    public function getBoxCarModels(String $whereString='')
    {
        $results    = [];
        $whereString = (empty($whereString) ? $whereString : rtrim($whereString, 'AND') . ' AND ') . 'bcm_isdel=' . static::ISDEL;
        $sql = "SELECT bcm_id,bcm_name,bcm_order FROM " . $this->Db->getTableNameAll('box_car_models') . " WHERE {$whereString} ORDER BY bcm_order DESC, bcm_id ASC";
        $resultsTemp = $this->Db->getData($sql);
        foreach($resultsTemp as $key=>$val){
            $results[$val['bcm_id']] = $val;
        }
        return $results;
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
                    $resultTmp['departure'][$val['ba_id']] = $val;
                }else{
                    // 目的地
                    $resultTmp['arrival'][$val['ba_id']] = $val;
                }
            }

            $results = $resultTmp;
            unset($resultTmp);
        }

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
     * 保存咨询信息
     * @param array $data
     */
    public function saveConsult(array $data = [])
    {
        $cTime = time();
        $sql = 'INSERT INTO ' . $this->Db->getTableNameAll('box_consult') . ' SET au_id=' . $data['au_id'] . ',
        us_id='.$data['us_id'].',
        bc_type='.$data['bc_type'].',
        ss_id=' . $data['ss_id'] . ',
        bc_count=' . $data['bc_count'] . ',
        bc_name=\'' . addslashes($data['bc_name']) . '\',
        bc_phone=\'' . $data['bc_phone'] . '\',
        bc_description=\'' . addslashes($data['bc_description']) . '\',
        bc_title=\'' . addslashes($data['bc_title']) . '\',
        bc_freight=\'' . addslashes($data['bc_freight']) . '\',
        bc_create_time=' . $cTime . ',
        bc_isdel=' . static::ISDEL;
        $result = $this->Db->getDataId($sql);
        // 返回结果
        if($result){
            return static::SUCCESS;
        }
        return static::FAIL;
    }


    /**
     * 保存拖车订单信息
     * @param array $data
     */
    public function saveTuocheOrder(array $data = [])
    {
        $cTime = time();
        $sql = 'INSERT INTO ' . $this->Db->getTableNameAll('box_order') . ' SET ss_id=' . $data['ss_id'] . ',
        au_id='.$data['au_id'].',
        us_id='.$data['us_id'].',
        or_num=' . $data['or_num'] . ',
        or_pay_money=' . $data['or_pay_money'] . ',
        or_name=\'' . addslashes($data['or_name']) . '\',
        or_mobile=\'' . $data['or_mobile'] . '\',
        or_phone=\'' . $data['or_phone'] . '\',
        or_company=\'' . addslashes($data['or_company']) . '\',
        or_address=\'' . addslashes($data['or_address']) . '\',
        or_send_time=\'' . $data['or_send_time'] . '\',
        or_pay_type=\'' . $data['or_pay_type'] . '\',
        or_pay_time=\'' . $data['or_pay_time'] . '\',
        or_create_time=\'' . $cTime . '\',
        or_status=' . 0 . ',
        or_isdel=' . static::ISDEL;
        $result = $this->Db->getDataId($sql);
        // 返回结果
        if($result){
            return static::SUCCESS;
        }
        return static::FAIL;
    }




    /**
     * @name getBasic
     * @desciption 获取基本信息
     * @return array
     */
    public function getBasic(int $usId=0)
    {
        $sql = 'SELECT * FROM '.$this->Db -> getTableNameAll('user_info').' WHERE us_id=\''.$usId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
        $seoInfo = $this->Db->getDataOne($sql);
        return $seoInfo;
    }

    /**
     * @name getCompany
     * @desciption 获取公司介绍
     * @return array
     */
    public function getCompany(int $usId=0)
    {
        $sql = 'SELECT * FROM '.$this->Db -> getTableNameAll('ent_company').' WHERE us_id=\''.$usId.'\' AND ec_isdel=0 ORDER BY ec_last_time DESC';
        $seoInfo = $this->Db->getDataOne($sql);
        return $seoInfo;
    }

    /**
     * @name getEnterprise
     * @desciption 获取企业认证信息
     * @return array
     */
    public function getEnterprise(int $usId=0)
    {
        $sql = 'SELECT * FROM '.$this->Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
        $seoInfo = $this->Db->getDataOne($sql);
        return $seoInfo;
    }

    /**
     * @name getEnterpriseAuth
     * @desciption 获取企业授权信息
     * @return array
     */
    public function getEnterpriseAuth(int $usId=0)
    {
        $sql = 'SELECT * FROM '.$this->Db -> getTableNameAll('enterprise_shouquan').' WHERE us_id=\''.$usId.'\' AND ents_isdel=0 ORDER BY ents_last_time DESC';
        $seoInfo = $this->Db->getDataOne($sql);
        return $seoInfo;
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