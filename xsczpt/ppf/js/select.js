/**
*select 控件相关数据处理
*lren@k83.cn 2014-4-11 10 
*/
/**显示数据到控件*/
function selectShowData(ctrl, jsn,id,name) {
    try {
        $(ctrl).empty();
        $(ctrl).append("<option value=''>—所有—</option>");
        for (i = 0; i < jsn.length; i++) {
            $(ctrl).append("<option value='" + jsn[i][id] + "'>" + jsn[i][name] + "</option>");
        }
    } catch (e) {
        showErr(e);
    }
}
//无头
function selectShowDataNoH(ctrl, jsn,id,name) {
    try {
        $(ctrl).empty(); 
        for (i = 0; i < jsn.length; i++) {
            $(ctrl).append("<option value='" + jsn[i][id] + "'>" + jsn[i][name] + "</option>");
        }
    } catch (e) {
        showErr(e);
    }
}

//学期
function selectShowDataNoH1(ctrl, jsn,id,name,year) {
    try {
        $(ctrl).empty(); 
        for (i = 0; i < jsn.length; i++) {
            $(ctrl).append("<option value='" + jsn[i][id] + "'>" + jsn[i][year] +"(" +jsn[i][name] + ")</option>");
        }
    } catch (e) {
        showErr(e);
    }
}
/*二级下列菜单*/
function selectShowData2(ctrl, jsn, id,pid,name) {    
    try {
        $(ctrl).empty();
        $(ctrl).append("<option value=''>—所有—</option>");
        for (i = 0; i < jsn.length; i++) {
            if (jsn[i][pid] == 0) {
                $(ctrl).append("<option name='" + jsn[i][id] + "' value='" + jsn[i][id] + "' style='color:blue;'>" + jsn[i][name] + "</option>");
            }
            else {
                $("<option name='" + jsn[i][pid] + "' value='" + jsn[i][id] + "'>&nbsp;&nbsp;" + jsn[i][name] + "</option>").insertAfter(ctrl + " option[name='" + jsn[i]["pid"] + "']:last");
            }
        } 
    } catch (e) {
        showErr(e);
    }
}
/**二级菜单1不可选择顶级*/
function selectShowData2n1(ctrl, d,id,pid,name) {
    try {
        $(ctrl).empty();
        $(ctrl).prepend("<option value=''>—所有—</option>");
        lid = Math.round( Math.random()*10000);
        for (i = 0; i < d.length; i++) {
            if (d[i][pid] == 0) {
                $(ctrl).append("<optgroup id='" + lid + d[i][id] + "' label='" + d[i][name] + "'></optgroup>");
            }
            else {
                $('#'+lid + d[i][pid]).append("<option value='" + d[i][id] + "'>" + d[i][name] + "</option>");
            }
        }
    } catch (e) { showErr(e); }
}
/**/
function selectAddHead(ctrl,v){
  $(ctrl).prepend("<option value=''>—"+v+"—</option>");
  $(ctrl).val("");   
}
/**显示值和文本到指定对象*/
function selectSelTxtTo(ctrl, obj) {
    if ($(obj).val() == "")
        $(ctrl).val("");
    else
        $(ctrl).val($.trim($(obj).find('option:selected').text()));
}
function selectSelValTo(ctrl, obj) {
    $(ctrl).val($(obj).val());
}
function selectSelTxtValTo(ctrl, ctrl1, obj) {
    if ($(obj).val() == "") {
        $(ctrl).val("");
        $(ctrl1).val("");
    } else {
        s = $(obj).find('option:selected').text();
        $(ctrl).val($.trim(s));
        $(ctrl1).val($(obj).val());
    }
}

function selectShowDataNation(ctrl, jsn,code_no,code_name) {
    try {
        $(ctrl).empty(); 
        for (i = 0; i < jsn.length; i++) {
            $(ctrl).append("<option value='" + jsn[i][code_no] + "'>" + jsn[i][code_name] + "</option>");
        }
    } catch (e) {
        showErr(e);
    }
}

function selectShowDataStatus(ctrl, jsn,code_no,code_name) {
    try {
        $(ctrl).empty(); 
        for (i = 0; i < jsn.length; i++) {
            $(ctrl).append("<option value='" + jsn[i][code_no] + "'>" + jsn[i][code_name] + "</option>");
        }
    } catch (e) {
        showErr(e);
    }
}

function selectShowDataCourse(ctrl, jsn,code_no,code_name) {
    try {
             $(ctrl).empty(); 
        for (i = 0; i < jsn.length; i++) {
            $(ctrl).append("<option value='" + jsn[i][code_no] + "'>" + jsn[i][code_name] + "</option>");
        }
    } catch (e) {
        showErr(e);
    }
}

/*二级下列菜单 del
function showCateToSelect2(ctrl, d) {
    try {
        $( ctrl).empty();
        $( ctrl).append("<option value=''>—所有—</option>"); 
        for (i = 0; i < d.length; i++) {
            if (d[i]["pid"] == 0) {
                $( ctrl).append("<option name='" + d[i]["id"] + "' value='" + d[i]["id"] + "' style='color:blue;'>"+ d[i]["name"]+ "</option>");
            }
            else {
                $("<option name='" + d[i]["pid"] + "' value='" + d[i]["id"] + "'>&nbsp;&nbsp;" + d[i]["name"] + "</option>").insertAfter(
                 ctrl + " option[name='" + d[i]["pid"] + "']:last");
            }
        }
        //$( ctrl).prepend("<option value=''>—所有—</option>");
    } catch (e) {
        showErr(e);
    }
}
*/
/*二级菜单1不可选择顶级  del
function showCateToSelect2_1(ctrl, d) {
    try {
        $("#" + ctrl).empty();
        $("#" + ctrl).prepend("<option value=''>—所有—</option>");
        for (i = 0; i < d.length; i++) {
            if (d[i]["pid"] == 0) {
                $("#" + ctrl).append("<optgroup id='" + ctrl + d[i]["id"] + "' label='" + d[i]["name"] + "'></optgroup>");
            }
            else {
                $("#" + ctrl + d[i]["pid"]).append("<option value='" + d[i]["id"] + "'>" + d[i]["name"] + "</option>");
            }
        }       
        //$("#" + ctrl).val('');
    } catch (e){alert(e);}
}*/