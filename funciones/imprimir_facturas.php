<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/funciones/imprimir_facturas.php
 * @description: Genera una vista de impresión (comprobante de entrega) optimizada 
 * para papel. Agrupa automáticamente los registros seleccionados
 * por Empresa destino, inyectando saltos de página físicos.
 * @dependencies:
 * - Config/Conexión: ../admin/funciones/conecta.php
 * - Frontend: Dispara la ventana nativa de impresión del navegador vía JS.
 * -----------------------------------------------------------------------------
 */

// Inclusión del script de conexión al motor de bases de datos
require_once("../admin/funciones/conecta.php");
$con = conecta();

// Recuperación de la cadena de IDs separados por comas desde la URL
$ids = $_GET['ids'] ?? [];

// Cláusula de salvaguarda: Detiene la ejecución si no se enviaron parámetros
if(empty($ids)){
    die("No hay facturas");
}

/**
 * 1. SANITIZACIÓN Y CONFIGURACIÓN SQL
 * Transforma el string de IDs en un array, fuerza cada elemento a entero estricto (intval) 
 * para prevenir inyecciones SQL y reconstruye una cadena segura para la cláusula IN.
 */
$ids = array_map('intval', explode(',', $ids));
$in = implode(',', $ids);

/* =============================================================================
   2. CONSULTA EXTRACTORA DE DATOS FISCALES (RELACIÓN MAESTRO)
   ============================================================================= */
$sql = "SELECT 
            f.id,
            f.fecha,
            f.folio,
            f.total,
            p.nombre AS proveedor,
            e.nombre AS empresa
        FROM factura f
        INNER JOIN provedores p ON f.id_provedor = p.id
        INNER JOIN empresa e ON f.id_empresa = e.id
        WHERE f.id IN ($in)
        ORDER BY e.nombre, f.fecha"; // Es indispensable ordenar por empresa para la agrupación limpia

$res = $con->query($sql);

/* =============================================================================
   3. ALGORITMO DE AGRUPACIÓN MULTIDIMENSIONAL
   ============================================================================= */
$empresas = [];

/**
 * Mapea los resultados de la base de datos dentro de un arreglo bidimensional.
 * La llave primaria del arreglo es el nombre de la empresa; dentro de cada una
 * se anida un sub-arreglo secuencial con sus respectivas facturas.
 */
while($row = $res->fetch_assoc()){
    $empresas[$row['empresa']][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Impresión de facturas</title>
    <link rel="icon" href="../admin/fotos/logo.png" type="image/jpg">

    <style>
        body { 
            font-family: Arial, sans-serif; 
        }

        /* Fuerza un salto de página físico al imprimir después de cada bloque de empresa */
        .empresa {
            page-break-after: always;
            margin-bottom: 40px;
        }

        h2 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Bloque inferior destinado a firmas físicas de acuse de recibo */
        .firma {
            margin-top: 80px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .linea {
            border-bottom: 1px solid #000;
            margin-top: 40px;
            width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Media Query: Aplica reglas exclusivas al momento de mandar a la impresora */
        @media print {
            button { 
                display: none; /* Oculta botones o elementos de interfaz no deseados en papel */
            }
        }
    </style>
</head>

<body>

<?php foreach($empresas as $nombreEmpresa => $facturas){ ?>

<div class="empresa">

    <h2>ENTREGA DE FACTURAS "<?= htmlspecialchars($nombreEmpresa) ?>"</h2>

    <table>
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>Fecha</th>
                <th>Folio</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($facturas as $f){ ?>
            <tr>
                <td><?= htmlspecialchars($f['proveedor']) ?></td>
                <td><?= date('d/m/Y', strtotime($f['fecha'])) ?></td>
                <td><?= htmlspecialchars($f['folio']) ?></td>
                <td>$<?= number_format($f['total'], 2) ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="firma">
        FIRMA ENTREGADO
        <div class="linea"></div>
    </div>

</div>

<?php } ?>

<script>
    window.print();
</script>

</body>
</html>