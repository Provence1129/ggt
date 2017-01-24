<?php
/**
 * @Copyright (C) 2016.
 * @Description Expert
 * @FileName Expert.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\User;

use \Libs\Frame\Action;
use \Libs\Comm\From;
use \App\Pub\Tips;
use \Libs\Frame\Url;
use \Libs\Comm\File;
use \Libs\Load;
use \Libs\Tag\Page;
use \App\Pub\Link;

class Expert extends Action
{
    public $tpl;
    public $expertData;
    private $Db         = NULL;         //数据库对象
    const SUCCESS       = "success";    // 成功
    const FAIL          = "fail";       // 失败
    const ISDEL         = 0;            // 删除

    //配置
    public function conf()
    {
        $this->tpl = $this->getTpl();
        $page = [];
        $page['Title'] = '港港通国际多式联运门户网';
        $page['Keywords'] = '行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布';
        $page['Description'] = '国内首家专业性多式联运行业门户网站，集行业前沿资讯，行业解读，在线国际贸易，运力发布与竞价，货盘发布等功能和内容';
        $this->tpl->assign('page', $page);

        $this->expertData = new ExpertData();
    }

    /**
     * @name main
     * @desciption 我是专家
     */
    public function main(string $action)
    {
        $this->tpl->show('User/expert_main.html');
    }

    /**
     * @name favorite
     * @desciption 我的收藏
     */
    public function favorite(string $action)
    {
        $this->tpl->show('User/expert_favorite.html');
    }

    /**
     * @name release
     * @desciption 案例发布
     */
    public function release(string $action)
    {
        $type = $this->expertData->getTypeList();
        $this->tpl->assign('type', $type);

        $id     = intval(From::valGet('id'));

        // 获取文章信息
        if($id){
            $articleInfo    = $this->expertData->getInfo($id);
            if(empty($articleInfo)) Tips::show('获取专家信息失败', Link::getLink('expert').'?A=expert-manage');
            $this->tpl->assign('info', $articleInfo);
        }

        $this->tpl->show('User/expert_release.html');
    }


    /**
     * @name save
     * @desciption 保存文章信息
     */
    public function save()
    {
        $id                 = intval(From::valTrim('saveid'));
        $au_id              = $_SESSION['TOKEN']['INFO']['id'];
        $zt_id              = From::post('zt_id');
        $zj_img_url         = From::valTrim('thumb_img');
        $zj_file_url        = From::valTrim('thumb_file_url');
        $zj_file_name       = From::valTrim('thumb_file_name');
        $zj_title           = From::valTrim('title');
        $zj_keywords        = From::valTrim('keywords');
        $zj_description     = From::valTrim('description');
        $zj_content         = From::post('content');
        $zj_order           = intval(From::valTrim('order'));
        $zj_hits            = intval(From::valTrim('hits'));
        $zj_heart           = intval(From::valTrim('heart'));
        $zj_source          = From::valTrim('source');
        $zj_address         = From::valTrim('address');
        $zj_first_time      = From::valTrim('first_time');

        if(empty($zt_id)) Tips::show('保存失败', Link::getLink('expert').'?A=expert-release');
        if(empty($zj_title) || empty($zj_content)) Tips::show('保存失败', Link::getLink('expert').'?A=expert-release');

        // 保存数据到数据库
        $result = $this->expertData->saveData([
            'id'                => $id,
            'au_id'             => $au_id,
            'zt_id'             => $zt_id,
            'zj_img_url'        => $zj_img_url,
            'zj_file_url'       => $zj_file_url,
            'zj_file_name'      => $zj_file_name,
            'zj_title'          => $zj_title,
            'zj_keywords'       => $zj_keywords,
            'zj_description'    => $zj_description,
            'zj_content'        => $zj_content,
            'zj_order'          => $zj_order,
            'zj_hits'           => $zj_hits,
            'zj_heart'          => $zj_heart,
            'zj_source'         => $zj_source,
            'zj_address'        => $zj_address,
            'zj_first_time'     => $zj_first_time
        ]);

        if(static::SUCCESS == $result){
            if($id >= 1){
                Tips::show('保存成功', Link::getLink('expert').'?A=expert-manage');
            }else{
                (new UserData()) -> addSetGgt('ZHUANJIA_REL', '发布专家文章获得');
                Tips::show('保存成功', Link::getLink('expert').'?A=expert-release');
            }
        }
        Tips::show('保存失败', Link::getLink('expert').'?A=expert-release');

    }


    /**
     * @name manage
     * @desciption 案例管理
     */
    public function manage(string $action)
    {
        $ac_id = From::val('ac_id');
        $title = From::valTrim('title');

        // 条件处理
        $whereArray = [];
        if (!empty($ac_id)) $whereArray['ac_id'] = $ac_id;
        if (!empty($title)) $whereArray['ar_title'] = $title;

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page->setParam('currPage', max(From::valInt('pg'), 1));
        if (!empty($title)) $Page->setQuery('title', $title);

        // 查询数据
        $articleList = $this->expertData->getDataList($whereArray, $Page);
        $this->tpl->assign('list', $articleList);

        $pageList = $Page->getPage(Url::getUrlAction('article_main'));
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('ac_id', $ac_id);
        $this->tpl->assign('title', $title);
        $this->tpl->show('User/expert_manage.html');
    }


    /**
     * 删除
     * @param string $action
     */
    public function del(string $action)
    {
        $id  = intval(From::valTrim('id'));
        if($id){
            $result = $this->expertData->delData($id);
            if(static::SUCCESS == $result){
                Tips::show('删除成功', Link::getLink('expert').'?A=expert-manage');
            }
        }
        Tips::show('删除失败', Link::getLink('expert').'?A=expert-manage');
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
            $photoUrl   = 'Static/data/Zhuanjia/'.$photoName;
            File::writeString($newUrl.$photoUrl, File::getContent($localUrl));

            // 返回JSON
            $json = [
                'name'      => !empty($_FILES['fileData']['name']) ? $_FILES['fileData']['name'] : '',
                'url'       => $photoUrl,
                'status'    => static::SUCCESS
            ];

            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }


}