dojo.require("dojox.grid.enhanced.plugins.Menu");
dojo.require("dijit.Menu");
dojo.require("dijit.MenuItem");
dojo.require('dijit.MenuSeparator');
dojo.require('dijit.PopupMenuItem');

dojo.declare("zwei.Menu", dijit.Menu, {
    domPrefix: null,
    
    constructor: function(args){
        dojo.mixin(this, args);
    },
    
    startup: function()
    {
        this.addChild(new dijit.MenuItem({
            label: "Lógica Campaña",
            iconClass: "dijitIconConnector",
            onClick : function(e){
                var items = dijit.byId(domPrefix + 'dataGrid').selection.getSelected();
                if (domPrefix == '') {
                    var cp1 = new dojox.layout.ContentPane({
                        title: "Lógica Campaña " + items[0].nombre,
                        href: base_url + "/campanas/logica?id="+items[0].id,
                        executeScripts: true,
                        closable: true,
                        selected: true,
                        onClose: function(){
                            return confirm("¿Cerrar lógica campaña "+items[0].nombre+"?");
                        }
                    });
                    tabContainerCampanas.addChild(cp1);
                    tabContainerCampanas.selectChild(cp1);
                } else {
                    if (typeof(dijit.byId('mainTabModulelogica' + globalModuleId)) != 'undefined')
                        alert('Debe cerrar lógica de campaña actual \nantes de abrir una nueva lógica de campaña');
                    
                    loadModuleTab("/campanas/logica?id="+items[0].id, 'logica' + globalModuleId /*+ '_' + items[0].id*/, "Lógica Campaña " + items[0].nombre, false);
                }
            }
        }));
        this.addChild(new dijit.MenuItem({
            label: "Causas de Término",
            iconClass: "dijitEditorIcon dijitEditorIconCancel",
            onClick : function(){
                var items = dijit.byId(domPrefix + 'dataGrid').selection.getSelected();
                
                var cp1 = new dojox.layout.ContentPane({
                    title: "Causas de Término " + items[0].nombre,
                    href: base_url + "/campanas/causas-de-termino",
                    executeScripts: true,
                    closable: true,
                    selected: true,
                    onClose: function(){
                        // confirm() returns true or false, so return that.
                        return confirm("¿Cerrar causas de término campaña?");
                    }
                    
               });
                
               tabContainerCampanas.addChild(cp1);
               tabContainerCampanas.selectChild(cp1);
            }
        }));
        this.addChild(new dijit.MenuItem({
            label: "Lista Blanca",
            iconClass: "dijitLeaf",
            onClick : function(){
                var items = dijit.byId(domPrefix + 'dataGrid').selection.getSelected();
                
                var cp1 = new dojox.layout.ContentPane({
                    title: "Lista Blanca " + items[0].nombre,
                    href: base_url + "/campanas/lista-blanca",
                    executeScripts: true,
                    closable: true,
                    selected: true,
                    onClose: function(){
                    // confirm() returns true or false, so return that.
                    return confirm("¿Cerrar lista blanca campaña?");
                    }
                    
               });
                
               tabContainerCampanas.addChild(cp1);
               tabContainerCampanas.selectChild(cp1);
            }
        }));
        this.inherited(arguments)
        this.startup();
    }
    
});

//var zweiMenu = new zwei.Menu();