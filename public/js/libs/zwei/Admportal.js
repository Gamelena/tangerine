dojo.declare("zwei.Admportal", null, {
    intervalListener: null,
    utils: null,
    noCache: null,
    template: 'default',
    constructor: function(args)
    {
        dojo.mixin(this, args);
        if (typeof zwei.Utils != 'undefined') this.utils = new zwei.Utils();
    },
    setTemplate: function (template)
    {
        this.template = template;
    },
    initLoad: function() 
    {
        this.loadEvents();
        this.loadLayoutSettings(dojo.byId("logosAdm"),dojo.byId("tituloAdm"), dojo.byId("zweicomLogo"), dojo.byId("zweicomLegend"));
        this.loadMainMenu();
    },
    loadEvents: function()
    {
        dojo.connect(dojo.byId("btnSalir"), "onclick", function(){
            window.location = base_url+"admin/logout";
        });
        dojo.connect(dijit.byId("tabContainer"), "selectChild", function(page){ 
            globalModuleId = parseInt(page.id.match(/\d+$/)); 
        });
    },
    initListeners: function()
    {
        var self = this;
        this.intervalListener = setInterval(function(){self.listenUserStatus();}, 10000);
    },
    listenUserStatus: function()
    {
        var self = this;
        var xhrArgsUpdateRole = {
            url: base_url + 'events/update-role',
            preventCache: true,
            handleAs: "json",
            load: function(data) {
                if (data.status == 'AUTH_FAILED') {
                    window.location.href = base_url + 'admin/login';
                } else {
                    self.intervalListener = setInterval(function(){self.listenUserStatus();}, 10000);
                }
            }
        };
        
        var xhrArgsEvents = {
            url: base_url + 'events',
            preventCache: true,
            handleAs: "json",
            load: function(data) {
                if (data.status != 'OK') {
                    if (data.status == 'AUTH_FAILED') {
                        window.location.href = base_url + 'admin/login';
                    } else if (data.status == 'ROLE_HAS_CHANGED') {
                        self.loadMainMenu();
                        clearInterval(self.intervalListener);
                        dojo.xhrPost(xhrArgsUpdateRole);
                    } 
                }
            },
            error: function(error){
                window.location.href=base_url + 'admin/login';
            }
        };
        dojo.xhrPost(xhrArgsEvents);
    },
    loadLayoutSettings: function(domLogo, domTitle, domFooterImg, domFooterLegend) 
    {
        if (domLogo == undefined) var domLogo = dojo.byId("logosAdm");
        if (domTitle == undefined) var domTitle = dojo.byId("tituloAdm");
        if (domFooterImg == undefined) var domFooterImg = dojo.byId("zweicomLogo");
        if (domFooterLegend == undefined) var domFooterLegend = dojo.byId("zweicomLegend");
        
        require(["dojo/data/ItemFileReadStore"], function(ItemFileReadStore){
            var store = new ItemFileReadStore({
                url: base_url+'crud-request?model=SettingsModel&format=json'
            });
            
            store.fetch({
                onError:function(errData, request){
                    alert(errData);
                },
                onComplete:function(items){
                    var images='';
                    dojo.forEach(items, function(i)
                    {
                        if (store.getValue(i, "id") == "url_logo_oper"){
                            images += "<img src=\""+base_url+ 'upfiles/corporative/' +store.getValue(i, "value")+ "\" />";
                        } else if(store.getValue(i, "id") == "titulo_adm") {
                            domTitle.innerHTML = store.getValue(i, "value");
                        } else if(store.getValue(i, "id") == "url_logo_zweicom") {
                            domFooterImg.src = base_url+ 'upfiles/corporative/' +store.getValue(i, "value");
                        } else if(store.getValue(i, "id") == "credits") {
                            domFooterLegend.innerHTML = store.getValue(i, "value");
                        }
                    });
                    domLogo.innerHTML = images;
                }
                
            });
        });
    },
    loadMainMenu: function() 
    {
        if (this.template == "cooler") {
            this.loadMenuCooler();
        } else {
            this.loadTree();
        }
    },
    loadMenuCooler: function()
    {
        var self = this;
        if (dijit.byId('arbolPrincipal')) {
            dijit.byId('arbolPrincipal').destroyRecursive(false);
        }
        
        require([
                 "dijit/MenuBar",
                 "dijit/PopupMenuBarItem",
                 "dijit/MenuBarItem",
                 "dijit/Menu",
                 "dijit/MenuItem",
                 "dijit/PopupMenuItem",
                 "dijit/DropDownMenu",
                 "dijit/MenuSeparator",
                 "dojo/dom",
                 "dojo/request",
                 "dojo/json",
                 "dojo/_base/array",
                 "dijit/registry"
             ], function(MenuBar, PopupMenuBarItem, MenuBarItem, Menu, MenuItem, PopupMenuItem, DropDownMenu, MenuSeparator, dom, request, JSON, arrayUtil) {
                 request.post( base_url + 'admin/modules', {
                     data: { format: 'json' }, 
                     handleAs: "json"
                 }).then(
                     function(data) {
                         // Display the data sent from the server
                         var pMenuBar = new MenuBar({id: 'arbolPrincipal'});
                         
                         
                         arrayUtil.forEach(data.items, function(item, i) {
                             pMenuBar.addChild(recursiveMakeMenuItem(item) );
                         });
        
                         pMenuBar.placeAt("mainMenu");
                         pMenuBar.startup();
                         borderContainer.resize();
                         self.myMenu = pMenuBar;
                     },
                     function(error) {
                         console.error(error);
                     }
                 );
                 
                 function recursiveMakeMenuItem(item) {
                     var pSubMenu = item.children ? new DropDownMenu() : new Menu();
                     var widget;
                     
                     menuParams = {id: "dijitEditorMenuModule" + item.id, label: (item.url ? '<span class="linkableItem"><a style="color:inherit">' + item.label +'</a></span>'  : '<span>' + item.label + '</span>')};
                     

                     var loadModule = function() {
                         self.loadModuleTab(item.url, item.id, item.label, item.refresh_on_load, item.image);
                     };
                     
                     if (item.parent_id) {
                         if (item.children) {
                             if (item.url) {
                                 menuParams.onDblClick = loadModule;
                             }
                             widget = new PopupMenuItem(menuParams);
                         } else {
                             if (item.url) {
                                 menuParams.onClick = loadModule;
                             }
                             widget = new MenuItem(menuParams); 
                         }
                         
                         
                     } else {
                         //pSubMenu = new Menu();
                         if (item.url) {
                             if (item.children) {
                                 menuParams.onDblClick = loadModule;
                             } else {
                                 menuParams.onClick = loadModule;
                             }
                         }
                         widget = item.children ? new PopupMenuBarItem(menuParams) : new MenuBarItem(menuParams);

                     }
                     
                     if (item.image) {
                         //widget.set('iconClass', 'dijitEditorIcon');
                         //widget.domNode.style.backgroundImage = "none";
                         widget.domNode.children[0].style.backgroundImage = "url('"+base_url+"/upfiles/16/"+item.image+"')";
                         widget.domNode.children[0].style.backgroundRepeat = "no-repeat";
                         widget.domNode.children[0].style.width = '16px'; 
                         widget.domNode.children[0].style.height = '16px'; 
                         
                         if (!item.parent_id) {
                             widget.domNode.children[0].style.paddingLeft = '18px';
                             widget.domNode.children[0].style.paddingBottom = '1px'; 
                         }
                         
                     }
                     if (item.children) {
                         arrayUtil.forEach(item.children, function(child) {
                             pSubMenu.addChild(recursiveMakeMenuItem(child));
                             
                         });
                         widget.set("popup", pSubMenu);
                     }
                     
                     
                     
                     return widget;
                 }
             });
       
        
    },
    loadTree: function() {
        var self = this;
        
        require(["dojo/data/ItemFileWriteStore", "dijit/Tree", "dijit/tree/ForestStoreModel"], function(ItemFileWriteStore, Tree, ForestStoreModel){
            var store = new ItemFileWriteStore({
                url: base_url + 'admin/modules',
                data: {format: 'json'},
                clearOnClose:true,
                identifier: 'id',
                label: 'label',
                urlPreventCache:true
            });

            if (!dijit.byId('arbolPrincipal')) {
                store.fetch({queryOptions:{deep: true}});
                var treeModel = new ForestStoreModel({
                    store: store
                });
                var treeControl = new Tree({
                    model: treeModel,
                    showRoot: false,
                    persist: true,
                    onClick: function(item){
                        this.openOnClick = false;
                        if (item.url != undefined) {
                            self.loadModuleTab(item.url, item.id, item.label, item.refresh_on_load, item.image);
                        } else {
                            this.openOnClick = true;
                        }     
                    },
                    onClose: function(item){
                        this.openOnClick = false;
                        if (item.url != undefined) {
                            this.openOnClick = false;
                        } else {
                            this.openOnClick = true;
                        }     
                    },
                    _createTreeNode: function(args) {
                        var tnode = new dijit._TreeNode(args);
                        if (typeof args.item.id !== 'undefined') {
                        	var id = 'dijitEditorMenuModule' + args.item.id.toString();
                        	tnode.set('id', id);
                        }
                        tnode.labelNode.innerHTML = args.label;
                        return tnode;
                    },
                    getIconStyle:function(item, opened){
                        if (item.image != undefined && item.image[0] != null && item.image[0] != '') {
                            return {
                                backgroundPosition: 0,
                                backgroundImage: 'url('+base_url + 'upfiles/16/' + item.image[0] +')'
                            }
                        }
                    }
                },
                'arbolPrincipal');
            } else {
                var tree = dijit.byId('arbolPrincipal');
                
                tree.dndController.selectNone();
    
                tree.model.store.clearOnClose = true;
                tree.model.store.close();
    
                // Completely delete every node from the dijit.Tree     
                tree._itemNodesMap = {};
                tree.rootNode.state = "UNCHECKED";
                tree.model.root.children = null;
                //tree.getParent().setContent('<p>&nbsp;&nbsp;Cargando ...</p>');
    
                // Destroy the widget
                tree.rootNode.destroyRecursive();
    
                // Recreate the model, (with the model again)
                tree.model.constructor(dijit.byId('arbolPrincipal').model)
                //tree.getParent().setContent('<p>&nbsp;&nbsp;Cargando ...</p>');
    
                // Rebuild the tree
                tree.postMixInProperties();
                tree._load();        
            }
        });
    },
    switchMainPane: function(){
        if (contentPaneTop.domNode.style.display != 'none') {
            this.maximizeMainPane();
        } else {
            this.minimizeMainPane();
        }
    },
    maximizeMainPane: function(){
        contentPaneTop.domNode.style.display = 'none';
        contentPaneBottom.domNode.style.display = 'none';
        if (typeof menuExpand != 'undefined') menuExpand.domNode.style.display = 'none';
        dijit.byId('borderContainer').resize();
    },
    minimizeMainPane: function(){
        contentPaneTop.domNode.style.display = 'block';
        contentPaneBottom.domNode.style.display = 'block';
        if (typeof menuExpand != 'undefined') menuExpand.domNode.style.display = 'block';
        dijit.byId('borderContainer').resize();
    },
    loadModuleTab: function(url, moduleId, moduleTitle, refreshOnShow, image) 
    {
        if (typeof(refreshOnShow) === 'undefined') var refreshOnShow = false;
        if (typeof(image) === 'undefined') var image = null;
        refreshOnShow = Boolean(parseInt(refreshOnShow));
        
        var self = this;
        require(["dojox/layout/ContentPane", "dojox/html", "dojox/html/styles"], function(ContentPane, html, styles){
            if (image) {
                html.insertCssRule(
                    ".iconClassModule"+moduleId, 
                    "background-image: url("+base_url+"/upfiles/16/"+image+");" +
                    "background-repeat: no-repeat;"+
                    "width:16px;height: 16px;"
                );
            }
            if (!dojo.byId('mainTabModule'+moduleId)) {
                var tab = tabContainer.addChild(
                    new ContentPane({
                        title:moduleTitle, 
                        closable:true, 
                        id: 'mainTabModule'+moduleId, 
                        jsId: 'mainTabModule'+moduleId,
                        parseOnLoad: true,
                        executeScripts: true,
                        scriptHasHooks: true,
                        refreshOnShow: refreshOnShow,
                        href: base_url + url,
                        style: {background: 'transparent', top: '0px'},
                        selected: true,
                        iconClass: image ? "iconClassModule"+moduleId : "dijitNoIcon",
                        onClose: function() {
                            if (tabContainer.getChildren().length == 1) {
                                self.minimizeMainPane();
                            }
                            return true;
                        }
                    })
                );
                tabContainer.selectChild(dijit.byId('mainTabModule' + moduleId));
                
                dojo.connect(dojo.byId('tabContainer_tablist_mainTabModule' + moduleId), 'dblclick', function(){self.switchMainPane();});
                dojo.connect(dijit.byId('mainTabModule' + moduleId), 'resize', function(){
                    
                });
                dojo.byId('tabContainer_tablist_mainTabModule' + moduleId).style.cursor = 'pointer';
            } else {
                if (dijit.byId('mainTabModule' + moduleId).get('selected', true)) {
                    dijit.byId('mainTabModule' + moduleId).refresh();
                } else {
                    tabContainer.selectChild(dijit.byId('mainTabModule' + moduleId));
                }
            }
        });
    },
    loadModuleSingle: function(url, widget, tab) 
    {
        if (typeof widget === 'undefined') var widget = dijit.byId("mainPane");
        widget.set('href', base_url+url);
    },
    switchTabs: function(containerId, tabId, areaId) {
        var tabs = dojo.query('#' + containerId + ' > .settingsTab');
        var areas = dojo.query('#' + containerId + ' > .settingsArea');
        
        for (var i=0; i < tabs.length; i++) {
            //tabs[i].style.background = '';
            dojo.removeClass(tabs[i], 'active');
            areas[i].style.display = 'none';
        }
        
        try {
            dojo.byId(areaId).style.display='block';
            //dojo.byId(tabId).style.background='url("/dojotoolkit/dijit/themes/claro/images/activeGradient.png") #CFE5FA repeat-x';
            dojo.addClass(dojo.byId(tabId), 'active');
        } catch (e) {
            console.debug(areaId, e.message);
        }    
    },
    lockScreen: function(lock) {
        if (typeof lock === 'undefined') var lock = true;
        if (lock) {
            dojo.byId('appLoader').style.opacity = 0.5;
            dojo.byId('appLoader').style.display = 'block';
            dojo.byId('appLoader').style.zIndex = 99999999;
        } else {
            dojo.byId('appLoader').style.display = 'none';
        }
    },
    resizeGrid: function(domPrefix){
        if (dojo.byId(domPrefix + 'contentCenter') && dijit.byId(domPrefix + 'dataGrid')) {
            var paneHeight = dojo.contentBox(dojo.byId(domPrefix + 'contentCenter')).h;
            
            var searcherHeight = dojo.byId(domPrefix + 'formSearch') 
               ? dojo.contentBox(dojo.byId(domPrefix + 'formSearch')).h 
               : 0;
            var gridHeight = paneHeight - searcherHeight - 12;
            dojo.contentBox(dojo.byId(domPrefix + 'dataGrid'), {h: gridHeight});
            dijit.byId(domPrefix + 'dataGrid').resize();
            
            var paginators = dojo.query('#' + domPrefix + 'dataGrid .dojoxGridPaginator')
            
            
            if (paginators.length > 0) {
                var paginator = paginators[0];
                var gridMasterHeader = dojo.query('#' + domPrefix + 'dataGrid .dojoxGridMasterHeader')[0];
                var gridMasterView = dojo.query('#' + domPrefix + 'dataGrid .dojoxGridMasterView')[0];
                var gridView = dojo.query('#' + domPrefix + 'dataGrid .dojoxGridView')[0];
                var gridScrollbox = dojo.query('#' + domPrefix + 'dataGrid .dojoxGridScrollbox')[0];
                
                var gridHeaderHeight = dojo.contentBox(gridMasterHeader).h;
                var paginatorHeight = dojo.contentBox(paginator).h;
                
                var newHeight = gridHeight - gridHeaderHeight - paginatorHeight;
                
                dojo.contentBox(gridMasterView, {h: newHeight});
                dojo.contentBox(gridView, {h: newHeight});
                dojo.contentBox(gridScrollbox, {h: newHeight});
            }
            
        } else {
            console.info('no existen: dojo.byId("' + domPrefix + 'contentCenter") y/o dijit.byId("'+ domPrefix + 'dataGrid")');
        }
        
    },
    execFunction: function(method, params, domPrefix, object, primary){
        if (object == undefined) var object = ''; 
        if (primary == undefined) var primary = 'id'; 

        var uri = escape(dijit.byId(domPrefix+'dataGrid').store.url);

        try {
            var items = dijit.byId(domPrefix + 'dataGrid').selection.getSelected();
            var id = "&"+primary+"="+ items[0][primary];
        } catch(e) {
            console.debug(e);
            var id = '';
        }
        
        document.getElementById('ifrm_process').src=base_url+'functions?method='+method+'&params='+params+id+"&object="+object+"&uri="+uri;
    }
});
