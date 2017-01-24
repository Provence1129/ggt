<?php
/**
 * @Copyright (C) 2016.
 * @Description UserDatas
 * @FileName UserDatas.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use \Libs\Comm\Time;
use \Libs\Tag\Db;
class UserDatas{
    private $defaultScore = 7;  //默认评分

    //添加访问记录
    public function addVisitor(int $usId, int $usIdTo):bool{
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'INSERT INTO '.$Db->getTableNameAll('user_visitors').' SET us_id='.$usId.', uv_us_id='.$usIdTo.', uv_frist_time='.$currTime;
        return $Db->getDataNum($sql) > 0 ? TRUE : FALSE;
    }

    //获取访问记录
    public function getVisitorList(int $usId, int $num):array{
        $num = max($num, 1);
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db->getTableNameAll('user_visitors').' WHERE uv_us_id='.$usId.' ORDER BY uv_frist_time DESC LIMIT '.$num;
        return $Db->getData($sql);
    }

    //添加关注
    public function addAttention(int $usId, int $usIdTo):bool{
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'INSERT INTO '.$Db->getTableNameAll('user_attention').' SET us_id='.$usId.', ua_us_id='.$usIdTo.', ua_isdel=0, uv_frist_time='.$currTime.', ua_end_time='.$currTime;
        return $Db->getDataNum($sql) > 0 ? TRUE : FALSE;
    }

    //获取关注数
    public function getAttentionNum(int $usId):int{
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT COUNT(*) AS num FROM '.$Db->getTableNameAll('user_attention').' WHERE ua_us_id='.$usId;
        return $Db->getDataInt($sql, 'num');
    }

    //获取评分数
    public function getReviewNum(int $usId):array{
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT SUM(er_avg_scope)/COUNT(er_id) AS avgnum, SUM(er_taidu_scope)/COUNT(er_id) AS taidu, SUM(er_zhiliang_scope)/COUNT(er_id) AS zhiliang, SUM(er_chengxin_scope)/COUNT(er_id) AS chengxin FROM '.$Db->getTableNameAll('ent_review').' WHERE us_id='.$usId.' AND er_isdel=0';
        $dataNum = $Db->getDataOne($sql);
        $defaultScore = $this -> defaultScore;
        $dataArray = ['avgnum' => 0, 'taidu' => 0, 'zhiliang' => 0, 'chengxin' => 0];
        $dataArray['avgnum'] = intval($dataNum['avgnum']);      //综合得分
        $dataArray['avgnum'] = min(($dataArray['avgnum'] < 1 ? $defaultScore : $dataArray['avgnum']), 10);
        $dataArray['avgstartnum'] = intval(ceil($dataArray['avgnum']/2));      //星星数最多五个
        $dataArray['taidu'] = intval($dataNum['taidu']);        //服务态度得分
        $dataArray['taidu'] = min(($dataArray['taidu'] < 1 ? $defaultScore : $dataArray['taidu']), 10);
        $dataArray['zhiliang'] = intval($dataNum['zhiliang']);  //服务质量得分
        $dataArray['zhiliang'] = min(($dataArray['zhiliang'] < 1 ? $defaultScore : $dataArray['zhiliang']), 10);
        $dataArray['chengxin'] = intval($dataNum['chengxin']);  //诚信度得分
        $dataArray['chengxin'] = min(($dataArray['chengxin'] < 1 ? $defaultScore : $dataArray['chengxin']), 10);
        return $dataArray;
    }

    //添加商铺点评
    public function addReview(int $usId, int $usIdTo, int $type, string $desc, int $avgScore, int $taiduScore, int $zhiliangScore, int $chengxinScore):bool{
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'INSERT INTO '.$Db->getTableNameAll('ent_review').' SET us_id='.$usIdTo.', er_us_id='.$usId.', er_type='.$type.', er_desc="'.addslashes($desc).'", er_avg_scope='.$avgScore.', er_taidu_scope='.$taiduScore.', er_zhiliang_scope='.$zhiliangScore.', er_chengxin_scope='.$chengxinScore.', er_time='.$currTime.', er_isdel=0, er_first_time='.$currTime.', er_last_time='.$currTime;
        return $Db->getDataNum($sql) > 0 ? TRUE : FALSE;
    }

    //添加商铺留言
    public function addEntMsg(int $usId, int $usIdTo, string $title, string $text, int $emId):bool{
        $currTime = Time::getTimeStamp();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'INSERT INTO '.$Db->getTableNameAll('ent_msg').' SET us_id='.$usIdTo.', em_us_id='.$usId.', em_title="'.addslashes($title).'", em_text="'.addslashes($text).'", em_pid='.$emId.', em_isread=0, em_isdel=0, em_first_time='.$currTime.', em_last_time='.$currTime;
        return $Db->getDataNum($sql) > 0 ? TRUE : FALSE;
    }
}