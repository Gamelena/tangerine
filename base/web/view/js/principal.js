/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//dojo.require("dijit.layout.ContentPane");
dojo.require("dojox.layout.ContentPane");
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.DropDownButton");
dojo.require("dijit.Menu");
dojo.require("dijit.MenuItem");
dojo.require("dojo.data.ItemFileWriteStore");
dojo.require("dijit.Tree");
dojo.require("dojo.cookie");

var InfoUsuario; //Datos de usuario extraidos via cookie


function eventos() {

    dojo.connect(dojo.byId("btnSalir"), "onclick", function(){
        dojo.cookie("InfoUsuario", null, {
            expires: -1
        });
		window.location = "/admportal/base/web/view/html/ingreso.html";

    });

    dojo.connect(dijit.byId("btnConfPersonal"), "onClick", function(){

        var widget = dijit.byId("panel_central");
        widget.set('href','../html/mDatosUsr.html')

    });

    dojo.connect(dijit.byId("btnConfUsuarios"), "onClick", function(){

        var widget = dijit.byId("panel_central");
        widget.set('href','../html/mConfUsuarios.html')

    });




}

function cargaInicial() {

	var valCookie = dojo.cookie("InfoUsuario");
    //No esta logueado
    if (typeof valCookie  == "undefined")
	{
		window.location = "/admportal/base/web/view/html/inicio.html";
		return;
	}

	InfoUsuario = dojo.fromJson(valCookie);
	
    //Configuracion de titulo, logos de operadora y zweicom
    cargarDatosTituloAdm(dojo.byId("logosAdm"),dojo.byId("tituloAdm"));

    var divCD = dojo.byId("infoUsr");
	
	if( typeof InfoUsuario.apellido_p != 'undefined' && InfoUsuario.apellido_p != null )
	{
		if( InfoUsuario.apellido_p.length > 0 )
			divCD.innerHTML = "Usuario: ["+InfoUsuario.nombre_p + " "+InfoUsuario.apellido_p + "]";
	}
 	else
		divCD.innerHTML = divCD.innerHTML = "Usuario: ["+InfoUsuario.nombre_p + "]";

    //Carga del panel lateral
    cargarArbolMenu();

    

}

function cargarDatosTituloAdm(bloqueLogoHTML, bloqueTituloHTML) {

    //Obtencion via AJAX de datos basicos del administrador web
    dojo.xhrGet( {
		url: "/admportal/base/web/ctrl/principal.php",
     content: {'fc':"cargarDatosTituloAdm"},
     handleAs: "json",
     preventCache: true,
     timeout: 5000,
     load: function(respuesta){
         
		 var cad = "<img src=\"/admportal/base/web/view/img/" + respuesta.URL_LOGO_OPER + "\" />";
		 cad += "<img src=\"/admportal/base/web/view/img/" + respuesta.URL_LOGO_ZWEICOM + "\" />";
         bloqueLogoHTML.innerHTML = cad;

         bloqueTituloHTML.innerHTML = respuesta.TITULO_ADM;

         document.title = respuesta.TITULO_ADM;

         return respuesta;
     },
     error:function(err){
         alert("Error en comunicacion de datos. error: "+err);
         return err;
     }
   });



}


// function configurarMenuUsuarios() {
// 
//     var opcItemConfUsuarios = dijit.byId("btnConfUsuarios");
// 
//     if(UsrData.GRUPO_NOMBRE != "OPERACIONES_PARAMETRIZADOR") {
// 
//         opcItemConfUsuarios.set("disabled","true");
// 
//     }
// 
// 
// }


function cargarArbolMenu() {

	var treedata = new Array();
	
	//Obtencion via AJAX de datos basicos del administrador web
	dojo.xhrGet( {
		url: "/admportal/base/web/ctrl/modulos.php",
		handleAs: "json",
		preventCache: true,
		sync : true,
		timeout: 5000,
		load: function(modulos){
			
			len = modulos.length;
			for( i = 0; i < len; i++ )
			{
				if( modulos[i].url.length > 0 )
				{
					name = modulos[i].nombre;
					dojo.xhrGet( {
						url: modulos[i].url + "/web/ctrl/tree.php",
						content: {
							'id_modulo': i,
							'id_usuario': InfoUsuario.id,
						},
						handleAs: "json",
						preventCache: true,
						sync : true,
						timeout: 5000,
						load: function(modtree){
							treedata[ i ] = modtree;
						},
						error:function(err){
							alert("Error al obtener arbol para [" + name + "]: "+err);
							return err;
						}
					});
				}
			}
		},
		error:function(err){
			alert("Error al obtener modulos activos: "+err);
			return err;
		}
	});

	var store = new dojo.data.ItemFileWriteStore({
		data: {
			identifier: 'id',
			label: 'label',
			items: treedata
		}
	});
	
	var treeModel = new dijit.tree.ForestStoreModel({
		store: store
	});
	
	var treeControl = new dijit.Tree({
		model: treeModel,
		showRoot: false,
		onClick: function(item){cargarPanelCentral(item);},
									 _createTreeNode: function(
										 /*Object*/
										 args) {
										 var tnode = new dijit._TreeNode(args);
										 tnode.labelNode.innerHTML = args.label;
										 return tnode;
										 }        
	},
	"arbolPrincipal");
}


function cargarPanelCentral(item) {

	var widget = dijit.byId("panel_central");

	widget.set('href',item.url);
}

dojo.ready(cargaInicial);
dojo.ready(eventos);

