<?php
/**
 * @Copyright (C) 2016.
 * @Description MyBaike
 * @FileName MyBaike.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\User;

use \Libs\Frame\Action;
use \App\Pub\Common;
use App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use Libs\Comm\Time;
use Libs\Comm\Valid;
use \Libs\Frame\Url;
use \Libs\Comm\File;
use \Libs\Load;
use \Libs\Tag\Page;
use \App\Auth\MyAuth;
use \Libs\Tag\Db;

class MyBaike extends Action
{
    public $tpl;
    public $baikeData;
    public $userid;
    const SUCCESS   = "success";    // 成功
    const FAIL      = "fail";       // 失败
    const ISDEL     = 0;            // 删除

    //配置
    public function conf(){
        $this->tpl = $this -> getTpl();

        $this->baikeData = new MyBaikeData();

        $this->userid   = $_SESSION['TOKEN']['INFO']['id'];
    }

    /**
     * @name main
     * @desciption 百科
     */
    public function main(string $action)
    {
        $this->tpl->show('User/mybaike_main.html');
    }

    /**
     * @name manage
     * @desciption 词条管理
     */
    public function manage(string $action)
    {
        $title      = From::valTrim('title');
        $au_id      = $_SESSION['TOKEN']['INFO']['id'];

        // 条件处理
        $whereString = "us_id={$au_id}";
        if (!empty($title)) $whereString .= " AND bk_title='{$title}'";

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));

        $list = $this->baikeData->getList($whereString, $Page);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('mybaike').'?A=mybaike-manage');
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('list', $list);
        $this->tpl->show('User/mybaike_manage.html');
    }

    /**
     * @name release
     * @desciption 发布词条
     */
    public function release(string $action)
    {
        $bk_id      = From::valInt('id');
        $au_id      = $_SESSION['TOKEN']['INFO']['id'];

        $category = $this->baikeData->getCategoryList();

        // 条件处理
        $info = $this->baikeData->getInfo($bk_id);
//        echo '<pre>';
//        print_r($info);
//        exit;

        $this->tpl->assign('info', $info);

        $this->tpl->assign('category', $category);
        $this->tpl->show('User/mybaike_release.html');
    }

    /**
     * 保存信息
     * @param string $action
     */
    public function save(string $action)
    {
        $id                 = intval(From::valTrim('saveid'));
        $au_id              = $_SESSION['TOKEN']['INFO']['id'];
        $bc_id              = From::valInt('bc_id');
        $bk_title           = From::post('title');
        $bk_keyword         = From::post('keyword');
        $bk_dataInfo        = From::post('dataInfo');
        $bk_description     = From::valPost('description');
        $bk_tags            = From::post('tags');
        $bk_thumb_img       = From::post('thumb_img');
        $bi_content         = From::valPost('content');

        if(empty($au_id)) Tips::show('保存失败', Link::getLink('mybaike').'?A=mybaike-manage');
        if(empty($bk_title)) Tips::show('保存失败', Link::getLink('mybaike').'?A=mybaike-manage');

        // 保存数据到数据库
        $result = $this->baikeData->saveData([
            'bk_id'             => $id,
            'us_id'             => $au_id,
            'bc_id'             => $bc_id,
            'bk_title'          => $bk_title,
            'bk_tags'           => $bk_tags,
            'bi_keywords'       => $bk_keyword,
            'bi_datas'          => $bk_dataInfo,
            'bi_pics'           => $bk_thumb_img,
            'bk_description'    => $bk_description,
            'bi_content'        => $bi_content
        ]);

        if(static::SUCCESS == $result){
            if($id <= 0){
                (new UserData()) -> addSetGgt('BAIKE_REL', '发布文库获得');
            }
            Tips::show('保存成功', Link::getLink('myzhidao').'?A=mybaike-manage');
        }
        Tips::show('保存失败', Link::getLink('myzhidao').'?A=mybaike-manage');
    }

    /**
     * 删除
     * @param string $action
     */
    public function del(string $action)
    {
        $id      = From::valInt('id');
        if(empty($id)) Tips::show('条件错误', Link::getLink('mybaike').'?A=mybaike-manage');

        // 条件处理
        $whereArray = [];
        if (!empty($id)) $whereArray['bk_id'] = $id;
        $result = $this->baikeData->del($whereArray);

        // 跳转处理
        if(static::SUCCESS == $result){
            Tips::show('操作成功', Link::getLink('mybaike').'?A=mybaike-manage');
        }
        Tips::show('操作失败', Link::getLink('mybaike').'?A=mybaike-manage');
    }

    /**
     * 上传封面图
     * @param string $action
     */
    public function upimg(string $action)
    {
        $allowAnnx = ['rar', 'zip', 'doc', 'jpg', 'png'];  //允许上传类型
        if(isset($_FILES['thumbData']) && isset($_FILES['thumbData']['tmp_name']) && strlen($_FILES['thumbData']['tmp_name']) > 0)
        {
            $localUrl = $_FILES['thumbData']['tmp_name'];
            $annx = '';
            $pos = strrpos($_FILES['thumbData']['name'], '.');
            if($pos > 0) $annx = strtolower(substr($_FILES['thumbData']['name'], $pos+1));
            if(!in_array($annx, $allowAnnx)){
                Tips::show('不允许的文件格式！', 'javascript: history.back();');
            }
            $newUrl = Load::getUrlRoot();
            $photoUrl = 'Static/data/BaikePhoto/'.md5($_SESSION['TOKEN']['INFO']['id'].microtime(true).rand(1000, 9999)).'.'.$annx;
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
     * @name favorite
     * @desciption 我的收藏
     */
    public function favorite(string $action)
    {
        $this->tpl->show('User/mybaike_favorite.html');
    }
}