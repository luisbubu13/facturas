/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/js/empresa_alta.js
 * @description: Controlador de interfaz del lado del cliente para el catálogo
 * de empresas corporativas. Valida campos obligatorios y realiza
 * peticiones asíncronas para el control de nombres duplicados.
 * @dependencies: 
 * - jQuery 3.x
 * - Backend: funciones/verificar_empresa.php
 * -----------------------------------------------------------------------------
 */

// Bandera analítica global que restringe el registro si la razón social está duplicada
let empresaExiste = false;

$(document).ready(function(){

    /* =============================================================================
       1. VERIFICACIÓN DE DISPONIBILIDAD ASÍNCRONA (EVENTO BLUR)
       ============================================================================= */
    /**
     * Evalúa la existencia de la empresa en la Base de Datos al perder el foco del input.
     * Envía de manera inteligente la llave primaria 'id' para dar soporte al flujo de edición.
     */
    $("#nombre").blur(function(){

        // Captura el valor suprimiendo espacios en blanco residuales
        let nombre = $.trim($("#nombre").val());

        // Si el campo se encuentra totalmente vacío, aborta la petición al backend
        if(nombre == ""){
            return;
        }

        // Si detecta el input '#id' en el DOM (Edición) extrae su valor; si no, inicializa en 0 (Alta)
        let id = $("#id").val() || 0;

        $.ajax({
            // Endpoint encargado de procesar la lógica de consulta
            url: "funciones/verificar_empresa.php",
            type: "POST",
            data: {
                nombre: nombre,
                id: id
            },
            success: function(respuesta){

                // El backend retorna un binario textual string. '1' = Duplicado encontrado.
                if($.trim(respuesta) == "1"){
                    empresaExiste = true;

                    $("#mensajeEmpresa").html(
                        "<span style='color: #cc0000; font-weight: bold;'>⚠️ La empresa ya existe</span>"
                    );

                } else {
                    empresaExiste = false;

                    $("#mensajeEmpresa").html(
                        "<span style='color: #008800; font-weight: bold;'>✅ Empresa disponible</span>"
                    );
                }
            },
            error: function() {
                console.error("Error crítico de comunicación con funciones/verificar_empresa.php");
            }
        });
    });

    /* =============================================================================
       2. INTERCEPCIÓN Y VALIDACIÓN DE SEGURIDAD (EVENTO SUBMIT)
       ============================================================================= */
    /**
     * Valida la integridad estructural del formulario antes de permitir la 
     * dispersión de los datos hacia los controladores nativos de PHP.
     */
    $("#formEmpresa").submit(function(e){

        let nombre = $.trim($("#nombre").val());

        // Regla de Negocio 1: El nombre de la organización es mandatorio
        if(nombre == ""){
            alert("Ingrese el nombre de la empresa");
            e.preventDefault(); // Detiene la propagación del flujo POST
            return false;
        }

        // Regla de Negocio 2: Bloquea la transacción si la bandera de redundancia está activa
        if(empresaExiste){
            alert("La empresa ya existe");
            e.preventDefault(); // Anula el guardado de datos duplicados
            return false;
        }
    });
});