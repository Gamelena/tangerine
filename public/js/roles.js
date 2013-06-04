function rolesInit()
{
    var domPrefix = (layout != undefined && layout == 'dijitTabs') ? 'roles_xml' : ''; 
        
    console.log('rolesInit');
   
    require(["dojo/on"], function(on){
        setTimeout(function() {
            on(dijit.byId(domPrefix+'formDialogo'), "hide", function(e){
                console.debug("formDialogo hide");
                dojo.query('.dijitDialog').style({'min-height':'416px',width:'424px'});
            });    
              
            on(dijit.byId(domPrefix+'formDialogoEditar'), "hide", function(e){
                console.debug("formDialogoEditar hide");
                dojo.query('.dijitDialog').style({'min-height':'416px',width:'424px'});
            });
              
            on(dijit.byId(domPrefix+'formDialogo'), "show", function(e){
                console.debug("formDialogo show");
                setTimeout(function() {
                    dojo.query('.dijitDialog').style({'min-height':null, height:'auto',width:'auto'});
                }, 1800);      
            });
              
            on(dijit.byId(domPrefix+'formDialogoEditar'), "show", function(e){
                console.debug("formDialogoEditar show");
                setTimeout(function() {
                    dojo.query('.dijitDialog').style({'min-height':null, height:'auto',width:'auto'});
                }, 1800);    
            });
        }, 800);  
    });
}
