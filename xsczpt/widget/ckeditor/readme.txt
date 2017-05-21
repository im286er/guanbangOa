<▆script src="/widget/ckeditor/ckeditor.js"><▆/script> 

在控件中添加data-edit="ck"
<▆textarea id="des" data-edit="ck" rows="5"><▆/textarea>  

js:
CKEDITOR.replace('des',{fullPage : true,width: 0,height: 200});