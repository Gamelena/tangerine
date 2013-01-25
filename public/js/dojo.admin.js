var global_opc;
var globalModuleId;
var globalModule;

dojo.declare(
        "dijit.form.ValidationTextarea",
        [dijit.form.ValidationTextBox, dijit.form.SimpleTextarea],
        {
          invalidMessage: "\u00e9ste campo es requerido",

          firstValidation: true,

          textbox: function() {
            return "";
          },

          postCreate: function() {
            this.inherited(arguments);
            dojo.connect(this.containerNode, 'onkeyup', this, 'validate');
          },

          isValid: function() {
            this.inherited(arguments);
            //the 'true' part of this is to allow validation override
            return arguments[0] == true || this.containerNode.value.length > 0;
          },

          validate: function() {
            this.inherited(arguments);
            var isValid = this.isValid(arguments[0])
            
            if (!isValid) {
              this.displayMessage(this.getErrorMessage());
            }
            return isValid;
          },

          onFocus: function() {
            this.validate(this.firstValidation);
            //make sure the first time they click on the box 
            //the user don't get an error
            if (this.firstValidation) {
              this.firstValidation = false;
            }
          },

          onBlur: function() {
            //pass in true so that the error bubble goes away
            this.validate(true);
          }
        }
);



function eventos() 
{
    dojo.connect(dojo.byId("btnSalir"), "onclick", function(){
        window.location = base_url+"index/logout";
    });
    dojo.connect(dijit.byId("tabContainer"), "selectChild", function(page){ 
        globalModuleId = parseInt(page.id.match(/\d+$/)); 
    });
}


function initLoad(layout) 
{
    eventos();
    //Configuracion de titulo, logos de operadora y zweicom
    cargarDatosTituloAdm(dojo.byId("logosAdm"),dojo.byId("tituloAdm"));
    
    //Carga del panel lateral
    cargarArbolMenu(layout);
}


function cargarDatosTituloAdm(bloqueLogoHTML, bloqueTituloHTML) 
{
    //Obtencion via AJAX de datos basicos del administrador web
    var store = new dojo.data.ItemFileReadStore({
        url: base_url+'objects?model=settings&format=json'
    });
    
    store.fetch({
        onError:function(errData, request){
            //console.log(errData+"-"+request);
            alert(errData);
        },
        onComplete:function(items){
             var images='';
                dojo.forEach(items, function(i)
                {
                    
                    if(store.getValue(i,"id")=="url_logo_oper"){
                        images += "<img src=\""+base_url+ store.getValue(i,"value")+ "\" />";
     
                    }else if(store.getValue(i,"id")=="titulo_adm"){
                        bloqueTituloHTML.innerHTML = store.getValue(i,"value");                         
                    }    
                });
             bloqueLogoHTML.innerHTML = images;
        }
        
    });    
}


function cargarPanelCentral(url, moduleId, moduleTitle) 
{
    var widget = dijit.byId("panel_central");
    widget.set('href',base_url+url);
}


function loadModuleTab(url, moduleId, moduleTitle) 
{
    if (!dojo.byId('mainTabModule'+moduleId)) {
        //require(["dijit/registry", "dijit/layout/ContentPane"], function(registry, ContentPane){
            

            if (dijit.byId('mainTabModule'+moduleId)) dijit.byId('mainTabModule'+moduleId).destroy();
            
            var tab = tabContainer.addChild(
                    new dojox.layout.ContentPane({
                        title:moduleTitle, 
                        closable:true, 
                        id: 'mainTabModule'+moduleId, 
                        jsId: 'mainTabModule'+moduleId,
                        executeScripts: true,
                        scriptHasHooks: true,
                        href: base_url+url,
                        style: {background: 'transparent', top: 0},
                        selected: true
                    })
            );
            //var tabs = registry.byId("mainTabModule");
            //tabs.selectChild(tab);
            tabContainer.selectChild(dijit.byId('mainTabModule'+moduleId));
            //dijit.byId('mainTabModule'+moduleId).set('selected', true);
            //dijit.byId('mainTabModule'+moduleId).style({top: 0});
        //});    
    } else {
        console.debug('ya esta abierto mainTabModule '+moduleId);
        //dijit.byId('mainTabModule'+moduleId).set('selected', true);
        tabContainer.selectChild(dijit.byId('mainTabModule'+moduleId));
    }
}

function loadModuleByConfig(url, moduleId, moduleTitle) {
    if (layout != undefined && layout == 'dijitTabs') {
        cargarPanelCentral(url, moduleId, moduleTitle);
    } else {
        loadModuleTab(url, moduleId, moduleTitle); 
    }    
}

