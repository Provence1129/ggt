<?php
/**
 * @Copyright (C) 2016.
 * @Description UserData
 * @FileName UserData.php
 * @Author   Huang.Xiang
 * @Version  1.0.1
 **/

declare(strict_types = 1);//strict
namespace App\User;
use \App\Pub\Common;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Tag\Db;
use \Libs\Comm\Net;
use \App\Auth\MyAuth;
use \Libs\Frame\Conf;
class UserData{
    private $Db           = NULL;         //数据库对象

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.USER', 'GMY');
    }

    /**
     * @name 获取登录广告位
     * @return mixed
     */
    public function getBannerData(){
        $sql = 'SELECT banner.* FROM '.$this->Db -> getTableNameAll('banner').' as  banner where banner.id = 16';
        $banner = $this->Db -> getDataOne($sql);
        return $banner;
    }
    /**
     * @name signout
     * @desciption 登出系统
     */
    public function signout(){
        $MyAuth = new MyAuth();
        $MyAuth -> getSess() -> delete(); //删除会话
    }

    /**
     * @name         signin
     * @desciption 登录系统
     * @param string $username
     * @param string $password
     * @return string
     */
    public function signin(string $username, string $password):string{
        $username = preg_replace('/[^a-z\d_@\.\-]+/i', '', $username);  //允许字母、数字_@.-
        $password = Common::getUserPassString($password);
        $isSuccess = FALSE;
        $UserAccountArray = $UserInfoArray = []; //用户信息
        if(Valid::isEmail($username)){  //E-mail
            $sql = 'SELECT us_id, ui_name, ui_photo, ui_sex, ui_birthday, ui_email, ui_mobile, ui_phone, ui_qq FROM '.$this -> Db -> getTableNameAll('user_info').' WHERE ui_email=\''.addslashes($username).'\' AND ui_isdel=0 ';
            $UserInfoList = $this->Db->getData($sql);
            $isFound = FALSE;
            if(count($UserInfoList) > 0){
                foreach($UserInfoList as $key => $val){
                    $usId = intval($val['us_id']);
                    if($usId > 0){
                        if(!$isFound) $isFound = TRUE;
                        $sql = 'SELECT us_id, us_account, us_islogin, us_last_logintime, us_last_loginip FROM '.$this -> Db -> getTableNameAll('user').' WHERE us_id=\''.$usId.'\' AND us_password=\''.addslashes($password).'\' AND us_isdel=0 ';
                        $UserInfo = $this->Db->getDataOne($sql);
                        if(isset($UserInfo['us_id']) && $UserInfo['us_id'] == $usId){
                            $UserInfoArray = $val;
                            $UserAccountArray = $UserInfo;
                            $isSuccess = TRUE;
                            break;
                        }
                    }
                }   //End-Foreach
            }
            if(!$isFound){  //不存在E-mail
                return 'ERRORUSERPASS';     //用户或密码错误
            }
        }else if(Valid::isMobile($username)){   //手机
            $sql = 'SELECT us_id, ui_name, ui_photo, ui_sex, ui_birthday, ui_email, ui_mobile, ui_phone, ui_qq FROM '.$this -> Db -> getTableNameAll('user_info').' WHERE ui_mobile=\''.addslashes($username).'\' AND ui_isdel=0 ';
            $UserInfoList = $this->Db->getData($sql);
            $isFound = FALSE;
            if(count($UserInfoList) > 0){
                foreach($UserInfoList as $key => $val){
                    $usId = intval($val['us_id']);
                    if($usId > 0){
                        if(!$isFound) $isFound = TRUE;
                        $sql = 'SELECT us_id, us_account, us_islogin, us_last_logintime, us_last_loginip FROM '.$this -> Db -> getTableNameAll('user').' WHERE us_id=\''.$usId.'\' AND us_password=\''.addslashes($password).'\' AND us_isdel=0 ';
                        $UserInfo = $this->Db->getDataOne($sql);
                        if(isset($UserInfo['us_id']) && $UserInfo['us_id'] == $usId){
                            $UserInfoArray = $val;
                            $UserAccountArray = $UserInfo;
                            $isSuccess = TRUE;
                            break;
                        }
                    }
                }   //End-Foreach
            }
            if(!$isFound){  //不存在E-mail
                return 'ERRORUSERPASS';     //用户或密码错误
            }
        }else{  //帐号
            $sql = 'SELECT us_id, us_account, us_islogin, us_last_logintime, us_last_loginip FROM '.$this -> Db -> getTableNameAll('user').' WHERE us_account=\''.addslashes($username).'\' AND us_password=\''.addslashes($password).'\' AND us_isdel=0 ';
            $UserInfo = $this->Db->getDataOne($sql);
            if(isset($UserInfo['us_id']) && $UserInfo['us_id'] > 0){
                $usId = intval($UserInfo['us_id']);
                $UserAccountArray = $UserInfo;
                $isSuccess = TRUE;
                $sql = 'SELECT us_id, ui_name, ui_photo, ui_sex, ui_birthday, ui_email, ui_mobile, ui_phone, ui_qq FROM '.$this -> Db -> getTableNameAll('user_info').' WHERE us_id='.$usId.' AND ui_isdel=0 ';
                $UserInfoList = $this->Db->getDataOne($sql);
                if(isset($UserInfoList['us_id']) && $UserInfoList['us_id'] == $usId){
                    $UserInfoArray = $UserInfoList;
                }
            }else{
                return 'ERRORUSERPASS';     //用户或密码错误
            }
        }
        if($isSuccess && isset($UserAccountArray['us_id']) && $UserAccountArray['us_id'] > 0){
            if($UserAccountArray['us_islogin'] != 1) return 'ERRORUSERISLOGIN';   //是否允许登录[1-是,0-否]
            $currIp             = Net::getIpLong();
            $currTime           = Time::getTimeStamp();
            $usId               = intval($UserAccountArray['us_id']);
            $usAccount          = trim($UserAccountArray['us_account']);
            $usLastLogintime    = intval($UserAccountArray['us_last_logintime']);            //上次登录时间戳
            $usLastLoginIp      = Net::longIp(intval($UserAccountArray['us_last_loginip'])); //上次登录IP
            $uName              = 'NONE';
            $usInfoArray        = [];
            if(isset($UserInfoArray['ui_name'])){
                $urlRes = Conf::get('URL.RES');
                $uName = trim($UserInfoArray['ui_name']);
                $usInfoArray['id']         = $usId;
                $usInfoArray['name']       = trim($UserInfoArray['ui_name']);
                $usInfoArray['photo']      = $urlRes.ltrim($UserInfoArray['ui_photo'], '/');
                $usInfoArray['sex']        = intval($UserInfoArray['ui_sex']);
                $usInfoArray['birthday']   = intval($UserInfoArray['ui_birthday']);
                $usInfoArray['email']      = trim($UserInfoArray['ui_email']);
                $usInfoArray['mobile']     = trim($UserInfoArray['ui_mobile']);
                $usInfoArray['phone']      = trim($UserInfoArray['ui_phone']);
                $usInfoArray['qq']         = trim($UserInfoArray['ui_qq']);
            }
            $usInfoArray['username']       = $usAccount;
            $usInfoArray['lastLoginTime']  = $usLastLogintime;
            $usInfoArray['lastLoginIp']    = $usLastLoginIp;
            $_SESSION['TOKEN'] = ['UID' => $usId, 'UNAME' => $uName, 'INFO' => $usInfoArray];
            //更新当前登录信息
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('user').' SET us_last_logintime='.$currTime.', us_last_loginip='.$currIp.' WHERE us_id='.$usId.' AND us_isdel=0';
            $this->Db->getDataNum($sql);
            return 'OK';    //成功
        }else{
            return 'ERRORUSERPASS';     //用户或密码错误
        }
    }

    /**
     * @name         getEntTypeList
     * @desciption 获取企业类型列表
     * @return array
     */
    public function getEntTypeList():array{
        $sql = 'SELECT entt_id, entt_name FROM '.$this -> Db -> getTableNameAll('enterprise_type').' WHERE entt_isdel=0 ORDER BY entt_last_time DESC';
        $list = $this->Db->getData($sql);
        $entTypeList = [];
        if(count($list) > 0){
            foreach ($list as $key => $val){
                $tmp = [];
                $tmp['entTypeId']   = intval($val['entt_id']);
                $tmp['entTypeName'] = trim($val['entt_name']);
                $entTypeList[] = $tmp;
            }
        }
        return $entTypeList;
    }

    /**
     * @name         setUserInfo
     * @desciption 设置用户的信息
     * @param int $usId
     * @param string $username
     * @param string $email
     * @param string $entname
     * @param string $enttype
     * @return bool
     */
    public function setUserInfo(int $usId, string $username, string $email, string $entname, string $enttype):bool{
        $currTime = Time::getTimeStamp();
        $sql = 'UPDATE '.$this -> Db -> getTableNameAll('user').' SET us_account=\''.addslashes($username).'\', us_last_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\' AND us_account=\'\' AND us_isdel=\'0\' AND us_islogin=\'1\'';
        $setUserNum = $this->Db->getDataNum($sql);
        $sql = 'UPDATE '.$this -> Db -> getTableNameAll('user_info').' SET ui_email=\''.addslashes($email).'\', ui_last_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\' AND ui_isdel=\'0\'';
        $setUserInfoNum = $this->Db->getDataNum($sql);
        $sql = 'SELECT ent_id FROM '.$this -> Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=\'0\'';
        $EntInfo = $this->Db->getDataOne($sql);
        if(isset($EntInfo['ent_id']) && $EntInfo['ent_id'] > 0) {
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('enterprise').' SET ent_name=\''.addslashes($entname).'\', ent_type_string=\''.addslashes($enttype).'\', ent_last_time=\''.$currTime.'\' WHERE ent_id=\''.intval($EntInfo['ent_id']).'\' AND ent_isdel=\'0\'';
            $setEntInfoNum = $this->Db->getDataNum($sql);
        }else{
            $sql = 'INSERT INTO '.$this -> Db -> getTableNameAll('enterprise').' SET us_id=\''.$usId.'\', ent_name=\''.addslashes($entname).'\', ent_type_string=\''.addslashes($enttype).'\', ent_status=0, ent_first_time=\''.$currTime.'\', ent_last_time=\''.$currTime.'\', ent_isdel=\'0\'';
            $setEntInfoNum = $this->Db->getDataId($sql);
        }
        if($setUserNum >= 0 && $setUserInfoNum > 0 && $setEntInfoNum > 0){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * @name         getUserInfo
     * @desciption 获取用户的信息
     * @param int $usId
     * @return array
     */
    public function getUserInfo(int $usId):array{
        $UserInfoArray = [];
        $sql = 'SELECT us_id, ui_name, ui_photo, ui_sex, ui_birthday, ui_email, ui_mobile, ui_phone, ui_fax, ui_poscode, ui_qq FROM '.$this -> Db -> getTableNameAll('user_info').' WHERE us_id='.$usId.' AND ui_isdel=0 ';
        $UserInfoList = $this->Db->getDataOne($sql);
        if(isset($UserInfoList['us_id']) && $UserInfoList['us_id'] == $usId){
            $urlRes = Conf::get('URL.RES');
            $UserInfoArray['id']         = $usId;
            $UserInfoArray['name']       = trim($UserInfoList['ui_name']);
            $UserInfoArray['photo']      = $urlRes.trim($UserInfoList['ui_photo']);
            $UserInfoArray['sex']        = intval($UserInfoList['ui_sex']);
            $UserInfoArray['birthday']   = intval($UserInfoList['ui_birthday']);
            $UserInfoArray['email']      = trim($UserInfoList['ui_email']);
            $UserInfoArray['mobile']     = trim($UserInfoList['ui_mobile']);
            $UserInfoArray['phone']      = trim($UserInfoList['ui_phone']);
            $UserInfoArray['fax']        = trim($UserInfoList['ui_fax']);
            $UserInfoArray['poscode']    = trim($UserInfoList['ui_poscode']);
            $UserInfoArray['qq']         = trim($UserInfoList['ui_qq']);
        }
        $sql = 'SELECT us_id, us_account, us_last_logintime, us_last_loginip FROM '.$this -> Db -> getTableNameAll('user').' WHERE us_id=\''.$usId.'\' AND us_isdel=0 ';
        $UserInfo = $this->Db->getDataOne($sql);
        if(isset($UserInfo['us_id']) && $UserInfo['us_id']){
            $usAccount          = trim($UserInfo['us_account']);
            $usLastLogintime    = intval($UserInfo['us_last_logintime']);            //上次登录时间戳
            $usLastLoginIp      = Net::longIp(intval($UserInfo['us_last_loginip'])); //上次登录IP
            $UserInfoArray['username']       = $usAccount;
            $UserInfoArray['lastLoginTime']  = $usLastLogintime;
            $UserInfoArray['lastLoginIp']    = $usLastLoginIp;
        }
        return $UserInfoArray;
    }

    /**
     * @name         accountInc
     * @desciption 用户帐号增加
     * @param int $usId
     * @param int $moneyNum
     * @param string $remark
     * @return bool
     */
    public function accountInc(int $usId, int $moneyNum, string $remark):bool{
        if($usId < 1) return FALSE;
        if($moneyNum < 1) return TRUE;
        $currTime = Time::getTimeStamp();
        $sql = 'SELECT us_id,ua_money FROM '.$this -> Db -> getTableNameAll('user_account').' WHERE us_id=\''.$usId.'\'';
        $moneyLast = 0;
        $UserInfo = $this->Db->getDataOne($sql);
        if(isset($UserInfo['us_id']) && $UserInfo['us_id']){
            $moneyLast = intval($UserInfo['ua_money']);
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('user_account').' SET ua_money=ua_money+'.$moneyNum.', ua_money_all=ua_money_all+'.$moneyNum.',ua_version=ua_version+1, ua_end_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\'';
            $result = $this->Db->getDataNum($sql) > 0 ? TRUE : FALSE;
        }else{
            $sql = 'INSERT INTO '.$this -> Db -> getTableNameAll('user_account').' SET us_id=\''.$usId.'\', ua_money=\''.$moneyNum.'\', ua_money_use_all=\'0\', ua_money_all=\''.$moneyNum.'\', ua_version=1, ua_first_time=\''.$currTime.'\', ua_end_time=\''.$currTime.'\'';
            $result = $this->Db->getDataNum($sql) > 0 ? TRUE : FALSE;
        }
        if($result){
            $sql = 'INSERT INTO '.$this -> Db -> getTableNameAll('user_account_record').' SET us_id=\''.$usId.'\', uar_type=1, uar_result=1, uar_money=\''.$moneyNum.'\', uar_money_before=\''.$moneyLast.'\', uar_money_after=\''.($moneyLast+$moneyNum).'\', uar_remark=\''.addslashes($remark).'\', uar_isdel=0, uar_first_time=\''.$currTime.'\', uar_end_time=\''.$currTime.'\'';
            $this->Db->getDataNum($sql);
        }
        return $result;
    }

    /**
     * @name         accountDec
     * @desciption 用户帐号减少
     * @param int $usId
     * @param int $moneyNum
     * @param string $remark
     * @return bool
     */
    public function accountDec(int $usId, int $moneyNum, string $remark):bool{
        if($usId < 1) return FALSE;
        if($moneyNum < 1) return TRUE;
        $currTime = Time::getTimeStamp();
        $sql = 'SELECT us_id,ua_money FROM '.$this -> Db -> getTableNameAll('user_account').' WHERE us_id=\''.$usId.'\'';
        $moneyLast = 0;
        $UserInfo = $this->Db->getDataOne($sql);
        if(isset($UserInfo['us_id']) && $UserInfo['us_id']){
            $moneyLast = intval($UserInfo['ua_money']);
            if($moneyLast < $moneyNum) return FALSE;    //不足
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('user_account').' SET ua_money=ua_money-'.$moneyNum.', ua_money_use_all=ua_money_use_all+'.$moneyNum.',ua_version=ua_version+1, ua_end_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\'';
            $result = $this->Db->getDataNum($sql) > 0 ? TRUE : FALSE;
        }else{
            return FALSE;
        }
        if($result){
            $sql = 'INSERT INTO '.$this -> Db -> getTableNameAll('user_account_record').' SET us_id=\''.$usId.'\', uar_type=2, uar_result=1, uar_money=\''.$moneyNum.'\', uar_money_before=\''.$moneyLast.'\', uar_money_after=\''.($moneyLast-$moneyNum).'\', uar_remark=\''.addslashes($remark).'\', uar_isdel=0, uar_first_time=\''.$currTime.'\', uar_end_time=\''.$currTime.'\'';
            $this->Db->getDataNum($sql);
        }
        return $result;
    }

    /**
     * @name         getSetGgt
     * @desciption 获取设置
     * @param string $keys
     * @return array
     */
    public function getSetGgt(string $keys):array
    {
        $sql = 'SELECT sg_val FROM '.$this -> Db -> getTableNameAll('set_ggb').' WHERE sg_key=\''.$keys.'\'';
        $dataString = $this -> Db->getDataString($sql, 'sg_val');
        if(strlen($dataString) > 0){
            $dataJson = @json_decode($dataString, TRUE);
            if(is_array($dataJson)) return $dataJson;
        }
        return [$dataString];
    }

    /**
     * @name         addSetGgt
     * @desciption 设置获取积分
     * @param string $keys
     * @param string $desc default
     * @return bool
     */
    public function addSetGgt(string $keys, string $desc = ''):bool
    {
        $usId = $_SESSION['TOKEN']['UID'];
        $sql = 'SELECT COUNT(*) as num FROM '.$this -> Db -> getTableNameAll('set_ggb_record').' WHERE sgr_key=\''.$keys.'\' AND us_id=\''.$usId.'\' AND sgr_isdel=0';
        $num = $this -> Db->getDataInt($sql, 'num');
        $dataArray = $this -> getSetGgt($keys);
        if(count($dataArray) == 2){
            $setValue   = intval($dataArray[0] ?? 0);
            $setNum     = intval($dataArray[1] ?? 1);
        }else{
            $setValue   = intval($dataArray[0] ?? 0);
            $setNum     = 0;
        }
        if($setNum > 0 && $num >= $setNum) return FALSE;
        $currTime = Time::getTimeStamp(0);
        $sql = 'INSERT INTO '.$this -> Db -> getTableNameAll('set_ggb_record').' SET sgr_key=\''.$keys.'\', sgr_value=\''.addslashes($desc).'\', us_id=\''.$usId.'\', sgr_isdel=0, sgr_first_time='.$currTime.', sgr_last_time='.$currTime;
        $this -> Db->getDataNum($sql);
        if($setValue > 0 && $usId > 0) $this -> accountInc($usId, $setValue, $desc);
        return TRUE;
    }

    /**
     * @name         getProcess
     * @desciption 设置获取积分进度
     * @return array
     */
    public function getProcess():array
    {
        $setData = [];
        $usId = $_SESSION['TOKEN']['UID'];
        $todayTimeInt = intval(strtotime(date('Y-m-d 00:00:00')));
        $sql = 'SELECT sgr_key, COUNT(*) as num FROM '.$this -> Db -> getTableNameAll('set_ggb_record').' WHERE sgr_first_time>='.$todayTimeInt.' AND us_id=\''.$usId.'\' AND sgr_isdel=0 GROUP BY sgr_key';
        $dataArray = $this -> Db->getData($sql);
        $dataSetRec = [];
        if(count($dataArray) > 0) foreach ($dataArray as $val){
            $dataSetRec[$val['sgr_key']] = intval($val['num']);
        }
        $sql = 'SELECT sg_key, sg_val FROM '.$this -> Db -> getTableNameAll('set_ggb');
        $dataTmp = $this -> Db->getData($sql);
        $dataSet = [];
        if(count($dataTmp) > 0) foreach ($dataTmp as $val){
            if(strlen($val['sg_val']) > 0){
                $dataJson = @json_decode($val['sg_val'], TRUE);
                if(is_array($dataJson)){
                    $dataSet[$val['sg_key']] = $dataJson;
                }else{
                    $dataSet[$val['sg_key']] = [intval($val['sg_val']), 0];
                }
            }
        }
        if(count($dataSet) > 0){
            foreach ($dataSet as $key => $val){
                $setData[$key] = [intval($dataSetRec[$key]??'0'), intval($val[1]??'0'), intval($val[0]??'0')];
            }
        }

        $currTime = Time::getTimeStamp();
        $todayStartTime = strtotime(date('Y-m-d 00:00:00', $currTime));
        $todayEndTime = $todayStartTime+86400;
        isset($setData['SIGNIN']) && $setData['SIGNIN'][1] = 1;
        isset($setData['SIGNIN_DAY']) && $setData['SIGNIN_DAY'][1] = 1;
        $sql = 'SELECT COUNT(DISTINCT us_id) as num FROM '.$this -> Db -> getTableNameAll('signin_record').' WHERE us_id='.$usId.' AND sr_first_time>=\''.$todayStartTime.'\' AND sr_first_time<\''.$todayEndTime.'\'';
        $isSign = $this -> Db->getDataInt($sql, 'num') > 0 ? 1 : 0; //是否已签到
        if($isSign > 0) $setData['SIGNIN'][0] = 1;
        $sql = 'SELECT sg_val FROM '.$this -> Db -> getTableNameAll('set_ggb').' WHERE sg_key=\'SIGNIN_DAY\'';
        $signDayNum = max($this -> Db->getDataInt($sql, 'sg_val'), 0);
        $todayEndTime = $todayStartTime+86400*$signDayNum;
        $sql = 'SELECT COUNT(DISTINCT us_id) as num FROM '.$this -> Db -> getTableNameAll('signin_record').' WHERE us_id='.$usId.' AND sr_first_time>=\''.$todayStartTime.'\' AND sr_first_time<\''.$todayEndTime.'\'';
        $isSignContinue = $this -> Db->getDataInt($sql, 'num') >= $signDayNum ? 1 : 0; //是否连续签到
        if($isSignContinue > 0) $setData['SIGNIN_DAY'][0] = 1;
        return $setData;
    }
}