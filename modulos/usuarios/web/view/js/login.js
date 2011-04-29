/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
dojo.require("dijit.form.Button");
dojo.require("dijit.form.ValidationTextBox");
dojo.require("dojo.cookie");

//Funcion para devolucion de autenticacion
function validaLogin() {

    if(!dijit.byId("username").isValid() || !dijit.byId("password").isValid())
        return;

    var usr = dijit.byId("username").get("value");
    var pwd = dijit.byId("password").get("value");

    //Llamada al php para verificacion de usuario y contrasena
    dojo.xhrGet( {
     url: "/admportal/modulos/usuarios/web/ctrl/login.php",
     content: {'usr':usr, "pwd": pwd},
     handleAs: "json",
     preventCache: true,
     timeout: 5000,
     load: function(respuesta){
         
         if( respuesta.exito ) {
            dojo.style("respLogin","background","green");
            dojo.byId("respLogin").innerHTML = "Login exitoso :" + respuesta.NOMBRES;
			dojo.byId("esperaCarga").innerHTML = "Cargando componentes... <img src=\"/admportal/modulos/usuarios/web/view/img/usuarios_loading.gif\"/>"
			dojo.cookie("InfoUsuario", dojo.toJson(respuesta), {path: "/admportal" } );
            console.log(dojo.toJson(respuesta));
			
			window.location = "/admportal/base/web/view/html/principal.html";
        } else {
             dojo.byId("respLogin").innerHTML = "Usuario o contrase&ntilde;a incorrecto";
        }
         return respuesta.exito;
     },
     error:function(err){
         dojo.byId("respLogin").innerHTML = "Error en comunicacion de datos. error: "+err;
         return err;
     }
   });
}