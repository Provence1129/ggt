<?php
/**
 * @Copyright (C) 2016.
 * @Description Entshop
 * @FileName Entshop.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;
use \App\Pub\Common;
use App\Pub\Link;
use \App\Pub\Tips;
use Libs\Comm\File;
use \Libs\Comm\From;
use Libs\Comm\Http;
use Libs\Comm\Time;
use Libs\Comm\Valid;
use \Libs\Frame\Action;
use Libs\Frame\Conf;
use \Libs\Frame\Url;
use \App\Auth\MyAuth;
use Libs\Load;
use \Libs\Plugins\Checkcode\Checkcode;
use Libs\Tag\Db;
use Libs\Tag\Page;

class Entshop extends Action{
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
     * @desciption 商铺管理
     */
    public function main(string $action){
        $Tpl = $this->getTpl();
        $Tpl->show('User/entshop_main.html');
    }

    /**
     * @name setting
     * @desciption 商铺设置
     */
    public function setting(string $action){
        $Tpl = $this->getTpl();
        $type = From::valTrim('type');
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $Db = Db::tag('DB.USER', 'GMY');
        //开通商铺
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $esInfo = $Db->getDataOne($sql);
        $isEs = intval($esInfo['es_id'] ?? 0) > 0 ? 1 : 0;
        if($isEs == 0) Tips::show('商铺还未开通，现在去开通!', Link::getLink('user').'?A=user-entauthmain');
        $id = From::valInt('id');
        $Tpl->assign('type', $type);
        $save       = From::valInt('save');
        switch ($type){
            case 'news':{
                $op = From::valTrim('op');
                $currTime = Time::getTimeStamp();
                $Db = Db::tag('DB.USER', 'GMY');
                if($op == 'add'){   //新增发布OR修改
                    if($save == 1){
                        $en_title = From::valTrim('en_title');
                        $en_content = From::post('en_content');
                        if($id > 0){
                            $sql = 'UPDATE '.$Db -> getTableNameAll('ent_news').' SET us_id=\''.$usId.'\', en_title=\''.addslashes($en_title).'\', en_content=\''.addslashes($en_content).'\', en_last_time='.$currTime.' WHERE en_id='.$id.' AND us_id='.$usId.' AND en_isdel=0';
                        }else{
                            $sql = 'INSERT INTO '.$Db -> getTableNameAll('ent_news').' SET us_id=\''.$usId.'\', en_title=\''.addslashes($en_title).'\', en_content=\''.addslashes($en_content).'\', en_isdel=0, en_first_time='.$currTime.', en_last_time='.$currTime;
                        }
                        $Db->getDataNum($sql);
                        (new UserData()) -> addSetGgt('ENT_NEWS_REL', '发布商铺新闻获得');
                        Tips::show('成功!', Link::getLink('entshop').'?A=entshop-set&type=news');
                    }else{
                        if($id > 0){
                            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_news').' WHERE en_id=\''.$id.'\' AND us_id=\''.$usId.'\' AND en_isdel=0';
                            $info = $Db->getDataOne($sql);
                            isset($info['en_content']) && $info['en_content'] = preg_replace("/\<br[ \/]*\>/", "", stripslashes($info['en_content']));
                            $Tpl->assign('info', $info);
                        }
                    }
                    $Tpl->show('User/entshop_set_news_add.html');
                }elseif($op == 'del'){  //删除
                    $sql = 'UPDATE '.$Db -> getTableNameAll('ent_news').' SET en_isdel=1, en_last_time='.$currTime.' WHERE en_id='.$id.' AND us_id='.$usId.' AND en_isdel=0';
                    $Db->getDataNum($sql);
                    Tips::show('成功!', Link::getLink('entshop').'?A=entshop-set&type=news');
                }
                $Page = Page::tag('ent', 'PLST');
                $Page -> setParam('currPage', max(From::valInt('pg'), 1));
                $limit = $Page -> getLimit();
                $whereString = 'us_id='.$usId.' AND en_isdel=0';
                $Page -> setQuery('type', $type);
                $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('ent_news').' WHERE '.$whereString.' ORDER BY en_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
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
                $Tpl -> assign('isstart', count($dataList) ? 1 : 0);
                $Tpl->show('User/entshop_set_news.html');
                break;
            }
            case 'honor':{
                $op = From::valTrim('op');
                $currTime = Time::getTimeStamp();
                $Db = Db::tag('DB.USER', 'GMY');
                $urlRes = Conf::get('URL.RES'); //资源地址
                if($op == 'add'){   //新增发布OR修改
                    if($save == 1){
                        $eh_name = From::valTrim('eh_name');
                        $eh_time = strtotime(From::valTrim('eh_time'));
                        $eh_img = '';
                        if(isset($_FILES['eh_img']) && isset($_FILES['eh_img']['tmp_name']) && strlen($_FILES['eh_img']['tmp_name']) > 0) {
                            $basicUrl = '/ent/'.md5($currTime.$usId).'.jpg';
                            $newUrl = Load::getUrlRoot().'Static/data'.$basicUrl;
                            $maxSize = 10*1024*1024; //10M
                            $upRes = File::upFile($_FILES['eh_img'], $newUrl, ['A' => ['jpg', 'jpeg', 'png', 'gif', 'bmp']], $maxSize);
                            if($upRes != 'Y'){
                                Tips::show('图片修改失败，请修改正确后提交！', 'javascript: history.back();');
                            }
                            $eh_img = $basicUrl;
                        }
                        if($id > 0){
                            $sql = 'UPDATE '.$Db -> getTableNameAll('ent_honor').' SET us_id=\''.$usId.'\', eh_name=\''.addslashes($eh_name).'\''.(strlen($eh_img) > 0 ? ', eh_img=\''.addslashes($eh_img).'\'' : '').', eh_time='.$eh_time.', eh_last_time='.$currTime.' WHERE eh_id='.$id.' AND us_id='.$usId.' AND eh_isdel=0';
                        }else{
                            $sql = 'INSERT INTO '.$Db -> getTableNameAll('ent_honor').' SET us_id=\''.$usId.'\', eh_name=\''.addslashes($eh_name).'\', eh_img=\''.addslashes($eh_img).'\', eh_time='.$eh_time.', eh_isdel=0, eh_first_time='.$currTime.', eh_last_time='.$currTime;
                        }
                        $Db->getDataNum($sql);
                        (new UserData()) -> addSetGgt('ENT_HONOR_REL', '发布商铺资质荣誉获得');
                        Tips::show('成功!', Link::getLink('entshop').'?A=entshop-set&type=honor');
                    }else{
                        if($id > 0){
                            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_honor').' WHERE eh_id=\''.$id.'\' AND us_id=\''.$usId.'\' AND eh_isdel=0';
                            $info = $Db->getDataOne($sql);
                            isset($info['eh_img']) && $info['eh_img'] = $urlRes.ltrim($info['eh_img'], '/');
                            $Tpl->assign('info', $info);
                        }
                    }
                    $Tpl->show('User/entshop_set_honor_add.html');
                }elseif($op == 'del'){  //删除
                    $sql = 'UPDATE '.$Db -> getTableNameAll('ent_honor').' SET eh_isdel=1, eh_last_time='.$currTime.' WHERE eh_id='.$id.' AND us_id='.$usId.' AND eh_isdel=0';
                    $Db->getDataNum($sql);
                    Tips::show('成功!', Link::getLink('entshop').'?A=entshop-set&type=honor');
                }
                $Page = Page::tag('ent', 'PLST');
                $Page -> setParam('size', 5);
                $Page -> setParam('currPage', max(From::valInt('pg'), 1));
                $limit = $Page -> getLimit();
                $whereString = 'us_id='.$usId.' AND eh_isdel=0';
                $Page -> setQuery('type', $type);
                $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('ent_honor').' WHERE '.$whereString.' ORDER BY eh_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
                $dataArray = $Db -> getData($sql);
                $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
                $Page -> setParam('totalNum', $totalNum);
                $dataList = [];
                if(isset($dataArray[0]) && is_array($dataArray[0])){
                    foreach($dataArray as $key => $val){
                        $val['eh_img'] = $urlRes.ltrim($val['eh_img'], '/');
                        $dataList[] = $val;
                    }
                }
                $pageList = $Page -> getPage(Url::getUrlAction(From::valTrim('A')));
                $Tpl -> assign('pageList', $pageList);
                $Tpl -> assign('dataList', $dataList);
                $Tpl -> assign('isstart', count($dataList) ? 1 : 0);
                $Tpl->show('User/entshop_set_honor.html');
                break;
            }
            case 'case':{
                $op = From::valTrim('op');
                $currTime = Time::getTimeStamp();
                $Db = Db::tag('DB.USER', 'GMY');
                $urlRes = Conf::get('URL.RES'); //资源地址
                if($op == 'add'){   //新增发布OR修改
                    if($save == 1){
                        $ec_name = From::valTrim('ec_name');
                        $ec_company = From::valTrim('ec_company');
                        $ec_time = strtotime(From::valTrim('ec_time'));
                        $ec_desc = From::valTrim('ec_desc');
                        $ec_img = '';
                        if(isset($_FILES['ec_img']) && isset($_FILES['ec_img']['tmp_name']) && strlen($_FILES['ec_img']['tmp_name']) > 0) {
                            $basicUrl = '/ent/'.md5($currTime.$usId).'.jpg';
                            $newUrl = Load::getUrlRoot().'Static/data'.$basicUrl;
                            $maxSize = 10*1024*1024; //10M
                            $upRes = File::upFile($_FILES['ec_img'], $newUrl, ['A' => ['jpg', 'jpeg', 'png', 'gif', 'bmp']], $maxSize);
                            if($upRes != 'Y'){
                                Tips::show('图片修改失败，请修改正确后提交！', 'javascript: history.back();');
                            }
                            $ec_img = $basicUrl;
                        }
                        if($id > 0){
                            $sql = 'UPDATE '.$Db -> getTableNameAll('ent_case').' SET us_id=\''.$usId.'\', ec_name=\''.addslashes($ec_name).'\', ec_company=\''.addslashes($ec_company).'\', ec_desc=\''.addslashes($ec_desc).'\''.(strlen($ec_img) > 0 ? ', ec_img=\''.addslashes($ec_img).'\'' : '').', ec_time='.$ec_time.', ec_last_time='.$currTime.' WHERE ec_id='.$id.' AND us_id='.$usId.' AND ec_isdel=0';
                        }else{
                            $sql = 'INSERT INTO '.$Db -> getTableNameAll('ent_case').' SET us_id=\''.$usId.'\', ec_name=\''.addslashes($ec_name).'\', ec_company=\''.addslashes($ec_company).'\', ec_desc=\''.addslashes($ec_desc).'\', ec_img=\''.addslashes($ec_img).'\', ec_time='.$ec_time.', ec_isdel=0, ec_first_time='.$currTime.', ec_last_time='.$currTime;
                        }
                        $Db->getDataNum($sql);
                        (new UserData()) -> addSetGgt('ENT_CASE_REL', '发布商铺成功案例获得');
                        Tips::show('成功!', Link::getLink('entshop').'?A=entshop-set&type=case');
                    }else{

                        if($id > 0){
                            $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_case').' WHERE ec_id=\''.$id.'\' AND us_id=\''.$usId.'\' AND ec_isdel=0';
                            $info = $Db->getDataOne($sql);
                            isset($info['ec_img']) && $info['ec_img'] = $urlRes.ltrim($info['ec_img'], '/');
                            isset($info['ec_desc']) && $info['ec_desc'] = preg_replace("/\<br[ \/]*\>/", "", stripslashes($info['ec_desc']));
                            $Tpl->assign('info', $info);
                        }
                    }
                    $Tpl->show('User/entshop_set_case_add.html');
                }elseif($op == 'del'){  //删除
                    $sql = 'UPDATE '.$Db -> getTableNameAll('ent_case').' SET ec_isdel=1, ec_last_time='.$currTime.' WHERE ec_id='.$id.' AND us_id='.$usId.' AND ec_isdel=0';
                    $Db->getDataNum($sql);
                    Tips::show('成功!', Link::getLink('entshop').'?A=entshop-set&type=case');
                }
                $Page = Page::tag('ent', 'PLST');
                $Page -> setParam('size', 5);
                $Page -> setParam('currPage', max(From::valInt('pg'), 1));
                $limit = $Page -> getLimit();
                $whereString = 'us_id='.$usId.' AND ec_isdel=0';
                $Page -> setQuery('type', $type);
                $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('ent_case').' WHERE '.$whereString.' ORDER BY ec_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
                $dataArray = $Db -> getData($sql);
                $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
                $Page -> setParam('totalNum', $totalNum);
                $dataList = [];
                if(isset($dataArray[0]) && is_array($dataArray[0])){
                    foreach($dataArray as $key => $val){
                        $val['ec_img'] = $urlRes.ltrim($val['ec_img'], '/');
                        $dataList[] = $val;
                    }
                }
                $pageList = $Page -> getPage(Url::getUrlAction(From::valTrim('A')));
                $Tpl -> assign('pageList', $pageList);
                $Tpl -> assign('dataList', $dataList);
                $Tpl -> assign('isstart', count($dataList) ? 1 : 0);
                $Tpl->show('User/entshop_set_case.html');
                break;
            }
            case 'seo':{
                $es_title = From::valTrim('es_title');
                $es_key = From::valTrim('es_key');
                $es_desc = From::valTrim('es_desc');
                $currTime = Time::getTimeStamp();
                $Db = Db::tag('DB.USER', 'GMY');
                if($save == 1){
                    if($id > 0){
                        $sql = 'UPDATE '.$Db -> getTableNameAll('ent_seo').' SET us_id=\''.$usId.'\', es_title=\''.addslashes($es_title).'\', es_key=\''.addslashes($es_key).'\', es_desc=\''.addslashes($es_desc).'\', es_last_time='.$currTime.' WHERE es_id='.$id.' AND us_id='.$usId.' AND es_isdel=0';
                    }else{
                        $sql = 'INSERT INTO '.$Db -> getTableNameAll('ent_seo').' SET us_id=\''.$usId.'\', es_title=\''.addslashes($es_title).'\', es_key=\''.addslashes($es_key).'\', es_desc=\''.addslashes($es_desc).'\', es_isdel=0, es_first_time='.$currTime.', es_last_time='.$currTime;
                    }
                    $Db->getDataNum($sql);
                    (new UserData()) -> addSetGgt('ENT_SEO_REL', '发布商铺SEO设置获得');
                }
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_seo').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
                $info = $Db->getDataOne($sql);
                isset($info['es_desc']) && $info['es_desc'] = preg_replace("/\<br[ \/]*\>/", "", stripslashes($info['es_desc']));
                $Tpl->assign('info', $info);
                $Tpl->show('User/entshop_set_seo.html');
                break;
            }
            default:{   //company
                $ecDesc = From::post('ec_desc');
                $currTime = Time::getTimeStamp();
                $Db = Db::tag('DB.USER', 'GMY');
                if($save == 1){
                    if($id > 0){
                        $sql = 'UPDATE '.$Db -> getTableNameAll('ent_company').' SET us_id=\''.$usId.'\', ec_desc=\''.addslashes($ecDesc).'\', ec_last_time='.$currTime.' WHERE ec_id='.$id.' AND us_id='.$usId.' AND ec_isdel=0';
                    }else{
                        $sql = 'INSERT INTO '.$Db -> getTableNameAll('ent_company').' SET us_id=\''.$usId.'\', ec_desc=\''.addslashes($ecDesc).'\', ec_isdel=0, ec_first_time='.$currTime.', ec_last_time='.$currTime;
                    }
                    $Db->getDataNum($sql);
                }
                $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_company').' WHERE us_id=\''.$usId.'\' AND ec_isdel=0 ORDER BY ec_last_time DESC';
                $info = $Db->getDataOne($sql);
                $Tpl->assign('info', $info);
                $Tpl->show('User/entshop_set_company.html');
            }
        }
    }

    /**
     * @name msgleave
     * @desciption 留言管理
     */
    public function msgleave(string $action){
        $Tpl = $this->getTpl();
        $op = From::valTrim('op');
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $id = From::valTrim('id');
        if($op == 'hf'){  //回复
            $Tpl->show('User/entshop_msgleave_huifu.html');
        }else if($op == 'del'){  //删除
            $sql = 'UPDATE '.$Db -> getTableNameAll('ent_msg').' SET em_isdel=1, em_last_time='.$currTime.' WHERE us_id='.$usId.' AND em_id='.$id.' AND em_isdel=0';
            $Db->getDataNum($sql);
            Tips::show('成功!', Link::getLink('entshop').'?A=entshop-msgleave');
        }
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND em_isdel=0';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('ent_msg').' WHERE '.$whereString.' ORDER BY em_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
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
        $Tpl->show('User/entshop_msgleave.html');
    }

    /**
     * @name reviews
     * @desciption 点评管理
     */
    public function reviews(string $action){
        $Tpl = $this->getTpl();
        $op = From::valTrim('op');
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $id = From::valTrim('id');
        if($op == 'ss'){  //申诉
            $Tpl->show('User/entshop_reviews_shensu.html');
        }else if($op == 'del'){  //删除
            $sql = 'UPDATE '.$Db -> getTableNameAll('ent_review').' SET er_isdel=1, er_last_time='.$currTime.' WHERE us_id='.$usId.' AND er_id='.$id.' AND er_isdel=0';
            $Db->getDataNum($sql);
            Tips::show('成功!', Link::getLink('entshop').'?A=entshop-reviews');
        }
        $Page = Page::tag('ent', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'us_id='.$usId.' AND er_isdel=0';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('ent_review').' WHERE '.$whereString.' ORDER BY er_last_time DESC LIMIT '.$limit[0].', '.$limit[1];
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
        $Tpl->show('User/entshop_reviews.html');
    }

    /**
     * @name gotomain
     * @desciption 进入商铺
     */
    public function gotomain(string $action){
        $Db = Db::tag('DB.USER', 'GMY');
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        //开通商铺信息
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $esInfo = $Db->getDataOne($sql);
        $isEs = intval($esInfo['es_id'] ?? 0) > 0 ? 1 : 0;
        if($isEs == 0) Tips::show('商铺还未开通，现在去开通!', Link::getLink('user').'?A=user-entauthmain');
        $es_status = intval($esInfo['es_status']) == 1 ? 1 : 0; //是否已禁止[1-是,0-否]禁止将无法访问
        if($es_status == 1) Tips::show('商铺信息已停用，请联系我们！', 'javascript: history.back();');
        $domain = trim($esInfo['es_domain']);
        $domain = preg_replace("/[^a-z\d\-]+/i", '', $domain);  //只允许字母数字和中线
        if(strlen($domain) < 1) Tips::show('商铺信息已停用，请联系我们！', 'javascript: history.back();');
        if(Conf::get('Ent.isDomain') == 1){ //域名模式
            $pubDomain = Conf::get('Ent.domain');
            if(strlen($pubDomain) < 1) Tips::show('商铺信息已停用，请联系我们！', 'javascript: history.back();');
            $isHttps = Conf::get('Ent.isHttps');
            $url = ($isHttps ? 'https://' : 'http://').$domain.'.'.ltrim(trim($pubDomain), '.');
        }else{
            $url = Http::getHttpDomain(TRUE).'ent/index.php?domain='.urlencode($domain);
        }
        header('Location: '.$url);
        exit;
    }

    /**
     * @name moban
     * @desciption 模板选择
     */
    public function moban(string $action){
        $Tpl = $this->getTpl();
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $userInfo = $_SESSION['TOKEN']['INFO'];
        $usId = $userInfo['id'];
        //开通商铺
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $esInfo = $Db->getDataOne($sql);
        $isEs = intval($esInfo['es_id'] ?? 0) > 0 ? 1 : 0;
        if($isEs == 0) Tips::show('商铺还未开通，现在去开通!', Link::getLink('user').'?A=user-entauthmain');
        $urlRes = Conf::get('URL.RES'); //资源地址
        $save = From::valInt('save');
        $id = From::valInt('id');
        //全部模板
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_moban').' WHERE em_isdel=0 ORDER BY em_last_time DESC';
        $mobanList = $Db->getData($sql);
        $Tpl->assign('mobanList', $mobanList);
        if($save == 1){
            $isFound = FALSE;
            if(count($mobanList) > 0) foreach ($mobanList as $val){
                if($val['em_id'] == $id){
                    $isFound = TRUE;
                    break;
                }
            }
            if(!$isFound) Tips::show('模板不正确，请正确提交！', 'javascript: history.back();');
            $sql = 'UPDATE '.$Db -> getTableNameAll('ent_setting').' SET em_id=\''.$id.'\', ec_last_time='.$currTime.' WHERE us_id='.$usId.' AND es_isdel=0';
            $Db->getDataNum($sql);
        }
        //我的模板设置
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $info = $Db->getDataOne($sql);
        $myEmId = intval($info['em_id'] ?? 0);
        if($myEmId < 1){
            if(count($mobanList) > 0) foreach ($mobanList as $val){
                if($val['em_isdefault'] == '1'){
                    $myEmId = intval($val['em_id']);
                    break;
                }
            }
        }
        $Tpl->assign('urlRes', $urlRes);    //我的模板ID
        $Tpl->assign('myEmId', $myEmId);    //我的模板ID
        $Tpl->assign('info', $info);        //设置信息
        $Tpl->show('User/entshop_moban.html');
    }
}