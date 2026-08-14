/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/js/factura_alta.js
 * @description: Controla la reactividad del formulario de alta de facturas.
 * Gestiona el agregado, remoción y cálculo aritmético de los
 * conceptos en tiempo real, empaquetándolos en JSON.
 * @dependencies: jQuery 3.1.1+, Select2 Plugin
 * -----------------------------------------------------------------------------
 */

// Estado global en memoria: Almacena la colección de conceptos (detalles) agregados
let detalles = [];

$(document).ready(function(){

    /* =============================================================================
       1. INICIALIZACIÓN DE COMPONENTES DE INTERFAZ
       ============================================================================= */

    $('.buscador').select2({
        width: '100%',
        language: {
            noResults: function() {
                return "Sin resultados";
            }
        }
    });

    actualizarTotales();


    /* =============================================================================
       2. BLOQUEO DEL SUBMIT MEDIANTE ENTER
       ============================================================================= */

    /**
     * Evita que presionar Enter dentro de cualquier campo del formulario
     * provoque el envío automático de la factura.
     *
     * La factura solamente podrá guardarse mediante el botón
     * "Guardar factura".
     */
    $("#formFactura").on("keydown", function(e){

        if(e.key === "Enter"){

            e.preventDefault();

            return false;
        }

    });


    /* =============================================================================
       3. CONTROLADORES DE EVENTOS
       ============================================================================= */

    /**
     * Evento: Adición de Conceptos al Detalle
     */
    $("#btnAgregar").click(function(){

        // Extracción de datos del DOM
        let id_autobus = $("#id_autobus").val();
        let unidad = $("#id_autobus option:selected").text();

        let orden = $.trim($("#orden").val());
        let oste = $.trim($("#oste").val());
        let concepto = $.trim($("#concepto").val());
        let subtotal = parseFloat($("#subtotal").val());

        // Validación estricta: Presencia obligatoria del concepto
        if(concepto == ""){

            alert("Ingrese el concepto.");
            $("#concepto").focus();

            return;
        }

        // Validación estricta: El subtotal debe ser mayor a cero
        if(isNaN(subtotal) || subtotal <= 0){

            alert("Ingrese un subtotal válido.");
            $("#subtotal").focus();

            return;
        }

        // Si no se vincula un autobús
        if(id_autobus == ""){

            unidad = "-----";

        }

        // Elimina la fila que indica que no existen detalles
        $("#sinDetalles").remove();


        // Construcción del objeto detalle
        let detalle = {

            id_autobus: id_autobus,
            unidad: unidad,
            orden: orden,
            oste: oste,
            concepto: concepto,
            subtotal: subtotal

        };


        // Agrega el detalle al arreglo
        detalles.push(detalle);


        // Índice actual
        let indice = detalles.length - 1;


        // Construcción de la fila
        let fila = `
            <tr data-indice="${indice}">
                <td>${unidad}</td>
                <td>${orden}</td>
                <td>${oste}</td>
                <td>${concepto}</td>
                <td>$${subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btnEliminar">🗑️</button>
                </td>
            </tr>
        `;


        // Inserta la fila
        $("#tablaDetalles tbody").append(fila);


        // Limpia los campos
        limpiarDetalle();


        // Actualiza los totales
        actualizarTotales();

    });


    /**
     * Evento Delegado: Eliminación de Conceptos
     */
    $(document).on("click", ".btnEliminar", function(){

        let fila = $(this).closest("tr");

        let indice = fila.data("indice");


        // Elimina el detalle del arreglo
        detalles.splice(indice, 1);


        // Reconstruye la tabla
        reconstruirTabla();


        // Recalcula totales
        actualizarTotales();

    });


    /**
     * Evento: Cambio de Proveedor
     */
    $("#id_provedor").change(function(){

        actualizarTotales();

    });


    /**
     * Evento: Envío del Formulario
     *
     * IMPORTANTE:
     * Este evento solamente se ejecutará cuando se presione
     * explícitamente el botón "Guardar factura".
     */
    $("#formFactura").submit(function(e){

        let empresa = $("#id_empresa").val();
        let proveedor = $("#id_provedor").val();
        let fecha = $("#fecha").val();
        let folio = $.trim($("#folio").val());


        // Validación de empresa
        if(empresa == ""){

            alert("Seleccione la empresa.");

            e.preventDefault();

            return false;

        }


        // Validación de proveedor
        if(proveedor == ""){

            alert("Seleccione el proveedor.");

            e.preventDefault();

            return false;

        }


        // Validación de fecha
        if(fecha == ""){

            alert("Seleccione la fecha.");

            e.preventDefault();

            return false;

        }


        // Validación de folio
        if(folio == ""){

            alert("Ingrese el folio.");

            e.preventDefault();

            return false;

        }


        // Validación de detalles
        if(detalles.length == 0){

            alert("Debe agregar al menos un detalle.");

            e.preventDefault();

            return false;

        }


        /**
         * SERIALIZACIÓN
         *
         * Convierte el arreglo de detalles a JSON
         * para enviarlo al PHP.
         */
        $("#detalles").val(JSON.stringify(detalles));

    });

});


/* =============================================================================
   4. FUNCIONES DE SOPORTE
   ============================================================================= */


/**
 * Limpia los campos utilizados para agregar un detalle.
 */
function limpiarDetalle(){

    $("#id_autobus").val("").trigger("change");

    $("#orden").val("");

    $("#oste").val("");

    $("#concepto").val("");

    $("#subtotal").val("");


    // Regresa el foco al concepto
    $("#concepto").focus();

}


/**
 * Motor de cálculo de totales.
 */
function actualizarTotales(){

    let subtotalGeneral = 0;


    // Sumatoria de subtotales
    detalles.forEach(function(det){

        subtotalGeneral += parseFloat(det.subtotal);

    });


    // IVA 16%
    let iva = subtotalGeneral * 0.16;


    // IRS
    let irs = 0;


    let proveedor = $("#id_provedor option:selected");


    if(proveedor.data("irs") == 1){

        irs = subtotalGeneral * 0.0125;

    }


    // Total
    let total = subtotalGeneral + iva - irs;


    // Renderizado
    $("#txtSubtotal").html(
        "$" + subtotalGeneral.toFixed(2)
    );

    $("#txtIVA").html(
        "$" + iva.toFixed(2)
    );

    $("#txtIRS").html(
        "$" + irs.toFixed(2)
    );

    $("#txtTotal").html(
        "$" + total.toFixed(2)
    );


    // Habilita el botón solamente cuando existe al menos un detalle
    $("#btnGuardar").prop(
        "disabled",
        detalles.length == 0
    );

}


/**
 * Reconstruye la tabla después de eliminar un detalle.
 */
function reconstruirTabla(){

    let tbody = $("#tablaDetalles tbody");


    // Limpia la tabla
    tbody.html("");


    // Si ya no existen detalles
    if(detalles.length == 0){

        tbody.html(`
            <tr id="sinDetalles">
                <td colspan="6">
                    No hay detalles agregados.
                </td>
            </tr>
        `);

        return;
    }


    // Reconstruye las filas
    detalles.forEach(function(det, i){

        let fila = `
            <tr data-indice="${i}">
                <td>${det.unidad}</td>
                <td>${det.orden}</td>
                <td>${det.oste}</td>
                <td>${det.concepto}</td>
                <td>$${parseFloat(det.subtotal).toFixed(2)}</td>
                <td>
                    <button type="button" class="btnEliminar">
                        🗑️
                    </button>
                </td>
            </tr>
        `;


        tbody.append(fila);

    });

}