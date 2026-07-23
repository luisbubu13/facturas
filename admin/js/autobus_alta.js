/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/js/autobus_alta.js
 * @description: Controlador de eventos del lado del cliente para el catálogo
 * de autobuses. Realiza validaciones previas al envío y
 * consultas asíncronas para el control de inventario de unidades.
 * @dependencies: 
 * - jQuery 3.x
 * - Backend: funciones/verificar_autobus.php
 * -----------------------------------------------------------------------------
 */

// Bandera lógica global que restringe el envío si la unidad de transporte está duplicada
let autobusExiste = false;

$(document).ready(function(){

    /* =============================================================================
       1. VALIDACIÓN ASÍNCRONA DE UNIDAD (EVENTO BLUR)
       ============================================================================= */
    /**
     * Evalúa la disponibilidad del número de unidad en tiempo real al perder el foco.
     * Soporta flujos de Alta y Edición inyectando dinámicamente la llave primaria.
     */
    $("#unidad").blur(function(){

        // Captura el valor removiendo espacios accidentales en los extremos
        let unidad = $.trim($("#unidad").val());

        // Si el operador vacía el campo, limpia el contenedor visual y aborta la petición
        if(unidad == ""){
            $("#mensajeAutobus").html("");
            return;
        }

        // Si existe el nodo '#id' (Modo Edición) toma su valor; si no, asume 0 (Modo Alta)
        let id = $("#id").val() || 0;

        $.ajax({
            // Ruta relativa estándar apuntando al controlador del backend
            url: "funciones/verificar_autobus.php",
            type: "POST",
            data: {
                unidad: unidad,
                id: id
            },
            success: function(respuesta){

                // Si la respuesta es exacta a '1', la unidad ya se encuentra ocupada
                if($.trim(respuesta) == "1"){
                    autobusExiste = true;

                    $("#mensajeAutobus").html(
                        "<span style='color: #cc0000; font-weight: bold;'>⚠️ La unidad ya existe</span>"
                    );

                } else {
                    autobusExiste = false;

                    $("#mensajeAutobus").html(
                        "<span style='color: #008800; font-weight: bold;'>✅ Unidad disponible</span>"
                    );
                }
            },
            error: function() {
                console.error("Error crítico de comunicación con funciones/verificar_autobus.php");
            }
        });
    });

    /* =============================================================================
       2. REGLAS DE CONTROL PREVIAS AL ENVÍO (EVENTO SUBMIT)
       ============================================================================= */
    /**
     * Intercepta la petición POST del formulario para verificar el estado
     * físico de los campos obligatorios antes de golpear el motor de BD.
     */
    $("#formAutobus").submit(function(e){

        let unidad     = $.trim($("#unidad").val());
        let id_empresa = $("#id_empresa").val(); // Almacena la FK de la empresa vinculada

        // Regla de negocio 1: El identificador numérico o alfanumérico de la unidad es requerido
        if(unidad == ""){
            alert("Ingrese la unidad");
            e.preventDefault(); // Cancela la propagación del submit hacia PHP
            return false;
        }

        // Regla de negocio 2: La unidad forzosamente debe pertenecer a una Empresa corporativa (Llave Foránea)
        if(id_empresa == ""){
            alert("Seleccione una empresa");
            e.preventDefault(); // Bloquea la transacción
            return false;
        }

        // Regla de negocio 3: Impide el registro si el validador AJAX determinó duplicidad en la BD
        if(autobusExiste){
            alert("La unidad ya existe");
            e.preventDefault(); // Salvaguarda anti-redundancia
            return false;
        }
    });
});