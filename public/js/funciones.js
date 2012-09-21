function cabecera_dinamica(ruta)
{
    var frame = top.frames['title'];
    var cels = frame.document.getElementsByTagName('td');
    cels[0].innerHTML = '<p CLASS=SUBT align=left><b>' + ruta + '</b></p>';
}

function imprime(){
    document.all.item("noprint").style.visibility='hidden';
    document.all.item("noprint").style.position = 'absolute';
    window.print();
    document.all.item("noprint").style.visibility='visible';
    document.all.item("noprint").style.position = 'relative';
}

function limitText(limitField, limitCount, limitNum)
{
        if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
        } else {
                limitCount.value = limitNum - limitField.value.length;
        }
}

function ValidaInfoBolsa(caso)
{
    var f = document.myform;
    if(caso == 'nueva')
    	var id_bolsa = trimobj(f.id_bolsa);
    var unidad = trimobj(f.unidad);
    var cantidad = trimobj(f.cantidad);
    var descripcion = trimobj(f.descripcion);
    var patron = /^\d{1,10}$/;

    if (caso == 'nueva' && id_bolsa == '')
    {
        alert("Debe introducir un ID para la bolsa");
        return false;
    }
    else if (unidad == '')
    {
        alert("Debe elegir una Unidad para la bolsa");
        return false;
    }
    else if (!cantidad.match(patron))
    {
        alert("La cantidad debe ser un numero");
        return false;
    }
    else if (cantidad > 2147483647)
    {
        alert("La cantidad no puede ser mayor a 2147483647");
        return false;
    }
    return true;
}



function ConfirmarEliminarRegistro(id)
{
    if (confirm('Esta seguro que desea eliminar la informacion ?'))
    {
    	document.myform.action = base_url+'/compra/index/eliminar?id=' + id;
        document.myform.submit();
    }
}




function VerMenu(index)
{
    document.myform.action = '../pl_menu/ver.php?index=' + index; 
    document.myform.submit();
}

function EditarMenu(index,tipo)
{
    document.myform.action = '../pl_menu/editar.php?index=' + index + "&tipo=" + tipo; 
    document.myform.submit();
}


function Cancelar(url)
{
    document.myform.action = url;
    document.myform.submit();
}

function addRowToTable(id_promociones, texto_promociones)
{
  var table = document.getElementById('Tabla');
  var lugar_insert = table.rows.length - 2;
  var posicion = table.rows.length - 10;
  var row = table.insertRow(lugar_insert);

  //Se permite un maximo de 10 opciones
  if(posicion < 11)
  {
    //Promocion
    var cell1 = row.insertCell(0);
    //Se crea el select
    var aux = "<select name='id_promo" + posicion + "' size='1' class='ListSelect'>";
    //Se agregan las opciones
    aux = aux + "<option value=''>Seleccione una promocion...</option>";
    for(var i = 0; i < id_promociones.length; ++i)
    {
      aux = aux + "<option value='" + id_promociones[i] + "'>" + id_promociones[i] + "</option>";
    }
    aux = aux + "</select>";
    //Se agrega a la celda
    cell1.innerHTML = aux;
    //Se le agrega al select la funcion texto_interactivo
    var rows = table.getElementsByTagName('tr');
    var cels = rows[lugar_insert].getElementsByTagName('td');
    cels[0].childNodes[0].onchange = function(){texto_interactivo(this, id_promociones, texto_promociones, 0)};

    //Posicion
    var cell2 = row.insertCell(1); 
    cell2.innerHTML = "<p CLASS=textoAzul align=center>" + posicion + "</p>";

    //Texto
    var cell3 = row.insertCell(2);
    cell3.colSpan = '3';
    cell3.innerHTML = "<input type='text' name='texto" + posicion + "' size='50' value='' maxlength='182' class='InputTexto'/>";

    //Quitar
    var cell4 = row.insertCell(3);
    cell4.align='center';
    cell4.innerHTML = "<a CLASS=textoAzul href='#'>Quitar</a>";
    //Se le agrega la funcion
    cell4.childNodes[0].onclick = function(){deleteRow(this); return false};

    //Se actualiza npromociones
    document.myform.npromociones.value = posicion;
  }
  else
    alert('Se permite un maximo de 10 opciones');
}

function deleteRow(row)
{
  //Se obtiene la fila de interes
  var index = row.parentNode.parentNode.rowIndex;
  document.getElementById('Tabla').deleteRow(index)
  //Se actualizan las filas
  var table = document.getElementById('Tabla');
  var rows = table.getElementsByTagName('tr');
  var posicion = 0;
  for(row = 9; row < rows.length - 2; row++)
  {
    posicion = row - 8;
    var cels = rows[row].getElementsByTagName('td')
    cels[0].childNodes[0].name = 'id_promo' + posicion;
    cels[1].innerHTML = "<p CLASS=textoAzul align=center>" + posicion + "</p>";
    cels[2].childNodes[0].name = 'texto' + posicion;
  }
  //Se actualiza npromociones
  document.myform.npromociones.value = posicion;
}

