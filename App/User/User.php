<?php
/**
 * @Copyright (C) 2016.
 * @Description User
 * @FileName User.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;
use \App\Index\UserDatas;
use \App\Pub\Common;
use \App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use \Libs\Comm\Http;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Frame\Action;
use \Libs\Frame\Url;
use \App\Auth\MyAuth;
use \Libs\Tag\Db;
use \Libs\Plugins\Sms\Sms;
use \Libs\Comm\Comm;
use \Libs\Plugins\Checkcode\Checkcode;
class User extends Action{
    private $smsRegNum          = 10;   //允许有效短信数量-注册
    private $smsResetPassNum    = 10;   //允许有效短信数量-重置密码

    //配置
    public function conf(){
        $Tpl = $this -> getTpl();
        $page = [];
        $page['Title']          = '港港通国际多式联运门户网';
        $page['Keywords']       = '行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布';
        $page['Description']    = '国内首家专业性多式联运行业门户网站，集行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布等功能和内容';
        $Tpl -> assign('page', $page);
    }

    /**
     * @name main
     * @desciption 主页
     */
    public function main(string $action){
        $getsignin  = From::valInt('getsignin');
        if($getsignin == 1){    //签到
            $currTime = Time::getTimeStamp();
            $todayStartTime = strtotime(date('Y-m-d 00:00:00', $currTime));
            $todayEndTime = $todayStartTime+86400;
            $userInfo = $_SESSION['TOKEN']['INFO'];
            $usId = $userInfo['id'];
            $Db = Db::tag('DB.USER', 'GMY');
            $sql = 'SELECT COUNT(DISTINCT us_id) as num FROM '.$Db -> getTableNameAll('signin_record').' WHERE us_id='.$usId.' AND sr_first_time>=\''.$todayStartTime.'\' AND sr_first_time<\''.$todayEndTime.'\'';
            $isSign = $Db->getDataInt($sql, 'num') > 0 ? 1 : 0; //是否已签到
            if($isSign == 1){
                $resArray = ['res' => 0, 'msg' => '今日已签到!'];
            }else{
                $sql = 'SELECT sg_val FROM '.$Db -> getTableNameAll('set_ggb').' WHERE sg_key=\'SIGNIN\'';
                $signNum = max($Db->getDataInt($sql, 'sg_val'), 0);
                $sql = 'SELECT sg_val FROM '.$Db -> getTableNameAll('set_ggb').' WHERE sg_key=\'SIGNIN_DAY\'';
                $signDayNum = max($Db->getDataInt($sql, 'sg_val'), 0);
                $todayEndTime = $todayStartTime+86400*$signDayNum;
                $sql = 'SELECT COUNT(DISTINCT us_id) as num FROM '.$Db -> getTableNameAll('signin_record').' WHERE us_id='.$usId.' AND sr_first_time>=\''.$todayStartTime.'\' AND sr_first_time<\''.$todayEndTime.'\'';
                $isSignContinue = $Db->getDataInt($sql, 'num') >= $signDayNum ? 1 : 0; //是否连续签到
                if($isSignContinue > 0) $signNum *= 2;   //翻倍
                $sql = 'INSERT INTO '.$Db -> getTableNameAll('signin_record').' SET us_id='.$usId.', sr_num=\''.$signNum.'\', sr_first_time=\''.$currTime.'\'';
                if($Db->getDataNum($sql) > 0){
                    //写入账户表和记录表
                    $UserData = new UserData();
                    if($isSignContinue > 0){
                        $UserData -> accountInc($usId, $signNum, '连续签到获得翻倍奖励.');
                    }else{
                        $UserData -> accountInc($usId, $signNum, '每日签到获得.');
                    }
                    $resArray = ['res' => 1, 'msg' => '签到成功!'];
                }else{
                    $resArray = ['res' => 1, 'msg' => '签到失败!'];
                }
            }
            die(json_encode($resArray));
        }
        $username   = From::valTrim('username');
        $email      = From::valTrim('email');
        $entname    = From::valTrim('entname');
        $enttype    = From::valTrim('enttypestr');
        $save       = From::valInt('save');
        if($save == 1){
            //Common::verifyFormSig(); //FormSig
            $username = preg_replace('/[^a-z\d_\.]/i', '', $username);   //只允许 字母 数字 下划线 点
            $email = preg_replace('/[^a-z\d_\.\@]/i', '', $email);   //只允许 字母 数字 下划线 点
            if(strlen($username) < 6 || strlen($enttype) < 1 || strlen($email) < 6){
                Tips::show('请填写正确的用户名、邮箱、所处行业', 'javascript: history.back();');
            }
            if(!Valid::isEmail($email)){
                Tips::show('请填写正确的邮箱', 'javascript: history.back();');
            }
            $userInfo = $_SESSION['TOKEN']['INFO'];
            $UserData = new UserData();
            if($UserData -> setUserInfo($userInfo['id'], $username, $email, $entname, $enttype)){
                $UserData -> addSetGgt('INFOOK', '完善资料获得');
                Common::toUrl(Link::getLink('user'));
            }else{
                Tips::show('提交设置信息失败，请稍后重试', 'javascript: history.back();');
            }
        }else {
            $Tpl = $this->getTpl();
            $userInfo = $_SESSION['TOKEN']['INFO'];
            $UserData = new UserData();
            $currUserInfo = $UserData -> getUserInfo($userInfo['id']);
            $isShowInfo = strlen(trim($currUserInfo['username'])) < 1 ? 1 : 0;  //没有设置用户名
            $_SESSION['TOKEN']['INFO'] = array_merge($_SESSION['TOKEN']['INFO'], $currUserInfo);    //重新让设置更新会话生效
            $Tpl->assign('isShowInfo', $isShowInfo);
            $UserData = new UserData();
            $entTypeList = $UserData->getEntTypeList();
            $Tpl->assign('entTypeList', $entTypeList);
            $Tpl->assign('userInfo', $userInfo);
            //签到信息
            $currTime = Time::getTimeStamp();
            $todayStartTime = strtotime(date('Y-m-d 00:00:00', $currTime));
            $todayEndTime = $todayStartTime+86400;
            $usId = $userInfo['id'];
            $Db = Db::tag('DB.USER', 'GMY');
            $sql = 'SELECT COUNT(DISTINCT us_id) as num FROM '.$Db -> getTableNameAll('signin_record').' WHERE sr_first_time>=\''.$todayStartTime.'\' AND sr_first_time<\''.$todayEndTime.'\'';
            $signNum = $Db->getDataInt($sql, 'num');
            $sql = 'SELECT COUNT(DISTINCT us_id) as num FROM '.$Db -> getTableNameAll('signin_record').' WHERE us_id='.$usId.' AND sr_first_time>=\''.$todayStartTime.'\' AND sr_first_time<\''.$todayEndTime.'\'';
            $isSign = $Db->getDataInt($sql, 'num') > 0 ? 1 : 0; //是否已签到
            $Tpl->assign('signNum', $signNum);
            $Tpl->assign('isSign', $isSign);
            $sql = 'SELECT sg_val FROM '.$Db -> getTableNameAll('set_ggb').' WHERE sg_key=\'SIGNIN_DAY\'';
            $signDayNum = max($Db->getDataInt($sql, 'sg_val'), 0);
            $Tpl->assign('signDayNum', $signDayNum);
            //邀请码
            $sql = 'SELECT ui_key FROM '.$Db -> getTableNameAll('user_invite').' WHERE us_id='.$usId.' AND ui_isdel=0 AND ui_key!=\'\' ORDER BY ui_last_time DESC';
            $inviteKey = $Db->getDataString($sql, 'ui_key');
            if(strlen($inviteKey) < 1){ //生成KEY
                $maxNum = 10;
                while(--$maxNum >= 0){
                    $inviteKey = Comm::getRandString(12);
                    $sql = 'INSERT INTO '.$Db -> getTableNameAll('user_invite').' SET us_id='.$usId.', ui_key=\''.addslashes($inviteKey).'\', ui_isdel=0, ui_first_time='.$currTime.', ui_last_time='.$currTime;
                    if($Db->getDataId($sql) > 0) break;
                }
            }
            $Tpl->assign('inviteKey', $inviteKey);
            $Tpl->assign('inviteUrl', Http::getHttpSelfDir(TRUE).'reg.php?key='.urlencode($inviteKey));
            $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($usId));   //评分
            //账户信息
            $sql = 'SELECT ua_money FROM '.$Db -> getTableNameAll('user_account').' WHERE us_id='.$usId;
            $uaMoney = $Db->getDataInt($sql, 'ua_money');
            $Tpl->assign('uaMoney', $uaMoney);
            //企业认证信息
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
            $entInfo = $Db->getDataOne($sql);
            $isEnt = intval($entInfo['ent_id'] ?? 0) > 0 ? 1 : 0;
            $Tpl->assign('isEnt', $isEnt);
            //红包邀请
            $moneyInviteNum = 5;  //单位元
            $moneyInviteNumAll = 0; //可以领取的金额
            $sql = 'SELECT COUNT(*) as num FROM '.$Db -> getTableNameAll('user_invite_record').' WHERE us_id=\''.$usId.'\' AND uir_isdel=0 AND uir_isget=0';
            $inviteNum = $Db->getDataInt($sql, 'num');
            $moneyInviteNumAll = $moneyInviteNum * $inviteNum;
            $Tpl->assign('moneyInviteNum', $moneyInviteNum);
            $Tpl->assign('moneyInviteNumAll', sprintf("%.2f", $moneyInviteNumAll));
            $Tpl->show('User/main.html');
        }
    }

    /**
     * @name signin
     * @desciption 登录
     */
    public function signin(string $action){
        $username   = From::valTrim('username');
        $password   = From::valTrim('password');
        $codes      = From::valTrim('codes');
        $save       = From::valInt('save');
        if($save == 1){
            Common::verifyFormSig(); //FormSig
            if(strlen($username) < 6 || strlen($password) != 32 || strlen($codes) < 4){
                Tips::show('请输入正确的帐号、密码、验证码', Link::getLink('signin'));
            }
            if(!isset($_SESSION['CODE.USERLOGIN']) || strlen($_SESSION['CODE.USERLOGIN']) < 4 || strtolower($_SESSION['CODE.USERLOGIN']) != strtolower($codes)){
                Tips::show('请输入正确的验证码', Link::getLink('signin'));
            }
            $_SESSION['CODE.USERLOGIN'] = '';   //清空验证码
            unset($_SESSION['CODE.USERLOGIN']);
            $UserData = new UserData();
            $result = $UserData -> signin($username, $password);
            switch($result){
                case 'ERRORUSERPASS':{      //用户或密码错误
                    Tips::show('用户或密码错误！', Link::getLink('signin'));
                    break;
                }
                case 'ERRORUSERISLOGIN':{   //被禁止登录
                    Tips::show('被禁止登录！', Link::getLink('signin'));
                    break;
                }
                case 'OK':{ //成功
                    Common::toUrl(Link::getLink('user'));
                    break;
                }
                default:{
                    Tips::show('请输入正确的帐号、密码！', Link::getLink('signin'));
                }
            }
        }else{
            $MyAuth = new MyAuth();
            $Sess = $MyAuth -> getSess();
            if(is_object($Sess) && is_array($Sess -> get('TOKEN'))){
                //$Sess -> delete(); //删除会话
                //$Sess -> restart(); //重置会话
                Common::toUrl(Link::getLink('user'));
            }
            $UserData = new UserData();
            $banner = $UserData->getBannerData();
            $Tpl = $this->getTpl();
            $Tpl->assign('banner', $banner);
            $this -> getTpl() -> show('User/signin.html');
        }
    }

    /**
     * @name signout
     * @desciption 登出
     */
    public function signout(string $action){
        $UserData = new UserData();
        $UserData -> signout();
        Common::toUrl(Link::getLink('signin'));
    }

    /**
     * @name signincode
     * @desciption 验证码输出登录
     **/
    public function signincode(string $action){
        $Checkcode = new Checkcode('LOGIN');
        $_SESSION['CODE.USERLOGIN'] = $Checkcode -> getCodeString();
        $Checkcode -> doimage();
    }

    /**
     * @name reg
     * @desciption 注册
     */
    public function reg(string $action){
        $save       = From::valInt('save');
        $sms        = From::valInt('sms');
        if($sms == 1){  //ajax
            $mobile = From::valTrim('mobile');        //mobile
            $codes = From::valTrim('codes');        //codes
            if(strlen($mobile) < 6 || !Valid::isMobile($mobile)){
                $resArray = ['res' => 0, 'msg' => '失败，请填写正确的手机号！'];
            }else{
                if(strlen($codes) != 4){
                    $resArray = ['res' => 0, 'msg' => '失败，请填写正确的验证码！'];
                }else{
                    if(!isset($_SESSION['CODE.USERREG']) || strlen($_SESSION['CODE.USERREG']) < 4 || strtolower($_SESSION['CODE.USERREG']) != strtolower($codes)){
                        $resArray = ['res' => 0, 'msg' => '失败，请填写正确的验证码！'];
                    }else{
                        $_SESSION['CODE.USERREG'] = '';   //清空验证码
                        unset($_SESSION['CODE.USERREG']);
                        $Db = Db::tag('DB.USER', 'GMY');
                        $sql = 'SELECT us_id FROM '.$Db -> getTableNameAll('user_info').' WHERE ui_mobile=\''.addslashes($mobile).'\' AND ui_isdel=0';
                        $usId = $Db->getDataInt($sql, 'us_id');
                        if($usId > 0){
                            $resArray = ['res' => 0, 'msg' => '失败，手机号已绑定,请重试！'];
                        }else{
                            $Sms = new Sms();
                            $code = Comm::getRandStringNum(6);
                            //写短信记录表
                            $currTime = Time::getTimeStamp();
                            $expireTime = $currTime+3600;
                            $sql = 'SELECT count(sc_id) as num FROM '.$Db -> getTableNameAll('sms_code').' WHERE us_id=\'0\' AND sc_type=1 AND sc_status!=1 AND sc_expire_time>'.$currTime.' AND sc_isdel=0';
                            $num = $Db->getDataInt($sql, 'num');
                            if($num > $this -> smsRegNum) { //有效的验证码超过条数限制
                                $resArray = ['res' => 1, 'msg' => '短信获取太过频繁请稍后再试!'];
                            }else{
                                $sql = 'INSERT INTO '.$Db -> getTableNameAll('sms_code').' SET sc_mobile=\''.addslashes($mobile).'\', us_id=0, sc_type=1,sc_string=\''.$code.'\', sc_status=0, sc_expire_time='.$expireTime.', sc_verify_time=0, sc_verify_num=0, sc_isdel=0, sc_first_time=\''.$currTime.'\', sc_end_time=\''.$currTime.'\'';
                                if($Db->getDataNum($sql) > 0){
                                    $result = $Sms -> sendSms('您的手机验证码：'.$code.'，该验证码10分钟内有效。如非本人操作，请忽略本信息。', $mobile);
                                    if(isset($result['result']) && $result['result'] == '0'){
                                        $resArray = ['res' => 1, 'msg' => '验证码已下发，请通过手机查收!'];
                                    }else{
                                        $resArray = ['res' => 1, 'msg' => '短信发送失败!'];
                                    }
                                }else{
                                    $resArray = ['res' => 1, 'msg' => '短信发送失败!'];
                                }
                            }
                        }
                    }
                }
            }
            die(json_encode($resArray));
        }
        $key        = From::valTrim('key');  //邀请key
        if($save == 1){
            Common::verifyFormSig(); //FormSig
            $mobile = From::valTrim('mobile');        //mobile
            $mobilecode = From::valTrim('mobilecode');        //mobilecode
            $passwordval = From::valTrim('passwordval');        //passwordval
            $passwordtwoval = From::valTrim('passwordtwoval');        //passwordtwoval
            if(strlen($mobile) < 6 || !Valid::isMobile($mobile)){
                Tips::show('请填写正确的手机号', Link::getLink('reg'));
            }
            if(strlen($passwordval) != 32 || strlen($passwordtwoval) != 32 || strlen($mobilecode) != 6){
                Tips::show('请输入正确的密码、验证码', Link::getLink('reg'));
            }
            if($passwordval != $passwordtwoval){
                Tips::show('请输入两次正确的密码', Link::getLink('reg'));
            }
            $password = Common::getUserPassString($passwordval);
            $currTime = Time::getTimeStamp();
            $Db = Db::tag('DB.USER', 'GMY');
            $sql = 'SELECT us_id FROM '.$Db -> getTableNameAll('user_info').' WHERE ui_mobile=\''.addslashes($mobile).'\' AND ui_isdel=0';
            $usId = $Db->getDataInt($sql, 'us_id');
            if($usId > 0){
                Tips::show('注册失败，手机号已注册，请重试!', Link::getLink('reg'));
            }
            $usId = 0;
            $sql = 'SELECT sc_id FROM '.$Db -> getTableNameAll('sms_code').' WHERE sc_mobile=\''.addslashes($mobile).'\' AND us_id=\''.$usId.'\' AND sc_type=1 AND sc_string=\''.$mobilecode.'\' AND sc_status!=1 AND sc_expire_time>'.$currTime.' AND sc_isdel=0';
            $scId = $Db->getDataInt($sql, 'sc_id');
            if($scId > 0) { //校验验证码
                $sql = 'UPDATE '.$Db -> getTableNameAll('sms_code').' SET sc_status=1,sc_verify_time='.$currTime.',sc_verify_num=sc_verify_num+1,sc_end_time='.$currTime.' WHERE sc_id=\''.$scId.'\'';
                $Db->getDataNum($sql);
            }else{
                Tips::show('失败，验证码错误请重新填写！', 'javascript: history.back();');
            }
            $sql = 'INSERT INTO '.$Db -> getTableNameAll('user').' SET us_account=\'\', us_password=\''.addslashes($password).'\', us_islogin=1, us_last_logintime=0,us_last_loginip=0,us_isdel=0, us_first_time=\''.$currTime.'\', us_last_time=\''.$currTime.'\'';
            $usId = $Db->getDataId($sql);
            if($usId > 0){
                if(strlen($key) > 0){   //邀请记录
                    $sql = 'SELECT us_id FROM '.$Db -> getTableNameAll('user_invite').' WHERE ui_key=\''.addslashes($key).'\' AND ui_isdel=0';
                    $usIdInvite = $Db->getDataInt($sql, 'us_id');
                    $sql = 'INSERT INTO '.$Db -> getTableNameAll('user_invite_record').' SET us_id=\''.$usIdInvite.'\', uir_us_id=\''.$usId.'\', uir_key=\''.addslashes($key).'\', uir_isdel=0, uir_first_time=\''.$currTime.'\', uir_last_time=\''.$currTime.'\'';
                    $Db->getDataNum($sql);
                    (new UserData()) -> addSetGgt('INVITE_USER', '邀请用户注册成功获得');
                }
                $sql = 'INSERT INTO '.$Db -> getTableNameAll('user_info').' SET us_id=\''.$usId.'\', ui_mobile=\''.addslashes($mobile).'\', ui_isdel=0, ui_first_time=\''.$currTime.'\', ui_last_time=\''.$currTime.'\'';
                $Db->getDataNum($sql);
                $sql = 'INSERT INTO '.$Db -> getTableNameAll('user_account').' SET us_id=\''.$usId.'\', ua_money=0, ua_money_use_all=0, ua_money_all=0, ua_version=1, ua_first_time=\''.$currTime.'\', ua_end_time=\''.$currTime.'\'';
                $Db->getDataNum($sql);
                //发送欢迎短信
                $Sms = new Sms();
                $Sms -> sendSms('亲爱的GGT会员，欢迎加盟GGT！您可以在这里发布运价、发布货盘、买卖箱、在线订车...，让多联操作更高效价廉！', $mobile);
                (new UserData()) -> addSetGgt('REGISTER', '注册帐号获得');
                Tips::show('注册成功，立即登录!', Link::getLink('signin'));
            }else{
                Tips::show('注册失败，请稍后重试!', Link::getLink('reg'));
            }
        }else{
            $userInfo = $_SESSION['TOKEN']['INFO'];
            $usId = $userInfo['id'];
            if($usId > 0){  //已经登录状态下
                Common::toUrl('/user/');
            }
            if(strlen($key) > 0){
                (new UserData()) -> addSetGgt('INVITE_IP', '注册邀请帐号获得');
            }
            $this -> getTpl() -> assign('inviteKey', $key);
            $this -> getTpl() -> show('User/reg.html');
        }
    }

    /**
     * @name signupcode
     * @desciption 验证码输出注册
     **/
    public function signupcode(string $action){
        $Checkcode = new Checkcode('LOGIN');
        $_SESSION['CODE.USERREG'] = $Checkcode -> getCodeString();
        $Checkcode -> doimage();
    }

    /**
     * @name getregcode
     * @desciption 获取手机验证码注册
     **/
    public function getregcode(string $action){
        $mobile     = From::valTrim('mobile');
        $codes      = From::valTrim('codes');
        $resData    = ['res' => 0, 'msg' => ''];
        //发送并写入短信验证码
        $resData['res'] = 1;
        $resData['msg'] = '验证码已下发，请通过手机查收!';
        die(json_encode($resData));
    }

    /**
     * @name resetpasscode
     * @desciption 验证码输出重置密码
     **/
    public function resetpasscode(string $action){
        $Checkcode = new Checkcode('LOGIN');
        $_SESSION['CODE.USERRESETPASS'] = $Checkcode -> getCodeString();
        $Checkcode -> doimage();
    }

    /**
     * @name forgetPassword
     * @desciption 忘记密码
     */
    public function forgetPassword(string $action){
        $save       = From::valInt('save');
        $sms        = From::valInt('sms');
        if($sms == 1){  //ajax
            $mobile = From::valTrim('mobile');        //mobile
            $codes = From::valTrim('codes');        //codes
            if(strlen($mobile) < 6 || !Valid::isMobile($mobile)){
                $resArray = ['res' => 0, 'msg' => '失败，请填写正确的手机号！'];
            }else{
                if(strlen($codes) != 4){
                    $resArray = ['res' => 0, 'msg' => '失败，请填写正确的验证码！'];
                }else{
                    if(!isset($_SESSION['CODE.USERRESETPASS']) || strlen($_SESSION['CODE.USERRESETPASS']) < 4 || strtolower($_SESSION['CODE.USERRESETPASS']) != strtolower($codes)){
                        $resArray = ['res' => 0, 'msg' => '失败，请填写正确的验证码！'];
                    }else{
                        $_SESSION['CODE.USERRESETPASS'] = '';   //清空验证码
                        unset($_SESSION['CODE.USERRESETPASS']);
                        $Db = Db::tag('DB.USER', 'GMY');
                        $sql = 'SELECT us_id FROM '.$Db -> getTableNameAll('user_info').' WHERE ui_mobile=\''.addslashes($mobile).'\' AND ui_isdel=0';
                        $usId = $Db->getDataInt($sql, 'us_id');
                        if($usId < 1){
                            $resArray = ['res' => 0, 'msg' => '失败，手机号未绑定,请重试！'];
                        }else{
                            $Sms = new Sms();
                            $code = Comm::getRandStringNum(6);
                            //写短信记录表
                            $currTime = Time::getTimeStamp();
                            $expireTime = $currTime+3600;
                            $sql = 'SELECT count(sc_id) as num FROM '.$Db -> getTableNameAll('sms_code').' WHERE us_id=\'0\' AND sc_type=3 AND sc_status!=1 AND sc_expire_time>'.$currTime.' AND sc_isdel=0';
                            $num = $Db->getDataInt($sql, 'num');
                            if($num > $this -> smsResetPassNum) { //有效的验证码超过条数限制
                                $resArray = ['res' => 1, 'msg' => '短信获取太过频繁请稍后再试!'];
                            }else{
                                $sql = 'INSERT INTO '.$Db -> getTableNameAll('sms_code').' SET sc_mobile=\''.addslashes($mobile).'\', us_id=0, sc_type=3,sc_string=\''.$code.'\', sc_status=0, sc_expire_time='.$expireTime.', sc_verify_time=0, sc_verify_num=0, sc_isdel=0, sc_first_time=\''.$currTime.'\', sc_end_time=\''.$currTime.'\'';
                                if($Db->getDataNum($sql) > 0){
                                    $result = $Sms -> sendSms('您的手机验证码：'.$code.'，该验证码10分钟内有效。如非本人操作，请忽略本信息。', $mobile);
                                    if(isset($result['result']) && $result['result'] == '0'){
                                        $resArray = ['res' => 1, 'msg' => '验证码已下发，请通过手机查收!'];
                                    }else{
                                        $resArray = ['res' => 1, 'msg' => '短信发送失败!'];
                                    }
                                }else{
                                    $resArray = ['res' => 1, 'msg' => '短信发送失败!'];
                                }
                            }
                        }
                    }
                }
            }
            die(json_encode($resArray));
        }
        $key        = From::valTrim('key');  //邀请key
        if($save == 1){
            Common::verifyFormSig(); //FormSig
            $mobile = From::valTrim('mobile');        //mobile
            $mobilecode = From::valTrim('mobilecode');        //mobilecode
            $passwordval = From::valTrim('passwordval');        //passwordval
            $passwordtwoval = From::valTrim('passwordtwoval');        //passwordtwoval
            if(strlen($mobile) < 6 || !Valid::isMobile($mobile)){
                Tips::show('请填写正确的手机号', Link::getLink('forgetpasswd'));
            }
            if(strlen($passwordval) != 32 || strlen($passwordtwoval) != 32 || strlen($mobilecode) != 6){
                Tips::show('请输入正确的密码、验证码', Link::getLink('forgetpasswd'));
            }
            if($passwordval != $passwordtwoval){
                Tips::show('请输入两次正确的密码', Link::getLink('forgetpasswd'));
            }
            $password = Common::getUserPassString($passwordval);
            $currTime = Time::getTimeStamp();
            $Db = Db::tag('DB.USER', 'GMY');
            $sql = 'SELECT us_id FROM '.$Db -> getTableNameAll('user_info').' WHERE ui_mobile=\''.addslashes($mobile).'\' AND ui_isdel=0';
            $usId = $Db->getDataInt($sql, 'us_id');
            if($usId < 1){
                Tips::show('失败，手机号未注册，请重试!', Link::getLink('forgetpasswd'));
            }
            $sql = 'SELECT sc_id FROM '.$Db -> getTableNameAll('sms_code').' WHERE sc_mobile=\''.addslashes($mobile).'\' AND us_id=\'0\' AND sc_type=3 AND sc_string=\''.$mobilecode.'\' AND sc_status!=1 AND sc_expire_time>'.$currTime.' AND sc_isdel=0';
            $scId = $Db->getDataInt($sql, 'sc_id');
            if($scId > 0) { //校验验证码
                $sql = 'UPDATE '.$Db -> getTableNameAll('sms_code').' SET sc_status=1,sc_verify_time='.$currTime.',sc_verify_num=sc_verify_num+1,sc_end_time='.$currTime.' WHERE sc_id=\''.$scId.'\'';
                $Db->getDataNum($sql);
            }else{
                Tips::show('失败，验证码错误请重新填写！', 'javascript: history.back();');
            }
            $sql = 'UPDATE '.$Db -> getTableNameAll('user').' SET us_password=\''.addslashes($password).'\', us_last_time=\''.$currTime.'\' WHERE us_id='.$usId.' AND us_isdel=0';
            if($Db->getDataNum($sql) > 0){
                Tips::show('重置密码成功，立即登录!', Link::getLink('signin'));
            }else{
                Tips::show('重置密码失败，请稍后重试!', Link::getLink('forgetpasswd'));
            }
        }else{
            $userInfo = $_SESSION['TOKEN']['INFO'];
            $usId = $userInfo['id'];
            if($usId > 0){  //已经登录状态下
                Common::toUrl('/user/');
            }
            $this -> getTpl() -> show('User/forget_password.html');
        }
    }
}