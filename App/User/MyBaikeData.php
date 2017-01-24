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

class MyBaikeData {
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
     * 获取百科分类信息
     */
    public function getCategoryList()
    {
        // 获取数据
        $sql    = 'SELECT bc_id,bc_parent_id,bc_name,bc_description,bc_thumb_image,bc_order,bc_first_time,bc_isdel,bc_icon FROM '.$this -> Db -> getTableNameAll('baike_category').' WHERE bc_isdel='.static::ISDEL.' ORDER BY bc_order DESC, bc_id DESC';
        $results = $this -> Db -> getData($sql);
        return $results;
    }

    /**
     * 获取百科详细信息
     */
    public function getCategoryInfo(int $bc_id = 0)
    {
        // 获取数据
        $sql    = 'SELECT bc_id,bc_parent_id,bc_name,bc_description,bc_thumb_image,bc_order,bc_first_time,bc_isdel,bc_icon FROM '.$this -> Db -> getTableNameAll('baike_category').' WHERE bc_isdel='.static::ISDEL.' AND bc_id='.$bc_id;
        $results = $this -> Db -> getDataOne($sql);
        return $results;
    }


    /**
     * 获取列表
     * @param array $whereArray
     * @return array
     */
    public function getList(string $whereString = '', Page $Page, string $orderString = 'bk_thumb_img DESC, bk_id DESC')
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'bk_isdel='.static::ISDEL;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS bk_id,us_id,bc_id,bk_title,bk_description,bk_tags,bk_thumb_img,bk_click_up,bk_favs,bk_hits,bk_status,bk_order,bk_last_time FROM '.$this -> Db -> getTableNameAll('baike').' WHERE '.$whereString.' ORDER BY '.$orderString.' LIMIT '.$limit[0].', '.$limit[1];
        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 数据处理
        if(isset($results[0]) && !empty($results[0])) {
            foreach ($results as $val) {
                $val['bk_thumb_img']    = $this->getThumbImgUrl($val['bk_thumb_img']);
                $val['bk_release_ip']   = Net::longIp(intval($val['bk_release_ip']));
                $val['bk_description']   = @preg_replace("/\s+/", '', strip_tags($val['bk_description']));
                $result[] = $val;
            }
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * 获取列表
     * @param string $whereString
     * @param array $limit
     * @return array
     */
    public function getBaikeList(string $whereString = '', array $limit = [], string $orderString = '')
    {
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'bk_isdel='.static::ISDEL;
        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS a.bk_id,a.us_id,a.bc_id,a.bk_title,a.bk_tags,a.bk_thumb_img,a.bk_click_up,a.bk_favs,a.bk_hits,a.bk_status,a.bk_order,a.bk_last_time,b.bc_name,bk_description,c.ui_name FROM '.$this -> Db -> getTableNameAll('baike').' AS a LEFT JOIN '.$this -> Db -> getTableNameAll('baike_category').' AS b ON a.bc_id = b.bc_id LEFT JOIN '.$this -> Db -> getTableNameAll('user_info').' AS c ON a.us_id = c.us_id  WHERE '.$whereString.' ORDER BY '.$orderString.' LIMIT '.$limit[0].', '.$limit[1];

        $results = $this -> Db -> getData($sql);

        // 数据处理
        if(isset($results[0]) && !empty($results[0])){
            foreach($results as $val){
                $val['bk_thumb_img']    = $this->getThumbImgUrl($val['bk_thumb_img']);
                $val['bk_release_ip']   = Net::longIp(intval($val['bk_release_ip']));
                $result[] = $val;
            }
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * 获取列表
     * @param array $whereArray
     * @return array
     */
    public function getBaikeListPage(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'bk_isdel='.static::ISDEL;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS a.bk_id,a.us_id,a.bc_id,a.bk_title,a.bk_tags,a.bk_thumb_img,a.bk_click_up,a.bk_favs,a.bk_hits,a.bk_status,a.bk_order,a.bk_last_time,a.bk_description,b.ui_name FROM '.$this -> Db -> getTableNameAll('baike').' AS a Left JOIN '.$this -> Db -> getTableNameAll('user_info').' AS b ON a.us_id = b.us_id  WHERE '.$whereString.' ORDER BY bk_id DESC LIMIT '.$limit[0].', '.$limit[1];

        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 数据处理
        foreach($results as $val){
            $val['bk_thumb_img']    = $this->getThumbImgUrl($val['bk_thumb_img']);
            $val['bk_release_ip']   = Net::longIp(intval($val['bk_release_ip']));
            $result[] = $val;
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * 获取详细信息
     */
    public function getInfo(int $bk_id = 0)
    {
        // 获取百科
        $sql            = 'SELECT bk_id,us_id,bc_id,bk_title,bk_description,bk_tags,bk_thumb_img,bk_status,bk_favs,bk_hits,bk_order,bk_click_up,bk_release_ip,bk_last_time FROM '.$this -> Db -> getTableNameAll('baike').' WHERE bk_id='.$bk_id.' AND us_id='.$this->userid.' AND bk_isdel='.static::ISDEL;
        $baikeResult    = $this->Db->getDataOne($sql);
        $baikeResult['bk_thumb_showimg'] = (!empty($baikeResult['bk_thumb_img'])) ? $this->getThumbImgUrl($baikeResult['bk_thumb_img']) : '';

        if($baikeResult){
            // 获取详情
            $sql                = 'SELECT bk_id,bi_keywords,bi_datas,bi_pics,bi_content FROM '.$this -> Db -> getTableNameAll('baike_info').' WHERE bk_id='.$bk_id;
            $baikeInfoResult    = $this->Db->getDataOne($sql);

            $bi_keywords    = isset($baikeInfoResult['bi_keywords']) ? json_decode($baikeInfoResult['bi_keywords']) : [];
            $bi_datas       = isset($baikeInfoResult['bi_datas']) ? json_decode($baikeInfoResult['bi_datas']) : [];
            $bi_picsTmp     = isset($baikeInfoResult['bi_pics']) ? json_decode($baikeInfoResult['bi_pics']) : [];
            $bi_pics        = [];
            if(!empty($bi_picsTmp)){
                foreach($bi_picsTmp as $key=>$val){
                    if(!empty($val)) $bi_pics[] = array(
                        'img'       => $val,
                        'showImg'   => $this->getThumbImgUrl($val)
                    );
                }
            }

            $baikeInfoResult['bi_keywords'] = $bi_keywords;
            $baikeInfoResult['bi_datas'] = $bi_datas;
            $baikeInfoResult['bi_pics'] = $bi_pics;
        }

        // 返回数据
        $result = [];
        if($baikeResult || $baikeInfoResult){
            $result = is_array($baikeResult) && is_array($baikeInfoResult) ? array_merge($baikeResult, $baikeInfoResult) : [];
        }
        return $result;

    }

    /**
     * 获取详细信息
     */
    public function getBaikeInfo(int $bk_id = 0)
    {
        // 获取百科
        $sql            = 'SELECT bk_id,us_id,bc_id,bk_title,bk_description,bk_tags,bk_thumb_img,bk_status,bk_favs,bk_hits,bk_order,bk_click_up,bk_release_ip,bk_last_time FROM '.$this -> Db -> getTableNameAll('baike').' WHERE bk_id='.$bk_id.' AND bk_isdel='.static::ISDEL;
        $baikeResult    = $this->Db->getDataOne($sql);
        $baikeResult['bk_thumb_showimg'] = (!empty($baikeResult['bk_thumb_img'])) ? $this->getThumbImgUrl($baikeResult['bk_thumb_img']) : '';

        if($baikeResult){
            // 获取详情
            $sql                = 'SELECT bk_id,bi_keywords,bi_datas,bi_pics,bi_content FROM '.$this -> Db -> getTableNameAll('baike_info').' WHERE bk_id='.$bk_id;
            $baikeInfoResult    = $this->Db->getDataOne($sql);

            $bi_keywords    = isset($baikeInfoResult['bi_keywords']) ? json_decode($baikeInfoResult['bi_keywords']) : [];
            $bi_datas       = isset($baikeInfoResult['bi_datas']) ? json_decode($baikeInfoResult['bi_datas']) : [];
            $bi_picsTmp     = isset($baikeInfoResult['bi_pics']) ? json_decode($baikeInfoResult['bi_pics']) : [];

            $bi_pics        = [];
            if(!empty($bi_picsTmp)){
                foreach($bi_picsTmp as $key=>$val){
                    if(!empty($val)) $bi_pics[] = array(
                        'img'       => $val,
                        'showImg'   => $this->getThumbImgUrl($val)
                    );
                }
            }

            $baikeInfoResult['bi_keywords'] = $bi_keywords;
            $baikeInfoResult['bi_datas'] = $bi_datas;
            $baikeInfoResult['bi_pics'] = $bi_pics;
        }

        // 返回数据
        $result = [];
        if($baikeResult || $baikeInfoResult){
            $result = is_array($baikeResult) && is_array($baikeInfoResult) ? array_merge($baikeResult, $baikeInfoResult) : [];
        }
        return $result;

    }

    /**
     * @name signout
     * @desciption 保存知道数据
     */
    public function saveData(array $data = [])
    {
        $bk_id              = isset($data['bk_id']) ? intval($data['bk_id']) : 0;
        $us_id              = isset($data['us_id']) ? intval($data['us_id']) : 0;
        $bc_id              = isset($data['bc_id']) ? intval($data['bc_id']) : 0;
        $bk_title           = isset($data['bk_title']) ? $data['bk_title'] : '';
        $bk_tags            = isset($data['bk_tags']) ? $data['bk_tags'] : '';
        $bi_keywords        = isset($data['bi_keywords']) ? $data['bi_keywords'] : [];
        $bi_datas           = isset($data['bi_datas']) ? $data['bi_datas'] : [];
        $bi_pics            = isset($data['bi_pics']) ? $data['bi_pics'] : [];
        $bi_content         = isset($data['bi_content']) ? $data['bi_content'] : '';
        $bk_description     = isset($data['bi_content']) ? $data['bk_description'] : '';

        $bk_order           = isset($data['bk_order']) ? intval($data['bk_order']) : 50;
        $bk_favs            = isset($data['bk_favs']) ? intval($data['bk_favs']) : 0;
        $bk_hits            = isset($data['bk_hits']) ? intval($data['bk_hits']) : 0;
        $bk_release_ip      = Net::getIpLong();
        $currTime           = Time::getTimeStamp();
        $bi_pic             = isset($bi_pics[0]) ? trim($bi_pics[0]) : '';

        // 详情数据
        $bi_keywords        = !empty($bi_keywords) ? json_encode($bi_keywords, JSON_UNESCAPED_UNICODE) : [];
        $bi_datas           = !empty($bi_datas) ? json_encode($bi_datas, JSON_UNESCAPED_UNICODE) : [];
        $bi_pics            = !empty($bi_pics) ? json_encode($bi_pics, JSON_UNESCAPED_UNICODE) : [];

        $bi_keywords        = !empty($bi_keywords) ? $bi_keywords : '';
        $bi_datas           = !empty($bi_datas) ? $bi_datas : '';
        $bi_pics            = !empty($bi_pics) ? $bi_pics : '';


//        echo '<pre>';
//        print_r($bi_keywords);
//        print_r($bi_datas);
//        exit;


        if(empty($bk_id)) {
            $sql = 'INSERT INTO ' . $this->Db->getTableNameAll('baike') . ' SET us_id=' . $us_id . ', bc_id=' . $bc_id . ',bk_title=\'' . addslashes($bk_title) .'\', bk_description=\'' . addslashes($bk_description) .'\',
                bk_tags=\'' . addslashes($bk_tags) . '\', bk_thumb_img=\'' . $bi_pic . '\', bk_status=0, bk_order=' . $bk_order . ', bk_favs=\'' . $bk_favs . '\', bk_hits=' . $bk_hits . ', bk_release_ip=' . $bk_release_ip . ',
                bk_first_time=' . $currTime . ', bk_last_time=' . $currTime . ', bk_isdel=' . static::ISDEL;
            $result_id = $this->Db->getDataId($sql);

            if($result_id){
                $sqlInfo        = 'INSERT INTO ' . $this->Db->getTableNameAll('baike_info') . ' SET bk_id=' . $result_id . ', bi_keywords=\'' . addslashes($bi_keywords) .'\', bi_datas=\'' . addslashes($bi_datas) .'\', bi_pics=\'' . addslashes($bi_pics) .'\', bi_content=\'' . addslashes($bi_content) .'\'';
                $resultInfo     = $this->Db->getDataId($sqlInfo);
            }

        }else{
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('baike').' SET bc_id=' . $bc_id . ',bk_title=\'' . addslashes($bk_title) .'\', bk_description=\'' . addslashes($bk_description) .'\',
                bk_tags=\'' . addslashes($bk_tags) . '\', bk_thumb_img=\'' . $bi_pic . '\', bk_last_time=' . $currTime . ' WHERE bk_id='.$bk_id.' AND us_id='.$us_id.' AND bk_isdel='.static::ISDEL;
            $result_id      = $this->Db->getDataNum($sql);

            if($result_id){
                $sqlInfo        = 'UPDATE ' . $this->Db->getTableNameAll('baike_info') . ' SET bi_keywords=\'' . addslashes($bi_keywords) .'\', bi_datas=\'' . addslashes($bi_datas) .'\', bi_pics=\'' . addslashes($bi_pics) .'\', bi_content=\'' . addslashes($bi_content) .'\' WHERE bk_id='.$bk_id;
                $resultInfo     = $this->Db->getDataNum($sqlInfo);
            }
        }

        // 返回结果
        if($result_id && $resultInfo){
            return static::SUCCESS;
        }
        return static::FAIL;
    }


    /**
     * 删除
     * @param array $whereArray
     * @return string
     */
    public function del(array $whereArray = [])
    {
        if(!isset($whereArray['bk_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'us_id='.$this->userid.' AND bk_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 标题
                case 'bk_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'bk_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除附件
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('baike').' SET bk_isdel=1 WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }

    /**
     * 更新状态
     * @param array $whereArray
     * @param int $status   状态[0-待处理 1-已解决 2-已关闭]
     * @return string
     */
    public function upStatus(array $whereArray = [], int $status = 0)
    {
        if(!isset($whereArray['zd_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'us_id='.$this->userid.' AND zd_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 标题
                case 'zd_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'zd_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除附件
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('zhidao').' SET zd_status='.intval($status).' WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
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