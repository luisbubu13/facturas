<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/factura_alta.php
 * @description: Formulario y lógica para el registro de nuevas facturas en el 
 * sistema. Implementa una arquitectura Maestro-Detalle utilizando
 * transacciones SQL para asegurar la integridad de los datos, 
 * además de calcular automáticamente IVA e IRS según el proveedor.
 * @dependencies:
 * - Config/Conexión: admin/funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - CSS: css/factura_alta.css, css/select2.min.css
 * - JS: js/jquery-3.1.1.min.js, js/select2.min.js, js/factura_alta.js
 * -----------------------------------------------------------------------------
 */

// Carga de librerías esenciales para conectividad y control de sesión
require_once("admin/funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización del objeto de conexión a la base de datos
$con = conecta();

/* =============================================================================
   1. PROCESAMIENTO DEL FORMULARIO (MÉTODO POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Extracción y sanitización de datos primarios del encabezado
    $id_empresa  = intval($_POST['id_empresa']);
    $id_provedor = intval($_POST['id_provedor']);
    $fecha       = $_POST['fecha'];
    $folio       = trim($_POST['folio']);

    // Deserialización del string JSON recibido desde el cliente con los detalles de la factura
    $detalles = json_decode($_POST['detalles'], true);

    // Validación estricta del lado del servidor para asegurar la presencia de datos requeridos
    if(
        $id_empresa == 0 ||
        $id_provedor == 0 ||
        $fecha == '' ||
        $folio == '' ||
        empty($detalles)
    ){
        echo "
        <script>
            alert('Datos incompletos');
            history.back();
        </script>";
        exit;
    }

    /* -------------------------------------------------------------------------
       A) CÁLCULO DE TOTALES E IMPUESTOS
       ------------------------------------------------------------------------- */
    $subtotalGeneral = 0;

    // Sumatoria de los subtotales de cada concepto en el arreglo deserializado
    foreach($detalles as $detalle){
        $subtotalGeneral += floatval($detalle['subtotal']);
    }

    // Cálculo del Impuesto al Valor Agregado (IVA 16%)
    $iva = $subtotalGeneral * 0.16;

    // Consulta para verificar si el proveedor está sujeto a retención de impuesto (IRS)
    $sql = "SELECT irs FROM provedores WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_provedor);
    $stmt->execute();
    $res = $stmt->get_result();
    $prov = $res->fetch_assoc();

    $irs = 0;
    // Si el proveedor aplica para IRS, se calcula la retención del 1.25% sobre el subtotal
    if($prov['irs'] == 1){
        $irs = $subtotalGeneral * 0.0125;
    }

    // Ecuación fiscal para determinar el monto neto final de la factura
    $total = $subtotalGeneral + $iva - $irs;

    /* -------------------------------------------------------------------------
       B) BLOQUE TRANSACCIONAL DE INSERCIÓN (ACID)
       ------------------------------------------------------------------------- */
    try {
        // Desactiva el autocommit para iniciar la transacción de manera segura
        $con->begin_transaction();

        $fecha_registro = date('Y-m-d');

        // Inserción del registro maestro (Encabezado de la Factura)
        $sql = "INSERT INTO factura (id_empresa, id_provedor, folio, fecha, total, fecha_registro)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("iissds", $id_empresa, $id_provedor, $folio, $fecha, $total, $fecha_registro);
        $stmt->execute();

        // Recuperación del ID auto-generado para ligar las relaciones hijas (detalles)
        $id_factura = $con->insert_id;

        // Inserción del registro detalle (Conceptos individuales asociados al ID Maestro)
        $sql = "INSERT INTO detalle_factura (concepto, subtotal, id_autobus, id_factura, oste, orden)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmtDetalle = $con->prepare($sql);

        // Iteración para procesar y escribir cada concepto en la base de datos
        foreach($detalles as $detalle){
            $concepto   = trim($detalle['concepto']);
            $subtotal   = floatval($detalle['subtotal']);
            $id_autobus = $detalle['id_autobus'];
            $oste       = trim($detalle['oste']);
            $orden      = trim($detalle['orden']);

            // Tratamiento de valores vacíos para respetar restricciones de llave foránea opcional (NULL)
            if($id_autobus == ''){
                $id_autobus = null;
            }

            $stmtDetalle->bind_param("sdiiss", $concepto, $subtotal, $id_autobus, $id_factura, $oste, $orden);
            $stmtDetalle->execute();
        }

        // Si todas las inserciones fueron exitosas, se consolidan permanentemente los datos en el motor
        $con->commit();

        // Redirección al panel de facturas con flujo finalizado de manera correcta
        header("Location: facturas.php");
        exit;

    } catch(Exception $e) {
        // Si ocurre cualquier fallo o interrupción, se revierte todo al estado original
        $con->rollback();

        echo "
        <script>
            alert('Error al guardar la factura');
            history.back();
        </script>";
    }
}

