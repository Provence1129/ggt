<?php
/**
 * @Copyright (C) 2016.
 * @Description MyZhidao
 * @FileName MyZhidao.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\User;

use \App\Pub\Common;
use App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use Libs\Comm\Time;
use Libs\Comm\Valid;
use \Libs\Frame\Action;
use \Libs\Frame\Url;
use \Libs\Comm\File;
use \Libs\Load;
use \Libs\Tag\Page;
use \App\Auth\MyAuth;
use \Libs\Tag\Db;

class MyZhidao extends Action{

    public $tpl;
    public $zhidaoData;
    public $userid;
    const SUCCESS   = "success";    // 成功
    const FAIL      = "fail";       // 失败
    const ISDEL     = 0;            // 删除

    //配置
    public function conf(){
        $this->tpl = $this -> getTpl();

        $this->zhidaoData = new MyZhidaoData();

        $this->userid   = $_SESSION['TOKEN']['INFO']['id'];
    }

    /**
     * @name main
     * @desciption 知道
     */
    public function main(string $action)
    {
        $this->tpl -> show('User/myzhidao_main.html');
    }

    /**
     * 添加知道
     */
    public function add()
    {
        $this->tpl -> show('User/myzhidao_add.html');
    }

    /**
     * 保存知道信息
     */
    public function save()
    {
        $id                 = intval(From::valTrim('saveid'));
        $au_id              = $_SESSION['TOKEN']['INFO']['id'];
        $zd_title           = From::post('title');
        $zd_thumb_img       = From::post('thumb_img');
        $zd_keywords        = From::valTrim('keywords');
        $zd_description     = From::valTrim('description');

        if(empty($au_id)) Tips::show('保存失败', Link::getLink('myzhidao').'?A=myzhidao-add');
        if(empty($zd_title) || empty($zd_description)) Tips::show('保存失败', Link::getLink('myzhidao').'?A=myzhidao-add');

        // 保存数据到数据库
        $result = $this->zhidaoData->saveData([
            'zd_id'             => $id,
            'us_id'             => $au_id,
            'zd_title'          => $zd_title,
            'zd_thumb_img'      => $zd_thumb_img,
            'zd_keywords'       => $zd_keywords,
            'zd_description'    => $zd_description
        ]);

        if(static::SUCCESS == $result){
            if($id <= 0){
                (new UserData()) -> addSetGgt('ZHIDAO_QUS', '多联知道中的提问获得');
            }
            Tips::show('保存成功', Link::getLink('myzhidao').'?A=myzhidao-add');
        }
        Tips::show('保存失败', Link::getLink('myzhidao').'?A=myzhidao-add');
    }


    /**
     * @name question
     * @desciption 我的问题
     */
    public function question(string $action)
    {
        $title      = From::valTrim('title');
        $au_id      = $_SESSION['TOKEN']['INFO']['id'];

        // 条件处理
        $whereString = "us_id={$au_id}";
        if (!empty($title)) $whereString .= " AND ar_title='{$whereString}'";

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));

        $list = $this->zhidaoData->getListPage($whereString, $Page);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('myzhidao').'?A=myzhidao-question');
//        echo '<pre>';
//        print_r($pageList);
//        exit;
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('list', $list);
        $this->tpl -> show('User/myzhidao_question.html');
    }

    /**
     * 关闭问答
     */
    public function close()
    {
        $id      = From::valInt('id');
        if(empty($id)) Tips::show('条件错误', Link::getLink('myzhidao').'?A=myzhidao-question');

        // 条件处理
        $whereArray = [];
        if (!empty($id)) $whereArray['zd_id'] = $id;
        $result = $this->zhidaoData->upStatus($whereArray, 2);

        // 跳转处理
        if(static::SUCCESS == $result){
            Tips::show('操作成功', Link::getLink('myzhidao').'?A=myzhidao-question');
        }
        Tips::show('操作失败', Link::getLink('myzhidao').'?A=myzhidao-question');
    }

    /**
     * 删除问题
     */
    public function del()
    {
        $id      = From::valInt('id');
        if(empty($id)) Tips::show('条件错误', Link::getLink('myzhidao').'?A=myzhidao-question');

        // 条件处理
        $whereArray = [];
        if (!empty($id)) $whereArray['zd_id'] = $id;
        $result = $this->zhidaoData->del($whereArray);

        // 跳转处理
        if(static::SUCCESS == $result){
            Tips::show('操作成功', Link::getLink('myzhidao').'?A=myzhidao-question');
        }
        Tips::show('操作失败', Link::getLink('myzhidao').'?A=myzhidao-question');
    }

    /**
     * @name answer
     * @desciption 我的回答
     */
    public function answer(string $action)
    {
        $title      = From::valTrim('title');
        $au_id      = $_SESSION['TOKEN']['INFO']['id'];

        // 条件处理
        $whereString = "a.us_id={$au_id}";
        if (!empty($title)) $whereString .= " AND a.ar_title='{$whereString}'";

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));

        $list = $this->zhidaoData->getAnswerList($whereString, $Page);

