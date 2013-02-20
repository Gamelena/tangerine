<?php
/**
 * Tiny MCE
 *
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Elements_TinyMce extends Zwei_Admin_Elements_Element
{

    public function edit($i, $j) {
        
        $string = 
"<script type=\"text/javascript\" src=\"".BASE_URL."js/libs/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
<script type=\"text/javascript\">
    window.tinyMCEPreInit = {
          suffix : '',
          base : '".BASE_URL."js/libs/tinymce/jscripts/tiny_mce', // your path to tinyMCE
          query : 'something'
      };
    tinyMCE.init({
        // General options
        mode : \"textareas\",
        theme : \"advanced\",
        plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave\",

        // Theme options
        theme_advanced_buttons1 : \"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect\",
        theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\",
        theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
        theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft\",
        theme_advanced_toolbar_location : \"top\",
        theme_advanced_toolbar_align : \"left\",
        theme_advanced_statusbar_location : \"bottom\",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
        // using false to ensure that the default browser settings are used for best Accessibility
        // ACCESSIBILITY SETTINGS
        content_css : false,
        // Use browser preferred colors for dialogs.
        browser_preferred_colors : true,
        detect_highcontrast : true,

        // Drop lists for link/image/media/template dialogs
		template_external_list_url : \"lists/template_list.js\",
		external_link_list_url : \"lists/link_list.js\",
		external_image_list_url : \"lists/image_list.js\",
		media_external_list_url : \"lists/media_list.js\",

        // Style formats
        style_formats : [
            {title : 'Bold text', inline : 'b'},
            {title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
            {title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
            {title : 'Example 1', inline : 'span', classes : 'example1'},
            {title : 'Example 2', inline : 'span', classes : 'example2'},
            {title : 'Table styles'},
            {title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
        ],

        // Replace values for the template plugin
        template_replace_values : {
            username : \"Some User\",
            staffid : \"991234\"
        }
    });
    base_url = '".BASE_URL."';
</script>
<!-- /TinyMCE -->
        <!-- Gets replaced with TinyMCE, remember HTML in a textarea should be encoded -->
        <div>
            <textarea id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" rows=\"15\" cols=\"80\" style=\"width: 80%\">
                &lt;p&gt;
                    Texto de Ejemplo &lt;strong&gt;Zweicom&lt;/strong&gt;.
                &lt;/p&gt;
                &lt;p&gt;
                Nam nisi elit, cursus in rhoncus sit amet, pulvinar laoreet leo. Nam sed lectus quam, ut sagittis tellus. Quisque dignissim mauris a augue rutrum tempor. Donec vitae purus nec massa vestibulum ornare sit amet id tellus. Nunc quam mauris, fermentum nec lacinia eget, sollicitudin nec ante. Aliquam molestie volutpat dapibus. Nunc interdum viverra sodales. Morbi laoreet pulvinar gravida. Quisque ut turpis sagittis nunc accumsan vehicula. Duis elementum congue ultrices. Cras faucibus feugiat arcu quis lacinia. In hac habitasse platea dictumst. Pellentesque fermentum magna sit amet tellus varius ullamcorper. Vestibulum at urna augue, eget varius neque. Fusce facilisis venenatis dapibus. Integer non sem at arcu euismod tempor nec sed nisl. Morbi ultricies, mauris ut ultricies adipiscing, felis odio condimentum massa, et luctus est nunc nec eros.
                &lt;/p&gt;
            </textarea>
        </div>
        
                <!-- Some integration calls -->
        <a href=\"javascript:;\" onclick=\"tinyMCE.get('edit{$i}_{$j}').show();return false;\">[Mostrar]</a>
        <a href=\"javascript:;\" onclick=\"tinyMCE.get('edit{$i}_{$j}').hide();return false;\">[Esconder]</a>
        <a href=\"javascript:;\" onclick=\"tinyMCE.get('edit{$i}_{$j}').execCommand('Bold');return false;\">[Negrita]</a>
        <a href=\"javascript:;\" onclick=\"alert(tinyMCE.get('edit{$i}_{$j}').getContent());return false;\">[Obtener contenido]</a>
        <a href=\"javascript:;\" onclick=\"alert(tinyMCE.get('edit{$i}_{$j}').selection.getContent());return false;\">[Obtener HTML seleccionado]</a>
        <a href=\"javascript:;\" onclick=\"alert(tinyMCE.get('edit{$i}_{$j}').selection.getContent({format : 'text'}));return false;\">[Obtener texto seleccionado]</a>
        <a href=\"javascript:;\" onclick=\"alert(tinyMCE.get('edit{$i}_{$j}').selection.getNode().nodeName);return false;\">[Get selected element]</a>
        <a href=\"javascript:;\" onclick=\"tinyMCE.execCommand('mceInsertContent',false,'<b>Hello world!!</b>');return false;\">[Insertar HTML]</a>
        <a href=\"javascript:;\" onclick=\"tinyMCE.execCommand('mceReplaceContent',false,'<b>{\$selection}</b>');return false;\">[Reemplazar Selección]</a>
        
";
        
        return $string;


        }
}