/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/js/empleados_alta.js
 * @description: Controlador de eventos del lado del cliente para Alta/Edición.
 * Valida campos obligatorios y realiza peticiones AJAX asíncronas
 * para verificar la disponibilidad del nombre de usuario.
 * -----------------------------------------------------------------------------
 */

let usuarioExiste = false;

$(document).ready(function(){

    // Evento al perder el foco en el campo de usuario
    $("#usuario").blur(function(){

        let usuario = $.trim($("#usuario").val());

        if(usuario == ""){
            return;
        }

        // Si existe el campo 'id' en el DOM (Edición), toma su valor; de lo contrario es 0 (Alta)
        let id = $("#id").val() || 0;

        $.ajax({
            // SOLUCIÓN 1: Corrección de la ruta ('erificar' -> 'verificar')
            url: "funciones/verificar_empleado.php",
            type: "POST",
            data: {
                usuario: usuario,
                id: id
            },
            success: function(respuesta){
                // Estandarización de selectores: Se buscan ambos IDs por si estás en Alta o Edición
                let contenedorMensaje = $("#mensajeEmpleado, #mensajeUsuario");

                if($.trim(respuesta) == "1"){
                    usuarioExiste = true;
                    contenedorMensaje.html(
                        "<span style='color: #cc0000; font-weight: bold;'>⚠️ El usuario ya existe</span>"
                    );
                } else {
                    usuarioExiste = false;
                    contenedorMensaje.html(
                        "<span style='color: #008800; font-weight: bold;'>✅ Usuario disponible</span>"
                    );
                }
            },
            error: function() {
                console.error("Error crítico: No se pudo contactar con funciones/verificar_empleado.php");
            }
        });
    });

    // Validación perimetral previa al envío (Submit)
    $("#formEmpleado").submit(function(e){

        let nombres   = $.trim($("#nombres").val());
        let apellidos = $.trim($("#apellidos").val());
        let usuario   = $.trim($("#usuario").val());
        let password  = $.trim($("#password").val());

        // Identifica si el formulario actual es de edición evaluando la existencia del campo ID
        let esEdicion = ($("#id").length > 0);

        if(nombres == ""){
            alert("Ingrese los nombres");
            e.preventDefault();
            return false;
        }

        if(apellidos == ""){
            alert("Ingrese los apellidos");
            e.preventDefault();
            return false;
        }

        if(usuario == ""){
            alert("Ingrese el usuario");
            e.preventDefault();
            return false;
        }

        // La contraseña sólo es obligatoria si es un registro nuevo (Alta)
        if(!esEdicion && password == ""){
            alert("Ingrese la contraseña");
            e.preventDefault();
            return false;
        }

        // Si el validador asíncrono asienta que el username está duplicado, frena el flujo
        if(usuarioExiste){
            alert("El usuario ya existe");
            e.preventDefault();
            return false;
        }
    });
});