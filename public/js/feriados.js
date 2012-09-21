function editar_feriado (id,fecha,descripcion){
  var form = document.myform;
  form.id.value = id;
  form.fecha.value = fecha;
  form.accion.value = "editar";
  form.descripcion.value = descripcion;
}

function elimina_feriado (id){
  var form = document.myform;
  form.id.value = id;
  form.accion.value = "borrar";
  form.submit();
}

function guarda_feriado (){
  document.myform.submit();
}

function lista_feriado (){
  document.myform.accion.value = '';
  document.myform.id.value = '';
  document.myform.fecha.value = '';
  document.myform.submit();
}
