function showDialog(mode, domPrefix) {
	if (additionalValidation == undefined) var additionalValidation = false;
	global_opc = mode;

	var idDlg = (mode=='edit') ? domPrefix + 'formDialogoEditar' : domPrefix + 'formDialogo';
    var formDlg =  dijit.byId(idDlg);
	var dijitId;
	var input;
    
    if (mode == 'add') {

    	
    } else if (mode == 'edit') {
    	var items = dijit.byId(domPrefix + 'main_grid').selection.getSelected();

        if (items[0] == undefined) {
            alert('Debes seleccionar una fila');
            return;
        }
        console.debug(formDlg);
    	require(["dojo/_base/array"], function(array){
  		  //array.forEach(formDlg.getChildren(), function(entry, i){
    	  array.forEach(items, function(selectedItem){	
    		  dojo.forEach(dijit.byId(domPrefix + 'main_grid').store.getAttributes(selectedItem), function(attribute){
                  // Get the value of the current attribute:
                  var value = dijit.byId(domPrefix + 'main_grid').store.getValues(selectedItem, attribute);
                  // Now, you can do something with this attribute/value pair.
                  // Our short example shows the attribute together
                  // with the value in an alert box, but we are sure, that
                  // you'll find a more ambitious usage in your own code:
                  console.debug('attribute: ' + attribute + ', value: ' + value);
                  if (dojo.query('*[name=\''+attribute+'[0]\']', idDlg)[0] != undefined) {
                	  
                	  input = dojo.query('*[name=\''+attribute+'[0]\']', idDlg)[0];
                	  dijitId = input.id;
                	  if (!dijitId) dijitId = input.parentNode.parentNode.id.replace('widget_','');

            		  
                	  if (!dijit.byId(dijitId)) { //zwei_pk_original
                		  console.debug(dijitId + "no byId");
                		  console.debug(dojo.query('*[name=\''+attribute+'[0]\']', idDlg));
                		  if (document.getElementById(dijitId)) {
                			  document.getElementById(dijitId).value = value;
                		  }
                		  	
     			  
            			  if (dojo.query('*[name=\''+attribute+'[0]\']', idDlg)[1]) {
                    		  input = dojo.query('*[name=\''+attribute+'[0]\']', idDlg)[1];
                    		  console.debug(input);
                    		  dijit.byId(input.id).set('value', value);
            			  }  
                	  } else {
                		  console.debug(dijitId + "byId");
                		  dijit.byId(dijitId).set('value', value);
                	  } 	  
                  }
              }); // end forEach
  			  //dojo.query('INPUT[name='+i+']', idDlg)[0].set('value', entry);
  		  });
    	});
    }
    
    formDlg.show();
}

function modify(mode, additionalValidation) {
	if (additionalValidation) eval(additionalValidation);	
}


function changePassword() {
	
}