<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends BaseController
{

    /**
     * 首页统计数据
     */
    public function index()
    {
        $userMap['UserLevel']=array('elt',1);
        $userMap['UserStatus']=array('eq',0);

        /*得到系统信息*/
        if (function_exists('gd_info')) {
            $gd = gd_info();
            $gd = $gd['GD Version'];
        } else {
            $gd = "不支持";
        }

        $info = array(
            '操作系统' => PHP_OS,
            '主机名IP端口' => $_SERVER['SERVER_NAME'] . ' (' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'] . ')',
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            '程序目录' => WEB_ROOT,
            'MYSQL版本' => function_exists("mysql_close") ? mysql_get_client_info() : '不支持',
            'GD库版本' => $gd,
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time') . "秒",
            '剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
            '服务器时间' => date("Y年n月j日 H:i:s"),
            '北京时间' => gmdate("Y年n月j日 H:i:s", time() + 8 * 3600),
            '采集函数检测' => ini_get('allow_url_fopen') ? '支持' : '不支持',
            'register_globals' => get_cfg_var("register_globals") == "1" ? "ON" : "OFF",
            'magic_quotes_gpc' => (1 === get_magic_quotes_gpc()) ? 'YES' : 'NO',
            'magic_quotes_runtime' => (1 === get_magic_quotes_runtime()) ? 'YES' : 'NO',
        );

        /*计算发帖统计图*/
        $chartModel = D('Topic')->getChart();
        $chartValue = '';
        foreach($chartModel as $k => $v)
        {
            $chartValue .= '{date:\''.$v['postdate'].'\',post:'.$v['postcnt'].',reply:'.$v['replycnt'].'},';
        }

        /*视图变量赋值*/
        $this->assign('chartValue',$chartValue);
        $this->assign('server_info', $info);
        $this->assign('TotalBoard',D('Board')->count());
        $this->assign('TotalPost',D('Topicinfo')->Where('BBSStatus = 0')->count());
        $this->assign('TotalUser',D('User')->Where($userMap)->count());
        $this->assign('title','首页');
        $this->display('');
    }

}