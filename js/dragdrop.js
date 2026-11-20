anteriordiv = 'inutil';

function dragdrop(local_click, caixa_movida) {

if(local_click != anteriordiv){
	
	document.getElementById(local_click).style.zIndex = '1000';
	document.getElementById(anteriordiv).style.zIndex = '999';
}

document.getElementById(local_click).style.cursor = 'move';

addEvent(local_click, 'mousedown', function(ev) {

  objSelecionado = caixa_movida;

  mouseOffset = getPosition(objSelecionado, ev);

});



var objSelecionado = null;

var mouseOffset = null;

function addEvent(obj, evType, fn) { //Função adaptada da  original de Christian Heilmann, em http://www.onlinetools.org/articles/unobtrusivejavascript/chapter4.html

if (typeof obj == "string") {

  if (null == (obj = document.getElementById(obj))) {

   throw new Error("Elemento HTML não encontrado. Não foi possível adicionar o evento.");

  }

}

if (obj.attachEvent) {

  return obj.attachEvent(("on" + evType), fn);

} else if (obj.addEventListener) {

  return obj.addEventListener(evType, fn, true);

} else {

  throw new Error("Seu browser não suporta adição de eventos.");

}

}

document.onmousemove = function(ev) {

var ev = ev || window.event;

var mousePos = mouseCoords(ev);

if (objSelecionado) {

  document.getElementById(objSelecionado).style.left = mousePos.x - mouseOffset.x + 'px';

  document.getElementById(objSelecionado).style.top = mousePos.y - mouseOffset.y + 'px';

  document.getElementById(objSelecionado).style.margin = '0px';

  return false;

}

}

function mouseCoords(ev){

if(ev.pageX || ev.pageY){

  return {x:ev.pageX, y:ev.pageY};

}

return {

  x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,

  y:ev.clientY + document.body.scrollTop  - document.body.clientTop

};

}

function getPosition(e, ev){

e = document.getElementById(e);

var left = 0;

var top  = 0;

var coords = mouseCoords(ev);

while (e.offsetParent){

  left += e.offsetLeft;

  top  += e.offsetTop;

  e     = e.offsetParent;

}

left += e.offsetLeft;

top  += e.offsetTop;

return {x: coords.x - left, y: coords.y - top};

}

document.onmouseup = function() {

objSelecionado = null;
}
	anteriordiv = local_click;
}