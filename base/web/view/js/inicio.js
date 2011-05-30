dojo.require("dojo.cookie");

function inicio() {
	dojo.xhrGet( {
		url: "/admportal/base/web/ctrl/inicio.php",
		handleAs: "json",
		preventCache: true,
		timeout: 5000,
		load: function(respuesta){
			if( respuesta == 0) {
				infousr = new Object();
				infousr.id = "-1";
				dojo.cookie("InfoUsuario", dojo.toJson(infousr), {path: "/admportal" } );
				window.location = "/admportal/base/web/view/html/principal.html";
			} else {
				window.location = "/admportal/modulos/usuarios/web/view/html/login.html";
			}
			return respuesta;
		},
		error:function(err){
			alert( "Error en comunicacion de datos: "+err);
			return err;
		}
	});
}

dojo.ready( inicio );
