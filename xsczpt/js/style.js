// JavaScript Document
$(function(){
	$("input[type=text],textarea,input[type=password]").bind({
		click:function(){$(this).addClass("add-class1");},
		focus:function(){$(this).addClass("add-class1");},
		blur:function(){$(this).removeClass("add-class1");}
	})
	});