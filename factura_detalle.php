<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/factura_detalle.php
 * @description: Pantalla de consulta individual y desglose de conceptos para
 * una factura seleccionada. Realiza la reconstrucción del 
 * registro maestro y mapea recursivamente sus elementos hijos.
 * @dependencies:
 * - Config/Conexión: admin/funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - CSS: css/factura_detalle.css
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias del núcleo del sistema para sesión y bases de datos
require_once("admin/funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización del controlador de conexión
$con = conecta();

// Validación y parseo del parámetro ID enviado mediante la URL (Método GET)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si el ID no es un número entero positivo válido, aborta y regresa al índice
if($id <= 0){
    header("Location: facturas.php");
    exit;
}

/* =============================================================================
   1. CONSULTA REGISTRO MAESTRO (ENCABEZADO DE LA FACTURA)
   ============================================================================= */
$sql = "SELECT
            f.*,
            e.nombre AS empresa,
            p.nombre AS proveedor
        FROM factura f
        INNER JOIN empresa e ON f.id_empresa = e.id
        INNER JOIN provedores p ON f.id_provedor = p.id
        WHERE f.id = $id";

$res = $con->query($sql);

// Cláusula de protección: si el ID no corresponde a ninguna factura, redirige
if($res->num_rows == 0){
    header("Location: facturas.php");
    exit;
}

// Extracción del dataset maestro de la factura
$factura = $res->fetch_assoc();

/* =============================================================================
   2. CONSULTA REGISTROS HIJOS (CONCEPTOS / DETALLES ASOCIADOS)
   ============================================================================= */
// Se utiliza LEFT JOIN para el catálogo de autobuses debido a que es una relación opcional (NULL)
$sql = "SELECT
            d.*,
            a.unidad
        FROM detalle_factura d
        LEFT JOIN autobus a ON d.id_autobus = a.id
        WHERE d.id_factura = $id
        ORDER BY d.id";

$resDetalles = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle de factura</title>
    <link rel="icon" href="admin/fotos/logo.png" type="image/jpg">
    
    <link rel="stylesheet" href="css/factura_detalle.css">
</head>

<body>

<div class="contenedor">

    <div class="barra">
        <a href="facturas.php" class="btnRegresar">← Regresar</a>
    </div>

    <h1>Detalle de factura</h1>

    <div class="info">

        <div class="campo">
            <label>Empresa</label>
            <span><?= htmlspecialchars($factura['empresa']) ?></span>
        </div>

        <div class="campo">
            <label>Proveedor</label>
            <span><?= htmlspecialchars($factura['proveedor']) ?></span>
        </div>

        <div class="campo">
            <label>Fecha de factura</label>
            <span><?= date('d/m/Y', strtotime($factura['fecha'])) ?></span>
        </div>

        <div class="campo">
            <label>Folio</label>
            <span><?= htmlspecialchars($factura['folio']) ?></span>
        </div>

        <div class="campo">
            <label>Fecha de registro</label>
            <span><?= date('d/m/Y', strtotime($factura['fecha_registro'])) ?></span>
        </div>

        <div class="campo">
            <label>Fecha entregado</label>
            <span>
                <?php
                if(
                    $factura['fecha_entregado'] &&
                    $factura['fecha_entregado'] != '0000-00-00'
                ){
                    echo date('d/m/Y', strtotime($factura['fecha_entregado']));
                } else {
                    // Texto comodín en caso de que la factura siga en flujo interno
                    echo 'Pendiente';
                }
                ?>
            </span>
        </div>

    </div>

    <hr>

    <h2>Detalles</h2>

    <table>
        <thead>
            <tr>
                <th>Unidad</th>
                <th>Orden</th>
                <th>OSTE</th>
                <th>Concepto</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>

        <?php
        // Acumulador local para validar la suma de los conceptos impresos
        $subtotal = 0;

        // Bucle iterativo sobre cada concepto ligado a la factura principal
        while($d = $resDetalles->fetch_assoc()){
            $subtotal += $d['subtotal'];
        ?>
            <tr>
                <td>
                    <?= $d['unidad'] ? htmlspecialchars($d['unidad']) : '-----' ?>
                </td>

                <td><?= htmlspecialchars($d['orden']) ?></td>
                <td><?= htmlspecialchars($d['oste']) ?></td>
                <td><?= htmlspecialchars($d['concepto']) ?></td>
                
                <td>
                    $<?= number_format($d['subtotal'], 2) ?>
                </td>
            </tr>
        <?php } ?>

        </tbody>
    </table>

    <div class="totales">

        <p>
            Subtotal:
            <span>$<?= number_format($subtotal, 2) ?></span>
        </p>

        <p>
            Total:
            <span>$<?= number_format($factura['total'], 2) ?></span>
        </p>

    </div>

</div>

</body>
</html>