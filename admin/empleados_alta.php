<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/empleados_alta.php
 * @description: Interfaz y controlador lógico para el registro (INSERT) de nuevos
 * empleados. Aplica hashing seguro Bcrypt para mantener la 
 * compatibilidad nativa con el validador de tu login actual.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias del núcleo del sistema
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

/* =============================================================================
   1. PROCESAMIENTO DE LA TRANSACCIÓN (MÉTODO HTTP POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Captura de datos eliminando espacios en blanco accidentales
    $nombres   = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $usuario   = trim($_POST['usuario']);
    $password  = trim($_POST['password']); 

    // Inicialización del puntero de conexión a la Base de Datos
    $con = conecta();

    /**
     * REGLA DE NEGOCIO: Verificación de disponibilidad de credenciales.
     * Evalúa si el identificador de usuario ya existe para evitar colisiones.
     */
    $sql = "SELECT id FROM empleados WHERE usuario = ? AND eliminado = 0";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){
        echo "
        <script>
            alert('El usuario ya existe');
            window.location='empleados_alta.php';
        </script>";
        exit;
    }

    /**
     * CRIPTOGRAFÍA COMPATIBLE:
     * Volvemos a activar el hash Bcrypt para que coincida con tus registros 
     * actuales ($2y$10$...) y tu validador de login lo procese con éxito.
     */
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    /* -------------------------------------------------------------------------
       EJECUCIÓN DE LA INSERCIÓN
       ------------------------------------------------------------------------- */
    $sql = "INSERT INTO empleados (nombres, apellidos, usuario, password) VALUES (?, ?, ?, ?)";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssss", $nombres, $apellidos, $usuario, $password_hash);

    try {
        $stmt->execute();

        // Redirección de éxito al catálogo principal
        header("Location: empleados_lista.php");
        exit;

    } catch (mysqli_sql_exception $e) {
        echo "
        <script>
            alert('Ocurrió un error al registrar el empleado');
            history.back();
        </script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Alta de empleado</title>
    <link rel="stylesheet" href="css/empleados/empleados_alta.css">
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/empleados_alta.js"></script>
</head>

<body>

<div class="contenedor">

    <h2>Alta de empleado</h2>

    <div class="barra">
        <a href="empleados_lista.php" class="btnRegresar">&larr; Regresar</a>
    </div>

    <form id="formEmpleado" method="POST">

        <label>Nombres</label>
        <input type="text" name="nombres" id="nombres" autocomplete="off" required>

        <label>Apellidos</label>
        <input type="text" name="apellidos" id="apellidos" autocomplete="off" required>

        <label>Usuario</label>
        <input type="text" name="usuario" id="usuario" autocomplete="off" required>

        <div id="mensajeEmpleado"></div>

        <label>Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Registrar</button>

    </form>

</div>

</body>
</html>