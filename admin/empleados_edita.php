<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/empleados_edita.php
 * @description: Interfaz y controlador unificado para la actualización (UPDATE)
 * de datos de empleados. Gestiona la edición condicional de contraseñas 
 * seguras y restringe colisiones de nombres de usuario redundantes.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias del núcleo
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización de la conexión activa a la Base de Datos
$con = conecta();

/* =============================================================================
   1. VALIDACIÓN PERIMETRAL DE ENTRADA (MÉTODO HTTP GET)
   ============================================================================= */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cláusula de salvaguarda primaria: Si el ID es inválido, aborta la renderización
if($id <= 0){
    header("Location: empleados_lista.php");
    exit;
}

/* =============================================================================
   2. SECCIÓN DE ACTUALIZACIÓN (PROCESAMIENTO DE PETICIÓN POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Captura y remoción de espacios accidentales de las variables del formulario
    $id        = intval($_POST['id']);
    $nombres   = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $usuario   = trim($_POST['usuario']);
    $password  = trim($_POST['password']);

    /**
     * REGLA DE NEGOCIO: Control de duplicidad de credenciales (Unique Username).
     * Verifica que el Nickname modificado no le pertenezca ya a otro empleado vigente.
     * La exclusión 'id != ?' evita disparar la alerta si el usuario conserva su propio username.
     */
    $sql = "SELECT id 
            FROM empleados 
            WHERE usuario = ? 
            AND id != ? 
            AND eliminado = 0";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $usuario, $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){
        echo "
        <script>
            alert('El usuario ya existe');
            history.back(); // Regresa al formulario preservando el texto digitado
        </script>";
        exit;
    }

    /* -------------------------------------------------------------------------
       FLUJO DE MUTACIÓN BIFURCADO: EVALUACIÓN DE CONTRASEÑA
       ------------------------------------------------------------------------- */
    if($password != ""){
        // ESCENARIO A: El operador capturó un nuevo password -> Se encripta con Bcrypt de forma nativa
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE empleados 
                SET nombres = ?, 
                    apellidos = ?, 
                    usuario = ?, 
                    password = ? 
                WHERE id = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssssi", $nombres, $apellidos, $usuario, $password_hash, $id);

    } else {
        // ESCENARIO B: Input password vacío -> Se actualiza el perfil omitiendo la columna en el query
        $sql = "UPDATE empleados 
                SET nombres = ?, 
                    apellidos = ?, 
                    usuario = ? 
                WHERE id = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssi", $nombres, $apellidos, $usuario, $id);
    }

    // Ejecución definitiva de la sentencia preparada elegida
    $stmt->execute();

    // Redirección de éxito al catálogo maestro
    header("Location: empleados_lista.php");
    exit;
}

/* =============================================================================
   3. EXTRACCIÓN DE DATOS DE ORIGEN PARA PRECARGA (MÉTODO GET)
   ============================================================================= */
// Recupera la información persistida del empleado para rellenar los inputs
$sql = "SELECT * FROM empleados WHERE id = ? AND eliminado = 0";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

// Cláusula de salvaguarda secundaria: Si el ID no coincide con un registro activo, aborta
if($resultado->num_rows == 0){
    header("Location: empleados_lista.php");
    exit;
}

$empleado = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar empleado</title>

    <link rel="stylesheet" href="css/empleados/empleados_alta.css">
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/empleados_alta.js"></script>
</head>

<body>

<div class="contenedor">

    <h2>Editar empleado</h2>

    <div class="barra">
        <a href="empleados_lista.php" class="btnRegresar">&larr; Regresar</a>
    </div>

    <form id="formEmpleado" method="POST">

        <input 
            type="hidden" 
            name="id" 
            id="id" 
            value="<?= $empleado['id']; ?>">

        <label>Nombres</label>
        <input 
            type="text" 
            name="nombres" 
            id="nombres" 
            autocomplete="off"
            value="<?= htmlspecialchars($empleado['nombres']); ?>"> <label>Apellidos</label>
        <input 
            type="text" 
            name="apellidos" 
            id="apellidos" 
            autocomplete="off"
            value="<?= htmlspecialchars($empleado['apellidos']); ?>">

        <label>Usuario</label>
        <input 
            type="text" 
            name="usuario" 
            id="usuario" 
            autocomplete="off"
            value="<?= htmlspecialchars($empleado['usuario']); ?>">

        <div id="mensajeEmpleado"></div>

        <label>Password</label>
        <input 
            type="password" 
            name="password" 
            id="password" 
            placeholder="Dejar vacío para conservar la actual">

        <button type="submit">Guardar cambios</button>

    </form>

</div>

</body>
</html>