var globalOpc;
var globalModuleId;
var globalModule;
var $globals = {};

/**
 * carga inicial
 * @returns void
 */
//dojo.registerModulePath("myapp", base_url + "/js/myapp");

var initLoad = function() 
{
    tangerine.loadEvents();
    tangerine.loadLayoutSettings(dojo.byId("logosAdm"),dojo.byId("tituloAdm"), dojo.byId("gamelenaLogo"), dojo.byId("gamelenaLegend"));
    tangerine.loadMainMenu();
    tangerine.initListeners();
};

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
 * Workaround para IE <= 8
 */
if (!Array.prototype.filter) {
  Array.prototype.filter = function(fun /*, thisp */)
  {
    "use strict";

    if (this === void 0 || this === null)
      throw new TypeError();

    var t = Object(this);
    var len = t.length >>> 0;
    if (typeof fun !== "function")
      throw new TypeError();

    var res = [];
    var thisp = arguments[1];
    for (var i = 0; i < len; i++)
    {
      if (i in t)
      {
        var val = t[i]; // in case fun mutates this
        if (fun.call(thisp, val, i, t))
          res.push(val);
      }
    }

    return res;
  };
}

/**
 * Extensión de dijit.form.Form para poder obtener widgets a través del nombre además de la id
 * @returns Array
 */
dijit.form.Form.prototype.getChildrenByName = function(name) { return this.getChildren().filter(function(w){ return w.get('name') == name;}); } 


