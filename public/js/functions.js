var rating=0;
var on=0;
var themes;
var rotating=false;
var thumbs=new Array();
var thumbs_curr=new Array();
var validate_form;
var validate_validators;

function light(mode, star){
    if(mode=="on"){
    	on=1;
        var i=1;
        for(i=1; i<=star; i++)document.getElementById("st"+i).src="images/star_on.gif";
        for(i=star+1; i<=5; i++)document.getElementById("st"+i).src="images/star_off.gif";
    }else{
        var i=1;
        on=0;
        for(i=1; i<=5; i++)document.getElementById("st"+i).src="images/star_off.gif";
        setTimeout('light_rating()',1000);
    }
}

function light_rating(){
	if(on==0){
        for(i=1; i<=rating; i++)document.getElementById("st"+i).src="images/star_on.gif";
     }	
}

function highlight(e,name,c,ch){
	elements=document.getElementsByName(name);
	var i;
	for(i=0; i<elements.length; i++) {
		elements[i].style.fontSize='12px';
		elements[i].style.fontWeight='normal';
	}
	e.style.fontWeight='bold';
	e.style.fontSize='13px';
}

function show_partial(text,limit,div){
	if(text.length>limit){
		document.getElementById(div).innerHTML='<div style="display:none">'+text+' (<span class="qlink" onclick="this.parentNode.style.display=\'none\'; this.parentNode.nextSibling.style.display=\'block\'">Less</span>)</div>';
		var parts=text.split(/<br\s?\/?>/g);
		var partial='';
		for(i=0; i<parts.length; i++){
			if(partial.length+parts[i].length+6<=100)partial+=parts[i]+'<br />';
			else break;
		}
		if(partial=='')partial=parts[0].substr(0,100);
		document.getElementById(div).innerHTML+='<div>'+partial+'... (<span class="qlink" onclick="this.parentNode.style.display=\'none\'; this.parentNode.previousSibling.style.display=\'block\'">More</span>)</div>';
	}else{
		document.getElementById(div).innerHTML=text;
	}
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
	
	document.getElementById('popup_body').style.width='480px';
	document.getElementById('popup_body').style.height='323px';
	
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

function livesearch(value){
	if(value!=''){
		popup('index.php?m=searchpreview&search='+escape(value),'topright');
	}else{
		close_popup();
	}
}

function update_preview_boxes(id){
	if(!document.getElementById('channel_'+id).checked){
		document.getElementById('preview_'+id).style.display='none';
	}else{
		document.getElementById('preview_'+id).style.display='block';
	}
}

function update_preview_thumbs(id){
	var c=parseInt(document.getElementById('channel_'+id+'_limit').value);
	var div=document.getElementById('preview_'+id).getElementsByTagName('div')[1];
	div.innerHTML='';
	for(i=0; i<c; i++){
		div.innerHTML+='<img src="images/blankthumb.jpg" width="20" height="15" style="width: 20px; height: 15px; float: left; margin: 0 10px 5px 0" />';
	}
	div.innerHTML+='<div class="brclear" style="width:90px;"></div>';
}

function update_preview_comments(id){
	var c=parseInt(document.getElementById('channel_'+id+'_limit').value);
	var div=document.getElementById('preview_'+id).getElementsByTagName('div')[1];
	div.innerHTML='';
	for(i=0; i<c; i++){
		div.innerHTML+='Comments<br />';
	}
}

function show_preview(){
	document.getElementById('preview_button').style.display='none';
	update_preview_boxes('vlog');
	update_preview_boxes('featured_videos');
	update_preview_boxes('featured_video');
	update_preview_boxes('latest_videos');
	update_preview_boxes('favorites');
	update_preview_boxes('subscriptions');
	update_preview_boxes('subscribers');
	update_preview_boxes('comments');
	update_preview_boxes('tags');
	update_preview_boxes('friends');
	update_preview_thumbs('featured_videos');
	update_preview_thumbs('latest_videos');
	update_preview_thumbs('favorites');
	update_preview_thumbs('subscriptions');
	update_preview_thumbs('subscribers');
	update_preview_thumbs('friends');
	update_preview_comments('comments');
	update_preview_theme(document.getElementById('theme').options[document.getElementById('theme').selectedIndex].value);
	document.getElementById('preview').style.display='block';
}

function enable_theme_options(c){
	document.getElementById('custom_bg_color').disabled=!c;
	document.getElementById('custom_border_color').disabled=!c;
	document.getElementById('custom_text_color').disabled=!c;
	document.getElementById('custom_box_color').disabled=!c;
	document.getElementById('custom_bg_image').disabled=!c;
	document.getElementById('custom_h_color').disabled=!c;
}

function update_preview_theme(id){
	if(themes){
		if(id!=0){
			var bg_color=themes[id].bg_color;
			var border_color=themes[id].border_color;
			var text_color=themes[id].text_color;
			var box_color=themes[id].box_color;
			var h_color=themes[id].h_color;
			enable_theme_options(false);
		}else{
			var bg_color=document.getElementById('custom_bg_color').value;
			var border_color=document.getElementById('custom_border_color').value;
			var text_color=document.getElementById('custom_text_color').value;
			var box_color=document.getElementById('custom_box_color').value;
			var h_color=document.getElementById('custom_h_color').value;
			enable_theme_options(true);
			document.getElementById('theme').selectedIndex=0;
		}
		var bg_image=document.getElementById('custom_bg_image').value;
		
		var preview=document.getElementById('preview');
		preview.style.backgroundColor=bg_color;
		preview.style.backgroundImage=bg_image;
		var e=preview.getElementsByTagName('div');
		for(i=0; i<e.length; i++){
			if(e[i].className=='box'){
				e[i].style.borderColor=border_color;
				e[i].style.color=text_color;
				e[i].style.backgroundColor=box_color;
			}else if(e[i].className=='heading' || e[i].className=='bottom'){
				e[i].style.backgroundImage=border_color!=''?'none':'';
				e[i].style.backgroundColor=border_color;
				e[i].style.color=h_color;
			}else if(e[i].className=='comment'){
				e[i].style.borderColor=border_color;
			}
		}
	}
}

function insert_tag(textarea, bbopen, bbclose){
	var clientPC = navigator.userAgent.toLowerCase();
	var clientVer = parseInt(navigator.appVersion);
	var is_ie = ((clientPC.indexOf('msie') != -1) && (clientPC.indexOf('opera') == -1));
	var is_win = ((clientPC.indexOf('win') != -1) || (clientPC.indexOf('16bit') != -1));
	
	var theSelection = '';
	textarea.focus();

	if ((clientVer >= 4) && is_ie && is_win){
		theSelection = document.selection.createRange().text;
		if (theSelection){
			document.selection.createRange().text = bbopen + theSelection + bbclose;
			textarea.focus();
			return;
		}
	}else if (textarea.selectionEnd && (textarea.selectionEnd - textarea.selectionStart > 0)){
		mozWrap(textarea, bbopen, bbclose);
		textarea.focus();
		return;
	}

	if(theSelection==''){
		textarea.value+=bbopen+bbclose;
	}
	textarea.focus();
}

/**
* From http://www.massless.org/mozedit/
*/
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	var scrollTop = txtarea.scrollTop;

	if (selEnd == 1 || selEnd == 2) 
	{
		selEnd = selLength;
	}

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd);
	var s3 = (txtarea.value).substring(selEnd, selLength);

	txtarea.value = s1 + open + s2 + close + s3;
	txtarea.selectionStart = selEnd + open.length + close.length;
	txtarea.selectionEnd = txtarea.selectionStart;
	txtarea.focus();
	txtarea.scrollTop = scrollTop;

	return;
}


