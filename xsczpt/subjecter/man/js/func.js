// 获取查询字符串的值
function getUrlParam(name) {
	var AllVars = window.location.search.substring(1);
    var Vars = AllVars.split("&");
    for (i = 0; i < Vars.length; i++) {
        var Var = Vars[i].split("=");
        if (Var[0] == name) return Var[1];
    }
    return "";
}

// JS 去除两端空格
function trim(str) {
	return str.replace(/(^\s*)|(\s*$)/g,"");
}

// 返回当前时间，用于赋值datetime类型，返回格式为 201508181758
function getDate() {
	var d = new Date();
	var vYear = d.getFullYear();
	var vMon = d.getMonth() + 1;
	var vDay = d.getDate();
	var h = d.getHours(); 
	var m = d.getMinutes(); 
	var se = d.getSeconds();
	s = vYear + "" +
		+ (vMon<10 ? "0" + vMon : vMon)
		+ (vDay < 10 ? "0" + vDay : vDay)
		+ (h < 10 ? "0" + h : h)
		+ (m < 10 ? "0" + m : m)
		+ (se < 10 ? "0" + se : se);
	return s;
}

// 回车键调用某个按钮的click事件
function doClick(event, targetButtonName) {
	if(event.keyCode){
		if (event.keyCode == 13) {
			event.keyCode = 9;
			event.returnValue = false;
			event.cancel = true;
			$("#" + targetButtonName).click();
		}
	} else {
		if (event.which == 13) {
			event.which = 9;
			event.returnValue = false;
			event.cancel = true;
			$("#" + targetButtonName).click();
		}
	}
}

// 学段、学科、年级、课本、章联动
function fillOpts(obj, target, bookid, chapterid) {
	var period = $(obj).val();
	var subId;
	var gradeId;
	if(bookid != null && bookid.length > 0) {
		$("#" + bookid).html('<option value="">—课本—</option>');
	}
	if(chapterid != null && chapterid.length > 0) {
		$("#" + chapterid).html('<option value="">—章—</option>');
	}
	
	switch(target) {
		case 'index':
			subId = "#sltSubject";
			gradeId = "#sltGrade";
			break;
		case 'form':
			subId = "#dlg1 #subject";
			gradeId = "#dlg1 #grade";
			break;
		case 'form2':
			subId = "#dlg2 #subject2";
			gradeId = "#dlg2 #grade2";
			break;
		case "res_add":
			subId = "#subject";
			gradeId = "#grade";
			break;
	}
	
	if(period == "") {
		$(subId).html('<option value="">—学科—</option>');
		$(gradeId).html('<option value="">—年级—</option>');
		return;
	}
	
	$(subId).html($("#hide_subjects_" + period).html());
	$(gradeId).html($("#hide_grades_" + period).html());
}

// 显示学科的筛选结果
function showSubFilter() {
	var str = "";
	var period = $("#sltPeriod").val();
	var subject = $("#sltSubject").val();
	var grade = $("#sltGrade").val();
	
	str = period != "" ? $("#sltPeriod").find("option:selected").text() : "学段";
	str += "&nbsp;/&nbsp;";
	str += subject != "" ? $("#sltSubject").find("option:selected").text() : "学科";
	str += "&nbsp;/&nbsp;";
	str += grade != "" ? $("#sltGrade").find("option:selected").text() : "年级";
	
	$("#btnSubFilter").html(str);
}

// 显示课本章节的筛选结果
function showBookFilter() {
	var str = "";
	var book = $("#sltBook").val();
	var chapter = $("#sltChapter").val();
	var part = $("#sltPart").val();
	
	str = book != "" ? $("#sltBook").find("option:selected").text().replace("课本：", "") : "课本";
	str += "&nbsp;/&nbsp;";
	str += chapter != "" ? $("#sltChapter").find("option:selected").text() : "章";
	str += "&nbsp;/&nbsp;";
	str += part != "" ? $("#sltPart").find("option:selected").text() : "节";
	
	$("#btnBookFilter").html(str);
}

// 显示版本版别选修年份的筛选结果
function showEditionFilter() {
	var str = "";
	var edition = $("#sltEdition").val();
	var volume = $("#sltVolume").val();
	var year = $("#sltYear").val();
	var required = $("#sltRequired").val();
	
	str = edition != "" ? $("#sltEdition").find("option:selected").text() : "版本";
	str += "&nbsp;/&nbsp;";
	str += volume != "" ? $("#sltVolume").find("option:selected").text() : "版别";
	str += "&nbsp;/&nbsp;";
	str += year != "" ? $("#sltYear").find("option:selected").text() : "年份";
	str += "&nbsp;/&nbsp;";
	str += required != "" ? $("#sltRequired").find("option:selected").text() : "选必修";
	
	$("#btnEditionFilter").html(str);
}

function resetBook() {
	$("#img_notice").css("display", "none");
	$("#book").html('<option value="">—课本—</option>').val("");
	$("#chapter").html('<option value="">—章—</option>').val("");
	$("#part").html('<option value="">—节—</option>').val("");
	
	$("#book").prop("disabled", "disabled");
	$("#chapter").prop("disabled", "disabled");
	$("#part").prop("disabled", "disabled");
	if($("#btnBookFilter").html() != null)$("#btnBookFilter").html("课本 / 章 / 节");
}

