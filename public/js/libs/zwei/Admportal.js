dojo.declare("zwei.Admportal", null, {
    constructor: function(args){
        dojo.mixin(this, args);
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
    loadLayoutSettings: function(domLogo, domTitle, domFooterImg, domFooterLegend) 
    {
        var store = new dojo.data.ItemFileReadStore({
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
                        images += "<img src=\""+base_url+ '/upfiles/' +store.getValue(i, "value")+ "\" />";
                    } else if(store.getValue(i, "id") == "titulo_adm") {
                        domTitle.innerHTML = store.getValue(i, "value");
                    } else if(store.getValue(i, "id") == "url_logo_zweicom") {
                        domFooterImg.src = base_url+ '/upfiles/' +store.getValue(i, "value");
                    } else if(store.getValue(i, "id") == "credits") {
                        domFooterLegend.innerHTML = store.getValue(i, "value");
                    }
                    
                });
                domLogo.innerHTML = images;
            }
            
        });    
    },
    loadMainMenu: function(layout) 
    {
        if (layout == undefined) var layout = false;
        var self = this;
        var store = new dojo.data.ItemFileWriteStore({
            url: base_url + 'admin/modules?format=json',
            clearOnClose:true,
            identifier: 'id',
            label: 'label',
            urlPreventCache:true
        });
        store.fetch({
            onComplete: function(items, request){
                if(items) {
                    var i;
                }
            },
            queryOptions: {
                deep: true
            }
        });
        var treeModel = new dijit.tree.ForestStoreModel({
            store: store
        });
        if (!dijit.byId('arbolPrincipal')) {
            var treeControl = new dijit.Tree({
                model: treeModel,
                showRoot: false,
                persist: true,
                openOnClick: true,
                onClick: function(item){
                    if (item.url != undefined) {
                        if (layout == 'dijitTabs') {
                            self.loadModuleTab(item.url, item.id, item.label, item.refresh_on_load);
                        } else {
                            self.loadModuleSingle(item.url);
                        }    
                    } else {
                        return false;
                    }     
                },
                _createTreeNode: function(
                    args) {
                    var tnode = new dijit._TreeNode(args);
                    tnode.labelNode.innerHTML = args.label;
                    return tnode;
                },
                getIconStyle:function(item, opened){
                    if (item.image != undefined && item.image[0] != null) {
                        return {
                            backgroundPosition: 0,
                            backgroundImage: 'url('+base_url + 'upfiles/16/' + item.image[0] +')'
                        }
                    }
                }
            },
            'arbolPrincipal');
        } else {
            var Tree = dijit.byId('arbolPrincipal');
            
            Tree.dndController.selectNone();

            Tree.model.store.clearOnClose = true;
            Tree.model.store.close();

            // Completely delete every node from the dijit.Tree     
            Tree._itemNodesMap = {};
            Tree.rootNode.state = "UNCHECKED";
            Tree.model.root.children = null;

            // Destroy the widget
            Tree.rootNode.destroyRecursive();

            // Recreate the model, (with the model again)
            Tree.model.constructor(dijit.byId('arbolPrincipal').model)

            // Rebuild the tree
            Tree.postMixInProperties();
            Tree._load();        
        }    
    },
    switchMainPane: function(){
        if (contentPaneTop.domNode.style.display != 'none') {
            this.maximizeMainPane();
        } else {
            this.minimizeMainPane();
        }
    },
    maximizeMainPane: function(){
        contentPaneTop.domNode.style.display='none';
        contentPaneBottom.domNode.style.display='none';
        menuExpand.domNode.style.display='none';
        dijit.byId('borderContainer').resize();
    },
    minimizeMainPane: function(){
        contentPaneTop.domNode.style.display='block';
        contentPaneBottom.domNode.style.display='block';
        menuExpand.domNode.style.display='block';
        dijit.byId('borderContainer').resize();
    },
    loadModuleTab: function(url, moduleId, moduleTitle, refresh) 
    {
        var self = this;
        if (typeof(refresh) == 'undefined') refresh = true;
        if (!dojo.byId('mainTabModule'+moduleId)) {
            var tab = tabContainer.addChild(
                    new dojox.layout.ContentPane({
                        title:moduleTitle, 
                        closable:true, 
                        id: 'mainTabModule'+moduleId, 
                        jsId: 'mainTabModule'+moduleId,
                        parseOnLoad: true,
                        executeScripts: true,
                        scriptHasHooks: true,
                        refreshOnShow: refresh,
                        href: base_url+url,
                        style: {background: 'transparent', top: '0px'},
                        selected: true
                    })
            );
            tabContainer.selectChild(dijit.byId('mainTabModule'+moduleId));
            
            dojo.connect(dojo.byId('tabContainer_tablist_mainTabModule'+moduleId), 'dblclick', function(){self.switchMainPane();});
            dojo.byId('tabContainer_tablist_mainTabModule'+moduleId).style.cursor = 'pointer';
        } else {
            console.debug('ya esta abierto mainTabModule '+moduleId);
            //dijit.byId('mainTabModule'+moduleId).set('selected', true);
            tabContainer.selectChild(dijit.byId('mainTabModule'+moduleId));
        }
    },
    loadModuleSingle: function(url, moduleId, moduleTitle) 
    {
        var widget = dijit.byId("panel_central");
        widget.set('href', base_url+url);
    },
    switchTabs: function(containerId, tabId, areaId) {
        var tabs = dojo.query('#' + containerId + ' > .settingsTab');
        var areas = dojo.query('#' + containerId + ' > .settingsArea');
        
        for (var i=0; i < tabs.length; i++) {
            tabs[i].style.background = '';
            areas[i].style.display = 'none';
        }
        
        try {
            dojo.byId(areaId).style.display='block';
            dojo.byId(tabId).style.background='url("/dojotoolkit/dijit/themes/claro/images/activeGradient.png") #CFE5FA repeat-x';
        } catch (e) {
            console.debug(e.message);
        }    
    },
    execFunction: function(method, params, object, primary, domPrefix){
        if (primary == undefined) var primary = 'id'; 
        if (domPrefix == undefined) var domPrefix = '';

        try {
            var items = dijit.byId(domPrefix + 'dataGrid').selection.getSelected();
            var id = "&"+primary+"="+ items[0][primary];
        } catch(e) {
            console.debug(e);
            var id = '';
        }    
        document.getElementById('ifrm_process').src=base_url+'functions?method='+method+'&params='+params+id+"&object="+object+"&uri="+escape(dijit.byId(domPrefix+'dataGrid').store.url);
    }
});