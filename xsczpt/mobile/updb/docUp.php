<?php
    header("Content-Type:text/html;charset=gbk");
    error_reporting( E_ERROR | E_WARNING );
    date_default_timezone_set("Asia/chongqing"); 
    include "Uploader.class.php";
    //上传配置
    $config = array(
        "savePath" => "../../upds/doc/",             //存储文件夹  DIR_ROOT.UP_FILE_DIR 
        "maxSize" => 10000 ,                   //允许的文件最大尺寸，单位KB
        "allowFiles" => array( ".avi" , ".3gp" , ".wmv" , ".jpeg" , ".bmp" )  //允许的文件格式
    );
    //上传文件目录
    $Path ="../../upds/doc/";

    //背景保存在临时目录中
    $config[ "savePath" ] = $Path;
    $up = new Uploader( "upfile" , $config );
    $type = $_REQUEST['type'];
    $callback=$_GET['callback'];

    $info = $up->getFileInfo();
    /**
     * 返回数据
     */
    if($callback) {
        echo '<script>'.$callback.'('.json_encode($info).')</script>';
    } else {
        echo json_encode($info);
    }
