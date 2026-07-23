/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/js/provedor_alta.js
 * @description: Controlador de eventos en el cliente (Frontend) para el módulo
 * de proveedores. Valida campos y realiza llamadas asíncronas
 * vía AJAX para evitar nombres duplicados.
 * @dependencies: 
 * - jQuery 3.x
 * - Backend: funciones/verificar_provedor.php
 * -----------------------------------------------------------------------------
 */

// Bandera global para bloquear el envío del formulario si hay duplicidad
let proveedorExiste = false;

$(document).ready(function(){

    /* =============================================================================
       1. VERIFICACIÓN ASÍNCRONA DE DUPLICADOS (EVENTO BLUR)
       ============================================================================= */
    /**
     * Se dispara cuando el operador remueve el foco del campo '#nombre'.
     * Envía el texto capturado al backend para validar disponibilidad en tiempo real.
     */
    $("#nombre").blur(function(){

        // Remoción de espacios en blanco al inicio y final de la cadena
        let nombre = $.trim($(this).val());

        // Si el campo está vacío, omitimos la llamada al servidor
        if(nombre == ""){
            return;
        }

        $.ajax({
            /**
             * ⚠️ NOTA DE ENTORNO: Si tus formularios están directamente en 'admin/',
             * y tu carpeta de funciones está dentro de 'admin/funciones/', la ruta 
             * correcta debería ser "funciones/verificar_provedor.php" sin el "../".
             */
            url: "../funciones/verificar_provedor.php",
            type: "POST",
            data: {
                nombre: nombre
            },
            success: function(respuesta){
                
                // Si el controlador responde '1', el proveedor ya está registrado
                if($.trim(respuesta) == "1"){
                    proveedorExiste = true;

                    $("#mensajeNombre").html(
                        "<span style='color: #cc0000; font-weight: bold;'>⚠️ El proveedor ya existe</span>"
                    );
                } else {
                    proveedorExiste = false;

                    $("#mensajeNombre").html(
                        "<span style='color: #008800; font-weight: bold;'>✅ Disponible</span>"
                    );
                }
            },
            error: function() {
                console.error("Error de red: No se pudo verificar el proveedor.");
            }
        });
    });

    /* =============================================================================
       2. CONTROL Y VALIDACIÓN PREVIA AL ENVÍO (EVENTO SUBMIT)
       ============================================================================= */
    /**
     * Intercepta el envío del formulario para asegurar el cumplimiento
     * de las reglas de negocio en el frontend.
     */
    $("#formProveedor").submit(function(e){

        let nombre = $.trim($("#nombre").val());
        let irs    = $("#irs").val();

        // Regla 1: El nombre comercial del proveedor es obligatorio
        if(nombre == ""){
            alert("Ingrese el nombre del proveedor");
            e.preventDefault(); // Cancela de golpe la petición POST hacia PHP
            return false;
        }

        // Regla 2: Obligatorio definir la situación fiscal ante el IRS
        if(irs == ""){
            alert("Seleccione si aplica IRS");
            e.preventDefault(); // Cancela la petición POST
            return false;
        }

        // Regla 3: Si la bandera asíncrona está activa, bloquea el registro por duplicidad
        if(proveedorExiste){
            alert("El proveedor ya existe");
            e.preventDefault(); // Evita la inserción de datos redundantes
            return false;
        }
    });
});