function texto_interactivo(row, id, texto, x)
{
  //Se obtiene la fila de interes
  var index = row.parentNode.parentNode.rowIndex;
  var table = document.getElementById('Tabla');
  var rows = table.getElementsByTagName('tr');
  var cels = rows[index].getElementsByTagName('td');
  //Se obtiene el id de la promocion seleccionado
  var id_seleccionado = cels[0].childNodes[x].value;
  //alert(cels[2].childNodes[0].value);
  //Se busca el texto correspondiente
  for(var i = 0; i < id.length; ++i)
  {
    if(id[i] == (id_seleccionado))
      break;
  }
  if(id_seleccionado != '')
    cels[2].childNodes[0].value = (texto[i].replace(/x/g,' ')).replace(/y/g,',');
  else
    cels[2].childNodes[0].value = '';
}

function nuevo_interactivo(caso)
{
  var table = document.getElementById('Tabla');
  var rows = table.getElementsByTagName('tr');
  var cels = rows[4].getElementsByTagName('td');
  var divs = table.getElementsByTagName('div');
  if(caso == 'si')
  {
    divs[0].style.visibility = 'visible';
    divs[1].style.visibility = 'visible';
  }
  else if(caso == 'no')
  {
    divs[0].style.visibility = 'hidden';
    divs[1].style.visibility = 'hidden';
    cels[2].childNodes[0].childNodes[0].value = '';
  }
}

function ValidaParam()
{
  var fechai = document.myform.fecha_ini.value;
  var fechaf = document.myform.fecha_fin.value;

  var msisdn = document.myform.msisdn.value;
  var patron = /^\d{1,14}$/;
  if(!msisdn.match(patron)) {
      alert('MSISDN no valido');
      return false;
  }

  if(fechai != "" && fechaf != "")
  {
  	if(fechai > fechaf)
  	{
    		alert('La fecha inicial no puede ser mayor que la final');
    		return false;
  	}
  	else
    		return true;
  }
  else
	return true;
}

function ValidaParamProvisioning()
{
  var fechai = document.myform.fecha_ini.value;
  var msisdn = document.myform.msisdn.value;
  var patron = /^\d{1,14}$/;
  
  if( msisdn != "" )
  {
  	if(!msisdn.match(patron)) {
      		alert('MSISDN no valido');
      		return false;
  	}
  }

  if(msisdn == "" && fechai == "" )
  {
    	alert('Debes ingresar una fecha de busqueda o un MSISDN');
    	return false;

  }
  else
	return true;
}

function ValidaInfoListaBlanca()
{
  console.debug(document.myform);	
  var ini_inscripcion = document.myform.ini_inscripcion.value;
  var fin_inscripcion = document.myform.fin_inscripcion.value;
  var ini_consumo = document.myform.ini_consumo.value;
  var fin_consumo = document.myform.fin_consumo.value;

  if( ini_inscripcion == "" )
  {
	alert('Debes ingresar la fecha inicial de inscripcion');
	return false;
  }
  if( fin_inscripcion == "" )
  {
	alert('Debes ingresar la fecha final de inscripcion');
	return false;
  }
  if( ini_consumo == "" )
  {
	alert('Debes ingresar la fecha inicial de consumo');
	return false;
  }
  if( fin_consumo == "" )
  {
	alert('Debes ingresar la fecha final de consumo');
	return false;
  }

  if(ini_inscripcion >= fin_inscripcion)
  {
    	alert('La fecha inicial de inscripcion debe ser menor que la final');
    	return false;
  }
  if(ini_consumo >= fin_consumo)
  {
    	alert('La fecha inicial de consumo debe ser menor que la final');
    	return false;
  }
  if(ini_inscripcion >= ini_consumo)
  {
    	alert('La fecha inicial de inscripcion debe ser menor que la fecha inicial de consumo');
    	return false;
  }

  return true;
}

function validaFechas()
{
  var fechai = document.myform.fecha_ini.value;
  var fechaf = document.myform.fecha_fin.value;
  if(fechai != "" && fechaf != "")
  {
  	if(fechai > fechaf)
  	{
    		alert('La fecha inicial no puede ser mayor que la final');
    		return false;
  	}
  	else
    		return true;
  }
  else
	return true;
}


function validaFecha()
{
  var fechai = document.myform.fecha_ini.value;
  

  if(fechai == "" )
  {
    	alert('Debes ingresar una fecha de busqueda');
    	return false;

  }
  else
	return true;
}

function ValidaMsisdn()
{
  var msisdn = document.myform.msisdn.value;
  var patron = /^\d{1,14}$/;
  if(!msisdn.match(patron)) {
      alert('El MSISDN debe ser un numero');
      return false;
  }

  return true;
}

function ConfirmarActivarBolsa(id_bolsa, unidad)
{
    if (confirm('Esta seguro que desea pegar la bolsa ' + id_bolsa + '?'))
    {
    	document.myform.action = 'reportes/bolsas/activar?id_bolsa=' + id_bolsa + '&unidad='+ unidad;
        document.myform.target = 'ifrm_process';
        document.myform.submit();
    }
}

