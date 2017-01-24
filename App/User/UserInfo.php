<?php
/**
 * @Copyright (C) 2016.
 * @Description UserInfo
 * @FileName UserInfo.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;
use App\Article\ArticleModel;
use App\Index\UserDatas;
use \App\Pub\Common;
use \App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\Comm;
use \Libs\Comm\File;
use \Libs\Comm\From;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Plugins\Sms\Sms;
use \Libs\Tag\Sql;
use \Libs\Frame\Action;
use \Libs\Frame\Url;
use \App\Auth\MyAuth;
use \Libs\Load;
use \Libs\Plugins\Checkcode\Checkcode;
use \Libs\Tag\Db;
use \Libs\Frame\Conf;
use \Libs\Tag\Page;
class UserInfo extends Action{
    private $smsNum = 10;   //允许有效短信数量
    const SUCCESS   = "success";    // 成功
    const FAIL      = "fail";       // 失败
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
     * @name basic
     * @desciption 基本资料
     */
    public function basic(string $action){
        $Tpl = $this->getTpl();
        $save = From::valInt('save');
        if($save == 1){
            $data = [];
            $data['ui_name']        = From::valTrim('ui_name');         //昵称
            $data['ui_sex']         = From::valInt('ui_sex');           //性别
            $data['ui_sex']         = !in_array($data['ui_sex'], [0, 1, 2]) ? 0: $data['ui_sex'];
            $data['ui_phone']       = From::valTrim('ui_phone');        //电话
            $data['ui_fax']         = From::valTrim('ui_fax');          //传真
            $data['ui_poscode']     = From::valTrim('ui_poscode');      //邮编
            $data['ui_qq']          = From::valTrim('ui_qq');           //QQ
            $currTime = Time::getTimeStamp();
            $data['ui_last_time']   = $currTime;
            $usId                   = intval($this -> userInfo['id']);
            if(isset($_FILES['ui_photo']) && isset($_FILES['ui_photo']['tmp_name']) && strlen($_FILES['ui_photo']['tmp_name']) > 0) {
                $basicPhotoUrl = '/user/'.md5($currTime.$usId).'.jpg';
                $newPhotoUrl = Load::getUrlRoot().'Static/data'.$basicPhotoUrl;
                $maxSize = 2*1024*1024; //2M
                $upRes = File::upFile($_FILES['ui_photo'], $newPhotoUrl, ['A' => ['jpg', 'jpeg', 'png', 'gif', 'bmp']], $maxSize);
                if($upRes != 'Y'){
                    Tips::show('头像修改失败，请修改正确后提交！', 'javascript: history.back();');
                }
                $data['ui_photo']   = $basicPhotoUrl;
            }
            $where = [];
            $where['us_id'] = $usId;
            $where['ui_isdel'] = 0;
            $result = Sql::tag('user_info', 'GMY') -> setByNum($data, $where);
            if($result){
                $UserData = new UserData();
                $_SESSION['TOKEN']['INFO'] = $UserData -> getUserInfo($usId);
                Tips::show('修改成功！', Link::getLink('user').'?A=user-basic');
            }else{
                Tips::show('修改失败，请修改正确后提交！', 'javascript: history.back();');
            }
        }
        $userInfo = $this -> userInfo;
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_basic.html');
    }

    /**
     * @name safe
     * @desciption 帐号安全
     */
    public function safe(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_safe.html');
    }

    /**
     * @name passwd
     * @desciption 修改密码
     */
    public function passwd(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $userInfo = $this -> userInfo;
        $save = From::valInt('save');
        if($save == 1){
            $oldpassword = From::valTrim('oldpassword');        //旧密码
            $newpassword = From::valTrim('newpassword');        //新密码
            $newpasswordtwo = From::valTrim('newpasswordtwo');  //新密码Two
            if(strlen($oldpassword) != 32 || strlen($newpassword) != 32 || strlen($newpasswordtwo) != 32){
                Tips::show('失败，请填写正确后提交！', 'javascript: history.back();');
            }
            if($newpassword != $newpasswordtwo){
                Tips::show('失败，两次密码不一样！', 'javascript: history.back();');
            }
            $oldpassword = Common::getUserPassString($oldpassword);
            $newpassword = Common::getUserPassString($newpassword);
            $newpasswordtwo = Common::getUserPassString($newpasswordtwo);
            $currTime = Time::getTimeStamp();
            $usId = $userInfo['id'];
            $sql = 'UPDATE '.$Db -> getTableNameAll('user').' SET us_password=\''.addslashes($newpassword).'\', us_last_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\' AND us_password=\''.addslashes($oldpassword).'\' AND us_isdel=\'0\' AND us_islogin=\'1\'';
            if($Db->getDataNum($sql) > 0){
                Tips::show('修改成功！', Link::getLink('user').'?A=user-passwd');
            }else{
                Tips::show('修改失败，原密码不正确！', 'javascript: history.back();');
            }
        }
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_passwd.html');
    }

    /**
     * @name account
     * @desciption 账户信息
     */
    public function account(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId  = intval($userInfo['id']);
        $Tpl->assign('userInfo', $userInfo);
        //账户信息
        $sql = 'SELECT ua_money FROM '.$Db -> getTableNameAll('user_account').' WHERE us_id='.$usId;
        $uaMoney = $Db->getDataInt($sql, 'ua_money');
        $Tpl->assign('uaMoney', $uaMoney);
        $type = From::valInt('type');
        if($type == 2) {    //消费记录
            $whereString = 'uar_isdel=0 AND us_id='.$usId.' AND uar_type=2';
            $baseUrl = '/user/index.php?A=user-account&type=2';
            $Page = Page::tag('ent', 'PLST');
            $Page -> setParam('size', 9);
            $Page -> setParam('currPage', max(From::valInt('pg'), 1));
            $limit = $Page -> getLimit();
            $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('user_account_record').' WHERE '.$whereString.' ORDER BY uar_first_time DESC LIMIT '.$limit[0].', '.$limit[1];
            $dataArray = $Db -> getData($sql);
            $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
            $Page -> setParam('totalNum', $totalNum);
            $dataList = [];
            if(isset($dataArray[0]) && is_array($dataArray[0])){
                foreach ($dataArray as $key => $val){
                    $dataList[] = $val;
                }
            }
            $pageList = $Page -> getPage($baseUrl);
            $Tpl -> assign('pageList', $pageList);
            $Tpl -> assign('dataList', $dataList);
            $Tpl->show('User/user_account_xiaofei.html');
        }else{  //充值记录
            $whereString = 'uar_isdel=0 AND us_id='.$usId.' AND uar_type=1';
            $baseUrl = '/user/index.php?A=user-account&type=1';
            $Page = Page::tag('ent', 'PLST');
            $Page -> setParam('size', 9);
            $Page -> setParam('currPage', max(From::valInt('pg'), 1));
            $limit = $Page -> getLimit();
            $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('user_account_record').' WHERE '.$whereString.' ORDER BY uar_first_time DESC LIMIT '.$limit[0].', '.$limit[1];
            $dataArray = $Db -> getData($sql);
            $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
            $Page -> setParam('totalNum', $totalNum);
            $dataList = [];
            if(isset($dataArray[0]) && is_array($dataArray[0])){
                foreach ($dataArray as $key => $val){
                    $dataList[] = $val;
                }
            }
            $pageList = $Page -> getPage($baseUrl);
            $Tpl -> assign('pageList', $pageList);
            $Tpl -> assign('dataList', $dataList);
            $Tpl->show('User/user_account.html');
        }
    }

    /**
     * @name order
     * @desciption 订单管理
     */
    public function order(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_order.html');
    }

    /**
     * @name address
     * @desciption 收货地址管理
     */
    public function address(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_address.html');
    }

    /**
     * @name editmobile
     * @desciption 修改或验证手机号
     */
    public function editmobile(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $this -> userInfo;
        $save = From::valInt('save');
        $sms = From::valInt('sms');
        if($sms == 1){  //ajax
            $mobile = From::valTrim('mobile');        //邮箱
            if(strlen($mobile) < 6 || !Valid::isMobile($mobile)){
                $resArray = ['res' => 0, 'msg' => '失败，请填写正确的手机号！'];
            }else{
                $Sms = new Sms();
                $code = Comm::getRandStringNum(6);
                //写短信记录表
                $usId = $userInfo['id'];
                $currTime = Time::getTimeStamp();
                $expireTime = $currTime+3600;
                $Db = Db::tag('DB.USER', 'GMY');
                $sql = 'SELECT count(sc_id) as num FROM '.$Db -> getTableNameAll('sms_code').' WHERE us_id=\''.$usId.'\' AND sc_type=2 AND sc_status!=1 AND sc_expire_time>'.$currTime.' AND sc_isdel=0';
                $num = $Db->getDataInt($sql, 'num');
                if($num > $this -> smsNum) { //有效的验证码超过条数限制
                    $resArray = ['res' => 1, 'msg' => '短信获取太过频繁请稍后再试!'];
                }else{
                    $sql = 'INSERT INTO '.$Db -> getTableNameAll('sms_code').' SET sc_mobile=\''.addslashes($mobile).'\', us_id=\''.$usId.'\', sc_type=2,sc_string=\''.$code.'\', sc_status=0, sc_expire_time='.$expireTime.', sc_verify_time=0, sc_verify_num=0, sc_isdel=0, sc_first_time=\''.$currTime.'\', sc_end_time=\''.$currTime.'\'';
                    if($Db->getDataNum($sql) > 0){
                        $result = $Sms -> sendSms('您的手机验证码：'.$code.'，该验证码10分钟内有效。如非本人操作，请忽略本信息。', $mobile);
                        if(isset($result['result']) && $result['result'] == '0'){
                            $resArray = ['res' => 1, 'msg' => '短信发送成功!'];
                        }else{
                            $resArray = ['res' => 1, 'msg' => '短信发送失败!'];
                        }
                    }else{
                        $resArray = ['res' => 1, 'msg' => '短信发送失败!'];
                    }
                }
            }
            die(json_encode($resArray));
        }
        if($save == 1){
            $mobile = From::valTrim('mobile');        //邮箱
            if(strlen($mobile) < 6 || !Valid::isMobile($mobile)){
                Tips::show('失败，请填写正确的手机号！', 'javascript: history.back();');
            }
            $code = From::valTrim('code');        //code
            if(strlen($code) != 6 || !is_numeric($code)){
                Tips::show('失败，请填写正确的验证码！', 'javascript: history.back();');
            }
            $Db = Db::tag('DB.USER', 'GMY');
            $currTime = Time::getTimeStamp();
            $usId = $userInfo['id'];
            $sql = 'SELECT sc_id FROM '.$Db -> getTableNameAll('sms_code').' WHERE sc_mobile=\''.addslashes($mobile).'\' AND us_id=\''.$usId.'\' AND sc_type=2 AND sc_string=\''.$code.'\' AND sc_status!=1 AND sc_expire_time>'.$currTime.' AND sc_isdel=0';
            $scId = $Db->getDataInt($sql, 'sc_id');
            if($scId > 0) { //校验验证码
                $sql = 'UPDATE '.$Db -> getTableNameAll('sms_code').' SET sc_status=1,sc_verify_time='.$currTime.',sc_verify_num=sc_verify_num+1,sc_end_time='.$currTime.' WHERE sc_id=\''.$scId.'\'';
                $Db->getDataNum($sql);
            }else{
                Tips::show('失败，验证码错误请重新填写！', 'javascript: history.back();');
            }
            $sql = 'UPDATE '.$Db -> getTableNameAll('user_info').' SET ui_mobile=\''.addslashes($mobile).'\', ui_last_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\' AND ui_isdel=\'0\'';
            if($Db->getDataNum($sql) > 0){
                $UserData = new UserData();
                $_SESSION['TOKEN']['INFO'] = $UserData -> getUserInfo($usId);
                Tips::show('修改成功！', Link::getLink('user').'?A=user-editmobile');
            }else{
                Tips::show('修改失败，请稍后重试！', 'javascript: history.back();');
            }
        }
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_editmobile.html');
    }

    /**
     * @name editemail
     * @desciption 修改或验证邮箱
     */
    public function editemail(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $this -> userInfo;
        $save = From::valInt('save');
        if($save == 1){
            $email = From::valTrim('email');        //邮箱
            if(strlen($email) < 6 || !Valid::isEmail($email)){
                Tips::show('失败，请填写正确的邮箱！', 'javascript: history.back();');
            }
            $currTime = Time::getTimeStamp();
            $usId = $userInfo['id'];
            $Db = Db::tag('DB.USER', 'GMY');
            $sql = 'UPDATE '.$Db -> getTableNameAll('user_info').' SET ui_email=\''.addslashes($email).'\', ui_last_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\' AND ui_isdel=\'0\'';
            if($Db->getDataNum($sql) > 0){
                $UserData = new UserData();
                $_SESSION['TOKEN']['INFO'] = $UserData -> getUserInfo($usId);
                Tips::show('修改成功！', Link::getLink('user').'?A=user-editemail');
            }else{
                Tips::show('修改失败，请稍后重试！', 'javascript: history.back();');
            }
        }
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_editemail.html');
    }

    /**
     * @name punchlist
     * @desciption 签到记录列表
     */
    public function punchlist(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $userInfo = $this -> userInfo;
        $usId = $userInfo['id'];
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId;
        /*
        $save       = From::valTrim('save');
        if($save == 1) {
            $Page -> setQuery('save', $save);
            $name = From::valTrim('name');
            if(strlen($name) > 0){
                $whereString .= ($whereString == ''?'':' AND ').'(aui_name like \'%'.addslashes($val).'%\' OR au_account like \'%'.addslashes($val).'%\')';
                $Page -> setQuery('name', $name);
                $Tpl -> assign('name', $name);
            }
        }
        */
        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS sr_num, sr_first_time FROM '.$Db -> getTableNameAll('signin_record').$whereString.' ORDER BY sr_first_time DESC LIMIT '.$limit[0].', '.$limit[1];
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
        $Tpl->show('User/user_punchlist.html');
    }

    /**
     * @name invitereg
     * @desciption 邀请注册
     */
    public function invitereg(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_invitereg.html');
    }

    /**
     * @name 公司认证信息上传图片
     * @desciption 消息中心
     */
    public function entauthupload(){
        $userInfo = $this -> userInfo;
        $usId = $userInfo['id'];
        $currTime = Time::getTimeStamp();
        if(isset($_FILES['ent_yingyezhizhao_img']) && isset($_FILES['ent_yingyezhizhao_img']['tmp_name']) && strlen($_FILES['ent_yingyezhizhao_img']['tmp_name']) > 0) {
            $basicPhotoUrl = '/user/'.md5($currTime.$usId).'.jpg';
            $newPhotoUrl = Load::getUrlRoot().'Static/data'.$basicPhotoUrl;
            $maxSize = 2*1024*1024; //2M
            $upRes = File::upFile($_FILES['ent_yingyezhizhao_img'], $newPhotoUrl, ['A' => ['jpg', 'jpeg', 'png', 'gif', 'bmp']], $maxSize);
            if($upRes != 'Y'){
                Tips::show('营业执照图片修改失败，请修改正确后提交！', 'javascript: history.back();');
            }
            $ent_yingyezhizhao_img = $basicPhotoUrl;
            // 返回JSON
            $json = [
                'url'       => $ent_yingyezhizhao_img,
                'status'    => static::SUCCESS
            ];
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }

    }

    /**
     * @name entauth
     * @desciption 公司认证信息
     */
    public function entauth(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $this -> userInfo;
        $usId = $userInfo['id'];
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $save = From::valInt('save');
        if($save == 1){
            $ent_name = From::valTrim('ent_name');
            $ent_regnum = From::valTrim('ent_regnum');
            $ent_fadingren = From::valTrim('ent_fadingren');
            $ent_regmoney = From::valTrim('ent_regmoney');
            $ent_address = From::valTrim('ent_address');
            $ent_addr_sheng = From::valTrim('ent_addr_sheng');
            $ent_addr_shi = From::valTrim('ent_addr_shi');
            $ent_addr_qu = From::valTrim('ent_addr_qu');
            $ent_yingyezhizhao_img = From::valTrim('ent_yingyezhizhao_img');
            //if(strlen($ent_name) < 2) Tips::show('失败，请填写正确的公司名称！', 'javascript: history.back();');
            if(strlen($ent_regnum) < 2) Tips::show('失败，请填写正确的注册号！', 'javascript: history.back();');
            if(strlen($ent_fadingren) < 2) Tips::show('失败，请填写正确的法定代表人！', 'javascript: history.back();');
            if(strlen($ent_regmoney) < 2) Tips::show('失败，请填写正确的注册资本！', 'javascript: history.back();');
            if(strlen($ent_address) < 2) Tips::show('失败，请填写正确的注册地址！', 'javascript: history.back();');

            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=\'0\'';
            $entShouquan = $Db->getDataOne($sql);
            if(!isset($entShouquan['us_id'])){
                $sql = 'INSERT INTO '.$Db -> getTableNameAll('enterprise').' SET ent_status=0,ent_name=\''.addslashes($ent_name).'\', ent_yingyezhizhao_img=\''.addslashes($ent_yingyezhizhao_img).'\',ent_regnum=\''.addslashes($ent_regnum).'\',ent_fadingren=\''.addslashes($ent_fadingren).'\',ent_regmoney=\''.addslashes($ent_regmoney).'\',ent_addr_sheng=\''.addslashes($ent_addr_sheng).'\',ent_addr_shi=\''.addslashes($ent_addr_shi).'\',ent_addr_qu=\''.addslashes($ent_addr_qu).'\',ent_address=\''.addslashes($ent_address).'\', ent_last_time=\''.$currTime.'\', us_id=\''.$usId.'\', ent_isdel=\'0\', ent_first_time=\''.$currTime.'\'';
                if($Db->getDataNum($sql) > 0){
                    Tips::show('提交成功1！', Link::getLink('user').'?A=user-entauth');
                }else{
                    Tips::show('提交失败2，请稍后重试！', 'javascript: history.back();');
                }
            }else{
                $sql = 'UPDATE '.$Db -> getTableNameAll('enterprise').' SET ent_status=0,ent_name=\''.addslashes($ent_name).'\''.($ent_yingyezhizhao_img!='' ? ', ent_yingyezhizhao_img=\''.addslashes($ent_yingyezhizhao_img).'\'':'').',ent_regnum=\''.addslashes($ent_regnum).'\',ent_fadingren=\''.addslashes($ent_fadingren).'\',ent_regmoney=\''.addslashes($ent_regmoney).'\',ent_addr_sheng=\''.addslashes($ent_addr_sheng).'\',ent_addr_shi=\''.addslashes($ent_addr_shi).'\',ent_addr_qu=\''.addslashes($ent_addr_qu).'\',ent_address=\''.addslashes($ent_address).'\', ent_last_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\' AND ent_isdel=\'0\'';
                if($Db->getDataNum($sql) > 0){
                    Tips::show('提交成功！', Link::getLink('user').'?A=user-entauth');
                }else{
                    Tips::show('提交失败，请稍后重试！', 'javascript: history.back();');
                }
            }
        }
        $urlRes = Conf::get('URL.RES');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=\'0\'';
        $entInfo = $Db->getDataOne($sql);
        //if(is_array($entInfo) && strlen(trim($entInfo['ent_yingyezhizhao_img'] ?? '')) > 0) $entInfo['ent_yingyezhizhao_img'] = ltrim($entInfo['ent_yingyezhizhao_img'], '/');
        $Tpl->assign('entInfo', $entInfo);
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_entauth.html');
    }

    /**
     * @name 公司授权信息上传图片
     * @desciption 消息中心
     */
    public function entauthorizeupload(){
        $userInfo = $this -> userInfo;
        $usId = $userInfo['id'];
        $currTime = Time::getTimeStamp();
        if(isset($_FILES['ents_shouquanshu_img']) && isset($_FILES['ents_shouquanshu_img']['tmp_name']) && strlen($_FILES['ents_shouquanshu_img']['tmp_name']) > 0) {
            $basicPhotoUrl = '/user/'.md5($currTime.$usId).'.jpg';
            $newPhotoUrl = Load::getUrlRoot().'Static/data'.$basicPhotoUrl;
            $maxSize = 2*1024*1024; //2M
            $upRes = File::upFile($_FILES['ents_shouquanshu_img'], $newPhotoUrl, ['A' => ['jpg', 'jpeg', 'png', 'gif', 'bmp']], $maxSize);
            if($upRes != 'Y'){
                Tips::show('授权书修改失败，请修改正确后提交！', 'javascript: history.back();');
            }
            $ents_shouquanshu_img = $basicPhotoUrl;
            // 返回JSON
            $json = [
                'url'       => $ents_shouquanshu_img,
                'status'    => static::SUCCESS
            ];
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }

    }

    /**
     * @name entauthorize
     * @desciption 公司授权信息
     */
    public function entauthorize(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $this -> userInfo;
        $usId = $userInfo['id'];
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $save = From::valInt('save');
        if($save == 1){
            $ents_name = From::valTrim('ents_name');
            $ents_bumen = From::valTrim('ents_bumen');
            $ents_zhiwei = From::valTrim('ents_zhiwei');
            $ents_shouquanshu_img = From::valTrim('ents_shouquanshu_img');
            if(strlen($ents_name) < 2) Tips::show('失败，请填写正确的真实姓名！', 'javascript: history.back();');
            if(strlen($ents_bumen) < 2) Tips::show('失败，请填写正确的我的部门！', 'javascript: history.back();');
            if(strlen($ents_zhiwei) < 2) Tips::show('失败，请填写正确的我的职位！', 'javascript: history.back();');
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise_shouquan').' WHERE us_id=\''.$usId.'\' AND ents_isdel=\'0\'';
            $entShouquan = $Db->getDataOne($sql);
            if(!isset($entShouquan['us_id'])){
                $sql = 'INSERT INTO '.$Db -> getTableNameAll('enterprise_shouquan').' SET ents_status=0,ents_name=\''.addslashes($ents_name).'\', ents_shouquanshu_img=\''.addslashes($ents_shouquanshu_img).'\',ents_bumen=\''.addslashes($ents_bumen).'\',ents_zhiwei=\''.addslashes($ents_zhiwei).'\', ents_last_time=\''.$currTime.'\', us_id=\''.$usId.'\', ents_isdel=\'0\', ents_first_time=\''.$currTime.'\'';
                if($Db->getDataNum($sql) > 0){
                    Tips::show('提交成功！', Link::getLink('user').'?A=user-entauthorize');
                }else{
                    Tips::show('提交失败，请稍后重试！', 'javascript: history.back();');
                }
            }else{
                $sql = 'UPDATE '.$Db -> getTableNameAll('enterprise_shouquan').' SET ents_status=0,ents_name=\''.addslashes($ents_name).'\''.($ents_shouquanshu_img!='' ? ', ents_shouquanshu_img=\''.addslashes($ents_shouquanshu_img).'\'':'').',ents_bumen=\''.addslashes($ents_bumen).'\',ents_zhiwei=\''.addslashes($ents_zhiwei).'\', ents_last_time=\''.$currTime.'\' WHERE us_id=\''.$usId.'\' AND ents_isdel=\'0\'';
                if($Db->getDataNum($sql) > 0){
                    Tips::show('提交成功！', Link::getLink('user').'?A=user-entauthorize');
                }else{
                    Tips::show('提交失败，请稍后重试！', 'javascript: history.back();');
                }
            }
        }
        $urlRes = Conf::get('URL.RES');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise_shouquan').' WHERE us_id=\''.$usId.'\' AND ents_isdel=\'0\'';
        $entShouquan = $Db->getDataOne($sql);
        if(!is_array($entShouquan)){
            $entShouquan = [];
        }else{
//            if(isset($entShouquan['ents_shouquanshu_img'])){
//                $entShouquan['ents_shouquanshu_img'] = ltrim($entShouquan['ents_shouquanshu_img'], '/');
//                if(strlen($entShouquan['ents_shouquanshu_img']) > 0) $entShouquan['ents_shouquanshu_img'] = $urlRes.$entShouquan['ents_shouquanshu_img'];
//            }else{
//                $entShouquan['ents_shouquanshu_img'] = '';
//            }
        }
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=\'0\'';
        $entInfo = $Db->getDataOne($sql);
        if(is_array($entInfo)) $entShouquan['ent_name'] = $entInfo['ent_name'];
        $Tpl->assign('entShouquan', $entShouquan);
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_entauthorize.html');
    }



    /**
     * @name msg
     * @desciption 消息中心
     */
    public function msg(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_msg.html');
    }

    /**
     * @name ggb
     * @desciption 港港币
     */
    public function ggb(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId  = intval($userInfo['id']);
        $Tpl->assign('userInfo', $userInfo);
        //账户信息
        $sql = 'SELECT ua_money FROM '.$Db -> getTableNameAll('user_account').' WHERE us_id='.$usId;
        $uaMoney = $Db->getDataInt($sql, 'ua_money');
        $Tpl->assign('uaMoney', $uaMoney);
        $type = From::valInt('type');
        $Tpl->assign('type', $type);
        $UserData = new UserData();
        $process = $UserData -> getProcess();
        $Tpl->assign('process', $process);
        if($type == 2) {    //消费记录
            $whereString = 'uar_isdel=0 AND us_id='.$usId.' AND uar_type=2';
            $baseUrl = '/user/index.php?A=user-ggb&type=2';
            $Page = Page::tag('ent', 'PLST');
            $Page -> setParam('size', 9);
            $Page -> setParam('currPage', max(From::valInt('pg'), 1));
            $limit = $Page -> getLimit();
            $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('user_account_record').' WHERE '.$whereString.' ORDER BY uar_first_time DESC LIMIT '.$limit[0].', '.$limit[1];
            $dataArray = $Db -> getData($sql);
            $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
            $Page -> setParam('totalNum', $totalNum);
            $dataList = [];
            if(isset($dataArray[0]) && is_array($dataArray[0])){
                foreach ($dataArray as $key => $val){
                    $dataList[] = $val;
                }
            }
            $pageList = $Page -> getPage($baseUrl);
            $Tpl -> assign('pageList', $pageList);
            $Tpl -> assign('dataList', $dataList);
        }else{  //充值记录
            $whereString = 'uar_isdel=0 AND us_id='.$usId.' AND uar_type=1';
            $baseUrl = '/user/index.php?A=user-ggb&type=1';
            $Page = Page::tag('ent', 'PLST');
            $Page -> setParam('size', 9);
            $Page -> setParam('currPage', max(From::valInt('pg'), 1));
            $limit = $Page -> getLimit();
            $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('user_account_record').' WHERE '.$whereString.' ORDER BY uar_first_time DESC LIMIT '.$limit[0].', '.$limit[1];
            $dataArray = $Db -> getData($sql);
            $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
            $Page -> setParam('totalNum', $totalNum);
            $dataList = [];
            if(isset($dataArray[0]) && is_array($dataArray[0])){
                foreach ($dataArray as $key => $val){
                    $dataList[] = $val;
                }
            }
            $pageList = $Page -> getPage($baseUrl);
            $Tpl -> assign('pageList', $pageList);
            $Tpl -> assign('dataList', $dataList);
        }
        //积分商城帮助
        $ArticleModel = new ArticleModel();
        $shopHelpList = $ArticleModel -> getArticleList(65, 5);    //获取积分商城帮助
        $Tpl -> assign('shopHelpList', $shopHelpList);
        //账户信息
        $sql = 'SELECT ua_money FROM '.$Db -> getTableNameAll('user_account').' WHERE us_id='.$usId;
        $uaMoney = $Db->getDataInt($sql, 'ua_money');
        $Tpl->assign('uaMoney', $uaMoney);
        //账户信息排行
        $sql = 'SELECT us_id, ua_money FROM '.$Db -> getTableNameAll('user_account').' order by ua_money DESC, ua_end_time ASC LIMIT 1000';
        $uaMoneyList = $Db->getData($sql);
        $uaMoneySort = 0;
        if(count($uaMoneyList) > 0) foreach ($uaMoneyList as $key => $val){
            if($val['us_id'] == $usId){
                $uaMoneySort = $key+1;
                break;
            }
        }
        $Tpl->assign('uaMoneySort', $uaMoneySort < 1 ? '999+' : $uaMoneySort);
        $Tpl->show('User/user_ggb.html');
    }

    /**
     * @name lgggb
     * @desciption 赚取港港币
     */
    public function lgggb(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $UserData = new UserData();
        $process = $UserData -> getProcess();
        $Tpl->assign('process', $process);
        $Tpl->show('User/user_lgggb.html');
    }

    /**
     * @name syggb
     * @desciption 使用港港币
     */
    public function syggb(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_syggb.html');
    }

    /**
     * @name xinyong
     * @desciption 信用管理
     */
    public function xinyong(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $Db = Db::tag('DB.USER', 'GMY');
        $viewUsId = intval($userInfo['id'] ?? 0);
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
            $Tpl -> assign('reviewNum', (new UserDatas()) -> getReviewNum($viewUsId));   //评分
        }
        $Tpl -> assign('entInfo', $entInfo);
        $Tpl->show('User/user_xinyong.html');
    }

    /**
     * @name help
     * @desciption 帮助说明
     */
    public function help(string $action){
        $Tpl = $this->getTpl();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $Tpl->assign('userInfo', $userInfo);
        $Tpl->show('User/user_help.html');
    }

    /**
     * @name entauthmain
     * @desciption 企业认证
     */
    public function entauthmain(string $action){
        $Tpl = $this->getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $currTime = Time::getTimeStamp();
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $save = From::valInt('save');
        //企业认证信息
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
        $entInfo = $Db->getDataOne($sql);
        $isEnt = intval($entInfo['ent_id'] ?? 0) > 0 ? 1 : 0;
        $Tpl->assign('isEnt', $isEnt);
        //企业授权信息
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise_shouquan').' WHERE us_id=\''.$usId.'\' AND ents_isdel=0 ORDER BY ents_last_time DESC';
        $entsInfo = $Db->getDataOne($sql);
        $isEnts = intval($entsInfo['ents_id'] ?? 0) > 0 ? 1 : 0;
        $Tpl->assign('isEnts', $isEnts);
        if($save == 1){
            //开通商铺
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
            $esInfo = $Db->getDataOne($sql);
            $isEs = intval($esInfo['es_id'] ?? 0) > 0 ? 1 : 0;
            if($isEs == 1){
                Tips::show('商铺已经成功开通！', 'javascript: history.back();');
            }
            if($isEnt != 1) Tips::show('企业认证信息未填写，现在区填写!', Link::getLink('user').'?A=user-entauth');
            if($isEnts != 1) Tips::show('企业授权信息未填写，现在区填写!', Link::getLink('user').'?A=user-entauthorize');
            $mobanDefault = 0;
            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_moban').' WHERE em_isdel=0 ORDER BY em_last_time DESC';
            $mobanList = $Db->getData($sql);
            if(count($mobanList) > 0) foreach ($mobanList as $val){
                if($val['em_isdefault'] == "1"){
                    $mobanDefault = intval($val['em_id']);
                    break;
                }
            }
            $esDomain = strtolower(Comm::getRandString(12));    //子域名
            $sql = 'INSERT INTO '.$Db -> getTableNameAll('ent_setting').' SET us_id='.$usId.', es_status=0, es_domain=\''.addslashes($esDomain).'\', em_id=\''.$mobanDefault.'\', es_isdel=0, es_first_time='.$currTime.', es_last_time='.$currTime;
            if($Db->getDataNum($sql) > 0){
                Tips::show('商铺开通成功!', Link::getLink('user').'?A=user-entauthmain');
            }else{
                Tips::show('商铺开通失败，请稍候重试!', Link::getLink('user').'?A=user-entauthmain');
            }
        }
        //开通商铺信息
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $esInfo = $Db->getDataOne($sql);
        $isEs = intval($esInfo['es_id'] ?? 0) > 0 ? 1 : 0;
        $Tpl->assign('isEs', $isEs);
        $Tpl->show('User/user_entauthmain.html');
    }
}