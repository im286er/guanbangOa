var can=document.getElementById("canvas");
var ctx=can.getContext("2d");

var Grd=ctx.createLinearGradient(0,0,300,300);
Grd.addColorStop(0,"#828bcd");
Grd.addColorStop(1,"#dfb4d7");
ctx.fillStyle=Grd;
ctx.fillRect(0,0,300,300);