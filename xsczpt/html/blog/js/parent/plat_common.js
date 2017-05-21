$(function(){
   var t="";
   if(queryStr("t")=="art"){
      t = "arts";
   }else if(queryStr("t")=="active" || queryStr("t")=="active_art" || queryStr("t")=="active_summary"){
      t = "actives";
   }else{
      t = queryStr("t");
   }
   $("#"+t).addClass("hov");
//导航栏begin      
        $(".qjf_findnav li").each(function() {
            if($(this).is(".hov")){$(this).prev().addClass("nobg");}
            else{
                $(this).prev().removeClass("nobg")
                $(this).hover(function(){
                    $(this).is(".nobg")&&$(this).removeClass("nobg");
                    $(this).addClass("hov");
                    !$(this).prev().is(".hov")&&$(this).prev().addClass("nobg")
                },function(){
                   $(this).next("li").is(".hov")&&$(this).addClass("nobg");
                   $(".qjf_findnav li.hov").not($(this))&&$(this).removeClass("hov").prev().removeClass("nobg");
                })
            };
        });
        $(".qjf_findnav li:first").css("border-left","0");
        $(".qjf_findnav li:last").css({'border-right':'0'});            
        $(".qjf_findnav li:last,.qjf_infolike li:last,.qjf_active li:last").css('border-bottom','0');
})