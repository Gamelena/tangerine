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
     * formulario de edicion
     * dijit.form.Form
     */
    dijitForm: null,
    /**
     * formulario de búsqueda
     * dijit.form.Form
     */
    dijitFormSearch: null,
    /**
    * Diálogo desplegable.
    * dijit.form.Dialog
    */
    dijitDialog: null,
    /**
     * Grilla
     * dijit.form.DataGrid
     */
    dijitDataGrid: null,
    /**
     * Iframe
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
    /**
     * string
     */
    title: null,
    /*
     * array
     */
    row: null,
    /**
     * array
     */
    keys: [],
    /**
     * string
     */
    prefix: null,
    /**
     * string
     */
    sufix: null,
    /**
     * string
     */
    model: null,
    /**
     * 
     * @param Object args 
     */
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
        var self = this;
        
        if (this.dijitFormSearch != undefined && this.dijitFormSearch != null) {
            dojo.forEach(this.dijitFormSearch.getChildren(), function(entry, i) {
                if (entry.type != 'submit' && entry.type != 'radio' && entry.type != 'button' && entry.get('disabled') != true) {
                    if (self.dijitFormSearch.getChildrenByName(entry.get('name')).length > 1) {
                        auxTwice = '['+i+']';
                    } else {
                        auxTwice = '';
                    }
                    
                    var valueIsArray = Object.prototype.toString.call(entry.get('value')) == '[object Array]';
                    
                    if (entry.baseClass == 'dijitCheckBox' && !entry.get('checked')) {
                        searchUrl += '&search['+entry.get('name')+'][value]=' +encodeURIComponent(entry.get('uncheckedvalue'));
                    } else {
                        if (entry.declaredClass == "dijit.form.DateTextBox") {
                            value = entry.get('value') == null ? '' : dojo.date.locale.format(entry.get('value'), {datePattern: "yyyy-MM-dd", selector: "date"});
                        } else {
                            value = entry.get('value');
                        }
                        if (valueIsArray) {
                            dojo.forEach(entry.get('value'), function(value, i) {
                                searchUrl += '&search['+ dijit.byId(entry.id).get('name').replace('[]', '') +']['+i+'][value]='+encodeURIComponent(value);
                            });
                        } else {
                            searchUrl += '&search['+dijit.byId(entry.id).get('name')+']'+auxTwice+'[value]='+encodeURIComponent(value);
                        }
                    }
                    
                    if (valueIsArray) {
                        dojo.forEach(entry.get('value'), function(value, i) {
                            searchUrl += '&search['+entry.get('name').replace('[]', '')+']['+i+'][format]='+encodeURIComponent(dojo.byId(entry.id+'_format').value);
                            searchUrl += '&search['+entry.get('name').replace('[]', '')+']['+i+'][operator]='+encodeURIComponent(dojo.byId(entry.id+'_operator').value);
                            if (dojo.byId(entry.id+'_sufix') != undefined) searchUrl += '&search['+entry.get('name').replace('[]', '')+']['+i+'][sufix][0]=' + encodeURIComponent(dojo.byId(entry.id+'_sufix').value);
                            if (dojo.byId(entry.id+'_prefix') != undefined) searchUrl += '&search['+entry.get('name').replace('[]', '')+']['+i+'][prefix][0]=' + encodeURIComponent(dojo.byId(entry.id+'_prefix').value);
                            if (dojo.byId(entry.id+'_sufix2') != undefined) searchUrl += '&search['+entry.get('name').replace('[]', '')+']['+i+'][sufix][1]=' + encodeURIComponent(dojo.byId(entry.id+'_sufix2').value);
                            if (dojo.byId(entry.id+'_prefix2') != undefined) searchUrl += '&search['+entry.get('name').replace('[]', '')+']['+i+'][prefix][1]=' + encodeURIComponent(dojo.byId(entry.id+'_prefix2').value);
                        });
                    } else {
                        searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[format]='+encodeURIComponent(dojo.byId(entry.id+'_format').value);
                        searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[operator]='+encodeURIComponent(dojo.byId(entry.id+'_operator').value);
                        if (dojo.byId(entry.id+'_sufix') != undefined) searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[sufix][0]=' + encodeURIComponent(dojo.byId(entry.id+'_sufix').value);
                        if (dojo.byId(entry.id+'_prefix') != undefined) searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[prefix][0]=' + encodeURIComponent(dojo.byId(entry.id+'_prefix').value);
                        if (dojo.byId(entry.id+'_sufix2') != undefined) searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[sufix][1]=' + encodeURIComponent(dojo.byId(entry.id+'_sufix2').value);
                        if (dojo.byId(entry.id+'_prefix2') != undefined) searchUrl += '&search['+entry.get('name')+']'+auxTwice+'[prefix][1]=' + encodeURIComponent(dojo.byId(entry.id+'_prefix2').value);
                    }
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
                if (response.state != 'AUTH_FAILED' && response.type != 'error') self.postSave();
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
        var domForm;
        
        var items = this.dijitDataGrid.selection.getSelected();
        if (items[0].i != undefined && items[0].r._items != undefined) items[0] = items[0].i;//workaround, a Dojo bug?
        
        var messageConfirm = multiForm && items.length > 1 ? '\u00BFDesea eliminar los ' + items.length + ' registros seleccionados?' : '\u00BFDesea eliminar el registro seleccionado?';
        
        if (confirm(messageConfirm)) {
            var self = this;
            
            
            
            if (!multiForm) {
                items = [items[0]];
            } 
            
            console.log(items);
            
            for (var j=0; j<items.length; j++) {
                if (items[j].i != undefined && items[j].r._items != undefined) {items[j] = items[j].i;}//workaround, a Dojo bug?
                
                var xhrContent = {
                    'model': this.model,
                    'action': 'delete',
                    'format': 'json' 
                };
                
                for (var i = 0; i < this.keys.length; i++) {
                    xhrContent['primary['+this.keys[i]+']'] = encodeURIComponent(items[j][this.keys[i]]);
                }
                
                dojo.xhrPost({
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
            self.postSave();
        }
    },
    showMultipleDialogs: function() {
        if (this.action != 'add') {
            var items = this.dijitDataGrid.selection.getSelected();
            for (var j = 0; j < items.length; j++) {
                if (items[j].i != undefined && items[j].r._items != undefined) {items[j] = items[j].i;}//workaround, a Dojo bug?
                
                for (var i=0; i < this.keys.length; i++) {
                    this.primary[this.keys[i]] = items[j][this.keys[i]];
                }
                this.sufix = '';
                
                if (this.primary) {
                    for (var index in this.primary) {
                        this.sufix += this.primary[index];
                    }
                }
                
                var dialogId = (this.prefix + 'dialog_' + this.action + this.sufix).trim();
                
                if (dijit.byId(dialogId) == undefined) {
                    this.dijitDialog = new dojox.widget.DialogSimple({
                        title: this.title,
                        id: dialogId
                    });
                } else {
                    this.dijitDialog = dijit.byId(dialogId);
                }
                
                
                this.row = items[j];
                this.showDialog();
            }
            
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
        
        if (this.dijitDialog == null) {
            var dialogId = this.prefix + 'dialog_' + this.action;
            if (dijit.byId(dialogId) == undefined) {
                this.dijitDialog = new dojox.widget.DialogSimple({
                    title: this.title,
                    id: dialogId
                });
            } else {
                this.dijitDialog = dijit.byId(dialogId);
            }
        }
        
        if (this.dijitDataGrid != null && this.dijitForm != null) {
            globalOpc = this.action;
            var name;
            if (this.action != 'add') {
                if (this.row == null) {
                    var items = this.dijitDataGrid.selection.getSelected();
                    if (items[0].i != undefined && items[0].r._items != undefined) {
                        items[0] = items[0].i;//workaround, a Dojo bug?
                    }
                    item = items[0];
                } else {
                    console.log(this.row);
                    item = this.row;
                }
                //Buscar inputs (hidden) con nombre primary[$idCampo] para obtener las PKs
                var idForm = this.dijitForm ? this.dijitForm.id : this.prefix + 'form_edit' + this.sufix;
                
                console.log("#"+idForm+" input[name^=primary]");
                dojo.forEach(dojo.query("#"+idForm+" input[name^=primary]"), function(entry, i) {
                    name = entry.name.replace('primary[', '').replace(']', '');
                    if (typeof(item[name]) != 'undefined') {
                        entry.value = item[name];
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
                        if (typeof(item[name]) != 'undefined') {
                            if (entry.baseClass != 'dijitCheckBox') {
                                entry.set('value', item[name]);
                            } else {
                                entry.set('checked', entry.value == item[name]);
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
                        ids += '&primary[' + primal + ']=' + encodeURIComponent(primary[primal]);
                    }
                    if (dijitForm != null) {
                        var listener = dojo.connect(this.dijitDialog, "onLoad", function() {
                             if (dijitForm) {var domForm = dojo.byId(dijitForm.id);}
                             for (var id in primary) {
                                 domForm['primary['+ id + ']'].value = primary[id];
                             }
                             dojo.disconnect(listener);
                        });
                    }
                }
            } else console.debug(ids);
            
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
