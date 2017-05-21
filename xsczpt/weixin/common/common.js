/**
 * 功能：公共js
 * 作者：Adrian·Tian
 * 日期：2013-06-20
 */
 
//图片上传验证
function checkImg(obj){
	//文件名称及路径
	var $pathFile  = $(obj).val();
	var expArray   = $pathFile.split('.');
	var extendName = expArray.pop();
	var allowExt = 'jpg,png,gif';
	var searchResult = allowExt.search(extendName.toLowerCase());
	var img;
	if(extendName.length!=3||searchResult==-1){
		alert('仅允许上传的图片格式：jpg,png,gif.');
		$(obj).val('');
	}
}

//文件上传验证
function checkFile(obj){
	//文件名称及路径
	var $pathFile  = $(obj).val();
	var expArray   = $pathFile.split('.');
	var extendName = expArray.pop();
	var allowExt = 'zip,rar';
	var searchResult = allowExt.search(extendName.toLowerCase());
	var img;
	if(extendName.length!=3||searchResult==-1){
		alert('仅允许上传的文件格式：zip、rar。');
		$(obj).val('');
	}
}

//excel文件上传验证
function checkXls(obj){
	//文件名称及路径
	var $pathFile  = $(obj).val();
	var expArray   = $pathFile.split('.');
	var extendName = expArray.pop();
	var allowExt = 'xls,xlsx';
	var searchResult = allowExt.search(extendName.toLowerCase());
	var img;
	if(searchResult==-1){
		alert('仅允许上传的文件格式：xls、xlsx。');
		$(obj).val('');
	}
}

//txt上传验证
function checkTxt(obj){
	//文件名称及路径
	var $pathFile  = $(obj).val();
	var expArray   = $pathFile.split('.');
	var extendName = expArray.pop();
	var allowExt = 'txt';
	var searchResult = allowExt.search(extendName.toLowerCase());
	var img;
	if(extendName.length!=3||searchResult==-1){
		alert('仅允许上传的文件格式：txt.');
		$(obj).val('');
	}
}

//显示层
function showDiv(obj,id)
{
	$(".boxtitle > label").removeClass();
	$(obj).parent().addClass("current");
	$(".tabbody").hide();
	$("#"+id).show();
}

//点击复选框进行全选
function selectAllC(obj,name)
{
	if($(obj).attr("checked")==false){
		$("input[name='"+name+"']").attr("checked",false);	
	}else{
		$("input[name='"+name+"']").attr("checked",true);
	}
}

//全选
function selectAll(name)
{
	$("input[name='"+name+"']").attr("checked",true);
}

//取消全选
function selectNone(name)
{
	$("input[name='"+name+"']").attr("checked",false);
}

//email判断
function isEmail(email)
{
	//Email验证规则
	var regE = /^[A-Za-z0-9-_\.]+@[a-zA-Z0-9\.]+\.[a-zA-Z0-9]{2,}$/;
	if(regE.test(email)==false){
		return false;
	}else{
		return true;	
	}
}

//手机判断
function isMobile(mobile)
{
	//手机号码验证规则
	var regM = /^(13|15|18|14)\d{9}$/;
	if(regE.test(mobile)==false){
		return false;
	}else{
		return true;	
	}
}

if (!Array.prototype.indexOf)     //解决ie9一下indexof（）不兼容的问题
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length >>> 0;
    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;
    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}
