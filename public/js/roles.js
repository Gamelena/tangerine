function rolesInit()
{
	console.log('rolesInit');
	//var formDialogo = dijit.byId('formDialogo'); // para explicacion, esto no es necesario ya existe var tabForm global (jsId)
	require(["dojo/on"], function(on){
		  on(formDialogo, "hide", function(e){
			  dojo.query('.dijitDialog').style({'min-height':'416px',width:'424px'});
		  });
		  on(formDialogoEditar, "hide", function(e){
			  dojo.query('.dijitDialog').style({'min-height':'416px',width:'424px'});
		  });
		  on(formDialogo, "show", function(e){
			  setTimeout(function() {
				  dojo.query('.dijitDialog').style({'min-height':null, height:'auto',width:'auto'});
			  }, 2800);
		  });
		  on(formDialogoEditar, "show", function(e){
			  setTimeout(function() {
				  dojo.query('.dijitDialog').style({'min-height':null, height:'auto',width:'auto'});
			  }, 2800);
		  });
		  
	});
}