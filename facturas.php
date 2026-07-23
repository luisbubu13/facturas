<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/facturas.php
 * @description: Panel principal de consulta de facturas para usuarios generales.
 * Maneja filtros avanzados de búsqueda, paginación de resultados
 * y renderiza el listado dinámico con controles de acción.
 * @dependencies:
 * - Config/Conexión: admin/funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - Componente: menu.php (Barra lateral e interfaz de filtros)
 * - CSS: css/facturas.css, css/menu.css
 * - JS: js/jquery-3.1.1.min.js, js/facturas_acciones.js
 * -----------------------------------------------------------------------------
 */

// Importación de archivos de configuración, conexión y verificación de seguridad
require_once("admin/funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicializa la conexión orientada a objetos de MySQLi
$con = conecta();

/* =============================================================================
   1. RECEPCIÓN Y SANITIZACIÓN DE FILTROS (MÉTODO GET)
   ============================================================================= */

$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin    = isset($_GET['fecha_fin'])    ? $_GET['fecha_fin']    : '';

// Casting estricto a entero (intval) para evitar inyecciones SQL en IDs numéricos
$id_provedor  = isset($_GET['id_provedor'])  ? intval($_GET['id_provedor'])  : 0;
$id_empresa   = isset($_GET['id_empresa'])   ? intval($_GET['id_empresa'])   : 0;

// Eliminación de espacios en blanco redundantes en la cadena de búsqueda
$folio        = isset($_GET['folio'])        ? trim($_GET['folio'])        : '';

$estado       = isset($_GET['estado'])       ? $_GET['estado']             : '';

/* =============================================================================
   2. CONSTRUCCIÓN DINÁMICA DE LA CLÁUSULA WHERE (SQL)
   ============================================================================= */

// Condición base que siempre evalúa como verdadera (facilita la concatenación con AND)
$where = "WHERE 1=1";

if($fecha_inicio != ''){
    $where .= " AND f.fecha >= '$fecha_inicio'";
}

if($fecha_fin != ''){
    $where .= " AND f.fecha <= '$fecha_fin'";
}

if($id_provedor > 0){
    $where .= " AND f.id_provedor = $id_provedor";
}

if($id_empresa > 0){
    $where .= " AND f.id_empresa = $id_empresa";
}

if($folio != ''){
    // Escapa caracteres especiales para bloquear ataques de inyección SQL en búsquedas parciales
    $folio = $con->real_escape_string($folio);
    $where .= " AND f.folio LIKE '%$folio%'";
}

if($estado !== ''){
    $estado = intval($estado);
    $where .= " AND f.estado = $estado";
}

/* =============================================================================
   3. CONFIGURACIÓN Y MATEMÁTICA DE PAGINACIÓN
   ============================================================================= */

$registros_por_pagina = 30; // Bloque límite de registros a desplegar por pantalla

// Obtiene la página actual, si no está definida asigna la primera por defecto
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
if($pagina < 1){
    $pagina = 1;
}

// Calcula el índice de desplazamiento (offset) de inicio para la consulta SQL
$inicio = ($pagina - 1) * $registros_por_pagina;

/* =============================================================================
   4. CONSULTA DEL TOTAL DE REGISTROS Y CÁLCULO DE PÁGINAS
   ============================================================================= */

// Cuenta cuántos registros cumplen con las condiciones actuales de filtrado
$sqlTotal = "SELECT COUNT(*) AS total
             FROM factura f
             INNER JOIN provedores p ON f.id_provedor = p.id
             INNER JOIN empresa e ON f.id_empresa = e.id
             $where";

$resTotal = $con->query($sqlTotal);
$filaTotal = $resTotal->fetch_assoc();
$total_registros = $filaTotal['total'];

// Determina la cantidad total de páginas necesarias mediante redondeo hacia arriba (ceil)
$total_paginas = ceil($total_registros / $registros_por_pagina);

/* =============================================================================
   5. CONSULTA PRINCIPAL LIMITADA (DATASET FINAL)
   ============================================================================= */

// Recupera la información detallada de las facturas ordenadas de forma cronológica descendente
$sql = "SELECT
            f.id,
            f.id_empresa,
            p.nombre AS proveedor,
            e.nombre AS empresa,
            f.fecha,
            f.folio,
            f.total,
            f.estado
        FROM factura f
        INNER JOIN provedores p ON f.id_provedor = p.id
        INNER JOIN empresa e ON f.id_empresa = e.id
        $where
        ORDER BY f.fecha DESC
        LIMIT $inicio, $registros_por_pagina";

$resultado = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Facturas</title>
    <link rel="icon" href="admin/fotos/logo.png" type="image/jpg">
    
    <link rel="stylesheet" href="css/facturas.css">
    <link rel="stylesheet" href="css/menu.css">

    <script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/facturas_acciones.js"></script>
</head>

<body>

<div class="contenedor">

    <?php include("menu.php"); ?>

    <div class="encabezado">
        <div></div>
        <div>Proveedor</div>
        <div>Empresa</div>
        <div>Fecha</div>
        <div>Folio</div>
        <div>Total</div>
        <div>Estado</div>
        <div>Detalle</div>
    </div>

    <?php while($fila = $resultado->fetch_assoc()){ ?>

        <?php
        // Inicialización de variables para la asignación dinámica de clases CSS y etiquetas
        $clase = "";
        $texto = "";

        // Evaluación del código de estado del registro para dar formato visual en el frontend
        switch($fila['estado']){
            case 0:
                $clase = "registrada";
                $texto = "Registrada";
                break;
            case 1:
                $clase = "proceso";
                $texto = "En proceso";
                break;
            case 2:
                $clase = "entregada";
                $texto = "Entregada";
                break;
            case 3:
                $clase = "cancelada";
                $texto = "Cancelada";
                break;
        }
        
        switch($fila['id_empresa']){

            case 1:
                $empresa = "OM";
                break;

            case 2:
                $empresa = "R380";
                break;

            case 3:
                $empresa = "CIM";
                break;

            case 4:
                $empresa = "CM";
                break;

            case 5:
                $empresa = "CGDL";
                break;

            case 6:
                $empresa = "ACT";
                break;

            case 7:
                $empresa = "MA";
                break;

            default:
                $empresa = "-";
        }
        ?>

        <div class="fila">

            <div>
                <input type="checkbox" class="chkFactura" value="<?= $fila['id'] ?>">
            </div>

            <div>
                <?= htmlspecialchars($fila['proveedor']) ?>
            </div>

            <div>
                <?= $empresa ?>
            </div>

            <div>
                <?= date('d/m/Y', strtotime($fila['fecha'])) ?>
            </div>

            <div>
                <?= htmlspecialchars($fila['folio']) ?>
            </div>

            <div>
                $<?= number_format($fila['total'], 2) ?>
            </div>

            <div>
                <span class="estado <?= $clase ?>">
                    <?= $texto ?>
                </span>
            </div>

            <div class="accion">
                <a href="factura_detalle.php?id=<?= $fila['id'] ?>">👁️</a>
            </div>

        </div>

    <?php } ?>

    <div class="paginacion">

        <?php if($pagina > 1){ ?>
            <a href="?pagina=<?= $i ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>&id_provedor=<?= $id_provedor ?>&id_empresa=<?= $id_empresa ?>&folio=<?= urlencode($folio) ?>&estado=<?= urlencode($estado) ?>">
                ← Anterior
            </a>
        <?php } ?>

        <?php for($i = 1; $i <= $total_paginas; $i++){ ?>
            <a
    href="?pagina=<?= $i ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>&id_provedor=<?= $id_provedor ?>&id_empresa=<?= $id_empresa ?>&folio=<?= urlencode($folio) ?>&estado=<?= urlencode($estado) ?>"
                class="<?= ($i == $pagina) ? 'actual' : '' ?>">
                <?= $i ?>
            </a>
        <?php } ?>

        <?php if($pagina < $total_paginas){ ?>
            <a href="?pagina=<?= $pagina + 1 ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>&id_provedor=<?= $id_provedor ?>&id_empresa=<?= $id_empresa ?>&folio=<?= urlencode($folio) ?>&estado=<?= urlencode($estado) ?>">
                Siguiente →
            </a>
        <?php } ?>

    </div>

</div>

</body>
</html>