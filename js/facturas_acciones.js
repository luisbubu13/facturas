/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - ARCHIVO DE CONTROL JAVASCRIPT
 * -----------------------------------------------------------------------------
 * @file: facturas/js/facturas_acciones.js
 * @description: Gestiona la selección masiva de facturas en el tablero principal
 * y coordina las peticiones asíncronas (AJAX) para modificar estados
 * por lote (Entregado, Cancelado, Imprimir).
 * -----------------------------------------------------------------------------
 */

// ============================================================================
// VARIABLES DE ESTADO GLOBAL
// ============================================================================

let seleccionadas = [];
let accionActual = "";


// ============================================================================
// INICIALIZACIÓN
// ============================================================================

$(document).ready(function(){


    // =========================================================================
    // SELECCIONAR / DESELECCIONAR TODAS LAS FACTURAS DE LA PÁGINA
    // =========================================================================

    $("#chkTodas").on("change", function(){

        let marcar = this.checked;

        seleccionadas = [];

        $(".chkFactura").each(function(){

            $(this).prop("checked", marcar);

            if(marcar){

                let id = $(this).val();

                if(!seleccionadas.includes(id)){
                    seleccionadas.push(id);
                }

            }

        });

        console.log("Seleccionadas:", seleccionadas);

    });


    // =========================================================================
    // SELECCIÓN INDIVIDUAL DE FACTURAS
    // =========================================================================

    $(document).on("change", ".chkFactura", function(){

        let id = $(this).val();


        if($(this).is(":checked")){

            // Agrega el ID solamente si todavía no existe
            if(!seleccionadas.includes(id)){
                seleccionadas.push(id);
            }

        }else{

            // Elimina el ID del arreglo
            seleccionadas = seleccionadas.filter(function(x){

                return x != id;

            });

        }


        // Actualiza el checkbox maestro
        let total = $(".chkFactura").length;
        let marcadas = $(".chkFactura:checked").length;

        $("#chkTodas").prop(
            "checked",
            total > 0 && total === marcadas
        );


        console.log("Seleccionadas:", seleccionadas);

    });


    // =========================================================================
    // BOTÓN ENTREGADO
    // =========================================================================

    $("#btnEntregado").on("click", function(e){

        e.preventDefault();

        accionActual = "entregado";

        ejecutarAccion("entregado");

    });


    // =========================================================================
    // BOTÓN CANCELAR
    // =========================================================================

    $("#btnCancelar").on("click", function(e){

        e.preventDefault();

        accionActual = "cancelado";

        ejecutarAccion("cancelado");

    });


    // =========================================================================
    // BOTÓN IMPRIMIR
    // =========================================================================

    $("#btnImprimir").on("click", function(e){

        e.preventDefault();

        accionActual = "imprimir";

        ejecutarAccion("imprimir");

    });


});


// ============================================================================
// EJECUTAR ACCIÓN
// ============================================================================

function ejecutarAccion(accion){


    // =========================================================================
    // VALIDAR SELECCIÓN
    // =========================================================================

    if(seleccionadas.length === 0){

        alert("Selecciona al menos una factura");

        return;

    }


    console.log("Acción:", accion);
    console.log("Facturas:", seleccionadas);


    // =========================================================================
    // PETICIÓN AJAX
    // =========================================================================

    $.ajax({

        url: "funciones/factura_accion.php",

        type: "POST",

        dataType: "json",

        data: {

            accion: accion,

            ids: seleccionadas

        },


        // =====================================================================
        // RESPUESTA EXITOSA
        // =====================================================================

        success: function(data){

            console.log("Respuesta:", data);


            // -----------------------------------------------------------------
            // ERROR DEVUELTO POR PHP
            // -----------------------------------------------------------------

            if(!data.ok){

                alert(
                    data.msg ||
                    "Error desconocido"
                );

                return;

            }


            // -----------------------------------------------------------------
            // IMPRESIÓN
            // -----------------------------------------------------------------

            if(accionActual === "imprimir"){

                let url =
                    "funciones/imprimir_facturas.php?ids=" +
                    seleccionadas.join(",");


                window.open(
                    url,
                    "_blank"
                );


                return;

            }


            // -----------------------------------------------------------------
            // ENTREGAR / CANCELAR
            // -----------------------------------------------------------------

            location.reload();

        },


        // =====================================================================
        // ERROR DE AJAX
        // =====================================================================

        error: function(xhr){

            console.log(
                xhr.responseText
            );

            alert(
                "Error de conexión con el servidor"
            );

        }

    });

}