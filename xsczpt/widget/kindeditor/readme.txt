<link rel="stylesheet" href="/widget/kindeditor/themes/default/default.css" />
<script charset="utf-8" src="/widget/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/widget/kindeditor/lang/zh_CN.js"></script>


<textarea id="des" name="des" data-edit="ke" style="width:100%;height:250px;visibility:hidden;"></textarea>

js:-------------------------------------------------
var keditor=[];
KindEditor.ready(function(K) {
	keditor["des"] = K.create('#des');  
});