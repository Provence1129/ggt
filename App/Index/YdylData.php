<?php
/**
 * @Copyright (C) 2016.
 * @Description MyZhidao
 * @FileName MyZhidao.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types = 1);//strict
namespace App\Index;

use \App\Pub\Common;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Tag\Db;
use \Libs\Comm\Net;
use \Libs\Tag\Page;
use \App\Auth\MyAuth;
use \Libs\Frame\Conf;

class YdylData {
    private $Db         = NULL;         //数据库对象
    const SUCCESS       = "success";    // 成功
    const FAIL          = "fail";       // 失败
    const ISDEL         = 0;            // 删除
    const IMG_HOST      = 'http://res.ggt.gzphp.cn/';

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.USER', 'GMY');
    }
    //一带一路首页轮播图
    public function getSlide(){
        $sql = 'SELECT banner.* FROM '.$this -> Db -> getTableNameAll('banner').' as  banner where banner.id = 15';
        $topslide = $this -> Db -> getDataOne($sql);
        return $topslide;
    }
    /**
     * 获取列表
     * @param array $whereArray
     * @return array
     */
    public function getList(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'yy_isdel='.static::ISDEL;
        // echo $whereString;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS yy_id,ys_id,yt_id,au_id,yy_title,yy_description,yy_hits,yy_heart,yy_order,yy_first_time FROM '.$this -> Db -> getTableNameAll('ydyl').' WHERE '.$whereString.' ORDER BY yy_id DESC LIMIT '.$limit[0].', '.$limit[1];
        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 数据处理
        $result = [];
        foreach($results as $val){
            $val['pic']    = $this->getPicData($val['yy_id']);
            $result[] = $val;
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }


    /**
     * 获取详情
     * @param string $whereString
     */
    public function getInfo(string $whereString = '')
    {
        // 获取资讯详情
        $sql        = 'SELECT yy_id,ys_id,yt_id,au_id,yy_title,yy_description,yy_hits,yy_heart,yy_order,yy_last_time FROM '.$this -> Db -> getTableNameAll('ydyl').' WHERE '.$whereString;
        $result     = $this->Db->getDataOne($sql);

        // 图片信息
        if(isset($result['yy_id'])) $result['pic'] = $this->getPicData($result['yy_id']);

        // 返回数据
        return isset($result) && !empty($result) ? $result : [];
    }

    /**
     * @name signout
     * @desciption 保存知道数据
     */
    public function saveData(array $data = [])
    {
        $yy_id              = isset($data['yy_id']) ? intval($data['yy_id']) : 0;
        $au_id              = isset($data['au_id']) ? intval($data['au_id']) : 0;
        $ys_id              = isset($data['ys_id']) ? intval($data['ys_id']) : 0;
        $yt_id              = isset($data['yt_id']) ? intval($data['yt_id']) : 0;

        $yy_title           = isset($data['yy_title']) ? $data['yy_title'] : '';
        $yy_description     = isset($data['yy_description']) ? $data['yy_description'] : '';
        $yy_hits            = isset($data['yy_hits']) ? intval($data['yy_hits']) : 0;
        $yy_heart           = isset($data['yy_heart']) ? intval($data['yy_heart']) : 0;
        $yy_order           = isset($data['yy_order']) ? intval($data['yy_order']) : 50;
        $zd_release_ip      = Net::getIpLong();
        $currTime           = Time::getTimeStamp();

        if(empty($yy_id)) {
            $sql = 'INSERT INTO ' . $this->Db->getTableNameAll('ydyl') . ' SET au_id=' . $au_id . ', ys_id=' . $ys_id . ', yt_id=' . $yt_id . ', yy_title=\'' . addslashes($yy_title) .'\',
                yy_description=\'' . addslashes($yy_description) . '\', yy_order=' . $yy_order . ', yy_hits=' . $yy_hits . ', yy_heart=' . $yy_heart . ', yy_first_time=' . $currTime . ', yy_last_time=' . $currTime . ', yy_isdel=' . static::ISDEL;
            $result_id = $this->Db->getDataId($sql);

            // 保存图片信息
            $this->savePicData($result_id, $data);
        }else{
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('ydyl').' SET ys_id=' . $ys_id . ', yt_id=' . $yt_id . ', yy_title=\'' . addslashes($yy_title) .'\',
                yy_description=\'' . addslashes($yy_description) . '\', yy_order=' . $yy_order . ', yy_hits=' . $yy_hits . ', yy_last_time=' . $currTime . ' WHERE yy_id='.$yy_id.' AND au_id='.$au_id.' AND yy_isdel='.static::ISDEL;
            $result_id      = $this->Db->getDataNum($sql);

            // 保存图片信息
            $this->savePicData($yy_id, $data);
        }


        // 返回结果
        if($result_id){
            return static::SUCCESS;
        }
        return static::FAIL;
    }

    /**
     * @param array $data
     */
    public function savePicData($yy_id, $data = [])
    {
        $itemImg            = isset($data['itemImg']) ? $data['itemImg'] : [];
        $itemName           = isset($data['itemName']) ? $data['itemName'] : [];
        $currTime           = Time::getTimeStamp();

        if(!empty($yy_id)){
            // 删除所有图片信息
            $sql = 'DELETE FROM ' . $this->Db->getTableNameAll('ydyl_pic') . ' WHERE yy_id=' . $yy_id;
            $this->Db->getDataNum($sql);

            foreach ($itemImg as $key => $val) {
                $sql = 'INSERT INTO ' . $this->Db->getTableNameAll('ydyl_pic') . ' SET yy_id=' . $yy_id . ', yp_title=\'' . addslashes($itemName[$key]) . '\',
                    yp_url=\'' . addslashes($itemImg[$key]) . '\', yp_first_time=' . $currTime . ', yp_last_time=' . $currTime . ', yp_isdel=' . static::ISDEL;;
                $this->Db->getDataId($sql);
            }
        }
    }

    /**
     * 获取图片信息
     * @param $yy_id
     * @return mixed
     */
    public function getPicData($yy_id)
    {
        $whereString    = 'yy_id='.$yy_id.' AND yp_isdel='.static::ISDEL;
        $sql    = 'SELECT yp_id,yy_id,yp_title,yp_url FROM '.$this -> Db -> getTableNameAll('ydyl_pic').' WHERE '.$whereString.' ORDER BY yp_id ASC';
        $results = $this -> Db -> getData($sql);
        $result = [];
        foreach ($results as $key => $val) {
            $result[] = [
                'title'     => $val['yp_title'],
                'url'       => $val['yp_url'],
                'show_url'  => $this->getThumbImgUrl($val['yp_url'])
            ];
        }
        return $result;
    }


    /**
     * 获取图片信息
     * @param $yy_id
     * @return mixed
     */
    public function getStyle()
    {
        $whereString    = 'ys_isdel='.static::ISDEL;
        $sql    = 'SELECT ys_id,ys_name,ys_order FROM '.$this -> Db -> getTableNameAll('ydyl_style').' WHERE '.$whereString.' ORDER BY ys_id ASC';
        $results = $this -> Db -> getData($sql);
        $result = [];
        foreach ($results as $key => $val) {
            $result[$val['ys_id']] = $val;
        }
        return $result;
    }


    /**
     * 获取图片信息
     * @param $yy_id
     * @return mixed
     */
    public function getType()
    {
        $whereString    = 'yt_isdel='.static::ISDEL;
        $sql    = 'SELECT yt_id,yt_name,yt_order FROM '.$this -> Db -> getTableNameAll('ydyl_type').' WHERE '.$whereString.' ORDER BY yt_id ASC';
        $results = $this -> Db -> getData($sql);
        $result = [];
        foreach ($results as $key => $val) {
            $result[$val['yt_id']] = $val;
        }
        return $result;
    }


    /**
     * 删除
     * @param array $whereArray
     * @return string
     */
    public function del(array $whereArray = [])
    {
        if(!isset($whereArray['yy_id'])) static::FAIL;

        // 条件处理
        $userid         = $_SESSION['TOKEN']['INFO']['id'];
        $whereString    = 'au_id='.$userid.' AND yy_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 标题
                case 'yy_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'yy_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除附件
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('ydyl').' SET yy_isdel=1 WHERE '.$whereString;
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