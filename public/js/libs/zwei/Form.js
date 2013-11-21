dojo.require("dojo.Stateful");
dojo.declare("zwei.Form", dojo.Stateful, {
    /**
     * mensaje para retornar
     * string 
     */
    message: null,
    /**
     * codigo para retornar
     * string
     */
    status: null,
    /**
     * dijit.form.Form
     */
    dijitForm: null,
    /**
     * dijit.form.Form
     */
    dijitFormSearch: null,
    /**
    * dijit.form.Dialog
    */
    dijitDialog: null,
    /**
     * dijit.form.DataGrid
     */
    dijitDataGrid: null,
    /**
     * dom Element
     */
    iframe: null,
    /**
     * zwei.Utils
     */
    utils: null,
    /**
     * boolean
     */
    ajax: false,
    /**
     * Object
     */
    response: null,
    /**
     * Object
     */
    primary: {},
    /**
     * string
     */
    
    queryParams: '',
    
    constructor: function(args){
        this.utils = new zwei.Utils();
        dojo.mixin(this, args);
    },
    /**
     * @constructorParam dijit.form.DataGrid    this.dijitDataGrid
     * @constructorParam dijit.form.Form    this.dijitForm
     * @constructorParam iframe    this.iframe
     * @return void 
     */
    loadData: function() {
        var domForm = this.dijitFormSearch != null ? dojo.byId(this.dijitFormSearch.id) : dojo.byId(this.dijitForm.id);
        var searchUrl = base_url+'crud-request?model=' + domForm['model'].value + '&format=' + domForm['format'].value+'&'+this.queryParams;
        var value;
        var auxTwice;
        var self=this;
        
        if (this.dijitFormSearch != undefined && this.dijitFormSearch != null) {
            dojo.forEach(this.dijitFormSearch.getChildren(), function(entry, i) {
                if (entry.type != 'submit' && entry.type != 'radio' && entry.type != 'button') {
                    if (self.dijitFormSearch.getChildrenByName(entry.get('name')).length > 1) {
                        auxTwice = '['+i+']';
                    } else {
                        auxTwice = '';
                    }
                    if (entry.baseClass == 'dijitCheckBox' && !entry.get('checked')) {
                        searchUrl += '&search['+entry.get('name')+'][value]=' +encodeURIComponent(entry.get('uncheckedvalue'));
                    } else {
                        if (entry.declaredClass != "dijit.form.DateTextBox") {
                            value = entry.get('value');
                        } else {
                            value = entry.get('value') == null ? '' : dojo.date.locale.format(entry.get('value'), {datePattern: "yyyy-MM-dd", selector: "date"});
                        }
                        searchUrl += '&search['+dijit.byId(entry.id).get('name')+']'+auxTwice+'[value]='+encodeURIComponent(value);
                    }
                    searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[format]='+encodeURIComponent(dojo.byId(entry.id+'_format').value);
                    searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[operator]='+encodeURIComponent(dojo.byId(entry.id+'_operator').value);
                    if (dojo.byId(entry.id+'_sufix') != undefined) searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[sufix][0]=' + encodeURIComponent(dojo.byId(entry.id+'_sufix').value);
                    if (dojo.byId(entry.id+'_prefix') != undefined) searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[prefix][0]=' + encodeURIComponent(dojo.byId(entry.id+'_prefix').value);
                    if (dojo.byId(entry.id+'_sufix2') != undefined) searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[sufix][1]=' + encodeURIComponent(dojo.byId(entry.id+'_sufix2').value);
                    if (dojo.byId(entry.id+'_prefix2') != undefined) searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[prefix][1]=' + encodeURIComponent(dojo.byId(entry.id+'_prefix2').value);
                }
            });
            searchUrl += '&p='+domForm.p.value;
        }
        
        if (this.iframe != null && this.queryParams.indexOf('format=excel') >= 0){
            this.iframe.src = searchUrl;
        } else {
            var params = {url: searchUrl, clearOnClose: true, urlPreventCache:true};
            var store = (domForm['storeType'] != undefined && domForm['storeType'].value == 'query') ? new dojox.data.QueryReadStore(params) : dojo.data.ItemFileReadStore(params);
            if (this.dijitDataGrid != null) {
                this.dijitDataGrid.setStore(store);
            }
        }
        return searchUrl;
    },
    /**
     * @constructorParam dijit.form.form    this.dijitForm
     * @constructorParam iframe    this.iframe
     * @return void 
     */
    save: function() {
        this.response = null;
        var domForm = dojo.byId(this.dijitForm.id);
        
        var fakeChecked = [];
        var iframeId = this.iframe.id;
        var self = this;

        //escuchar al iframe, como si el post normal hablara en ajax pero con posibilidad de hacer file uploads :)
        var listener = dojo.connect(this.iframe, "onload", function() {
            //Desmarcar checkbox con valores negativos
            dojo.forEach(fakeChecked, function(entry, i) {
                entry.set('value', entry.get('bkpvalue'));
                entry.set('checked', false);
            });
            dojo.require('dojo.json');
            
            if (dojo.byId(iframeId).contentDocument.body.innerText) //chrome
                rawResponse = dojo.byId(iframeId).contentDocument.body.innerText;
            else if (dojo.byId(iframeId).contentDocument.getElementsByTagName('pre')[0]) //firefox
                rawResponse = dojo.byId(iframeId).contentDocument.getElementsByTagName('pre')[0].innerHTML;
            else if (dojo.byId(iframeId).contentDocument.getElementsByTagName('body')[0]) //firefox
                rawResponse = dojo.byId(iframeId).contentDocument.getElementsByTagName('body')[0].innerHTML;
            
            var response = dojo.json.parse(rawResponse);
            
            if (response.message != '' && response.message != null) {
                self.utils.showMessage(response.message, response.type);
                if (response.state != 'AUTH_FAILED') self.postSave();
            } else if(response.state == 'UPDATE_OK') {
                self.utils.showMessage('Datos Actualizados');
                self.postSave();
            } else if(response.state == 'ADD_OK') {
                self.utils.showMessage('Datos Ingresados');
                self.postSave();
            } else if(response.state == 'UPDATE_FAIL') {
                self.utils.showMessage('Ha ocurrido un error, o no ha modificado datos', 'error');
            } else if(response.state == 'ADD_FAIL') {
                self.utils.showMessage('Ha ocurrido un error, verifique datos o intente más tarde', 'error');
            } else if(response.state == 'DELETE_OK') {
                self.utils.showMessage('Se ha borrado correctamente.');
                self.postSave();
            } else if(response.state == 'DELETE_FAIL') {
                self.utils.showMessage('Ha ocurrido un error, verifique datos o intente más tarde', 'error');
            }
            dojo.disconnect(listener);
            self.set("response", response);
        });
        
       
        //Enviar valores "negativos" para los checkboxes sin marcar, estos son temporalmente chequeados
        dojo.forEach(this.dijitForm.getChildren(), function(entry, i) {
            if (entry.baseClass == 'dijitCheckBox' && !entry.get('checked')) {
                entry.set('bkpvalue', entry.get('value'));
                entry.set('value', entry.get('uncheckedvalue'));
                fakeChecked.push(entry);
            }
        });
        
        /*//Esto siempre toma el camino function(err) aunque la respuesta se vea bien, debido a que espera otro tipo de headers
         * 
        require(["dojo/request/iframe"], function(iframe){
            iframe.post(base_url+'crud-request', {
              form: dojo.byId(formId)
            }).then(function(data){
                console.debug(data);
              // Do something with the document
            }, function(err){
                console.debug(err);
              // Handle the error condition
            });
            // Progress events are not supported using the iframe provider
          });
          */
        
        domForm.submit();
    },
    'delete': function() {
        var domForm = dojo.byId(this.dijitForm.id);
        var items = this.dijitDataGrid.selection.getSelected();
        if (items[0].i != undefined && items[0].r._items != undefined) items[0] = items[0].i;//workaround, a Dojo bug?
        
        var xhrContent = {
            'model': domForm['model'].value,
            'action': 'delete',
            'format': 'json' 
        };
        
        dojo.forEach(dojo.query("#"+this.dijitForm.id+" input[name^=primary]"), function(entry, i){
            name = entry.name.replace('primary[', '').replace(']', '');
            if (typeof(items[0][name]) != 'undefined') {
                entry.value = items[0][name];
                xhrContent['primary['+name+']'] = encodeURIComponent(entry.value);
            }
        });
        console.debug(xhrContent);

        
        if (confirm('\u00BFDesea eliminar el registro seleccionado?')) {
            var self = this;
            
            dojo.xhrPost( {
                url: base_url+'crud-request',
                content: xhrContent,
                handleAs: 'json',
                sync: true,
                preventCache: true,
                timeout: 5000,
                load: function(response){
                    if(response.message != "" && response.message != null){
                        self.utils.showMessage(response.message);
                        if (response.state != 'AUTH_FAILED') self.postSave();
                    } else if(response.state == 'DELETE_OK') {
                        self.utils.showMessage('Se ha borrado correctamente.');
                        self.postSave();
                    } else if(response.state == 'DELETE_FAIL') {
                        self.utils.showMessage('Ha ocurrido un error, verifique datos o intente más tarde', 'error');
                    }
                    return response;
                },
                error:function(err){
                    self.utils.showMessage('Error en comunicacion de datos. error: '+err, 'error');
                    return err;
                }
            });
        }
    },
    /**
     * [TODO] en proceso, para soportar múltiples diálogos
     */
    showMultipleDialogs: function() {
        if (this.action != 'add') {
            var items = this.dijitDataGrid.selection.getSelected();
            dojo.forEach(items, function(i, item){
                if (item.i != undefined && item.r._items != undefined) item = item.i;//workaround, a Dojo bug?
                showDialog(i);
            });
        }
    },
    
    /**
     * @constructorParam dijit.form.Dialog    this.dijitDialog
     * @constructorParam dijit.form.form    this.dijitForm
     * @constructorParam dijit.form.DataGrid    this.dataGrid
     * @return void 
     */
    showDialog: function() {
        var ids = '';
        var primaries = {};
        
        if (this.dijitDataGrid != null && this.dijitForm != null) {
            globalOpc = this.action;
            var name;
            if (this.action != 'add') {
                var items = this.dijitDataGrid.selection.getSelected();
                if (items[0].i != undefined && items[0].r._items != undefined) items[0] = items[0].i;//workaround, a Dojo bug?
                
                
                //Buscar inputs (hidden) con nombre primary[$idCampo] para obtener las PKs
                dojo.forEach(dojo.query("#"+this.dijitForm.id+" input[name^=primary]"), function(entry, i){
                    name = entry.name.replace('primary[', '').replace(']', '');
                    if (typeof(items[0][name]) != 'undefined') {
                        entry.value = items[0][name];
                        primaries[name] = entry.value;
                    }
                });
                
                if (this.ajax) {
                    for (var primary in primaries) {
                        ids += '&primary['+primary + "]=" + encodeURIComponent(primaries[primary]);
                    }
                } else {
                    //Poblar campos con datos fila de datagrid seleccionada
                    dojo.forEach(this.dijitForm.getChildren(), function(entry, i){
                        name = entry.get('name').replace('data[', '').replace(']', '');
                        if (typeof(items[0][name]) != 'undefined') {
                            if (entry.baseClass != 'dijitCheckBox') {
                                entry.set('value', items[0][name]);
                            } else {
                                entry.set('checked', entry.value == items[0][name]);
                            }
                        }
                    });
                }
            }
        }
        
        if (this.ajax) {
            if (dojo.objectToQuery(this.primary) != '') {
                if (this.action == 'edit' || this.action == 'clone') {
                    var primary = this.primary;
                    var dijitForm = this.dijitForm;
                    
                    for (var primal in primary) {
                        ids += '&primary['+ primal + ']=' + encodeURIComponent(primary[primal]);
                    }
                    if (dijitForm != null) {
                        var listener = dojo.connect(this.dijitDialog, "onLoad", function(){
                             if (dijitForm)
                             var domForm = dojo.byId(dijitForm.id);
                             for (var id in primary) {
                                 domForm['primary['+ id + ']'].value = primary[id];
                             }
                             dojo.disconnect(listener);
                        });
                    }
                }
            }
            
            this.dijitDialog.set('href', base_url+'components/dojo-simple-crud/'+this.action+'?p='+this.component+'&'+ids+'&'+this.queryParams);
        }
        
        this.dijitDialog.show();
    },
    hideDialog: function(){
        this.dijitDialog.hide();
    },
    getMessage: function(){
        return this.message;
    },
    postSave: function(){
        if (this.dijitDialog != null) this.hideDialog();
        if (this.dijitFormSearch != null) this.dijitForm = this.dijitFormSearch;
        if (this.dijitDataGrid != null) this.loadData();
    }
});
