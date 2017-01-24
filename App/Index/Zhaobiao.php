<?php
/**
 * @Copyright (C) 2016.
 * @Description Zhaobiao
 * @FileName Zhaobiao.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use \App\Article\ArticleModel;
use \App\Pub\Link;
use \App\Pub\Tips;
use \Libs\Comm\From;
use \Libs\Frame\Action;
use \Libs\Load;
use \Libs\Tag\Db;
use \Libs\Tag\Page;

class Zhaobiao extends Action{
    //配置
    public function conf(){
        $Tpl = $this -> getTpl();
        $Tpl->assign('modelName', '项目竞标');
    }

    //Main
    public function main(string $action){
        $Tpl = $this -> getTpl();
        $Db = Db::tag('DB.USER', 'GMY');
        $Page = Page::tag('ent', 'PLST');
        //$Page -> setParam('size', 12);
        $Page -> setParam('currPage', max(From::valInt('pg'), 1));
        $limit = $Page -> getLimit();
        $whereString = 'td_isdel=0 and published = 1';
        //$Page -> setQuery('type', $type);
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$Db -> getTableNameAll('tender').' WHERE '.$whereString.' ORDER BY td_id DESC LIMIT '.$limit[0].', '.$limit[1];
        $dataArray = $Db -> getData($sql);
        $totalNum = $Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $dataList = [];
        if(isset($dataArray[0]) && is_array($dataArray[0])){
            foreach ($dataArray as $key => $val){
                $dataList[] = $val;
            }
        }
        $pageList = $Page -> getPage('/zhaobiao/');
        $Tpl -> assign('pageList', $pageList);
        $Tpl -> assign('dataList', $dataList);
        //国内资讯
        $ArticleModel = new ArticleModel();
        $articleList = $ArticleModel -> getArticleList(16, 5);    //获取国内咨询下的10条信息
        $Tpl -> assign('articleList', $articleList);
        $Tpl -> show('Zhaobiao/index.html');
    }

    //view
    public function view(string $action){
        $Tpl = $this -> getTpl();
        $id = From::valTrim('td_id');
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('tender').' WHERE td_id='.$id.' AND td_isdel=0';
        $info = $Db->getDataOne($sql);
        if(!isset($info['td_file'])) Tips::show('招标信息已过期！', 'javascript: history.back();');
        $Db->getDataNum('UPDATE '.$Db -> getTableNameAll('tender').' SET td_view_num=td_view_num+1 WHERE td_id='.$id.' AND td_isdel=0');    //增加阅读次数
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$info['us_id'].'\' AND ent_isdel=0';
        $entInfo = $Db->getDataOne($sql);
        if(count($entInfo) > 0) $info = array_merge($info, $entInfo);
        $info['url'] = '';
        if(strlen($info['td_file']) > 0){
            $url = '/zhaobiao/view.php?A=zhaobiao-download&td_id='.$id;
            $info['url'] = $url;
        }
        $Tpl -> assign('info', $info);
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('tender').' WHERE td_id<'.$id.' AND td_isdel=0 ORDER BY td_id DESC';
        $preInfo = $Db->getDataOne($sql);
        if(!isset($preInfo['td_file'])) $preInfo = [];
        $Tpl -> assign('preInfo', $preInfo);
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('tender').' WHERE td_id>'.$id.' AND td_isdel=0 ORDER BY td_id ASC';
        $nextInfo = $Db->getDataOne($sql);
        if(!isset($nextInfo['td_file'])) $nextInfo = [];
        $Tpl -> assign('nextInfo', $nextInfo);
        //国内资讯
        $ArticleModel = new ArticleModel();
        $articleList = $ArticleModel -> getArticleList(16, 5);    //获取国内咨询下的10条信息
        $Tpl -> assign('articleList', $articleList);
        $Tpl -> show('Zhaobiao/view.html');
    }

    //download
    public function download(string $action){
        $id = From::valTrim('td_id');
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('tender').' WHERE td_id='.$id.' AND td_isdel=0';
        $info = $Db->getDataOne($sql);
        if(!isset($info['td_file'])) Tips::show('招标信息已过期！', 'javascript: history.back();');
        if(strlen($info['td_file']) > 0){
            //处理中文文件名
            $file = Load::getUrlRoot().trim($info['td_file']);
            if(!file_exists($file)){
                Tips::show('招标不存在，请查其他招标！', '/zhaobiao/');
            }
            $Db->getDataNum('UPDATE '.$Db -> getTableNameAll('tender').' SET td_down_num=td_down_num+1 WHERE td_id='.$id.' AND td_isdel=0');    //增加下载次数
            $fileSize = filesize($file);
            $filename = $info['td_title'].substr($file, strrpos($file, '.'));
            header("Content-type: application/octet-stream");
            $userAgent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : 'Chrome';
            $encoded_filename = rawurlencode($filename);
            if(preg_match("/MSIE/", $userAgent)){
                header('Content-Disposition: attachment; filename="'.$encoded_filename.'"');
            } else if (preg_match("/Firefox/", $userAgent)){
                header("Content-Disposition: attachment; filename*=\"utf8''".$filename.'"');
            }else{
                header('Content-Disposition: attachment; filename="'.$filename.'"');
            }
            header("Content-Length: ".$fileSize);
            readfile($file);
            exit(0);
        }else{
            Tips::show('招标不存在，请查其他招标！', '/zhaobiao/');
        }
    }
}