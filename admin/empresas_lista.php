<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/empresas_lista.php
 * @description: Pantalla del catálogo maestro de empresas. Muestra el listado 
 * completo de corporativos vigentes y provee los accesos directos
 * para las operaciones CRUD (Crear, Leer, Actualizar, Borrar).
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
   1. EXTRACCIÓN DEL DATASET DE EMPRESAS (READ)
   ============================================================================= */
/**
 * Condición de negocio: Solo se extraen registros activos (eliminado = 0).
 * Los elementos se ordenan alfabéticamente por su nombre para optimizar 
 * la legibilidad y búsqueda visual del operador.
 */
$sql = "SELECT *
        FROM empresa
        WHERE eliminado = 0
        ORDER BY nombre";

$resultado = $con->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de empresas</title>

    <link rel="stylesheet" href="css/empresas/empresas_lista.css">
    <link rel="stylesheet" href="css/menu.css">

    <link rel="icon" href="fotos/logo.png" type="image/jpg" />
</head>

<body>

<div class="contenedor">

    <?php include("menu.php"); ?>

    <div class="barra_superior">
        <a href="empresa_alta.php" class="btn_agregar">
            + Agregar empresa
        </a>
    </div>

    <div class="encabezado">
        <div>ID</div>
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
                <?= htmlspecialchars($fila['nombre']); ?>
            </div>

            <div class="accion">
                <a href="empresa_edita.php?id=<?= $fila['id']; ?>">
                    ✏️
                </a>
            </div>

            <div class="accion">
                <a href="funciones/empresa_elimina.php?id=<?= $fila['id']; ?>">
                    🗑️
                </a>
            </div>

        </div>

    <?php } ?>

</div>

</body>

</html>