function loadModuleInSelfTab(url)
{
    console.debug(globalModuleId);
    dijit.byId('mainTabModule'+globalModuleId).href = base_url+url;
    dijit.byId('mainTabModule'+globalModuleId).refresh();
}


function cargarArbolMenu(layout) 
{
    if (layout == undefined) var layout = false;
    
    var store = new dojo.data.ItemFileWriteStore({
        url: base_url+'index/modules?format=json',
        clearOnClose:true,
        identifier: 'id',
        label: 'label',
        urlPreventCache:true
    });

    
    store.fetch({
        onComplete: function(items, request){
            if(items) {
                var i;
                for (i = 0; i < items.length; i++) {
                    var item = items[i];
                  }
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
                        loadModuleTab(item.url, item.id, item.label);
                    } else {
                        cargarPanelCentral(item.url);
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
}


function cargarTabsPanelCentral(component, action, primary, domPrefix)
{
    global_opc = action;
    //if (iframe == undefined) var iframe = false;
    if (primary == undefined) var primary = "id";
    if (domPrefix == undefined) var domPrefix = "";
    
    var formDlg;
    if (action == 'edit') formDlg = dijit.byId(domPrefix + 'formDialogoEditar');
    //else if (action == 'clone') formDlg = dijit.byId('formDialogoClonar');
    else formDlg = dijit.byId(domPrefix + 'formDialogo');

    if (action == 'edit' || action == 'clone') {
        try {
            console.debug(domPrefix + 'main_grid');
            var items = dijit.byId(domPrefix + 'main_grid').selection.getSelected();
            var id = "&"+primary+"="+ eval("items[0]."+primary);
            console.debug(id);
        } catch(e) {
            console.debug(e);
            alert('Debe seleccionar un registro');
            return;
        }
    } else {
        var id = '';
    }    
    
    //if (iframe) { //ifrmDlg!?
    //    ifrmDlg.src = base_url+'index/tabs?p='+component+'&action='+action+id+"&is_iframe=true";
    //} else {
        formDlg.set('href',base_url+'index/tabs?p='+component+'&action='+action+id);
    //}    
    formDlg.show();
}



function cargarDatos(model, search_in_fields, format_date, cast, between, formato, component, domPrefix) 
{
    if(search_in_fields==undefined)var search_in_fields=false;
    if(format_date==undefined)var format_date=false;
    if(cast==undefined)var cast=false;
    if(between==undefined)var between=false;
    if(formato==undefined)var formato='json';
    if(component==undefined)var component=false;
    if(domPrefix==undefined)var domPrefix='';    
    
    try{
        var search = dijit.byId(domPrefix+"search").get("value");
        if(format_date){
            search = dojo.date.locale.format(search, {datePattern: "yyyy-MM-dd", selector: "date"});
            if(dijit.byId(domPrefix+"search2") != undefined){
                var search2 = dijit.byId(domPrefix+"search2").get("value");
                search2 = dojo.date.locale.format(search2, {datePattern: "yyyy-MM-dd", selector: "date"});
                search+=";"+search2;
            }    
        }    
    }catch(e){
        var search = "";
    }
    
    try{
        var id = "&id="+document.getElementById(domPrefix+"id").value;
    }catch(e){
        var id="";
    }
    
    var search_url;
    var search_fields='';

    if(search_in_fields){
        var search_fields_lenght;
        try{
            search_fields_length = dojo.byId(domPrefix+'search_form').elements['search_fields'].length;
        }catch(e){
            console.debug(e);
            search_fields_length = 0;
        }
        
        for(var i = 0; i < search_fields_length; i++)
        {
            if(dojo.byId(domPrefix+'search_form').elements['search_fields'][i].checked)
            {
                   search_fields+=dojo.byId(domPrefix+'search_form').elements['search_fields'][i].value+';';
               }
           }
        search_fields=(search_fields!='')? '&search_fields='+search_fields:'';
    }
    
    var search_format;
    
    try{
        search_format='&search_format='+ document.getElementById(domPrefix+'search_format[0]').value;
    }catch(e){
        search_format='';
    }    
    
    search_between=between?'&between=1':'';
    component_param=component!=false?'&p='+component:'';
    
    search_url = base_url+'objects?model='+model+'&format='+formato+'&search='+search+search_fields+id+search_format+search_between+component_param;
    
    try{
        dojo.byId(domPrefix+'data_url').value=search_url;
    }catch(e){}    
    
 
    if(formato=='excel'){
        dojo.byId('ifrm_process').src=search_url;
    }else{
        console.debug(domPrefix+'main_grid');
        var store = new dojo.data.ItemFileWriteStore({
            url: search_url,
            clearOnClose: true,
            urlPreventCache: true
        });
        console.debug(domPrefix+'main_grid');
        dijit.byId(domPrefix+'main_grid').setStore(store);       
    }    
}


function searchMultiple(model, fields, search_format,  between, response_format, component, domPrefix, storeType)
{
    if (response_format==undefined) {var response_format='json';}
    if (search_format==undefined) {var search_format=false;}
    if (between==undefined) {var between=false;}
    if (component==undefined) {var component=false;}
    if (domPrefix==undefined) {var domPrefix='';}
    if (storeType==undefined) {var storeType=false;}    
    
    var form=dojo.byId(domPrefix+'search_form');
    var search='';
    var aux_search;
    
    try{
        dojo.forEach(form.elements, function(entry, i){
            
            if(entry.id!=''){
              //console.debug(entry.id);    
              if(dijit.byId(entry.id).get('declaredClass')=='dijit.form.FilteringSelect'){    
                  search+=dijit.byId(entry.id).get('value');
                  search+=';';
                
              }else if(dijit.byId(entry.id).get('declaredClass')=='dijit.form.DateTextBox'){
    
                  aux_search=dijit.byId(entry.id).get('value');
                  search+= dojo.date.locale.format(aux_search, {datePattern: "yyyy-MM-dd", selector: "date"});
                  search+=';';
    
              }else{
                  
                  search+=document.getElementById(entry.id).value;
                  search+=';';
              }
           }
        
        });
    }catch(e){
        console.debug(e);
    }
    
    if(search_format) search_format='&search_format='+search_format;    
    search_between=between!=false?'&between='+between:'';
    
    var search_url = base_url+'objects?model='+model+'&search='+search+'&search_fields='+fields+'&format='+response_format+'&'+search_format+search_between+'&p='+component+'&search_type=multiple';
    
    try{
        dojo.byId(domPrefix+'data_url').value=search_url;
    }catch(e){}    
    
    
    if(response_format=='excel'){
        dojo.byId('ifrm_process').src=search_url;
    }else{
        if (storeType == 'query') {
            var store = new dojox.data.QueryReadStore({
                url: search_url,
                clearOnClose: true,
                urlPreventCache: true
            });
        } else {
            var store = new dojo.data.ItemFileReadStore({
                url: search_url,
                clearOnClose: true,
                urlPreventCache: true
            });            
        }    
        dijit.byId(domPrefix+'main_grid').setStore(store);       
    }    
    //console.debug(form.elements);
}

function loadDataUrl(model, fields, search_format,  between, response_format, domPrefix)
{
    if (response_format==undefined) {var response_format='json';}
    if (search_format==undefined) {var search_format=false;}
    if (between==undefined) {var between=false;}
    if (domPrefix==undefined) {var domPrefix='';}
    
    if (search_format) search_format='&search_format='+search_format;    
    var search_between=between!=false?'&between='+between:'';
    var search = '';
    
    var form=dojo.byId(domPrefix+'search_form');
    console.debug(domPrefix+'search_form');
    dojo.forEach(form.elements, function(entry, i){
        
        if(entry.id!=''){
          //console.debug(entry.id);    
          if(dijit.byId(entry.id).get('declaredClass')=='dijit.form.FilteringSelect'){    
              search += dijit.byId(entry.id).get('value');
              search += ';';
            
          }else if(dijit.byId(entry.id).get('declaredClass')=='dijit.form.DateTextBox'){

              aux_search = dijit.byId(entry.id).get('value');
              search += dojo.date.locale.format(aux_search, {datePattern: "yyyy-MM-dd", selector: "date"});
              search += ';';

          }else{
              
              search += document.getElementById(entry.id).value;
              search += ';';
          }
       }
    
    });
    
    var search_url = base_url+'objects?model='+model+'&search='+search+'&search_fields='+fields+'&format='+response_format+'&'+search_format+search_between+'&search_type=multiple';
    
       dojo.byId(domPrefix+'data_url').value = search_url;
}


function eliminar(model, primary, domPrefix)
{
    if (domPrefix == undefined) domPrefix = "";
    var items = dijit.byId(domPrefix+'main_grid').selection.getSelected();
    if(confirm('Desea eliminar el registro seleccionado?')) {
        eval("eliminarRegistro(model, items[0]."+primary+", '"+ domPrefix+"')");
        //main_grid.removeSelectedRows();
    }
}

function eliminarRegistro(model, id, domPrefix) {
    if (domPrefix == undefined) domPrefix = "";
    var res = '';
    dojo.xhrPost( {
        url: base_url+'objects',
        content: {
            'action' :'delete',
            'id'  : id,
            'model': model,
               'format': 'json' 
        },
        handleAs: 'json',
        sync: true,
        preventCache: true,
        timeout: 5000,
        load: function(respuesta){

            resp = respuesta;
            
            if(resp.message != "" && resp.message != null){
                alert(resp.message);
            
            }else if(resp.state == 'DELETE_OK'){
                alert('Se ha borrado correctamente.');
                //main_grid.removeSelectedRows();
                cargarDatos(model, false, false, false, false, false, false, domPrefix);
            }else if(resp.state == 'DELETE_FAIL'){
                alert('Ha ocurrido un error, verifique datos o intente más tarde');
            }

            return respuesta;
        },
        error:function(err){
            alert('Error en comunicacion de datos. error: '+err);
            //window.location.href = base_url+'index/login';
            return err;
        }
    });
    return res;
}


function execFunction(method, params, object, primary, domPrefix){
    if (primary == undefined) var primary = 'id'; 
    if (domPrefix == undefined) var domPrefix = '';

    try {
        var items = dijit.byId(domPrefix + 'main_grid').selection.getSelected();
        var id = "&"+primary+"="+ eval("items[0]."+primary);
    } catch(e) {
        console.debug(e);
        var id = '';
    }    
    document.getElementById('ifrm_process').src=base_url+'functions?method='+method+'&params='+params+id+"&object="+object+"&uri="+escape(dojo.byId(domPrefix+'data_url').value);
}

/*
function popupGrid(module, iframe, primary, i){
    if (primary == undefined) var primary = 'id'; 
    if (iframe == undefined) var iframe=false;
    if (i == undefined) var i = '0';
    var formDlg = dijit.byId('formDialogo'+i);
    
    try{
        var items = main_grid.selection.getSelected();
        var id = "&"+primary+"="+ eval("items[0]."+primary);
    }catch(e){
        var id = '';
    }


    //if(iframe){
    //    popupIframe(module+id+"&uri="+escape(dojo.byId('data_url').value)); 
    //}else{
        //popupIframe(module+id+"&uri="+escape(dojo.byId('data_url').value));//[FIXME] implementar lo de abajo
        formDlg.set('href', base_url+module+id+"&uri="+escape(dojo.byId('data_url').value));
        formDlg.show();
        //popup(module+id+"&uri="+escape(dojo.byId('data_url').value)); 
    //}
        
}
*/

function popupGrid(module, iframe, primary, title, domPrefix){ 
    if (primary == undefined) var primary = 'id'; 
    if (iframe == undefined) var iframe=false;
    if (domPrefix == undefined) var domPrefix='';
    //if (title == undefined) var i = '0';

    var formDlg = dijit.byId(domPrefix+'formDialogo0');
    
    try{
        var items = dijit.byId(domPrefix+'main_grid').selection.getSelected();
        var id = "&"+primary+"="+ eval("items[0]."+primary);
    }catch(e){
        var id = '';
    }

    if (iframe) {
        //popupIframe(module+id+"&uri="+escape(dojo.byId('data_url').value));
        formDlg.set('html', '');
        formDlg.set('content', '');
        if (title) formDlg.set('title', title);
        
        var iframe = document.createElement("iframe");
        iframe.src = base_url+module+id+"&uri="+escape(dojo.byId(domPrefix+'data_url').value);
        //iframe.src = base_url+'ajax/loading';
        iframe.width='810';
        iframe.height='593';
        iframe.frameBorder='0';
        iframe.id='ifrm_popup';
        iframe.name='ifrm_popup';
        iframe.style.marginTop='0px';
        iframe.style.marginBottom='0px';
        iframe.style.marginLeft='auto';
        iframe.style.marginRight='auto';    

        
        var domDlg = document.getElementById(domPrefix+'formDialogo0');
        //domDlg.innerHTML='';
        if (!document.getElementById("ifrm_popup")) {
            console.log('no hay iframe');
            domDlg.appendChild(iframe);
            document.getElementById(domPrefix+'formDialogo0').style.background = 'url('+base_url+'"css/i/loading.gif) #ffffff;';
            document.getElementById(domPrefix+'formDialogo0').style.backgroundPosition = 'center';
            
        } else {
            console.log('hay iframe');
            document.getElementById(domPrefix+'formDialogo0').style.background = '#ffffff;';
            if (window.frames["ifrm_popup"].document.getElementById('loading_overlay')) {
                window.frames["ifrm_popup"].document.getElementById('loading_overlay').style.display='block';
            }
            
            document.getElementById("ifrm_popup").src = base_url+module+id+"&uri="+escape(dojo.byId(domPrefix+'data_url').value);
        }    
        
        
        formDlg.show();
        
        
    } else {
        //popupIframe(module+id+"&uri="+escape(dojo.byId('data_url').value));//[FIXME] implementar lo de abajo
        if (title) formDlg.set('title', title);
        formDlg.set('href', base_url+module+id+"&uri="+escape(dojo.byId(domPrefix+'data_url').value));
        formDlg.show();
        //popup(module+id+"&uri="+escape(dojo.byId('data_url').value)); 
    }
        
}


function redirectToModule(url, domPrefix){
    if (primary == undefined) var primary = 'id';
    if (domPrefix == undefined) var domPrefix = ''; 
    try{
        var items = dijit.byId(domPrefix + 'main_grid').selection.getSelected();
        var id = "&"+primary+"="+ eval("items[0]."+primary);    
    } catch(e) {
        var id='';
    }
    if (domPrefix == '') {
        var widget = dijit.byId("panel_central");
        widget.set('href',base_url+url);
    } else {    
        loadModuleByConfig(url, globalModuleId, '');
    }
}

function execFunctionPopup(method, params, must_select, primary, domPrefix){
    if (domPrefix == undefined) var domPrefix = ''; 
    if (primary == undefined) var primary = 'id'
    try{
        var items = dijit.byId(domPrefix + 'main_grid').selection.getSelected();
        var id = "&"+primary+"="+ eval("items[0]."+primary);
    }catch(e){
        if(must_select){
            alert('Debe seleccionar una fila');
        }        
    }    
    popup(base_url+"functions?method="+method+"&params="+params+id);
}


function exportAll() {
    dijit.byId("main_grid").exportGrid("csv", function(str) {
        dojo.byId("output_grid").value = str;
    });
};

function exportSelected() {
    var str = dijit.byId("main_grid").exportSelected("csv");
    dojo.byId("output_grid").value = str;
};

function formatYesNo(valCell) {
    if (typeof valCell  == "undefined"){
        return valCell;
    }
    var val = valCell;
    if (valCell == '1') {val = 'Sí';}    
    else {val = 'No';}
    return val;
}

function limitText(limitField, limitCount, limitNum)
{
    if (limitField.value.length > limitNum) {
        limitField.value = limitField.value.substring(0, limitNum);
    } else {
        limitCount.value = limitNum - limitField.value.length;
    }
}

function limitTextDijit(limitField, limitCount, limitNum)
{
    if (limitField.get('value').length > limitNum) {
        limitField.set('value', limitField.get('value').substring(0, limitNum));
    } else {
        limitCount.set('value', limitNum - limitField.get('value').length);
    }
}

function showtab(tab, area) {
    console.debug(area);
    var e=document.getElementsByTagName('div');
    for (i=0; i<e.length; i++){
      if(e[i].className=='settings_area')e[i].style.display='none';
    }
    var e=document.getElementsByTagName('a');
    for (i=0; i<e.length; i++){
      if(e[i].className=='settings_tab')e[i].style.backgroundColor='';
    }
    try {
        dojo.byId(area).style.display='block';
        
        if (parseFloat(dojo.version.toString()) < 1.8) {
            dojo.byId(tab).style.background='url("/dojotoolkit/dijit/themes/claro/images/commonHighlight.png") #CFE5FA repeat-x';
        } else {    
            dojo.byId(tab).style.background='url("/dojotoolkit/dijit/themes/claro/images/activeGradient.png") #CFE5FA repeat-x';
        }    
    } catch (e) {
        console.debug(e.message);
    }    
}


var switchMainPane = function(){
    if (contentPaneTop.domNode.style.display != 'none') {
        maximizeMainPane();
    } else {
        minimizeMainPane();
    }
}

var maximizeMainPane = function(){
    contentPaneTop.domNode.style.display='none';
    contentPaneBottom.domNode.style.display='none';
    menuExpand.domNode.style.display='none';
    dijit.byId('borderContainer').resize();
}

var minimizeMainPane = function(){
    contentPaneTop.domNode.style.display='block';
    contentPaneBottom.domNode.style.display='block';
    menuExpand.domNode.style.display='block';
    dijit.byId('borderContainer').resize();
}


/*
window.alert = function(message) {
    myDialog = new dijit.Dialog({
        title: "GW Promociones",
        content: message,
        style: ""
    });
    myDialog.show();
};
*/
/*
window.confirm = function(message) {
    myDialog = new dijit.Dialog({
        title: "GW Promociones",
        content: message,
        style: ""
    });
    myDialog.show();
};
*/


//dojo.ready(eventos);
