<?php
/**
 * @Copyright (C) 2016.
 * @Description 资讯模块
 * @FileName Index.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/
declare(strict_types=1);//strict

namespace App\Article;

use \Libs\Frame\Action;
use \Libs\Comm\From;
use \App\Pub\Tips;
use \Libs\Frame\Url;
use \Libs\Comm\File;
use \Libs\Load;
use \Libs\Tag\Page;

use \App\Pub\Common;
use \Libs\Comm\Time;
use \App\Auth\MyAuth;
use \Libs\Comm\Net;
use \Libs\Plugins\Checkcode\Checkcode;

class Article extends Action
{
    public $tpl;
    public $articleData;
    const SUCCESS   = "success";    // 成功
    const FAIL      = "fail";       // 失败
    const ISDEL     = 0;            // 删除

    /**
     * @name __construct
     * @desciption 构造函数
     */
    public function __construct()
    {
        // 获取模板
        $this->tpl = $this -> getTpl();

        // 实例化Model
        $this->articleData = new ArticleData();
    }

    /**
     * @name conf
     * @desciption 配置
     */
    public function conf(){}


    /**
     * @name main
     * @desciption 显示主界面
     **/
    public function main()
    {
        $ac_id      = From::val('ac_id');
        $ac_idStr   = is_array($ac_id) ? implode(',', $ac_id) : '';
        $title      = From::valTrim('title');

        // 条件处理
        $whereArray = [];
        if (!empty($ac_idStr)) $whereArray['ac_id'] = $ac_idStr;
        if (!empty($title)) $whereArray['ar_title'] = $title;

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $Page -> setQuery('title', $title);
        if(is_array($ac_id) && !empty($ac_id)) {
            foreach ($ac_id as $key => $val) $Page->setQuery('ac_id[' . $key . ']', intval($val));
        }

        // 查询数据
        $articleList = $this->articleData->getDataList($whereArray, $Page);
        $this->tpl->assign('articleList', $articleList);

        $pageList = $Page -> getPage(Url::getUrlAction('article_main'));
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('ac_id', $ac_idStr);
        $this->tpl->assign('title', $title);
        $this->tpl->show('Article/articleMain.html');
    }

    /**
     * @name shenhelist
     * @desciption 文章审核记录
     **/
    public function shenhelist()
    {
        $ar_id      = intval(From::valGet('id'));

        // 保存数据到数据库
        if($ar_id){
            $shenheList = $this->articleData->getShenheList($ar_id);
            $this->tpl->assign("shenheList", $shenheList);
        }

        $this->tpl->show('Article/articleShenhelist.html');
    }

    /**
     * @name shenhe
     * @desciption 文章审核
     **/
    public function shenhe()
    {
        $ar_id      = From::val('ar_id');
        $ar_status  = From::valTrim('ar_status');
        $ar_description = From::valTrim('ar_description');

        // 保存数据到数据库
        $result = $this->articleData->saveShenheData([
            'ar_id'             => $ar_id,
            'ar_status'         => $ar_status,
            'ar_description'    => $ar_description,
            'check_au_id'       => $_SESSION['TOKEN']['INFO']['id'],
            'check_au_name'     => $_SESSION['TOKEN']['INFO']['name']
        ]);

        // 返回数据
        if(static::SUCCESS == $result){
            $json = ['status' => static::SUCCESS];
        }else{
            $json = ['status' => static::FAIL];
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * @name edit
     * @desciption 添加/编辑文章
     */
    public function edit()
    {
        $id     = intval(From::valGet('id'));
        $cid    = intval(From::valGet('cid'));

        // 获取文章信息
        if($id){
            $articleInfo    = $this->articleData->getArticleInfo($id);
            if(empty($articleInfo)) Tips::show('获取文章信息失败', Url::getUrlAction('article_main'));

            // 获取关联信息
            $articleInfo['ac_id'] = $this->articleData->getRelation($id);

            // 获取附件信息
            $articleInfo['attachmentList'] = $this->articleData->getAttachment($id);

            $this->tpl->assign('articleInfo', $articleInfo);
        }

        // 获取分类信息
        $typeList = $this->articleData->getCategoryTypeList($cid);
        $this->tpl->assign('typeList', $typeList);

        // 获取来源信息
        $sourceList = $this->articleData->getSourceList();
        $this->tpl->assign('sourceList', $sourceList);
        $this->tpl->assign('cid', $cid);
        $this->tpl -> show('Article/articleEdit.html');
    }

    /**
     * @name save
     * @desciption 保存文章信息
     */
    public function save()
    {
        $id                 = intval(From::valTrim('saveid'));
        $au_id              = $_SESSION['TOKEN']['INFO']['id'];
        $ac_id              = From::post('ac_id');
        $ar_thumb_img       = From::valTrim('thumb_img');
        $ar_title           = From::valTrim('title');
        $ar_keywords        = From::valTrim('keywords');
        $ar_description     = From::valTrim('description');
        $ar_content         = From::post('content');
        $ar_order           = intval(From::valTrim('order'));
        $ar_hits            = intval(From::valTrim('hits'));
        $ar_heart           = intval(From::valTrim('heart'));
        $ar_source          = From::valTrim('source');
        $attachment_id      = From::post('attachment_id');
        $ar_iscommend       = intval(From::valTrim('ar_iscommend'));
        $ar_first_time      = From::valTrim('first_time');

        if(empty($ac_id)) Tips::show('保存失败', Url::getUrlAction('article_main'));
        if(empty($ar_title) || empty($ar_content)) Tips::show('保存失败', Url::getUrlAction('article_main', '?cid='.$ac_id.''));

        // 保存数据到数据库
        $result = $this->articleData->saveData([
            'id'                => $id,
            'au_id'             => $au_id,
            'ac_id'             => $ac_id,
            'ar_thumb_img'      => $ar_thumb_img,
            'ar_title'          => $ar_title,
            'ar_keywords'       => $ar_keywords,
            'ar_description'    => $ar_description,
            'ar_content'        => $ar_content,
            'ar_order'          => $ar_order,
            'ar_hits'           => $ar_hits,
            'ar_heart'          => $ar_heart,
            'ar_source'         => $ar_source,
            'attachment_id'     => $attachment_id,
            'ar_iscommend'      => $ar_iscommend,
            'ar_first_time'    => $ar_first_time
        ]);

        if(static::SUCCESS == $result){
            if($id >= 1){
                Tips::show('保存成功', Url::getUrlAction('article_main', '?cid='.$ac_id.''));
            }else{
                Tips::show('保存成功', Url::getUrlAction('article_main', '?cid='.$ac_id.''));
            }
        }
        Tips::show('保存失败', Url::getUrlAction('article_main', '?cid='.$ac_id.''));

    }

    /**
     * @name del
     * @desciption 删除文章
     */
    public function del()
    {
        $id  = intval(From::valTrim('id'));
        if($id){
            $result = $this->articleData->deleteArticle($id);
            if(static::SUCCESS == $result){
                Tips::show('删除成功', Url::getUrlAction('article_main'));
            }
        }
        Tips::show('删除失败', Url::getUrlAction('article_main'));
    }

    /**
     * @name upimg
     * @desciption 上传图片
     */
    public function upimg()
    {
        $allowAnnx = ['rar', 'zip', 'doc', 'jpg', 'png'];  //允许上传类型
        if(isset($_FILES['thumbData']) && isset($_FILES['thumbData']['tmp_name']) && strlen($_FILES['thumbData']['tmp_name']) > 0){ //头像
            $localUrl = $_FILES['thumbData']['tmp_name'];
            $annx = '';
            $pos = strrpos($_FILES['thumbData']['name'], '.');
            if($pos > 0) $annx = strtolower(substr($_FILES['thumbData']['name'], $pos+1));
            if(!in_array($annx, $allowAnnx)){
                Tips::show('不允许的文件格式！', 'javascript: history.back();');
            }
            $newUrl = Load::getUrlRoot();
            $photoUrl = 'Static/data/ArticlePhoto/'.md5($_SESSION['TOKEN']['INFO']['id'].microtime(true).rand(1000, 9999)).'.'.$annx;
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
     * @name upfile
     * @desciption 上传附件
     */
    public function upfile()
    {
        $allowAnnx = ['rar', 'zip', 'doc','jpg'];  //允许上传类型
        if(isset($_FILES['fileData']) && isset($_FILES['fileData']['tmp_name']) && strlen($_FILES['fileData']['tmp_name']) > 0)
        {
            $localUrl = $_FILES['fileData']['tmp_name'];
            $annx = '';
            $pos = strrpos($_FILES['fileData']['name'], '.');
            if($pos > 0) $annx = strtolower(substr($_FILES['fileData']['name'], $pos+1));
            if(!in_array($annx, $allowAnnx)){
                echo '文件格式错误';
            }
            $newUrl     = Load::getUrlRoot();
            $photoName  = md5($_SESSION['TOKEN']['INFO']['id'].microtime(true).rand(1000, 9999)).'.'.$annx;
            $photoUrl   = 'Static/data/ArticlePhoto/'.$photoName;
            File::writeString($newUrl.$photoUrl, File::getContent($localUrl));
            $photo = $photoUrl;

            // 保存到数据库
            $attachmentID = $this->articleData->saveAttachment([
                'aa_name'       => !empty($_FILES['fileData']['name']) ? $_FILES['fileData']['name'] : $photoName,
                'aa_path'       => $photo
            ]);

            // 返回JSON
            $json = [
                'id'        => $attachmentID,
                'name'      => !empty($_FILES['fileData']['name']) ? $_FILES['fileData']['name'] : '',
                'url'       => $photoUrl,
                'status'    => static::SUCCESS
            ];

            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }


    /**
     * @name delattachment
     * @desciption 删除附件
     */
    public function delattachment()
    {
        $id  = intval(From::valTrim('id'));
        $result = $this->articleData->deleteAttachment($id);

        // 返回JSON
        $json = [
            'id'       => $id,
            'status'    => static::SUCCESS
        ];

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit;
    }

}