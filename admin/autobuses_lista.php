<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/autobuses_lista.php
 * @description: Pantalla del catálogo de autobuses (unidades). Desarrolla un 
 * sistema de búsqueda concurrente, paginación indexada y
 * persistencia de parámetros Query String en los controles.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - Componentes: menu.php, autobus_filtro.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de módulos del núcleo administrativo
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización de la conexión a la base de datos
$con = conecta();

/* =============================================================================
   1. CAPTURA Y SANITIZACIÓN DE FILTROS (MÉTODO GET)
   ============================================================================= */
$unidad = isset($_GET['unidad']) ? trim($_GET['unidad']) : '';
$id_empresa = isset($_GET['id_empresa']) ? intval($_GET['id_empresa']) : 0;

// Condición base obligatoria: Excluir registros que tengan borrado lógico activo
$where = "WHERE a.eliminado = 0";

// Extensión condicional del Query por coincidencia parcial de texto (Búsqueda LIKE)
if($unidad != ''){
    // Se mitigan inyecciones SQL aplicando el método real_escape_string al texto plano
    $where .= " AND a.unidad LIKE '%" . $con->real_escape_string($unidad) . "%'";
}

// Extensión condicional por segmentación relacional (ID de la Empresa)
if($id_empresa > 0){
    $where .= " AND a.id_empresa = $id_empresa";
}

/* =============================================================================
   2. MOTOR DE PAGINACIÓN DINÁMICA - CÁLCULO DE LÍMITES
   ============================================================================= */
$registros_por_pagina = 30; // Tamaño del bloque de registros por llamada al servidor

// Captura e inicialización segura del puntero de página actual
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
if($pagina < 1){
    $pagina = 1;
}

/**
 * Fórmula matemática para hallar el offset del puntero en MySQL:
 * $inicio = (Página Actual - 1) * Registros Permitidos
 */
$inicio = ($pagina - 1) * $registros_por_pagina;

/* =============================================================================
   3. CONSULTA DE AUDITORÍA DE TOTALES (MATH CEIL)
   ============================================================================= */
// Determina cuántos registros coinciden globalmente con los filtros aplicados
$sqlTotal = "SELECT COUNT(*) total FROM autobus a $where";
$resTotal = $con->query($sqlTotal);
$filaTotal = $resTotal->fetch_assoc();
$total_registros = $filaTotal['total'];

// Determina el número máximo de páginas redondeando hacia el entero superior
$total_paginas = ceil($total_registros / $registros_por_pagina);

/* =============================================================================
   4. EXTRACCIÓN DE DATASET INDEXADO (MÉTODO LIMIT)
   ============================================================================= */
// Realiza un INNER JOIN para mapear el nombre de la empresa ligada en lugar del ID numérico
$sql = "SELECT
            a.id,
            a.unidad,
            e.nombre AS empresa
        FROM autobus a
        INNER JOIN empresa e ON a.id_empresa = e.id
        $where
        ORDER BY a.unidad
        LIMIT $inicio, $registros_por_pagina";

$resultado = $con->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de autobuses</title>
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <link rel="stylesheet" href="css/autobuses/autobuses_lista.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/autobuses/autobus_filtro.css">
</head>

<body>

<div class="contenedor">

    <?php include("menu.php"); ?>
    <?php include("autobus_filtro.php"); ?>

    <div class="barra_superior">
        <a href="autobus_alta.php" class="btn_agregar">+ Agregar autobús</a>
    </div>

    <div class="encabezado">
        <div>ID</div>
        <div>Unidad</div>
        <div>Empresa</div>
        <div>Editar</div>
        <div>Eliminar</div>
    </div>

    <?php while($fila = $resultado->fetch_assoc()){ ?>

        <div class="fila">
            <div>
                <?= $fila['id']; ?>
            </div>

            <div>
                <?= htmlspecialchars($fila['unidad']); ?>
            </div>

            <div>
                <?= htmlspecialchars($fila['empresa']); ?>
            </div>

            <div class="accion">
                <a href="autobus_edita.php?id=<?= $fila['id']; ?>">✏️</a>
            </div>

            <div class="accion">
                <a href="funciones/autobus_elimina.php?id=<?= $fila['id']; ?>">🗑️</a>
            </div>
        </div>

    <?php } ?>

    <div class="paginacion">

        <?php if($pagina > 1){ ?>
            <a href="?pagina=<?= $pagina - 1 ?>&unidad=<?= urlencode($unidad) ?>&id_empresa=<?= $id_empresa ?>">
                &larr; Anterior
            </a>
        <?php } ?>

        <?php for($i = 1; $i <= $total_paginas; $i++){ ?>
            <a href="?pagina=<?= $i ?>&unidad=<?= urlencode($unidad) ?>&id_empresa=<?= $id_empresa ?>"
               class="<?= ($i == $pagina) ? 'actual' : ''; ?>">
                <?= $i ?>
            </a>
        <?php } ?>

        <?php if($pagina < $total_paginas){ ?>
            <a href="?pagina=<?= $pagina + 1 ?>&unidad=<?= urlencode($unidad) ?>&id_empresa=<?= $id_empresa ?>">
                Siguiente &rarr;
            </a>
        <?php } ?>

    </div>

</div>

</body>
</html>