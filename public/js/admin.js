var p;
var start;
var search;
var sort='';
var sortdir='';
var update_id;
var update_count;
var params=new Array();
var values=new Array();
var menuTimeOut=new Array();

function Init(){
    try{
        req=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch(e){
        try{
            req=new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch(oc){
            req=null;
        }
    }
    if(!req&&typeof XMLHttpRequest!="undefined"){
        req = new XMLHttpRequest();
	}
}

function isset(variable_name) {
    try {
		if (typeof(eval(variable_name)) != 'undefined')
		if (eval(variable_name) != null)
        return true;
	}catch(e) { }
	return false;
}


function edit(id,count,p){
  var c_edit=document.getElementById('c_edit'+id);
  var c_update=document.getElementById('c_update'+id);
  var c_cancel=document.getElementById('c_cancel'+id);
  console.debug('field'+id+'_1');
  var field=document.getElementById('field'+id+'_1');
  var param_str='';
  for(i=0; i<params.length; i++){
  	param_str+='&'+params[i]+'='+values[params[i]];
  }
  if(!isset(start) && isset(document.getElementById('start'))) var start=document.getElementById('start').value;
  popup('index/components?p='+p+'&sort='+escape(sort)+'&dir='+sortdir+param_str+'&a=edit&'+field.name+'='+escape(field.value));
}

function cancel(id,count){
  var c_edit=document.getElementById('c_edit'+id);
  var c_update=document.getElementById('c_update'+id);
  var c_cancel=document.getElementById('c_cancel'+id);
  var i;
  for(i=0; i<count; i++){
  	var field=document.getElementById('field'+id+'_'+i);
  	var edit=document.getElementById('edit'+id+'_'+i);
  	if (field!=undefined && edit!=undefined){
  		field.style.display='block';
  		edit.style.display='none';
  		c_edit.style.display='inline';
  		c_update.style.display='none';
  		c_cancel.style.display='none';
  		if(edit.type=='checkbox')edit.checked=field.checked;
	  	else if(edit.options)edit.selectedIndex=field.selectedIndex;
	  	else edit.value=html_decode(field.innerHTML);
  	}
  }
}

function update(id,count){
  	Init();
  	if(req!=null){
  		var c_loading=document.getElementById('loading'+id);
  		var c_update=document.getElementById('c_update'+id);
  		c_loading.style.display='inline';
  		c_update.style.display='none';
	    req.onreadystatechange = getResult_update;
	    req.open("post", "admin?p="+p+"&a=edit&save=1&ajax=1", true);
	    try {
	       req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	       var send="";
	       var i;
  		   for(i=0; i<count; i++){
  		   	var edit=document.getElementById('edit'+id+'_'+i);
  		   	var field=document.getElementById('field'+id+'_'+i);
  		   	if(field!=undefined){
  		   		if(edit!=undefined){
					var value;
					var name=edit.name.substring(0,edit.name.indexOf('['))+"[]";
					if(edit.type=='checkbox'){
						if(edit.checked)value=edit.value;
						else value='';
					}
					else if(edit.options){
						value=edit.options[edit.selectedIndex].value;
					}
					else {
						value=edit.value;
					}
  		   			send+=name+"="+escape(value)+"&";
  		   		} else if(field.value!=undefined) {
  		   			send+=field.name+"="+escape(field.value)+"&";
  		   		}
  		   	}
  		   }
  		   update_id=id;
  		   update_count=count;
	       req.send(send);
	    } catch (ex) { }
	}
}

function add(url, form){
  	Init();
  	if(req!=null){
	    req.onreadystatechange = getResult_add;
	    req.open("post", url, true);
	    try {
	       req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	       var send="";
	       var i;
  		   for(i=0; i<form.elements.length; i++){
  		   	var el=form.elements[i];
  		   	var value;
  		   	if(el.options)value=el.options[el.selectedIndex].value;
  		   	else if(el.type=='checkbox'){
  		   		if(el.checked)value=el.value;
  		   		else value="";
  		   	}
  		   	else value=el.value;
  		   	send+=form.elements[i].name+"="+escape(value)+"&";
  		   }
  		   document.getElementById('ajax_box').innerHTML="<div style=\"text-align:center\"><img alt=\"Cargando...\" src=\"images/admin/loading.gif\" /></div>";
	       req.send(send);
	    } catch (ex) { }
	}	
}

function getResult_update(){
  if (req.readyState == 4){
    if (req.status == 200){
    	if(req.responseText!="0"){
		  var c_edit=document.getElementById('c_edit'+update_id);
	      var c_cancel=document.getElementById('c_cancel'+update_id);
	      var c_loading=document.getElementById('loading'+update_id);
	  	  var i;
		  for(i=0; i<update_count; i++){
		  	var field=document.getElementById('field'+update_id+'_'+i);
		  	var edit=document.getElementById('edit'+update_id+'_'+i);
		  	if (field!=undefined && edit!=undefined){
		  	  edit.style.display='none';
		  	  if(edit.type=='checkbox')field.checked=edit.checked;
		  	  else if(edit.options)field.selectedIndex=edit.selectedIndex;
		  	  else field.innerHTML=html_encode(edit.value);
		  	  field.style.display='block';
		      c_edit.style.display='inline';
		  	  c_cancel.style.display='none';
		  	  c_loading.style.display='none';
		  	}
		  }
    	} else {
    		cancel(update_id,update_count);
    	}
    }
    else {
        alert("Internal Server Error!");
    }
  }
}

function getResult_add(){
  if (req.readyState == 4){
    if (req.status == 200){

      document.getElementById('ajax_box').innerHTML=req.responseText;
    }
    else {

        document.getElementById('ajax_box').innerHTML="Internal Server Error!";
    }
  }
}

function check(form, name, mode){
	var sel=false;
	var i;
	if(form.elements[name].length!=undefined){
		for(i=0; i<form.elements[name].length; i++){
			if(form.elements[name][i].checked){
				sel=true;
				break;
			}
		}
	}else{
		sel=form.elements[name].checked;
	}
	if(sel){
		form.elements['a'].value=mode;
		var param_str='';
		for(i=0; i<params.length; i++){
		  	param_str+='&'+params[i]+'='+values[params[i]];
		}
		
		if(!isset(start) && isset(document.getElementById('start'))) var start=document.getElementById('start').value;
		form.action+=param_str+'&sort='+escape(sort)+'&dir='+sortdir+'&start='+start+'&search='+search;
		form.submit();
	}else{
		alert('No records selected! Select the records you want to edit first.');
	}
}

function select_all(el){
	var i;
	var check;
	if(el.length!=undefined){
		if(el[0].checked)check=false;
		else check=true;
		for(i=0; i<el.length; i++)el[i].checked=check;
	}else{
		el.checked=!el.checked;
	}
}

function select_all_dijit(el){
	var nodes = dojo.query(el);
	var check;
	if(dijit.getEnclosingWidget(nodes[0]).get("checked",false)) check=false;
	else check=true;
	dojo.forEach(nodes,
	    function(node)
	    {
	        dijit.getEnclosingWidget(node).set("checked",check);
	    }
	);

}

function html_decode(value){
	var decoded;
	decoded=value.replace(/&lt;/gi,"<");
	decoded=decoded.replace(/&gt;/gi,">");
	decoded=decoded.replace(/&amp;/gi,"&");
	decoded=decoded.replace(/&quot;/gi,'"');
	return decoded;
}

function html_encode(value){
	var encoded;
	encoded=value.replace(/</gi,"&lt;");
	encoded=encoded.replace(/>/gi,"&gt;");
	encoded=encoded.replace(/&/gi,"&amp;");
	encoded=encoded.replace(/"/gi,"&quot;");
	return encoded;
}

function showmenu(e){
	var posx = 0;
	var posy = 0;
	if (!e) var e = window.event;
	if (e.pageX || e.pageY) 	{
		posx = e.pageX;
		posy = e.pageY;
	}
	else if (e.clientX || e.clientY) 	{
		posx = e.clientX + document.body.scrollLeft
			+ document.documentElement.scrollLeft;
		posy = e.clientY + document.body.scrollTop
			+ document.documentElement.scrollTop;
	}
	var menu=document.getElementById('menu'+this.id);
	if(menu && menu.style.display!='block'){
		var top=this.offsetTop+25;
		var left=this.offsetLeft;
		if(menu.offsetParent){
			top+=15;
			left+=15;
		}
		menu.style.top=top+'px';
		menu.style.left=left+'px';
		menu.style.display='block';
	}

}

function hidemenu(div){
  div=document.getElementById(div);
  div.style.display='none';
}

function context(url){
	var pos;
	if (window.innerHeight){
		pos = window.pageYOffset;
	}else if (document.documentElement && document.documentElement.scrollTop){
		pos = document.documentElement.scrollTop;
	}else if (document.body){
		pos = document.body.scrollTop;
	}
	
    var x=document.documentElement.clientWidth/2-240;
    var y=pos+100;
	document.getElementById('context').style.top=y+'px';
	document.getElementById('context').style.left=x+'px';
	
	var popup=document.getElementById('context');
	popup.style.display='block';
	if(url!=null)get_url_contents(url,'context_body');
	
}

function close_context(){
	document.getElementById('context').style.display='none';
}


function popup(url,position){
	var pos;
	if (window.innerHeight){
		pos = window.pageYOffset;
	}else if (document.documentElement && document.documentElement.scrollTop){
		pos = document.documentElement.scrollTop;
	}else if (document.body){
		pos = document.body.scrollTop;
	}
	
	document.getElementById('popup_body').style.width='800px';
	document.getElementById('popup_body').style.height='633px';
	
	switch(position){
		case 'topright':
		  var x=document.documentElement.clientWidth-540;
		  var y=pos+40;
		break;
		case 'fullscreen':
		  	document.getElementById('popup_body').style.width=(document.documentElement.clientWidth-60)+'px';
		  	document.getElementById('popup_body').style.height=(document.documentElement.clientHeight-90)+'px';
		  	var x=10;
		  	var y=pos+10;
		break;
		default:
		  var x=document.documentElement.clientWidth/2-240;
		  var y=pos+100;
	}
	document.getElementById('popup').style.top=y+'px';
	document.getElementById('popup').style.left=x+'px';

	var embeds=document.getElementsByTagName('object');
	if(embeds){
		for(i=0; i<embeds.length; i++){
		  embeds[i].style.display='none';
		}
	}
	var embeds=document.getElementsByTagName('embed');
	if(embeds){
		for(i=0; i<embeds.length; i++){
		  embeds[i].style.display='none';
		}
	}
	
	if(position!='topright')if(blank=document.getElementById('black_overlay'))blank.style.display='block';
	var popup=document.getElementById('popup');
	changeOpacity(0,'popup');
	popup.style.display='block';
	if(url!=null)get_url_contents(url,'popup_body');
	
	fadeIn('popup');
}


function popupIframe(url,position){
	
	var pos;
	if (window.innerHeight){
		pos = window.pageYOffset
	}else if (document.documentElement && document.documentElement.scrollTop){
		pos = document.documentElement.scrollTop;
	}else if (document.body){
		pos = document.body.scrollTop;
	}
	
	document.getElementById('popup_body').style.width='830px';
	document.getElementById('popup_body').style.height='600px';
	
	removeChildrensFromNode('popup_body');
	
	
	switch(position){
		case 'topright':
		  var x=document.documentElement.clientWidth-540;
		  var y=pos+40;
		break;
		case 'fullscreen':
		  	document.getElementById('popup_body').style.width=(document.documentElement.clientWidth-60)+'px';
		  	document.getElementById('popup_body').style.height=(document.documentElement.clientHeight-90)+'px';
		  	var x=10;
		  	var y=pos+10;
		break;
		default:
		  var x=document.documentElement.clientWidth/2-400;
		  var y=pos+100;
	}
	document.getElementById('popup').style.top=y+'px';
	document.getElementById('popup').style.left=x+'px';

	var embeds=document.getElementsByTagName('object');
	if(embeds){
		for(i=0; i<embeds.length; i++){
		  embeds[i].style.display='none';
		}
	}
	var embeds=document.getElementsByTagName('embed');
	if(embeds){
		for(i=0; i<embeds.length; i++){
		  embeds[i].style.display='none';
		}
	}
	
	if(position!='topright')if(blank=document.getElementById('black_overlay'))blank.style.display='block';
	var popup=document.getElementById('popup');
	changeOpacity(0,'popup');
	popup.style.display='block';
	var iframe = document.createElement("iframe");
	iframe.src = url;
	iframe.width='810';
	iframe.height='593';
	iframe.frameBorder='0';
	iframe.style.marginTop='0px';
	iframe.style.marginBottom='0px';
	iframe.style.marginLeft='auto';
	iframe.style.marginRight='auto';	
		
	document.getElementById('popup_body').appendChild(iframe);
	
	//if(url!=null)get_url_contents(url,'popup_body');
	
	//Fade in
	fadeIn('popup');
}


function fadeIn(id){
	var speed=7; 
    var timer=0;
	for(i=0; i<=100; i++) { 
	    setTimeout("changeOpacity("+i+",'"+id+"')",timer*speed); 
	    timer++; 
	} 
}

function changeOpacity(opacity, id) { 
	var o=document.getElementById(id);
    o.style.opacity=opacity/100; 
    o.style.MozOpacity=opacity/100; 
    o.style.KhtmlOpacity=opacity/100; 
    o.style.filter="alpha(opacity="+opacity+")"; 
}

function close_popup(){
	if(blank=document.getElementById('black_overlay'))blank.style.display='none';
	document.getElementById('popup').style.display='none';
	
	var embeds=document.getElementsByTagName('object');
	if(embeds){
		for(i=0; i<embeds.length; i++){
		  embeds[i].style.display='inline';
		}
	}
	var embeds=document.getElementsByTagName('embed');
	if(embeds){
		for(i=0; i<embeds.length; i++){
		  embeds[i].style.display='inline';
		}
	}
}

function selectValueSet(id, value) {
	eval('SelectObject = document.getElementById("'+ id +'");');
	for(index = 0; index < SelectObject.length; index++) {
		if(SelectObject[index].value == value)
			SelectObject.selectedIndex = index;
	}
}

function binToInt(bin){
	var	currnum = 128;
	var	num1 = bin;
	var	num2 = eval(num1.charAt(0)) * currnum;
	for (i = 1; i <= 7; i++){
		currnum = currnum / 2;
		num2 = num2 + (eval(num1.charAt(i)) * currnum);
	}
	return num2;
}

function intToBin(int){
	var	currnum = 128;
	var num1 = eval(int);
	if(num1 >= currnum){
		var num2 = "1";
		num1 = num1 - currnum;
		currnum = currnum / 2;
	}else{
		var num2 = "0";
		currnum = currnum / 2;
	}

	for (p = 1; p <= 7; p++){
		if(num1 >= currnum){
			num2 = num2 + "1";
			num1 = num1 - currnum;
			currnum = currnum / 2;
		}else{
			num2 = num2 + "0";
			currnum = currnum / 2;
		}
	}
	return num2;
}	

function trim(myString) {
	return myString.replace(/^\s+/g,'').replace(/\s+$/g,'');
}

function removeChildrensFromNode(e) {
	var cell = document.getElementById(e);

	if ( cell.hasChildNodes() )
	{
	    while ( cell.childNodes.length >= 1 )
	    {
	        cell.removeChild( cell.firstChild );       
	    } 
	}
}