function insert_emoticon(emoticon, target){
	var t=document.getElementById(target);
	if(t){
		t.value+=' '+emoticon+' ';
	}
}

function show_usermenu(){
	var menu=document.getElementById('usermenu');
	if(menu.style.display=='none'||menu.style.display==''){
		var player=document.getElementById('player_wrap');
		if(player!=undefined)player.style.display='none';
		menu.style.display='block';
	}else{
		var player=document.getElementById('player_wrap');
		if(player!=undefined)player.style.display='block';
		menu.style.display='none';
	}
}

function change_days(){
	var year=document.getElementById('bdayyear').options[document.getElementById('bdayyear').selectedIndex].value;
	var month=document.getElementById('bdaymonth').options[document.getElementById('bdaymonth').selectedIndex].value;
	var days=document.getElementById('bdayday');
	days.innerHTML='';
	if(month!=2){
		var n=month<8?30+month%2:31-month%2;
	}else{
		if(year%4==0) var n=29;
		else var n=28;
	}
	for(i=1; i<=n; i++){
		var o=document.createElement('option');
		o.text=i;
		o.value=i;
		o.innerHTML=i;
		days.appendChild(o);
	}
}

function taf(form){
    var send="";
    var i;
	for(i=0; i<form.elements.length; i++){
	  var el=form.elements[i];
	  send+=form.elements[i].name+"="+escape(el.value)+"&";
	}
	var ajax=new Ajax();
	ajax.post('index.php?m=tafsend&ajax',send,'taf_box');
}

function watch_here(mode,a,id){
	if(mode){
		get_url_contents('index.php?m=watchhere&v='+id,'watch_here_'+id);
		document.getElementById('hide_'+id).style.display='inline';
		a.style.display='none';
	}else{
		document.getElementById('watch_here_'+id).innerHTML='';
		document.getElementById('watch_'+id).style.display='inline';
		a.style.display='none';
	}
}

function rotate_thumbs(id, a){
	if(a && !rotating){
		rotating=true;
		setTimeout('change_thumb('+id+')', 750);
	}else if(!a){
		rotating=false;
	}
}

function change_thumb(id){
    if(thumbs[id]){
    	if(thumbs_curr[id]==undefined){
    		if(thumbs[id].length-1<1)thumbs_curr[id]=0;
    		else thumbs_curr[id]=1;
    	}else{
	    	if(thumbs_curr[id]>=thumbs[id].length-1 || thumbs_curr[id]==undefined) thumbs_curr[id]=0;
			else thumbs_curr[id]++;
		}
		if(thumbs[id][thumbs_curr[id]])document.getElementById('thumb_'+id).src=thumbs[id][thumbs_curr[id]];
	}
	
	if(rotating) setTimeout('change_thumb('+id+')', 750);
}

function adjust_width(base_height,id_block, id_wrapper){
	var orig_height=parseInt($(id_wrapper).css("height"));
	var new_height=(base_height*1)-orig_height;
	document.getElementById(id_block).style.height = new_height+"px";
}
