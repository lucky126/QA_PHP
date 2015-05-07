<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="zh-CN">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="互动交流系统" />
    <meta name="author" content="" />

    <title>互动交流系统（PHP版）</title>

    <link rel="stylesheet" href="http://fonts.useso.com/css?family=Arimo:400,700,400italic">
    <link rel="stylesheet" href="/BBS/Public/assets/css/fonts/linecons/css/linecons.css">
    <link rel="stylesheet" href="/BBS/Public/assets/css/fonts/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/BBS/Public/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/BBS/Public/assets/css/xenon-core.css">
    <link rel="stylesheet" href="/BBS/Public/assets/css/xenon-forms.css">
    <link rel="stylesheet" href="/BBS/Public/assets/css/xenon-components.css">
    <link rel="stylesheet" href="/BBS/Public/assets/css/xenon-skins.css">
    <link rel="stylesheet" href="/BBS/Public/assets/css/custom.css">

    <script src="/BBS/Public/assets/js/jquery-1.11.1.min.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="page-body">

<div class="page-container">
    <header class="navbar navbar-inverse " role="banner">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">互动交流系统--PHP版</a>
        </div>
        <nav class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

            <ul class="nav navbar-nav navbar-right">
                <?php if(session('?User')): ?><li>
                        <a href="" class="iconText,ui-btn-active"><?php echo session('User')['NickName'];?></a>
                    </li><?php endif; ?>
                <li>
                    <a href="<?php echo U('Index/index');?>" class="iconText,ui-btn-active">首页</a>
                </li>
                <?php if(session('?User')): ?><li>
                        <a href="" class="iconText,ui-btn-active">控制面板</a>
                    </li>
                    <li>
                        <a href="<?php echo U('User/Logout','','');?>" class="iconText,ui-btn-active">注销</a>
                    </li>
                    <?php else: ?>
                    <li>
                        <a href="<?php echo U('User/Register','','');?>" class="iconText,ui-btn-active">注册</a>
                    </li>
                    <li>
                        <a href="<?php echo U('User/Login','','');?>" class="iconText,ui-btn-active">登录</a>
                    </li><?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
    <div class="container">
        <div class="body clearfix">
            <div class="row">&nbsp;</div>
<div class="SearchBlock row">
    <div class="col-xs-12 col-md-11">
        <form action="<?php echo U('Topic/SearchResult','','');?>" method="post">
        <div class="col-xs-10 col-md-10">
            <div class="input-group">
                <input type="text" id="SearchContext" name="SearchContext" class="form-control no-right-border form-focus-purple" placeholder="请输入要查询的内容">
                <input type="hidden" id="BoardId" name="BoardId" value="<?php echo ($bid); ?>">
                <span class="input-group-btn">
                    <input type="submit" value=" 搜 索 " class="btn btn-purple" />
                </span>
            </div>
        </form>
        </div>
        <div class="col-xs-2  col-md-1">
            <a href="<?php echo U('Topic/SearchInfo',array('BoardId' => $bid),'');?>" class="btn btn-blue">高级搜索</a>
        </div>
    </div>
    <?php if(($bid > 0) AND session('?User')): ?><div class="col-xs-2 col-md-1">
            <a href="<?php echo U('Topic/NewTopic',array('boardid' => $bid),'');?>" class="btn btn-info">发帖</a>
        </div><?php endif; ?>
</div>
<div class="row">&nbsp;</div>

<?php if(is_array($parent)): foreach($parent as $key=>$p): ?><div class="col-md-12">
        <div class="panel panel-color panel-info">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo ($p["boardname"]); ?></h3>

                <div class="panel-options">
                    <a href="<?php echo U('Board',array('id' => $p['id']),'');?>">进入</a>
                </div>
            </div>
            <div class="panel-body">
                <?php if(is_array($p["children"])): foreach($p["children"] as $key=>$c): ?><div class="col-md-3">
                        <h5>
                            <a href="<?php echo U('Board',array('id' => $c['id']),'');?>"><?php echo ($c["boardname"]); ?></a>
                        </h5>
                        <div class="text-muted">
                            主题：<?php echo ($c["topicnum"]); ?>，回帖：<?php echo ($c["postnum"]); ?>
                        </div>
                    </div><?php endforeach; endif; ?>
            </div>
        </div>
    </div><?php endforeach; endif; ?>

        </div>
        
    </div>
</div>

<!-- Bottom Scripts -->
<script src="/BBS/Public/assets/js/bootstrap.min.js"></script>
<script src="/BBS/Public/assets/js/TweenMax.min.js"></script>
<script src="/BBS/Public/assets/js/resizeable.js"></script>
<script src="/BBS/Public/assets/js/joinable.js"></script>
<script src="/BBS/Public/assets/js/xenon-api.js"></script>
<script src="/BBS/Public/assets/js/xenon-toggles.js"></script>

<!-- JavaScripts initializations and stuff -->
<script src="/BBS/Public/assets/js/xenon-custom.js"></script>

</body>
</html>