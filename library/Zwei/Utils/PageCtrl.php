<?php

/**
 * Paginador
 * 
 * @package Zwei_Utils
 * @version $Id:$
 * @since 0.1
 *
 */

class Zwei_Utils_PageCtrl{
  function getCtrl($count,$start,$limit,$url,$ajax=false,$ajax_target="ajax_box",$prefix='',$is_video_comment=false){
  	$isNext=false;
  	$isPrev=false;
    if($start-$limit>=0){
        $prev=$start-$limit;
        $isPrev=true;
    }
   
    $pages=$limit==0?0:ceil($count/$limit);
    $page_numbers="";
    if($pages>10&&$limit>0){
	  $pstart=floor($start/$limit/10)*10;
	  if($pages-$pstart<11)$pcount=$pstart+($pages-$pstart);
	  else $pcount=$pstart+11;
	  if($pstart>0)$pstart--;
	}
    else{
	  $pcount=$pages;
	  $pstart=0;
	}
	if($pstart>0){
		if($ajax)$page_numbers.="<a class=\"pagenum\" href=\"javascript:void(0)\" onclick=\"get_url_contents('$url&start=0','$ajax_target');\">1</a> ";
    	else $page_numbers.="<a class=\"pagenum\" href=\"$url&{$prefix}start=0\">1</a> ";
    	$page_numbers.="<span class=\"pagenum\">...</span> ";
	}
    for($i=$pstart; $i<$pcount; $i++){
      if($i*$limit==$start){
      	$num=$i+1;
      	$page_numbers.="<span class=\"pagenumh\">$num</span> ";
      }else{
        $pos=$i*$limit;
        $num=$i+1;
        
        if($ajax)$page_numbers.="<a class=\"pagenum\" href=\"javascript:void(0)\" onclick=\"get_url_contents('$url&start=$pos','$ajax_target');\">$num</a> ";
        else $page_numbers.="<a class=\"pagenum\" href=\"$url&{$prefix}start=$pos\">$num</a> ";
      }
    }
    if($pages>$pcount){
    	$page_numbers.="<span class=\"pagenum\">...</span> ";
    	if($ajax)$page_numbers.="<a class=\"pagenum\" href=\"javascript:void(0)\" onclick=\"get_url_contents('$url&start=".($pages-1)*$limit."','$ajax_target');\">$pages</a> ";
    	else $page_numbers.="<a class=\"pagenum\" href=\"$url&{$prefix}start=".($pages-1)*$limit."\">$pages</a> ";
    }
    if(($start+$limit)<$count){
        $next=$start+$limit;
        $isNext=true;
    }
    $prev=isset($prev)?$prev:"";
    $next=isset($next)?$next:"";
    $output="";

    
    if ($is_video_comment) {
  		$video_id = $_GET['v'];
  		$prevLimit = $prev/$limit + 1;
  		$nextLimit = $next/$limit + 1;
  		
   	}else{

   	}
    
    
    if($isPrev&&$ajax)$output.="<a class=\"pagenum\" href=\"javascript:void(0)\" onclick=\" get_url_contents('$url&start=$prev','$ajax_target');Prev\">&laquo;</a> ";
    elseif($isPrev)$output.="<a class=\"pagenum\" href=\"$url&{$prefix}start=$prev\">&laquo;</a> ";
    $output.=$page_numbers;
    if($isNext&&$ajax)$output.="<a class=\"pagenum\" href=\"javascript:void(0)\" onclick=\" get_url_contents('$url&start=$next','$ajax_target');Next\">&raquo;</a>";
    elseif($isNext)$output.="<a class=\"pagenum\" href=\"$url&{$prefix}start=$next\">&raquo;</a>";
    return $output;
  }
}
?>