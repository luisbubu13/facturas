<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/provedor_lista.php
 * @description: Vista principal del catálogo maestro de proveedores. Despliega
 * de forma tabular las empresas proveedoras activas y mapea
 * las banderas operativas como el estado ante el IRS.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - Componentes: menu.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias del núcleo del sistema
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   1. EXTRACCIÓN DEL DATASET DE PROVEEDORES (READ)
   ============================================================================= */
/**
 * Filtro de negocio: Excluye de forma estricta los registros con borrado lógico (eliminado = 1).
 * Ordena los resultados alfabéticamente para estandarizar la búsqueda visual del operador.
 */
$sql = "SELECT * FROM provedores WHERE eliminado = 0 ORDER BY nombre";
$resultado = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Proveedores</title>

    <link rel="icon" href="fotos/logo.png" type="image/jpg" />
    
    <link rel="stylesheet" href="css/provedores/provedores_lista.css">
    <link rel="stylesheet" href="css/menu.css">
</head>
<body>

<div class="contenedor">

    <div class="barra_superior">
        <?php include("menu.php"); ?>
    </div>

    <div class="barra_superior">
        <a href="provedor_alta.php" class="btn_agregar">
            + Agregar proveedor
        </a>
    </div>

    <div class="encabezado">
        <div>ID</div>
        <div>Proveedor</div>
        <div>IRS</div>
        <div>Editar</div>
        <div>Eliminar</div>
    </div>

    <?php while($fila = $resultado->fetch_assoc()){ ?>

        <div class="fila">

            <div><?= $fila['id'] ?></div>

            <div><?= htmlspecialchars($fila['nombre']) ?></div>

            <div class="irs">
                <?= ($fila['irs'] == 1) ? '✅' : '❌' ?>
            </div>

            <div class="accion">
                <a href="provedor_edita.php?id=<?= $fila['id'] ?>">
                    ✏️
                </a>
            </div>

            <div class="accion">
                <a href="funciones/provedor_elimina.php?id=<?= $fila['id'] ?>">
                    🗑️
                </a>
            </div>

        </div>

    <?php } ?>

</div>

</body>
</html>