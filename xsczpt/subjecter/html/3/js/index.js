/**	function artBySubject(obj,subjectid) {
		$("#lstArtsSubject li[class='on']").removeAttr("class");
		$(obj).parent().attr("class", "on");
		if(subjectid == null) subjectid="";
		$.post("./srv/rdo.php?", {tpl:"chg_art_by_subject", ntid:{noticeId}, sjt:subjectid}, function (d, e) {
			$("#lstArts").html(d);
		});
	}
**/
	function fillGrade(obj) {
		var period = $(obj).val();
		if(period == "") {
			$("#sltGrade").html("<option value=''>�꼶</option>");
			return;
		}
		$.post("./srv/rdo.php?", {tpl:"fill_grade", p:period}, function (d, e) {
			$("#sltGrade").html(d);
		});
	}

	function resByGrade(obj) {
		var subjectid = $("#lstResSubject li[class='on'] a").attr("data-subject");
		$.post("./srv/rdo.php?", {tpl:"chg_res_by_subject", sjt:subjectid, gra:$(obj).val()}, function (d, e) {
			$("#lstRes").html(d);
		});
	}

	function resBySubject(obj,subjectid) {
		$("#lstResSubject li[class='on']").removeAttr("class");
		$(obj).parent().attr("class", "on");
		if(subjectid == null) subjectid="";
		$.post("./srv/rdo.php?", {tpl:"chg_res_by_subject", sjt:subjectid, gra:$("#sltGrade").val()}, function (d, e) {
			$("#lstRes").html(d);
		});
	}

	/**
* JavaScript�ű�ʵ�ֻص�ҳ�涥��ʾ��
* @param acceleration �ٶ�
* @param stime ʱ���� (����)
**/
function gotoTop(acceleration,stime) {
   acceleration = acceleration || 0.1;
   stime = stime || 10;
   var x1 = 0;
   var y1 = 0;
   var x2 = 0;
   var y2 = 0;
   var x3 = 0;
   var y3 = 0; 
   if (document.documentElement) {
       x1 = document.documentElement.scrollLeft || 0;
       y1 = document.documentElement.scrollTop || 0;
   }
   if (document.body) {
       x2 = document.body.scrollLeft || 0;
       y2 = document.body.scrollTop || 0;
   }
   var x3 = window.scrollX || 0;
   var y3 = window.scrollY || 0;
 
   // ��������ҳ�涥����ˮƽ����
   var x = Math.max(x1, Math.max(x2, x3));
   // ��������ҳ�涥���Ĵ�ֱ����
   var y = Math.max(y1, Math.max(y2, y3));
 
   // �������� = Ŀǰ���� / �ٶ�, ��Ϊ����ԭ��ԽС, �ٶ��Ǵ��� 1 ����, ���Թ��������Խ��ԽС
   var speeding = 1 + acceleration;
   window.scrollTo(Math.floor(x / speeding), Math.floor(y / speeding));
 
   // ������벻Ϊ��, �������ú���
   if(x > 0 || y > 0) {
       var run = "gotoTop(" + acceleration + ", " + stime + ")";
       window.setTimeout(run, stime);
   }
}
//�ص�������ʾ
$(window).scroll(function(){  //ֻҪ���ڹ���,�ʹ���������� 
	var scrollt = document.documentElement.scrollTop + document.body.scrollTop; //��ȡ������ĸ߶� 
	if(scrollt >200){  //�жϹ�����߶ȳ���200px,����ʾ  
	$(".totop").fadeIn(); //����     
	}else{      
	$(".totop").fadeOut(); //������ػ���û�г���,�͵���.�������stop()ֹ֮ͣǰ����,������������   
	}
});