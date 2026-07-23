<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/empleados_lista.php
 * @description: Vista del catálogo maestro de empleados / usuarios internos.
 * Extrae y lista el personal vigente en el sistema, permitiendo
 * canalizar flujos hacia los módulos de creación, edición y baja.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - Componentes: menu.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de módulos de seguridad perimetral y base de datos
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización de la conexión activa a la Base de Datos
$con = conecta();

/* =============================================================================
   1. EXTRACCIÓN DEL DATASET DE EMPLEADOS (READ)
   ============================================================================= */
/**
 * Condición restrictiva: Se omiten filas marcadas con baja lógica (eliminado = 1).
 * La ordenación se indexa de forma secuencial por ID y alfabéticamente por
 * nombres y apellidos para estandarizar el despliegue visual en la cuadrícula.
 */
$sql = "SELECT * FROM empleados 
        WHERE eliminado = 0
        ORDER BY id, nombres, apellidos";

$resultado = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Empleados</title>
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <link rel="stylesheet" href="css/empleados/empleados_lista.css">
    <link rel="stylesheet" href="css/menu.css">
</head>

<body>

<div class="contenedor">

    <div class="barra_superior">
        <?php include("menu.php"); ?>
    </div>

    <div class="barra_superior">
        <a href="empleados_alta.php" class="btn_agregar">
            + Agregar empleado
        </a>
    </div>

    <div class="encabezado">
        <div>ID</div>
        <div>Nombre</div>
        <div>Usuario</div>
        <div>Editar</div>
        <div>Eliminar</div>
    </div>
    
    <?php while($fila = $resultado->fetch_assoc()){ ?>

        <div class="fila">

            <div><?= $fila['id'] ?></div>

            <div>
                <?= htmlspecialchars(
                    $fila['nombres'] . ' ' . $fila['apellidos']
                ) ?>
            </div>

            <div>
                <?= htmlspecialchars($fila['usuario']) ?>
            </div>

            <div class="accion">
                <a href="empleados_edita.php?id=<?= $fila['id'] ?>">
                    ✏️
                </a>
            </div>

            <div class="accion">
                <a href="funciones/empleados_elimina.php?id=<?= $fila['id'] ?>">
                    🗑️
                </a>
            </div>

        </div>

    <?php } ?>

</div>

</body>
</html>