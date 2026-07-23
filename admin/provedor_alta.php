<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/provedor_alta.php
 * @description: Interfaz y controlador lógico para la creación (INSERT) de nuevos
 * proveedores. Gestiona de manera segura entradas de texto y
 * estados binarios parametrizados.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias esenciales de seguridad y base de datos
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

/* =============================================================================
   1. PROCESAMIENTO DE LA INSERCIÓN (MÉTODO HTTP POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Captura y sanitización elemental de las entradas del formulario
    $nombre = trim($_POST['nombre']);
    $irs    = intval($_POST['irs']); // Forzado de tipo entero (1 para Sí, 0 para No)

    // Inicialización del puntero de conexión a la Base de Datos
    $con = conecta();

    /* -------------------------------------------------------------------------
       EJECUCIÓN DEL INSERT MEDIANTE SENTENCIA PREPARADA
       ------------------------------------------------------------------------- */
    $sql = "INSERT INTO provedores (nombre, irs) VALUES (?, ?)";

    $stmt = $con->prepare($sql);
    
    // Vinculación de parámetros estrictos: s = string (nombre), i = integer (irs)
    $stmt->bind_param("si", $nombre, $irs);

    if($stmt->execute()){
        // Redirección limpia al listado maestro de proveedores tras un registro exitoso
        header("Location: provedor_lista.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de proveedor</title>

    <link rel="stylesheet" href="css/provedores/provedores_alta.css">
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/provedor_alta.js"></script>
</head>

<body>

<div class="contenedor">

    <h2>Alta de proveedor</h2>
    
    <div class="barra">
        <a href="provedor_lista.php" class="btnRegresar">
            &larr; Regresar
        </a>
    </div>

    <form id="formProveedor" method="POST">

        <label>Proveedor</label>
        <input
            type="text"
            name="nombre"
            id="nombre"
            maxlength="255"
            autocomplete="off"
            required>

        <div id="mensajeNombre"></div>

        <label>IRS</label>
        <select name="irs" id="irs" required>
            <option value="">Seleccionar</option>
            <option value="1">Sí</option>
            <option value="0">No</option>
        </select>

        <button type="submit">
            Registrar
        </button>

    </form>

</div>

</body>
</html>