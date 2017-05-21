	function artBySubject(obj,subjectid) {
		$("#lstArtsSubject li[class='on']").removeAttr("class");
		$(obj).parent().attr("class", "on");
		if(subjectid == null) subjectid="";
		$.post("./srv/rdo.php?", {tpl:"chg_art_by_subject", ntid:{noticeId}, sjt:subjectid}, function (d, e) {
			$("#lstArts").html(d);
		});
	}

	function fillGrade(obj) {
		var period = $(obj).val();
		if(period == "") {
			$("#sltGrade").html("<option value=''>Äê¼¶</option>");
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