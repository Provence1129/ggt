<?php
/**
 * @Copyright (C) 2016.
 * @Description Common
 * @FileName Common.php
 * @Author   Huang.Xiang
 * @Version  1.0.1
 **/

declare(strict_types = 1);//strict
namespace App\Pub;
use \Exception;
use \Libs\Comm\Comm;
use \Libs\Tag\Cache;
use \Libs\Frame\Conf;
use \Libs\Comm\From;
use \Libs\Comm\Net;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Tag\Db;
class Common{
    /**
     * @name getDb
     * @desciption 获取数据操作对象
     * @return mixed
     */
    public static function getDb(){
        return Db::tag('DB.ADMIN', 'GMY');
    }
    /**
     * @name getUserPassword
     * @desciption 获取计算密码
     * @param string $password
     * @return string
     **/
    public static function getUserPassString(string $password):string{
        $newPassword = $password;
        $secNum = 128;   //反复加密次数
        while($secNum-- > 0){
            $newPassword = md5($newPassword);
            $newPasswordLeng = strlen($newPassword);
            $index = hexdec(substr($newPassword, 0, 1));
            $newPassword = substr($newPassword.$newPassword, $index, $newPasswordLeng);
        }
        return $newPassword;
    }

    /**
     * @name getFormSig
     * @desciption 获取表单签名
     * @throws Exception
     * @return string
     */
    public static function getFormSig():string{
        $sigString = Comm::getRandString(32);
        $expireTime = intval(Conf::get('FORM.sigExpireTime'));
        $expireTime = max($expireTime, 0);
        if(!Cache::tag(Conf::get('FORM.sigTagName'), Conf::get('FORM.sigTagConf')) -> set('FORMSIG_'.$sigString, $sigString, $expireTime)){
            throw new Exception('Cache FORMSIG Error.');
        }
        return $sigString;
    }

    /**
     * @name checkFormSig
     * @desciption 检测表单签名
     * @param string $sig
     * @return bool
     */
    public static function checkFormSig(string $sig):bool{
        $sigString = Cache::tag(Conf::get('FORM.sigTagName'), Conf::get('FORM.sigTagConf')) -> get('FORMSIG_'.$sig);
        if(is_string($sigString) && strlen($sigString) > 1 && $sigString == $sig){
            Cache::tag(Conf::get('FORM.sigTagName'), Conf::get('FORM.sigTagConf')) -> del('FORMSIG_'.$sig);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @name verifyFormSig
     * @desciption 校验表单签名
     * @throws Exception
     * @return void
     */
    public static function verifyFormSig(){
        return TRUE;
        $sig = From::valTrim('sig');
        if(strlen($sig) > 0 && !self::checkFormSig($sig)){
            throw new Exception('Check From Sig FAILED, Please try again.');
        }
    }

    /**
     * @name toUrl
     * @desciption 跳转到新地址
     * @param string $url
     */
    public static function toUrl(string $url){
        header('location: '.$url);
        exit(0);
    }

    /**
     * @name         logs
     * @desciption 写入管理员操作日志
     * @param string $key
     * @param int    $result
     * @param string $info
     * @param array  $data
     * @return bool
     */
    public static function logs(string $key, int $result, string $info, array $data):bool{
        $auId       = intval($_SESSION['TOKEN']['UID']);
        $aptId      = intval($_SESSION['TOKEN']['UTID']);
        $currIp     = Net::getIpLong();
        $currTime   = Time::getTimeStamp();
        $aulData    = json_encode($data);
        $aulInfo    = $info;
        $aulKey     = $key;
        $aulResult  = $result;     //[1-成功,0-失败]
        $Db         = self::getDb();
        $sql = 'INSERT INTO '.$Db -> getTableNameAll('adm_user_log').' SET au_id='.$auId.', apt_id='.$aptId.', aul_key=\''.addslashes($aulKey).'\', aul_result='.$aulResult.', aul_data=\''.addslashes($aulData).'\', aul_info=\''.addslashes($aulInfo).'\', aul_ip='.$currIp.', aul_first_time='.$currTime;
        return $Db->getDataNum($sql) > 0 ? TRUE : FALSE;
    }

    /**
     * @name getChenYunRen
     * @desciption 获取承运人
     * @return array
     */
    public static function getChenYunRen():array{
        $Db = self::getDb();
        $sql = 'SELECT dcyr_id, dcyr_name_en, dcyr_name_zh FROM '.$Db -> getTableNameAll('data_chengyunren').' GROUP BY dcyr_name_zh ORDER BY dcyr_id ASC';
        return $Db -> getData($sql);
    }

    /**
     * @name getGangKou
     * @desciption 获取港口
     * @return array
     */
    public static function getGangKou():array{
        $Db = self::getDb();
        $sql = 'SELECT dgk_id, dgk_name_en, dgk_name_zh, dgk_name_country FROM '.$Db -> getTableNameAll('data_gangkou').'  GROUP BY dgk_name_zh ORDER BY dgk_id ASC';
        return $Db -> getData($sql);
    }

    /**
     * @name getHangKong
     * @desciption 获取航空公司
     * @return array
     */
    public static function getHangKong():array{
        $Db = self::getDb();
        $sql = 'SELECT dhk_id, dhk_name_en, dhk_name_zh FROM '.$Db -> getTableNameAll('data_hangkong').'  GROUP BY dhk_name_zh ORDER BY dhk_id ASC';
        return $Db -> getData($sql);
    }

    /**
     * @name getJiChang
     * @desciption 获取机场
     * @return array
     */
    public static function getJiChang():array{
        $Db = self::getDb();
        $sql = 'SELECT djc_id, djc_name_en, djc_name_zh FROM '.$Db -> getTableNameAll('data_jichang').'  GROUP BY djc_name_zh ORDER BY djc_id ASC';
        return $Db -> getData($sql);
    }

    /**
     * @name getArea
     * @desciption 获取地区
     * @return array
     */
    public static function getArea():array{
        $Db = self::getDb();
        $sql = 'SELECT ca_code, ca_name, ca_name_en FROM '.$Db -> getTableNameAll('data_area').'  GROUP BY ca_name ORDER BY ca_code ASC';
        return $Db -> getData($sql);
    }
}