//        echo '<pre>';
//        print_r($list);
//        exit;

        // 分页
        $pageList = $Page -> getPage(Link::getLink('myzhidao').'?A=myzhidao-answer');
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('list', $list);

        $this->tpl -> show('User/myzhidao_answer.html');
    }

    /**
     * @name answerwaite
     * @desciption 等我答
     */
    public function answerwaite(string $action)
    {
        $title      = From::valTrim('title');
        $au_id      = $_SESSION['TOKEN']['INFO']['id'];

        // 条件处理
        $whereString = "us_id!={$au_id} AND zd_status=0";
        if (!empty($title)) $whereString = "ar_title='{$whereString}'";

        // 分页处理
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));

        $list = $this->zhidaoData->getListPage($whereString, $Page);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('myzhidao').'?A=myzhidao-answerwaite');
//        echo '<pre>';
//        print_r($pageList);
//        exit;
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('list', $list);
        $this->tpl -> show('User/myzhidao_answerwaite.html');
    }


    /**
     * 查看回答
     * @param string $action
     */
    public function showwaitanswer(string $action)
    {
        $id         = From::valInt('id');
        $au_id      = $_SESSION['TOKEN']['INFO']['id'];
        if(empty($id)) Tips::show('条件错误', Link::getLink('myzhidao').'?A=myzhidao-answerwaite');

        // 条件处理
        $whereString = "zd_id={$id}";
        $info = $this->zhidaoData->getInfo($whereString);

        // 获取回答列表
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $Page -> setQuery('id', $id);

        // 条件
        $whereString = "zd_id={$id}";
        $list = $this->zhidaoData->getWaitanswer($whereString, $Page);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('myzhidao').'?A=myzhidao-waitanswer');
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('info', $info);
        $this->tpl->assign('list', $list);
        $this->tpl->show('User/myzhidao_waitanswer.html');
    }

    /**
     * 回答页
     * @param string $action
     */
    public function waitanswer(string $action)
    {
        $id         = From::valInt('id');
        $au_id      = $_SESSION['TOKEN']['INFO']['id'];
        if(empty($id)) Tips::show('条件错误', Link::getLink('myzhidao').'?A=myzhidao-answerwaite');

        // 条件处理
        $whereString = "zd_id={$id}";
        $info = $this->zhidaoData->getInfo($whereString);

        // 获取回答列表
        $Page = Page::tag('Admin', 'PLST');
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $Page -> setQuery('id', $id);

        // 条件
        $whereString = "zd_id={$id}";
        $list = $this->zhidaoData->getWaitanswer($whereString, $Page);

        // 分页
        $pageList = $Page -> getPage(Link::getLink('myzhidao').'?A=myzhidao-waitanswer');
        $this->tpl->assign('pageList', $pageList);

        $this->tpl->assign('info', $info);
        $this->tpl->assign('list', $list);
        $this->tpl->show('User/myzhidao_waitanswer.html');
    }

    /**
     * 保存回答信息
     * @param string $action
     */
    public function waitanswersave(string $action)
    {
        $data['zd_id']         = From::valPost('id');
        $data['us_id']         = $_SESSION['TOKEN']['INFO']['id'];
        $data['za_content']    = From::valPost('content');

        if(empty($data['zd_id'])) Tips::show('条件错误', Link::getLink('myzhidao').'?A=myzhidao-answerwaite');
        if(empty($data['za_content'])) Tips::show('内容不能为空', Link::getLink('myzhidao').'?A=myzhidao-waitanswer&id='.$data['zd_id']);
        $result = $this->zhidaoData->saveWaitanswer($data);
        if($result){
            // 更新回答次数
            $this->zhidaoData->upAnswer(-1, 'zd_id='.$data['zd_id']);
            (new UserData()) -> addSetGgt('ZHIDAO_ANS', '多联知道中的回答获得');
            Tips::show('保存成功', Link::getLink('myzhidao').'?A=myzhidao-waitanswer&id='.$data['zd_id']);
        }
        Tips::show('保存失败', Link::getLink('myzhidao').'?A=myzhidao-answerwaite');
    }












    /**
     * @name upimg
     * @desciption 上传图片
     */
    public function upimg()
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
            $photoUrl = 'Static/data/ZhidaoPhoto/'.md5($_SESSION['TOKEN']['INFO']['id'].microtime(true).rand(1000, 9999)).'.'.$annx;
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
}