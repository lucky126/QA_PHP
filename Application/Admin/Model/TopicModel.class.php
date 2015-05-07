<?php
namespace Admin\Model;
use Think\Model;
class TopicModel extends Model {


    /**
     * 计算统计图
     */
    public function getChart()
    {
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $result = $Model->query("SELECT date_format(PostTime, '%Y-%m-%d') AS PostDate ,
                        SUM(CASE WHEN IsTopic = 1 THEN 1 ELSE 0 END) AS PostCnt,
                        SUM(CASE WHEN IsTopic = 0 THEN 1 ELSE 0 END)  AS ReplyCnt
                        FROM bbs_topicinfo
                        WHERE BBSStatus=0
                        GROUP BY date_format(PostTime, '%Y-%m-%d')
                        ORDER BY date_format(PostTime, '%Y-%m-%d')");
        return $result;
    }
}