// 重置筛选条件
function resetOptions(targetName, id) {
	switch (targetName) {
		case "sub":
			$("#sltPeriod").val("");
			$("#sltSubject").val("");
			$("#sltGrade").val("");
			$("#sltSubject").html('<option value="">—学科—</option>').val("");
			$("#sltGrade").html('<option value="">—年级—</option>').val("");
			$("#btnSubFilter").html("学段 / 学科 / 年级");
			
			$("#sltBook").html('<option value="">—课本—</option>').val("");
			$("#sltChapter").html('<option value="">—章—</option>').val("");
			$("#sltPart").html('<option value="">—节—</option>').val("");
			$("#sltBook").prop("disabled", "disabled");
			$("#sltChapter").prop("disabled", "disabled");
			$("#sltPart").prop("disabled", "disabled");
			$("#btnBookFilter").html("课本 / 章 / 节");
			$("#img_notice").css("display", "none");
			break;
		case "edition":
			$("#sltEdition").val("");
			$("#sltVolume").val("");
			$("#sltRequired").val("");
			$("#sltYear").val("");
			$("#btnEditionFilter").html("版本 / 版别 / 年份 / 选必修");
			save_edition();
			break;
		case "book":
			$("#sltBook").val("");
			$("#sltChapter").html('<option value="">—章—</option>').val("");
			$("#sltPart").html('<option value="">—节—</option>').val("");
			$("#sltChapter").prop("disabled", "disabled");
			$("#sltPart").prop("disabled", "disabled");
			$("#btnBookFilter").html("课本 / 章 / 节");
			break;
		case "books_clear":
			$("#sltBook").html('<option value="">—课本—</option>').val("");
			$("#sltChapter").html('<option value="">—章—</option>').val("");
			$("#sltPart").html('<option value="">—节—</option>').val("");
			
			$("#sltBook").prop("disabled", "disabled");
			$("#sltChapter").prop("disabled", "disabled");
			$("#sltPart").prop("disabled", "disabled");
			
			$("#btnBookFilter").html("课本 / 章 / 节");
			$("#img_notice").css("display", "none");
			break;
		case "books_clear_add":
			$("#book").html('<option value="">—课本—</option>').val("");
			$("#chapter").html('<option value="">—章—</option>').val("");
			$("#part").html('<option value="">—节—</option>').val("");
			
			$("#book").prop("disabled", "disabled");
			$("#chapter").prop("disabled", "disabled");
			$("#part").prop("disabled", "disabled");
			
			$("#img_notice").css("display", "none");
			break;
	}
	
	$("#"+id).modal('hide');
}

// JQuery操作cookie
jQuery.cookie = function(name, value, options) {   
    if (typeof value != 'undefined') { // name and value given, set cookie  
        options = options || {};  
        if (value === null) {  
            value = '';  
            options.expires = -1;  
        }  
        var expires = '';  
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {  
            var date;  
            if (typeof options.expires == 'number') {  
                date = new Date();  
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));  
            } else {  
                date = options.expires;  
            }  
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE  
        }  
        // CAUTION: Needed to parenthesize options.path and options.domain  
        // in the following expressions, otherwise they evaluate to undefined  
        // in the packed version for some reason...  
        var path = options.path ? '; path=' + (options.path) : '';  
        var domain = options.domain ? '; domain=' + (options.domain) : '';  
        var secure = options.secure ? '; secure' : '';    
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');  
    } else { // only name given, get cookie  
        var cookieValue = null;  
        if (document.cookie && document.cookie != '') {  
            var cookies = document.cookie.split(';');  
            for (var i = 0; i < cookies.length; i++) {  
                var cookie = jQuery.trim(cookies[i]);  
                // Does this cookie string begin with the name we want?  
                if (cookie.substring(0, name.length + 1) == (name + '=')) {  
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));  
                    break;  
                }  
            }  
        }  
        return cookieValue;  
    }  
}

//$.cookie("cookie1","hello");//新建一个cookie，名称为cookie1，值为"hello",有效期到浏览器关闭  
//$.cookie("cookie2","word",{expires:2});//新建一个cookie，名称cookie2,值"word",有效期到2天后  
//var str = $.cookie("cookie1");//将cookie1的值"hello"赋给变量 str  
//$.cookie("cookie2",null);//删除cookie2  
//$.cookie("cookie1","bye");//将cookie1的值修改为"bye" 

function setCookie(key, value) {
	$.cookie(key, value, {expires:1});
}

function readCookie(key) {
	return $.cookie(key);
}

function delCookie(key) {
	$.cookie(key, null);
}

function adminControl(periodId, subId, gradeId) {
	var period = $("#user_period").val();
	var subject = $("#user_subject").val();
	
	var period_id = "#" + periodId;
	var subject_id = "#" + subId;
	var grade_id = "#" + gradeId;
	
	if($("#admin_level").val() != "top" ) {
		// 普通管理员固定学段、学科
		$(period_id).val(period);
		$(subject_id).html($("#hide_subjects_" + period).html());
		$(subject_id).val(subject);
		$(grade_id).html($("#hide_grades_" + period).html());
		
		$(period_id).prop("disabled", "disabled");
		$(subject_id).prop("disabled", "disabled");
		
		$(grade_id).html($("#hide_grades_" + period).html());
		var gra = getUrlParam('gra');
		if(gra!=null && gra!="") {
			$(grade_id).val(gra);
		}
	} else {
		
		// 总管理员按条件搜索学段、学科
		var per = getUrlParam('per');
		if(per!=null && per!="") {
			$(period_id).val(per);
			
			$(subject_id).html($("#hide_subjects_" + per).html());
			var sub = getUrlParam('sub');
			if(sub!=null && sub!="") {
				$(subject_id).val(sub);
			}
			$(grade_id).html($("#hide_grades_" + per).html());
			var gra = getUrlParam('gra');
			if(gra!=null && gra!="") {
				$(grade_id).val(gra);
			}
		}
	}
}