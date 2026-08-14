/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - ARCHIVO DE CONTROL JAVASCRIPT
 * -----------------------------------------------------------------------------
 * @file: facturas/js/facturas_acciones.js
 * @description: Gestiona la selección masiva de facturas en el tablero principal
 * y coordina las peticiones asíncronas (AJAX) para modificar estados
 * por lote (Entregado, Cancelado, Imprimir).
 * @dependencies: jQuery 3.1.1+
 * -----------------------------------------------------------------------------
 */

// VARIABLES DE ESTADO GLOBAL
let seleccionadas = []; // Almacena los IDs (Primary Keys) de las facturas cotejadas
let accionActual  = ""; // Registra el último comando síncrono/asíncrono invocado

$(document).ready(function () {

    /* =============================================================================
       1. DELEGACIÓN DE EVENTOS: REACTIVIDAD DE CHECKBOXES
       ============================================================================= */
    /**
     * Escucha los cambios de estado (Check/Uncheck) en las filas del tablero.
     * Al usar $(document).on() garantizamos que el script responda correctamente
     * incluso si la tabla se re-renderiza o pagina dinámicamente.
     */
    $(document).on("change", ".chkFactura", function () {

        let id = $(this).val();

        if ($(this).is(":checked")) {
            // Cláusula de control: Inserta el ID al array únicamente si no existe previamente
            if (!seleccionadas.includes(id)) {
                seleccionadas.push(id);
            }
        } else {
            // Remoción de elementos mutando el arreglo a través de un filtrado predictivo
            seleccionadas = seleccionadas.filter(x => x != id);
        }

        // Trazabilidad técnica en consola de desarrollo
        console.log("Seleccionadas:", seleccionadas);
    });

    /* =============================================================================
       2. CONTROLADORES DE DISPARO (ACCIONES MÁTRIX / BOTONERÍA)
       ============================================================================= */
    
    // Disparador de estado: "Entregado" -> Pasa las facturas a resguardo final
    $("#btnEntregado").on("click", function (e) {
        e.preventDefault(); // Cancela saltos de ancla o recargas de formularios colaterales
        accionActual = "entregado";
        ejecutarAccion("entregado");
    });

    // Disparador de estado: "Cancelado" -> Invalidación administrativa lógica
    $("#btnCancelar").on("click", function (e) {
        e.preventDefault();
        accionActual = "cancelado";
        ejecutarAccion("cancelado");
    });

    // Disparador de estado: "Imprimir" -> Transición de flujo a "En proceso" y generación de PDF/Papel
    $("#btnImprimir").on("click", function (e) {
        e.preventDefault();
        accionActual = "imprimir";
        ejecutarAccion("imprimir");
    });

    /* =============================================================================
       3. MOTOR DE TRANSPORTE ASÍNCRONO (ORQUESTADOR AJAX)
       ============================================================================= */
    /**
     * Transmite los lotes de información recolectados hacia el backend.
     * @param {string} accion - El tipo de comando a ejecutar (entregado|cancelado|imprimir)
     */
    function ejecutarAccion(accion) {

        // Cláusula de salvaguarda: Detiene el flujo si el operador no ha seleccionado filas
        if (seleccionadas.length === 0) {
            alert("Selecciona al menos una factura");
            return;
        }

        // Inicialización de la petición asíncrona HTTP POST
        $.ajax({
            url: "funciones/factura_accion.php",
            type: "POST",
            dataType: "json", // Espera una respuesta estructurada en JSON desde PHP
            data: {
                accion: accion,
                ids: seleccionadas // jQuery serializa automáticamente este array como ids[]
            },
            /**
             * Manejador de Éxito (Success Callback)
             * Procesa la respuesta del servidor una vez validada la transacción en la base de datos.
             */
            success: function (data) {

                // Valida si el controlador PHP retornó un estado operacional negativo
                if (!data.ok) {
                    alert(data.msg || "Error desconocido");
                    return;
                }

                /* -----------------------------------------------------------------
                   FLUJO ALTERNATIVO: RENDERIZACIÓN DE IMPRESIÓN
                   ----------------------------------------------------------------- */
                if (accionActual === "imprimir") {

                    /**
                     * Construye el query string uniendo el array con comas (e.g., ?ids=14,25,36)
                     * para que coincida con el formateador nativo del script de impresión.
                     */
                    let url = "funciones/imprimir_facturas.php?ids=" + seleccionadas.join(",");

                    // Despliega el reporte corporativo en una pestaña o ventana independiente
                    window.open(url, "_blank");

                    return; // Aborta la recarga para permitir que el usuario visualice sus cambios
                }

                /* -----------------------------------------------------------------
                   FLUJO GENERAL: REFREZCO DE TABLERO
                   ----------------------------------------------------------------- */
                // En cancelaciones o entregas, fuerza la recarga limpia para actualizar indicadores visuales
                location.reload();
            },
            /**
             * Manejador de Error Crítico (Fail Callback)
             * Se ejecuta ante caídas de servidor (500), errores de ruta (404) o pérdida de red.
             */
            error: function (xhr) {
                console.log(xhr.responseText); // Registra el volcado del stack trace para depuración
                alert("Error de conexión con el servidor");
            }
        });
    }

});

$(document).ready(function(){

    // ==========================================================
    // SELECCIONAR / DESELECCIONAR TODAS LAS FACTURAS
    // ==========================================================

    $("#chkTodas").on("change", function(){

        $(".chkFactura").prop("checked", this.checked);

    });


    // ==========================================================
    // ACTUALIZAR CHECKBOX GENERAL AL CAMBIAR UNA FACTURA
    // ==========================================================

    $(document).on("change", ".chkFactura", function(){

        const total = $(".chkFactura").length;
        const seleccionadas = $(".chkFactura:checked").length;

        $("#chkTodas").prop(
            "checked",
            total > 0 && total === seleccionadas
        );

    });

});