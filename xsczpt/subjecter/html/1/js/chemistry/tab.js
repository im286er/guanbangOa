//��optionClass: ������󹫹���class������ΨһclassΪoptionClass1, optionClass2 .....
//	��Ӧ�����ݹ���classΪ����classΪoptionClass_con������ΨһclassΪoptionClass_con1, optionClass_con2 .....
// mouse Ϊ��궯������Ϊ��click/hover ��ѡһ 
// btnAddClass Ϊ��ť��ǰ��ʾ����ʽ�� ����Ҫ����Ϊnull��
function tabChange(optionClass, mouse, btnAddClass) {
    var $action = null;
    $('.' + optionClass).each(function (index) {
        var run = function () {
            if (btnAddClass != null) {
                $('.' + optionClass).removeClass(btnAddClass);
                $('.' + optionClass + (index + 1)).addClass(btnAddClass);
            }
            $('.' + optionClass + "_con").hide();
            $('.' + optionClass + "_con" + (index + 1)).show();
        };
        if (mouse == "click") {
            $('.' + optionClass + (index + 1)).click(function () {
                run();
            });
        }
        if (mouse == "hover") {
            $('.' + optionClass + (index + 1))
				.mouseover(function () {
				    $action = setTimeout(run, 200);
				})
				.mouseout(function () {
				    clearTimeout($action);
				}); ;
        }
    });
    if (Request("index") != "") {
        if (btnAddClass != null) {
            $('.' + optionClass).removeClass(btnAddClass);
            $('.' + optionClass + Request("index")).addClass(btnAddClass);
        }
        $('.' + optionClass + "_con").hide();
        $('.' + optionClass + "_con" + Request("index")).show();
    }
}
function Request(paras) {
    var url = location.href;
    var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
    var paraObj = {}
    for (i = 0; j = paraString[i]; i++) {
        paraObj[j.substring(0, j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=") + 1, j.length);
    }
    var returnValue = paraObj[paras.toLowerCase()];
    if (typeof (returnValue) == "undefined") {
        return "";
    } else {
        return returnValue.replace("#", "");
    }
}