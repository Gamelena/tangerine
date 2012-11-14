function rolesInit()
{
    var domPrefix = (layout != undefined && layout == 'dijitTabs') ? 'roles_xml' : ''; 
        
    console.log('rolesInit');
    //var formDialogo = dijit.byId('formDialogo'); // para explicacion, esto no es necesario ya existe var tabForm global (jsId)
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
                dojo.query('.dijitDialog').style({'min-height':null, height:'auto',width:'auto'});
            });
              
            on(dijit.byId(domPrefix+'formDialogoEditar'), "show", function(e){
                console.debug("formDialogoEditar show");
                dojo.query('.dijitDialog').style({'min-height':null, height:'auto',width:'auto'});
            });
        }, 800);  
    });
}