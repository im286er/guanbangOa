<▆link href="/widget/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
<▆script type="text/javascript" charset="gbk"  src="/widget/umeditor/umeditor.config.js"></script>
<▆script type="text/javascript" charset="gbk"  src="/widget/umeditor/umeditor.min.js"></script>
<▆script type="text/javascript" charset="gbk"  src="/widget/umeditor/lang/zh-cn/zh-cn.js"></script>

在控件中添加data-edit="um"
<▆textarea id="des" name="des" data-edit="um" style="display:none;"><▆/textarea> 
<▆script type="text/plain" id="umdes" style="width:700px;height:200px;"></script>   
 
js:
var um = UM.getEditor('umdes')

调用接口


百度编辑器UEditor常用设置函数大全

在线文档对UEditor说明不够全面，收集了一些常用的方法和基本设置，以供参考。
1、创建编辑器
UE.getEditor('editor', {
initialFrameWidth:"100%" //初始化选项
})
精简版
UE.getEditor('editor')
2、删除编辑器
UE.getEditor('editor').destroy();
3、设置焦点
UE.getEditor('editor').focus();
4、获取编辑器内容
UE.getEditor('editor').getContent()
5、编辑器是否有内容
UE.getEditor('editor').hasContents()
6、获取编辑器内容纯文本格式
UE.getEditor('editor').getContentTxt()
7、获取带格式的纯文本
UE.getEditor('editor').getPlainTxt()
8、启用编辑器
UE.getEditor('editor').setEnabled();
9、禁止编辑
UE.getEditor('editor').setDisabled('fullscreen');
10、获取整个html内容
UE.getEditor('editor').getAllHtml()
11、常用设置
imageUrl:UEDITOR_HOME_URL + "../yunserver/yunImageUp.php", //图片上传接口
imagePath:"http://",

scrawlUrl:UEDITOR_HOME_URL + "../yunserver/yunScrawlUp.php",//涂鸦接口
scrawlPath:"http://",

fileUrl:UEDITOR_HOME_URL + "../yunserver/yunFileUp.php",//文件上传接口
filePath:"http://",

catcherUrl:UEDITOR_HOME_URL + "php/getRemoteImage.php",//获取远程图片接口
catcherPath:UEDITOR_HOME_URL + "php/",

imageManagerUrl:UEDITOR_HOME_URL + "../yunserver/yunImgManage.php",//图片管理接口
imageManagerPath:"http://",

snapscreenHost:'ueditor.baidu.com',
snapscreenServerUrl:UEDITOR_HOME_URL + "../yunserver/yunSnapImgUp.php",//截图接口
snapscreenPath:"http://",

wordImageUrl:UEDITOR_HOME_URL + "../yunserver/yunImageUp.php",//word图片转存接口
wordImagePath:"http://", //

getMovieUrl:UEDITOR_HOME_URL + "../yunserver/getMovie.php",//获取视频接口

lang:/^zh/.test(navigator.language || navigator.browserLanguage || navigator.userLanguage) ? 'zh-cn' : 'en',
langPath:UEDITOR_HOME_URL + "lang/",

webAppKey:"9HrmGf2ul4mlyK8ktO2Ziayd",
initialFrameWidth:860, //初始化宽度
initialFrameHeight:420, //初始化高度
focus:true //是否焦点

 