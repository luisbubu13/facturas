<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/empresa_alta.php
 * @description: Interfaz y controlador lógico para el registro (INSERT) de nuevas
 * empresas en el sistema. Valida campos vacíos y duplicidad de
 * nombres antes de realizar la inserción segura en la base de datos.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - Componentes: menu.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias esenciales de seguridad y conexión
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

/* =============================================================================
   1. PROCESAMIENTO DE LA TRANSACCIÓN (MÉTODO HTTP POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Captura del dato eliminando espacios en blanco accidentales en los extremos
    $nombre = trim($_POST['nombre']);

    // Cláusula de salvaguarda en servidor: Deniega la operación si el campo está vacío
    if($nombre == ""){
        echo "
        <script>
            alert('Ingrese el nombre de la empresa');
            window.location='empresa_alta.php'; // Redirección limpia para limpiar reenvíos de formulario
        </script>";
        exit;
    }

    // Inicialización del puntero de conexión a la Base de Datos
    $con = conecta();

    /**
     * REGLA DE NEGOCIO: Verificación estricta de Unicidad.
     * Busca colisiones de datos comprobando si el nombre comercial ya existe
     * en un registro vigente dentro del sistema (eliminado = 0).
     */
    $sql = "SELECT id 
            FROM empresa 
            WHERE nombre = ? 
            AND eliminado = 0";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $nombre); // Enlace parametrizado seguro (Evita Inyección SQL)
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){
        echo "
        <script>
            alert('La empresa ya existe');
            window.location='empresa_alta.php';
        </script>";
        exit;
    }

    /* -------------------------------------------------------------------------
       EJECUCIÓN DE LA INSERCIÓN (OPERACIÓN INSERT)
       ------------------------------------------------------------------------- */
    $sql = "INSERT INTO empresa (nombre) VALUES (?)";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();

    // Redirección de éxito al catálogo principal de empresas
    header("Location: empresas_lista.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Alta de empresa</title>

    <link rel="stylesheet" href="css/empresas/empresa_alta.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/empresa_alta.js"></script>
</head>

<body>

<div class="contenedor">

    <?php include("menu.php"); ?>

    <h2>Alta de empresa</h2>

    <div class="barra">
        <a href="empresas_lista.php" class="btnRegresar">&larr; Regresar</a>
    </div>

    <form id="formEmpresa" method="POST">

        <label>Nombre de la empresa</label>
        <input
            type="text"
            name="nombre"
            id="nombre"
            autocomplete="off"
            required>

        <div id="mensajeEmpresa"></div>

        <button type="submit">Registrar</button>

    </form>

</div>

</body>
</html>