<�}link href="/widget/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
<�}script type="text/javascript" charset="gbk"  src="/widget/umeditor/umeditor.config.js"></script>
<�}script type="text/javascript" charset="gbk"  src="/widget/umeditor/umeditor.min.js"></script>
<�}script type="text/javascript" charset="gbk"  src="/widget/umeditor/lang/zh-cn/zh-cn.js"></script>

�ڿؼ������data-edit="um"
<�}textarea id="des" name="des" data-edit="um" style="display:none;"><�}/textarea> 
<�}script type="text/plain" id="umdes" style="width:700px;height:200px;"></script>   
 
js:
var um = UM.getEditor('umdes')

���ýӿ�


�ٶȱ༭��UEditor�������ú�����ȫ

�����ĵ���UEditor˵������ȫ�棬�ռ���һЩ���õķ����ͻ������ã��Թ��ο���
1�������༭��
UE.getEditor('editor', {
initialFrameWidth:"100%" //��ʼ��ѡ��
})
�����
UE.getEditor('editor')
2��ɾ���༭��
UE.getEditor('editor').destroy();
3�����ý���
UE.getEditor('editor').focus();
4����ȡ�༭������
UE.getEditor('editor').getContent()
5���༭���Ƿ�������
UE.getEditor('editor').hasContents()
6����ȡ�༭�����ݴ��ı���ʽ
UE.getEditor('editor').getContentTxt()
7����ȡ����ʽ�Ĵ��ı�
UE.getEditor('editor').getPlainTxt()
8�����ñ༭��
UE.getEditor('editor').setEnabled();
9����ֹ�༭
UE.getEditor('editor').setDisabled('fullscreen');
10����ȡ����html����
UE.getEditor('editor').getAllHtml()
11����������
imageUrl:UEDITOR_HOME_URL + "../yunserver/yunImageUp.php", //ͼƬ�ϴ��ӿ�
imagePath:"http://",

scrawlUrl:UEDITOR_HOME_URL + "../yunserver/yunScrawlUp.php",//Ϳѻ�ӿ�
scrawlPath:"http://",

fileUrl:UEDITOR_HOME_URL + "../yunserver/yunFileUp.php",//�ļ��ϴ��ӿ�
filePath:"http://",

catcherUrl:UEDITOR_HOME_URL + "php/getRemoteImage.php",//��ȡԶ��ͼƬ�ӿ�
catcherPath:UEDITOR_HOME_URL + "php/",

imageManagerUrl:UEDITOR_HOME_URL + "../yunserver/yunImgManage.php",//ͼƬ����ӿ�
imageManagerPath:"http://",

snapscreenHost:'ueditor.baidu.com',
snapscreenServerUrl:UEDITOR_HOME_URL + "../yunserver/yunSnapImgUp.php",//��ͼ�ӿ�
snapscreenPath:"http://",

wordImageUrl:UEDITOR_HOME_URL + "../yunserver/yunImageUp.php",//wordͼƬת��ӿ�
wordImagePath:"http://", //

getMovieUrl:UEDITOR_HOME_URL + "../yunserver/getMovie.php",//��ȡ��Ƶ�ӿ�

lang:/^zh/.test(navigator.language || navigator.browserLanguage || navigator.userLanguage) ? 'zh-cn' : 'en',
langPath:UEDITOR_HOME_URL + "lang/",

webAppKey:"9HrmGf2ul4mlyK8ktO2Ziayd",
initialFrameWidth:860, //��ʼ�����
initialFrameHeight:420, //��ʼ���߶�
focus:true //�Ƿ񽹵�

 