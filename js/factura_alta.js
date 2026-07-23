/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - ARCHIVO DE CONTROL JAVASCRIPT
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
    // Transforma los elementos select tradicionales en cajas de búsqueda predictiva
    $('.buscador').select2({
        width: '100%',
        language: {
            noResults: function() {
                return "Sin resultados";
            }
        }
    });

    // Ejecución inicial para setear los contadores e inputs en ceros ($0.00)
    actualizarTotales();

    /* =============================================================================
       2. CONTROLADORES DE EVENTOS (EVENT HANDLERS)
       ============================================================================= */

    /**
     * Evento: Adición de Conceptos al Detalle
     * Extrae los valores de los campos de captura, valida la consistencia de los datos
     * y renderiza la nueva fila directamente en el árbol del DOM.
     */
    $("#btnAgregar").click(function(){

        // Extracción de datos del DOM
        let id_autobus = $("#id_autobus").val();
        let unidad = $("#id_autobus option:selected").text();

        let orden = $.trim($("#orden").val());
        let oste = $.trim($("#oste").val());
        let concepto = $.trim($("#concepto").val());
        let subtotal = parseFloat($("#subtotal").val());

        // Validación estricta: Presencia obligatoria de la descripción del concepto
        if(concepto == ""){
            alert("Ingrese el concepto.");
            $("#concepto").focus();
            return;
        }

        // Validación estricta: El subtotal debe ser un número real mayor a cero
        if(isNaN(subtotal) || subtotal <= 0){
            alert("Ingrese un subtotal válido.");
            $("#subtotal").focus();
            return;
        }

        // Si no se vincula un autobús, se define un formateador visual genérico
        if(id_autobus == ""){
            unidad = "-----";
        }

        // Elimina el row comodín que indica una tabla vacía
        $("#sinDetalles").remove();

        // Estructuración del objeto hijo
        let detalle = {
            id_autobus: id_autobus,
            unidad: unidad,
            orden: orden,
            oste: oste,
            concepto: concepto,
            subtotal: subtotal
        };

        // Mutación del estado global: se inserta el nuevo concepto
        detalles.push(detalle);

        // Obtención del índice actual para asociarlo directamente a la fila HTML
        let indice = detalles.length - 1;

        // Construcción dinámica del nodo tr mediante plantillas de cadena (Template Literals)
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

        // Inyección del nodo en el cuerpo de la tabla
        $("#tablaDetalles tbody").append(fila);

        // Reset de campos y recalculo financiero
        limpiarDetalle();
        actualizarTotales();
    });

    /**
     * Evento Delegado: Eliminación de Conceptos
     * Utiliza la delegación de eventos de jQuery para escuchar clics en botones
     * destructivos que fueron creados de manera dinámica posterior a la carga inicial.
     */
    $(document).on("click", ".btnEliminar", function(){

        let fila = $(this).closest("tr");
        let indice = fila.data("indice"); // Recupera la posición del elemento en el arreglo

        // Remueve el objeto específico del estado global
        detalles.splice(indice, 1);

        // Reestructura los índices del DOM y recalcula impuestos
        reconstruirTabla();
        actualizarTotales();
    });

    /**
     * Evento: Cambio de Proveedor
     * Si el usuario cambia el proveedor, se disparan de nuevo los totales ya que
     * las reglas de negocio dictan que la retención de IRS depende de esta entidad.
     */
    $("#id_provedor").change(function(){
        actualizarTotales();
    });

    /**
     * Evento: Envío del Formulario (Submit Guard)
     * Valida el encabezado maestro de la factura y serializa la colección de objetos
     * dentro del input oculto para su lectura nativa desde PHP.
     */
    $("#formFactura").submit(function(e){

        let empresa = $("#id_empresa").val();
        let proveedor = $("#id_provedor").val();
        let fecha = $("#fecha").val();
        let folio = $.trim($("#folio").val());

        // Validaciones de formulario del lado del cliente
        if(empresa == ""){
            alert("Seleccione la empresa.");
            e.preventDefault(); // Detiene el envío HTTP
            return false;
        }

        if(proveedor == ""){
            alert("Seleccione el proveedor.");
            e.preventDefault();
            return false;
        }

        if(fecha == ""){
            alert("Seleccione la fecha.");
            e.preventDefault();
            return false;
        }

        if(folio == ""){
            alert("Ingrese el folio.");
            e.preventDefault();
            return false;
        }

        // Restricción de negocio: No se permiten facturas sin conceptos
        if(detalles.length == 0){
            alert("Debe agregar al menos un detalle.");
            e.preventDefault();
            return false;
        }

        /**
         * SERIALIZACIÓN CLAVE: Convierte el arreglo multidimensional 'detalles'
         * en un string JSON legible y lo deposita en el input hidden '#detalles'.
         */
        $("#detalles").val(JSON.stringify(detalles));
    });

});

