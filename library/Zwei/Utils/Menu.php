<?php
/**
* Menu desplegable basado en Jquery
*
* 
* @package Zwei_Utils
* @version $Id:$
* @since 0.1
*/

class Zwei_Utils_Menu
{
    private $_out;
    
    /**
     * 
     * @param array $tree, arreglo con índices url y label con el cual se contruirá el menú.
     */
    function __construct($tree)
    {
        $this->_out="
            <script type=\"text/javascript\"> 
            $(document).ready(function(){
                $('ul.subnav').parent().append('<span></span>'); //Only shows drop down trigger when js is enabled - Adds empty span tag after ul.subnav
                $('ul.topnav li span').click(function() { //When trigger is clicked...
                
                //Following events are applied to the subnav itself (moving subnav up and down)
                $(this).parent().find('ul.subnav').slideDown('fast').show(); //Drop down the subnav on click
        
                $(this).parent().hover(function() {
                }, function(){  
                    $(this).parent().find('ul.subnav').slideUp('slow'); //When the mouse hovers out of the subnav, move it back up
                });
        
                //Following events are applied to the trigger (Hover events for the trigger)
                }).hover(function() { 
                    $(this).addClass('subhover'); //On hover over, add class 'subhover'
                }, function(){  //On Hover Out
                    $(this).removeClass('subhover'); //On hover out, remove class 'subhover'
            });
        
        });
        </script>
        ";
        $this->_out.='<ul class="topnav">';
        foreach($tree as $t){
            $this->_out.='<li><a href="'.BASE_URL.@$t['url'].'">'.htmlentities($t['label']).'</a>';
            if(isset($t['children'])){
                $this->_out.='<ul class="subnav">';
                foreach($t['children'] as $t2){
                    $this->_out.='<li><a href="'.BASE_URL.@$t2['url'].'">'.htmlentities($t2['label']).'</a>';
                }
                $this->_out.='</ul>';
            }
            $this->_out.='</li>';
            
        }
        
        $this->_outX.='
                <li><a href="#">Home</a></li>
                <li>
                    <a href="#">Tutorials</a>
    
                    <ul class="subnav">
                        <li><a href="#">Sub Nav Link</a></li>
                        <li><a href="#">Sub Nav Link</a></li>
                        <li><a href="#">Sub Nav Link</a></li>
                        <li><a href="#">Sub Nav Link</a></li>
                        <li><a href="#">Sub Nav Link</a></li>
    
                    </ul>
                </li>
                <li>
                    <a href="#">Resources</a>
                    <ul class="subnav">
                        <li><a href="#">Sub Nav Link</a></li>
                        <li><a href="#">Sub Nav Link</a></li>
    
                        <li><a href="#">Sub Nav Link</a></li>
                        <li><a href="#">Sub Nav Link</a></li>
                        <li><a href="#">Sub Nav Link</a></li>
                        <li><a href="#">Sub Nav Link</a></li>
                    </ul>
                </li>
                <li><a href="#">About Us</a></li>
    
                <li><a href="#">Advertise</a></li>
                <li><a href="#">Submit</a></li>
                <li><a href="#">Contact Us</a></li>';
        $this->_out.='</ul>';
    }
    
    /**
     * Despliega el menú ya construido.
     * @return string html
     */
    
    public function display(){
        return $this->_out;
    }
}

