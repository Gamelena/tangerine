function Ajax(){
	var ajax_box;
	var req;
	
	this.Init=function (){
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
		return req;
	}
	
	this.get=function(url,div){
	  	this.Init();
	  	if(req!=null){
	  		ajax_box=div;
		    req.onreadystatechange = this.getResult;
		    if(ajax_box)document.getElementById(ajax_box).innerHTML="<div style=\"text-align:center\"><img alt=\"Loading...\" src=\"images/loading.gif\" /></div>";
		    url=url.replace(/&amp;/g,'&');
		    req.open("get", base_url+url, true);
		    try {
		       req.send(null);
		    } catch (ex) { if(ajax_box)document.getElementById(ajax_box).innerHTML="Your browser does not support this feature!";}
		}
	}
	

	this.post=function(url,data,div){
		this.Init();
	  	if(req!=null){
	  		ajax_box=div;
		    req.onreadystatechange = this.getResult;
		    if(ajax_box != 'comments_box') 
				document.getElementById(ajax_box).innerHTML="<div style=\"text-align:center\"><img alt=\"Loading...\" src=\"images/loading.gif\" /></div>";
		    url=url.replace(/&amp;/g,'&');
		    req.open("post", base_url+url, true);
		    try {
			   req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		       req.send(data);
		    } catch (ex) { document.getElementById(ajax_box).innerHTML="Your browser does not support this feature!";}
		}
	}
	
	
	this.getResult=function (){
	  if (req.readyState == 4){
	    if (req.status == 200){
	      if(ajax_box!='comments_box'){
		      changeOpacity(0,ajax_box);
		      fadeIn(ajax_box);
	      }
	      document.getElementById(ajax_box).innerHTML=req.responseText;
	    }
	    else {
	        document.getElementById(ajax_box).innerHTML="Internal Server Error!";
	    }
	  }
	}
}

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

function get_url_contents(url,div){
	var ajax=new Ajax();
	ajax.get(url,div);
}


function getResult_alert(){
  if (req.readyState == 4){
    if (req.status == 200){
      alert(req.responseText);
    }
    else {
      alert("Internal Server Error!");
    }
  }
}