/* =============================================================================
   3. FUNCIONES DE SOPORTE Y LÓGICA DE NEGOCIO
   ============================================================================= */

/**
 * Limpia los inputs del bloque de agregación para permitir capturas consecutivas.
 */
function limpiarDetalle(){
    $("#id_autobus").val("").trigger("change"); // Deshace la selección en Select2
    $("#orden").val("");
    $("#oste").val("");
    $("#concepto").val("");
    $("#subtotal").val("");

    $("#concepto").focus(); // Devuelve el foco al inicio del flujo de captura
}

/**
 * Motor Aritmético: Calcula y despliega los importes de la Factura en caliente.
 */
function actualizarTotales(){

    let subtotalGeneral = 0;

    // Sumatoria cíclica de los subtotales en memoria
    detalles.forEach(function(det){
        subtotalGeneral += parseFloat(det.subtotal);
    });

    // Cálculo del Impuesto al Valor Agregado (16%)
    let iva = subtotalGeneral * 0.16;
    let irs = 0;

    // Inspección de banderas fiscales del Proveedor vía atributos Data del DOM
    let proveedor = $("#id_provedor option:selected");
    if(proveedor.data("irs") == 1){
        // Si el proveedor retiene, se aplica la tasa del 1.25% sobre el subtotal
        irs = subtotalGeneral * 0.0125;
    }

    // Neto definitivo
    let total = subtotalGeneral + iva - irs;

    // Renderizado e inyección de los valores formateados a dos posiciones decimales
    $("#txtSubtotal").html("$" + subtotalGeneral.toFixed(2));
    $("#txtIVA").html("$" + iva.toFixed(2));
    $("#txtIRS").html("$" + irs.toFixed(2));
    $("#txtTotal").html("$" + total.toFixed(2));

    // Deshabilita o habilita el botón de envío si la lista de conceptos está vacía
    $("#btnGuardar").prop("disabled", detalles.length == 0);
}

/**
 * Sincronizador de Índices del DOM.
 * Cuando se elimina un row intermedio, los índices de la matriz cambian. Esta función 
 * limpia el HTML de la tabla y lo vuelve a construir emparejando los atributos 
 * 'data-indice' de los renglones con las nuevas posiciones reales del array.
 */
function reconstruirTabla(){
    let tbody = $("#tablaDetalles tbody");
    tbody.html(""); // Vaciado completo del contenedor

    // Si la matriz quedó vacía tras la remoción, devuelve la fila de advertencia
    if(detalles.length == 0){
        tbody.html(`
            <tr id="sinDetalles">
                <td colspan="6">No hay detalles agregados.</td>
            </tr>
        `);
        return;
    }

    // Re-renderizado secuencial indexado
    detalles.forEach(function(det, i){
        let fila = `
            <tr data-indice="${i}">
                <td>${det.unidad}</td>
                <td>${det.orden}</td>
                <td>${det.oste}</td>
                <td>${det.concepto}</td>
                <td>$${parseFloat(det.subtotal).toFixed(2)}</td>
                <td>
                    <button type="button" class="btnEliminar">🗑️</button>
                </td>
            </tr>
        `;
        tbody.append(fila);
    });
}