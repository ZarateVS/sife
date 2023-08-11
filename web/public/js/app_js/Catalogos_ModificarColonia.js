$(document).ready(function(){
    $('.selectpicker').selectpicker({
        size: 8,
        language: 'ES'
    });
    $('.selectpicker').selectpicker('refresh');
});

function Habilitarcampos(){
    $("#editarColonia").addClass('disabled');
    $(".form-control").each(function(){
        $(this).attr('readonly', false);
        $(this).attr('disabled', false);
    });
    $(".selectpicker").attr('disabled',false);
    $("#Guardar").removeClass('hidden');
    $("#Guardar").attr('disabled', false);
    $('.selectpicker').selectpicker('refresh');
    
}
function ValidarCampoVacio(){
    var error=0;
    var x=0;
    $("[required]").each(function(){
        if($(this).val()==0 || $(this).val()==""){
            $(this).focus();
            error++;
        }
    
    });
    if(error>0){
        x=1;
    }
   if($("#estado").val()=="" || $("#municipio").val()==""){
       x=1;
   } 
  return x;  
}
function ValidarCP(cp){
    expr = /[0-9]/;
    if (expr.test(cp))
        return "1";
    else
        return "0";
}
function justNumbers(e)
{
   var keynum = window.event ? window.event.keyCode : e.which;
   if (keynum == 8) 
        return true;


  return /\d/.test(String.fromCharCode(keynum));
}
function ValidarLongitud(){
    var x=0;
    if($("#nombre").val().length>100 || $("#cp").val().length>10 || $("#cd").val().length>100){
        x=1;
    }
    return x;
}
$("#estado").change(function(){
    $("#municipio").empty();
    var id_edo = $(this).val();
    $.ajax({
        async: false,
        url:'ObtenerMunicipios',
        type:'POST',
        data:'id='+id_edo,
        success:function(data){
            var data = JSON.parse(data);
            $("#municipio").append('<option value="">-- Seleccione --</option>');
            $.each(data,function(id,v){
                $("#municipio").append('<option value='+v.id_municipio+'>'+v.municipio+'</option>');
            });
        },
        error:function(data){
            $().toastmessage('showToast',{
                sticky:true,
                text:'Error al obtener datos.',
                type:'error',
                position:'middle-center'
            });
        }
    });
    $('#municipio').selectpicker('refresh');
});

$("#Regresar").click(function(){ 
    var id_edo = $("#estado").val();
    var id_mpio = $("#municipio").val();
    document.location.href='Colonia?id_edo='+id_edo+'&id_mpio='+id_mpio;
});

$("#Guardar").click(function(){
    $(this).attr('disabled',true);
    var campos=ValidarCampoVacio();
    var cp=ValidarCP($("#cp").val());
    var longitud=ValidarLongitud();
    if(campos==0){
        if(cp=="1"){
            if(longitud==0){
                $.ajax({
                    url:'ModificarColonia',
                    type:'POST',
                    data:JSON.stringify($('#frmColonia').serializeObject()),
                    success:function(respuesta){
                        if($.trim(respuesta)==1){
                            $().toastmessage('showToast',{
                                sticky:true,
                                text:'Cambios realizados con éxito.',
                                type:'success',
                                position:'middle-center',
                                close:function(){
                                    var id_edo = $("#estado").val();
                                    var id_mpio = $("#municipio").val();
                                    document.location.href='Colonia?id_edo='+id_edo+'&id_mpio='+id_mpio;
                                }
                                
                            });
                            
                        }else if($.trim(respuesta)==2){
                            $().toastmessage('showToast',{
                                sticky:true,
                                text:'Esta colonia ya existe en este municipio.',
                                type:'warning',
                                position:'middle-center',
                                close:function(){
                                    $("#Guardar").attr('disabled',false);
                                }
                            });
                        }else if($.trim(respuesta)==0){
                            $().toastmessage('showToast',{
                                sticky:true,
                                text:'No se realizaron cambios.',
                                type:'notice',
                                position:'middle-center',
                                close:function(){
                                    var id_edo = $("#estado").val();
                                    var id_mpio = $("#municipio").val();
                                    document.location.href='Colonia?id_edo='+id_edo+'&id_mpio='+id_mpio;
                                }
                            });
                        }
                        
                        
                    },
                    error:function(){
                        $().toastmessage('showToast',{
                            sticky:true,
                            text:'La operación fallo',
                            type:'error',
                            position:'middle-center',
                            close:function(){
                                $("#Guardar").attr('disabled',false);
                            }
                        });
                    }
                });
                
            }else{
                $().toastmessage({
                    sticky:true,
                    text:'Algunos campos rebasaron el límite de longitud permitida.',
                    type:'warning',
                    position:'middle-center',
                    close:function(){
                        $("#Guardar").attr('disabled',false);
                    }
                });
            }
            
        }else{
            $().toastmessage('showToast',{
                sticky:true,
                text:'El código postal debe ser un valor númerico positivo.',
                type:'warning',
                position:'middle-center',
                close:function(){
                    $("#Guardar").attr('disabled',false);
                }
            });
        }
        
    }else{
        $().toastmessage('showToast',{
            sticky:true,
            text:'Los campos con * son requeridos.',
            type:'warning',
            position:'middle-center',
            close:function(){
                $("#Guardar").attr('disabled',false);
            }
        });
    }
});