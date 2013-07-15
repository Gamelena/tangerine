var globalOpc;
var globalModuleId;
var globalModule;

/**
 * carga inicial
 * @returns void
 */
var initLoad = function(layout) 
{
    admportal.loadEvents();
    admportal.loadLayoutSettings(dojo.byId("logosAdm"),dojo.byId("tituloAdm"), dojo.byId("zweicomLogo"), dojo.byId("zweicomLegend"));
    admportal.loadMainMenu(layout);
}

/**
 * configuracion de tiny MCE 
 * @returns void
 */
window.tinyMCEPreInit = {
        suffix : '',
        base : '/libs/tinymce/jscripts/tiny_mce', // your path to tinyMCE
        query : 'something'
};

/**
 * Extensión de dijit.form.Form para poder obtener widgets a través del nombre además de la id
 * @returns Array
 */
dijit.form.Form.prototype.getChildrenByName = function(name) { return this.getChildren().filter(function(w){ return w.get('name') == name;}); } 

function redirectToModule(url, domPrefix){
    if (primary == undefined) var primary = 'id';
    if (domPrefix == undefined) var domPrefix = ''; 
    try{
        var items = dijit.byId(domPrefix + 'dataGrid').selection.getSelected();
        var id = "&"+primary+"="+ eval("items[0]."+primary);    
    } catch(e) {
        var id='';
    }
    if (domPrefix == '') {
        var widget = dijit.byId("mainPane");
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


var limitText = function(limitField, limitCount, limitNum)
{
    if (limitField.value.length > limitNum) {
        limitField.value = limitField.value.substring(0, limitNum);
    } else {
        limitCount.value = limitNum - limitField.value.length;
    }
}

var limitTextDijit = function(limitField, limitCount, limitNum)
{
    if (limitField.get('value').length > limitNum) {
        limitField.set('value', limitField.get('value').substring(0, limitNum));
    } else {
        limitCount.set('value', limitNum - limitField.get('value').length);
    }
}

 
var wrapInTabContainer = function(domNode, domPrefix){
    if (domPrefix == undefined) var domPrefix = ''; 
    var div = new dojox.layout.TabContainer({
        style: "height: 100%; width: 100%;"
    }, domPrefix + "tc1-prog");
    div.id = domPreffix + "wrapperContainer";
};