function ConfirmarCompra( promo, msisdn )
{
	//javascript:cargarPanelCentral('reportes/compras/bl?msisdn=$msisdn&id_promo=$id_promo')
    if (confirm('Esta seguro que desea comprar la promocion ' + promo + '?'))
    {
		//document.myform.action = '../reportes/promociones/comprar?id_promo=' + promo + '&msisdn='+ msisdn;
        //document.myform.submit();
        document.myform.action = 'reportes/compras/bl?id_promo=' + promo + '&msisdn='+ msisdn;
        document.myform.submit();
      	return true;
    }
	return false;
}

function excelConsOnline(msisdn)
{
	document.myform.action = 'reportes/cons-online/excel?msisdn='+ msisdn;
    document.myform.target = 'ifrm_process';
    document.myform.submit();
}

function excelPromocionesCliente(msisdn)
{
	document.myform.action = 'reportes/compras/excel?msisdn='+ msisdn;
    document.myform.target = 'ifrm_process';
    document.myform.submit();	
}

function ConfirmarCompra2( promo, msisdn )
{
    if (confirm('Esta seguro que desea comprar la promocion ' + promo + '?'))
    {
        document.myform.action = 'reportes/promociones/comprar?id_promo=' + promo + '&msisdn='+ msisdn;
        document.myform.submit();
    }
}

function ConfirmarEliminarPromo(id_promo, msisdn)
{
    if (confirm('Esta seguro que desea eliminar la promocion ' + id_promo + '?'))
    {
        document.myform.action = 'reportes/promociones/eliminar?id_promo=' + id_promo + '&msisdn='+ msisdn;
        document.myform.target = 'ifrm_process';
        document.myform.submit();
    }

}

function validaFallas()
{
  var fechai = document.myform.fecha_ini.value;
  var fechaf = document.myform.fecha_fin.value;
  var operacion = document.myform.operacion.value;
 
   if( operacion == "") {
       alert('Debe seleccionar una operacion');
       return false;
   }

  if(fechai != "" && fechaf != "")
  {
  	if(fechai > fechaf)
  	{
    		alert('La fecha inicial no puede ser mayor que la final');
    		return false;
  	}
  	else
    		return true;
  }
  else
	return true;
}

function validaRecurrencia()
{
	/*
	0: No recurrente
	1: Recurrencia Ilimitada
	2: Recurrencia Limitada
	*/

	if( document.myform.recurrencia.value == "1" )
	{
		document.myform.cod_tasacion_rec.disabled = false;
		document.myform.limite_recurrencia.disabled = true;
	}
	else if( document.myform.recurrencia.value == "2" )
	{
		document.myform.cod_tasacion_rec.disabled = false;
		document.myform.limite_recurrencia.disabled = false;
		
	}
	else
	{
		document.myform.cod_tasacion_rec.value = '';
		document.myform.cod_tasacion_rec.disabled = true;
	
		document.myform.limite_recurrencia.value = '0';
		document.myform.limite_recurrencia.disabled = true;

	}
	return true;
}

function activaFormaCobro()
{
   if( document.myform.tipo_cliente.value == "T" || document.myform.tipo_cliente.value == "H" ) 
   {
        document.myform.cobro_hib.disabled = false;
   }
   else
   {
        document.myform.cobro_hib.disabled = true;
   }
   if( document.myform.costo.value == 0 && (document.myform.tipo_cliente.value == "P" || document.myform.tipo_cliente.value == "H") )
   {
	document.myform.abono.disabled = false;
   }
   else
   {
	document.myform.abono.disabled = true;
	document.myform.abono.value = '0';
   }
}

function activaAbono()
{
	if( document.myform.costo.value == 0 )
	{
        	document.myform.abono.disabled = false;
   	}
   	else
	{
        	document.myform.abono.disabled = true;
		document.myform.abono.value = '0';
	}
}

function validaNotifica()
{
// alert(document.myform.notifica.checked);
   if( document.myform.notifica.checked == true || document.myform.envia_sms.checked == true ) //notifica
   {
	document.myform.txt_exito.disabled = false;
	document.myform.txt_error.disabled = false;
	document.myform.txt_sin_saldo.disabled = false;
// 	document.myform.txt_exito.value = 'aaaalgo';
   }
   else
   {
// 	document.myform.txt_exito.value = '';
	document.myform.txt_exito.disabled = true;
	document.myform.txt_error.disabled = true;
	document.myform.txt_sin_saldo.disabled = true;
   }
}

function activaBotones()
{
 alert('Debe seleccionar una operacion');
 for (i=0; ele=document.myform.kk[i]; i++)
     ele.disabled = false;
 document.myform.Todos.disabled[0] = true;
}

function seleccionar_todo()
{
	for (i=0;i<document.myform.elements.length;i++)
	{
		if(document.myform.elements[i].type == "checkbox")
			document.myform.elements[i].checked=1;
	}
}

function deseleccionar_todo()
{
	for (i=0;i<document.myform.elements.length;i++)
	{
		if(document.myform.elements[i].type == "checkbox")
			document.myform.elements[i].checked=0;
	}
}

