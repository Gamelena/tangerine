function validainfoFranja (f_ini, f_ter, h_ini, h_ter){
  document.myform.accion.value = "ACTUALIZAR";
  nuevainfoFranja(f_ini, f_ter, h_ini, h_ter);
  //document.myform.submit();
}

function nuevainfoFranja (f_ini, f_ter, h_ini, h_ter){
  if (f_ini.value!="" && f_ter.value!="")
  {
      if (h_ini.value >= h_ter.value){
          alert('Hora Inicio debe ser menor que hora de termino');
          return false;
      }
      document.myform.submit();
  }
  else{
      alert('debe llenar fechas');
      return false;
  }

  

}

function deleteFranja (id){
  if (confirm('Esta seguro de elimnar esta franja')){
    document.myform.accion.value = "BORRAR";
    document.myform.id.value = id;
    document.myform.submit();
  }
}

function desplaza_vista (offset) {
  document.myform.offset.value = offset;
  document.myform.submit();
}

function start_limit(){
  f_ini = document.myform.f_ini.value;
  if (f_ini){
    var date_arr = f_ini.split("-");
    var f_start = new Date(date_arr[0],date_arr[1]-1,date_arr[2]);
    return f_start;
  }
  else{
    return '';
  } 
}

function end_limit(){
  f_ter = document.myform.f_ter.value;
  if (f_ter){
    var date_arr = f_ter.split('-');
    var f_end = new Date(date_arr[0],date_arr[1]-1,date_arr[2]);
    return f_end;
  }
  else{
    return '';
  }
}