/* =============================================================================
   2. CONSULTAS DE LLENADO PARA CATÁLOGOS DINÁMICOS (ELEMENTOS SELECT)
   ============================================================================= */

// Extracción del catálogo activo de Empresas
$sql = "SELECT id, nombre FROM empresa WHERE eliminado = 0 ORDER BY nombre";
$resEmpresas = $con->query($sql);

// Extracción del catálogo activo de Proveedores (incluyendo bandera fiscal 'irs')
$sql = "SELECT id, nombre, irs FROM provedores WHERE eliminado = 0 ORDER BY nombre";
$resProveedores = $con->query($sql);

// Extracción del catálogo activo de Unidades de Autobuses
$sql = "SELECT id, unidad FROM autobus WHERE eliminado = 0 ORDER BY unidad";
$resAutobuses = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Alta de factura</title>

    <link rel="stylesheet" href="css/factura_alta.css">
    <link rel="stylesheet" href="css/select2.min.css">
    <link rel="icon" href="admin/fotos/logo.png" type="image/jpg">

    <script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/select2.min.js"></script>
    <script src="js/factura_alta.js"></script>
</head>

<body>

<div class="contenedor">

    <h1>Alta de factura</h1>

    <div class="barra">
        <a href="facturas.php" class="btnRegresar">← Regresar</a>
    </div>

    <form id="formFactura" method="POST">

        <h2>Datos de la factura</h2>

        <div class="datosFactura">

            <div>
                <label>Empresa</label>
                <select name="id_empresa" id="id_empresa" class="buscador">
                    <option value="">Seleccione una empresa</option>
                    <?php while($empresa = $resEmpresas->fetch_assoc()){ ?>
                        <option value="<?= $empresa['id'] ?>">
                            <?= htmlspecialchars($empresa['nombre']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label>Proveedor</label>
                <select name="id_provedor" id="id_provedor" class="buscador">
                    <option value="">Seleccione un proveedor</option>
                    <?php while($prov = $resProveedores->fetch_assoc()){ ?>
                        <option value="<?= $prov['id'] ?>" data-irs="<?= $prov['irs'] ?>">
                            <?= htmlspecialchars($prov['nombre']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label>Fecha de factura</label>
                <input type="date" name="fecha" id="fecha" value="<?= date('Y-m-d') ?>">
            </div>

            <div>
                <label>Folio</label>
                <input type="text" name="folio" id="folio">
            </div>

        </div>

        <hr>

        <h2>Agregar detalle</h2>

        <div class="datosDetalle">

            <div>
                <label>Autobús (opcional)</label>
                <select id="id_autobus" class="buscador">
                    <option value="">Sin autobús</option>
                    <?php while($bus = $resAutobuses->fetch_assoc()){ ?>
                        <option value="<?= $bus['id'] ?>">
                            <?= htmlspecialchars($bus['unidad']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label>Orden (opcional)</label>
                <input type="text" id="orden">
            </div>

            <div>
                <label>OSTE (opcional)</label>
                <input type="text" id="oste">
            </div>

            <div>
                <label>Subtotal</label>
                <input type="number" step="0.01" min="0.01" id="subtotal">
            </div>

        </div>

        <label>Concepto</label>
        <input type="text" id="concepto">

        <button type="button" id="btnAgregar">Agregar detalle</button>

        <hr>

        <h2>Detalle de la factura</h2>

        <table id="tablaDetalles">
            <thead>
                <tr>
                    <th>Unidad</th>
                    <th>Orden</th>
                    <th>OSTE</th>
                    <th>Concepto</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr id="sinDetalles">
                    <td colspan="6">No hay detalles agregados.</td>
                </tr>
            </tbody>
        </table>

        <div class="totales">
            <p>Subtotal: <span id="txtSubtotal">$0.00</span></p>
            <p>IVA (16%): <span id="txtIVA">$0.00</span></p>
            <p>IRS (1.25%): <span id="txtIRS">$0.00</span></p>
            <h3>TOTAL: <span id="txtTotal">$0.00</span></h3>
        </div>

        <input type="hidden" name="detalles" id="detalles">

        <button type="submit" id="btnGuardar" disabled>Guardar factura</button>

    </form>

</div>

</body>
</html>