// ��ȡ��ѯ�ַ�����ֵ
function getUrlParam(name) {
	var AllVars = window.location.search.substring(1);
    var Vars = AllVars.split("&");
    for (i = 0; i < Vars.length; i++) {
        var Var = Vars[i].split("=");
        if (Var[0] == name) return Var[1];
    }
    return "";
}

// JS ȥ�����˿ո�
function trim(str) {
	return str.replace(/(^\s*)|(\s*$)/g,"");
}

// ���ص�ǰʱ�䣬���ڸ�ֵdatetime���ͣ����ظ�ʽΪ 201508181758
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

// �س�������ĳ����ť��click�¼�
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

// ѧ�Ρ�ѧ�ơ��꼶���α���������
function fillOpts(obj, target, bookid, chapterid) {
	var period = $(obj).val();
	var subId;
	var gradeId;
	if(bookid != null && bookid.length > 0) {
		$("#" + bookid).html('<option value="">���α���</option>');
	}
	if(chapterid != null && chapterid.length > 0) {
		$("#" + chapterid).html('<option value="">���¡�</option>');
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
		$(subId).html('<option value="">��ѧ�ơ�</option>');
		$(gradeId).html('<option value="">���꼶��</option>');
		return;
	}
	
	$(subId).html($("#hide_subjects_" + period).html());
	$(gradeId).html($("#hide_grades_" + period).html());
}

// ��ʾѧ�Ƶ�ɸѡ���
function showSubFilter() {
	var str = "";
	var period = $("#sltPeriod").val();
	var subject = $("#sltSubject").val();
	var grade = $("#sltGrade").val();
	
	str = period != "" ? $("#sltPeriod").find("option:selected").text() : "ѧ��";
	str += "&nbsp;/&nbsp;";
	str += subject != "" ? $("#sltSubject").find("option:selected").text() : "ѧ��";
	str += "&nbsp;/&nbsp;";
	str += grade != "" ? $("#sltGrade").find("option:selected").text() : "�꼶";
	
	$("#btnSubFilter").html(str);
}

// ��ʾ�α��½ڵ�ɸѡ���
function showBookFilter() {
	var str = "";
	var book = $("#sltBook").val();
	var chapter = $("#sltChapter").val();
	var part = $("#sltPart").val();
	
	str = book != "" ? $("#sltBook").find("option:selected").text().replace("�α���", "") : "�α�";
	str += "&nbsp;/&nbsp;";
	str += chapter != "" ? $("#sltChapter").find("option:selected").text() : "��";
	str += "&nbsp;/&nbsp;";
	str += part != "" ? $("#sltPart").find("option:selected").text() : "��";
	
	$("#btnBookFilter").html(str);
}

// ��ʾ�汾���ѡ����ݵ�ɸѡ���
function showEditionFilter() {
	var str = "";
	var edition = $("#sltEdition").val();
	var volume = $("#sltVolume").val();
	var year = $("#sltYear").val();
	var required = $("#sltRequired").val();
	
	str = edition != "" ? $("#sltEdition").find("option:selected").text() : "�汾";
	str += "&nbsp;/&nbsp;";
	str += volume != "" ? $("#sltVolume").find("option:selected").text() : "���";
	str += "&nbsp;/&nbsp;";
	str += year != "" ? $("#sltYear").find("option:selected").text() : "���";
	str += "&nbsp;/&nbsp;";
	str += required != "" ? $("#sltRequired").find("option:selected").text() : "ѡ����";
	
	$("#btnEditionFilter").html(str);
}

function resetBook() {
	$("#img_notice").css("display", "none");
	$("#book").html('<option value="">���α���</option>').val("");
	$("#chapter").html('<option value="">���¡�</option>').val("");
	$("#part").html('<option value="">���ڡ�</option>').val("");
	
	$("#book").prop("disabled", "disabled");
	$("#chapter").prop("disabled", "disabled");
	$("#part").prop("disabled", "disabled");
	if($("#btnBookFilter").html() != null)$("#btnBookFilter").html("�α� / �� / ��");
}

// ����ɸѡ����
function resetOptions(targetName, id) {
	switch (targetName) {
		case "sub":
			$("#sltPeriod").val("");
			$("#sltSubject").val("");
			$("#sltGrade").val("");
			$("#sltSubject").html('<option value="">��ѧ�ơ�</option>').val("");
			$("#sltGrade").html('<option value="">���꼶��</option>').val("");
			$("#btnSubFilter").html("ѧ�� / ѧ�� / �꼶");
			
			$("#sltBook").html('<option value="">���α���</option>').val("");
			$("#sltChapter").html('<option value="">���¡�</option>').val("");
			$("#sltPart").html('<option value="">���ڡ�</option>').val("");
			$("#sltBook").prop("disabled", "disabled");
			$("#sltChapter").prop("disabled", "disabled");
			$("#sltPart").prop("disabled", "disabled");
			$("#btnBookFilter").html("�α� / �� / ��");
			$("#img_notice").css("display", "none");
			break;
		case "edition":
			$("#sltEdition").val("");
			$("#sltVolume").val("");
			$("#sltRequired").val("");
			$("#sltYear").val("");
			$("#btnEditionFilter").html("�汾 / ��� / ��� / ѡ����");
			save_edition();
			break;
		case "book":
			$("#sltBook").val("");
			$("#sltChapter").html('<option value="">���¡�</option>').val("");
			$("#sltPart").html('<option value="">���ڡ�</option>').val("");
			$("#sltChapter").prop("disabled", "disabled");
			$("#sltPart").prop("disabled", "disabled");
			$("#btnBookFilter").html("�α� / �� / ��");
			break;
		case "books_clear":
			$("#sltBook").html('<option value="">���α���</option>').val("");
			$("#sltChapter").html('<option value="">���¡�</option>').val("");
			$("#sltPart").html('<option value="">���ڡ�</option>').val("");
			
			$("#sltBook").prop("disabled", "disabled");
			$("#sltChapter").prop("disabled", "disabled");
			$("#sltPart").prop("disabled", "disabled");
			
			$("#btnBookFilter").html("�α� / �� / ��");
			$("#img_notice").css("display", "none");
			break;
		case "books_clear_add":
			$("#book").html('<option value="">���α���</option>').val("");
			$("#chapter").html('<option value="">���¡�</option>').val("");
			$("#part").html('<option value="">���ڡ�</option>').val("");
			
			$("#book").prop("disabled", "disabled");
			$("#chapter").prop("disabled", "disabled");
			$("#part").prop("disabled", "disabled");
			
			$("#img_notice").css("display", "none");
			break;
	}
	
	$("#"+id).modal('hide');
}

// JQuery����cookie
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

//$.cookie("cookie1","hello");//�½�һ��cookie������Ϊcookie1��ֵΪ"hello",��Ч�ڵ�������ر�  
//$.cookie("cookie2","word",{expires:2});//�½�һ��cookie������cookie2,ֵ"word",��Ч�ڵ�2���  
//var str = $.cookie("cookie1");//��cookie1��ֵ"hello"�������� str  
//$.cookie("cookie2",null);//ɾ��cookie2  
//$.cookie("cookie1","bye");//��cookie1��ֵ�޸�Ϊ"bye" 

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
		// ��ͨ����Ա�̶�ѧ�Ρ�ѧ��
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
		
		// �ܹ���Ա����������ѧ�Ρ�ѧ��
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