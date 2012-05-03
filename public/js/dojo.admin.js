var global_opc;

dojo.declare(
	    "dijit.form.ValidationTextarea",
	    [dijit.form.ValidationTextBox,dijit.form.SimpleTextarea],
	    [dijit.form.ValidationTextBox,dijit.form.SimpleTextarea],
	    {
	      invalidMessage: "This field is required",

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
}


function cargaInicial() 
{
	eventos();
    //Configuracion de titulo, logos de operadora y zweicom
    cargarDatosTituloAdm(dojo.byId("logosAdm"),dojo.byId("tituloAdm"));
	
    //Carga del panel lateral
    cargarArbolMenu();
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

function cargarPanelCentral(url) 
{
	var widget = dijit.byId("panel_central");
    widget.set('href',base_url+url);
}

function cargarArbolMenu() 
{
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
	
	
	var Model_tree = new dijit.tree.ForestStoreModel({
	        store: store
	});
	
	
	if (!dijit.byId('arbolPrincipal')) {
		var treeControl = new dijit.Tree({
		    model: Model_tree,
		    showRoot: false,
		    persist:false,
		    onClick: function(item){
		    	if (item.url != undefined) {
		    		cargarPanelCentral(item.url);
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


function cargarTabsPanelCentral(component, action)
{
	var widget = dijit.byId("panel_central");
	if(action=='edit' || action=='clone'){
		try{
			var items = main_grid.selection.getSelected();
			var id = "&id="+items[0].id;
		}catch(e){
			alert('Debe seleccionar un registro');
			return;
		}
	}else{
		var id = '';
	}	
	
    widget.set('href',base_url+'index/tabs?p='+component+'&action='+action+id);
}

function cargarDatos(model, search_in_fields, format_date, cast, between, formato, component) 
{
	if(search_in_fields==undefined)var search_in_fields=false;
	if(format_date==undefined)var format_date=false;
	if(cast==undefined)var cast=false;
	if(between==undefined)var between=false;
	if(formato==undefined)var formato='json';
	if(component==undefined)var component=false;	
	
	try{
		var search = dijit.byId("search").get("value");
		if(format_date){
			search = dojo.date.locale.format(search, {datePattern: "yyyy-MM-dd", selector: "date"});
			if(dijit.byId("search2") != undefined){
				var search2 = dijit.byId("search2").get("value");
				search2 = dojo.date.locale.format(search2, {datePattern: "yyyy-MM-dd", selector: "date"});
				search+=";"+search2;
			}	
		}	
	}catch(e){
		var search = "";
	}
	
	try{
		var id = "&id="+document.getElementById("id").value;
	}catch(e){
		var id="";
	}
	
    var search_url;
	var search_fields='';

    if(search_in_fields){
    	var search_fields_lenght;
    	try{
    		search_fields_length = dojo.byId('search_form').elements['search_fields'].length;
    		console.log(search_fields_length);
    	}catch(e){
    		console.debug(e);
    		search_fields_length = 0;
    	}
    	
    	for(var i = 0; i < search_fields_length; i++)
    	{
    		if(dojo.byId('search_form').elements['search_fields'][i].checked)
    		{
	   			search_fields+=dojo.byId('search_form').elements['search_fields'][i].value+';';
	   		}
	   	}
    	search_fields=(search_fields!='')? '&search_fields='+search_fields:'';
    }
    
    var search_format;
    
    try{
    	search_format='&search_format='+ document.getElementById('search_format[0]').value;
    }catch(e){
    	search_format='';
    }	
    
    search_between=between?'&between=1':'';
    component_param=component!=false?'&p='+component:'';
    
    search_url = base_url+'objects?model='+model+'&format='+formato+'&search='+search+search_fields+id+search_format+search_between+component_param;
    
    try{
    	dojo.byId('data_url').value=search_url;
    }catch(e){}	
    
 
    if(formato=='excel'){
    	dojo.byId('ifrm_process').src=search_url;
    }else{
        var store = new dojo.data.ItemFileWriteStore({
            url: search_url,
            clearOnClose: true,
            urlPreventCache: true
        });    	
    	main_grid.setStore(store);   	
    }	
}


function searchMultiple(model, fields, search_format,  between, response_format, component)
{
	if(response_format==undefined) var response_format='json';
	if(search_format==undefined) var search_format=false;
	if(between==undefined) var between=false;
	if(component==undefined)var component=false;	
	
	var form=dojo.byId('search_form');
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
	
	var search_url = base_url+'objects?model='+model+'&search='+search+'&search_fields='+fields+'&format='+response_format+'&'+search_format+search_between+'&search_type=multiple';
	
    try{
    	dojo.byId('data_url').value=search_url;
    }catch(e){}	
	
	
    if(response_format=='excel'){
    	dojo.byId('ifrm_process').src=search_url;
    }else{
        var store = new dojo.data.ItemFileWriteStore({
            url: search_url,
            clearOnClose: true,
            urlPreventCache: true
        });    	
    	main_grid.setStore(store);   	
    }	
	//console.debug(form.elements);
}


function eliminar(model)
{
    var items = main_grid.selection.getSelected();
    console.debug(items);
    console.debug(items[0].id);
    if(confirm('Desea eliminar el registro seleccionado?')) {
        eliminarRegistro(model, items[0].id);
        //main_grid.removeSelectedRows();
    }
}

function eliminarRegistro(model, id) {
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
                cargarDatos(model);
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


function execFunction(method, params, object){
	try{
		var items = main_grid.selection.getSelected();
		var id = items[0].id;
	}catch(e){
		var id = '';
	}	
		
    document.getElementById('ifrm_process').src=base_url+'functions?method='+method+'&params='+params+"&id="+id+"&object="+object;
}

function popupGrid(module, iframe){
	if(iframe==undefined) var iframe=false;
	try{
		var items = main_grid.selection.getSelected();
		var id = "&search="+items[0].id;
	}catch(e){
		var id = '';
	}
	
	if(iframe){
		popupIframe(module+id+"&uri="+escape(dojo.byId('data_url').value)); 
	}else{
		popup(module+id+"&uri="+escape(dojo.byId('data_url').value)); 
	}	
}


function redirectToModule(url){
	try{
		var items = main_grid.selection.getSelected();
	    var id = items[0].id;		
	}catch(e){
		var id='';
	}
	var widget = dijit.byId("panel_central");
    widget.set('href',base_url+url);	
}


function execFunctionPopup(method, params, must_select){
	try{
		var items = main_grid.selection.getSelected();
		var id = items[0].id;
	}catch(e){
		if(must_select){
			alert('Debe seleccionar una fila');
		}		
	}	
	popup(base_url+"functions?method="+method+"&params="+params+"&id="+id);
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
    if (valCell == '0') val = 'No';
    else if (valCell == '1') val = 'Sí';	
    
    return val;
}


dojo.ready(cargaInicial);
//dojo.ready(eventos);
