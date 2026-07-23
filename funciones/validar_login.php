<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/funciones/validar_login.php
 * @description: Controlador de autenticación de usuarios (Login). Procesa las
 * credenciales enviadas por POST, valida la identidad contra la
 * base de datos utilizando sentencias preparadas y criptografía,
 * e inicializa la sesión en caso de éxito.
 * @dependencies:
 * - Config/Conexión: ../admin/funciones/conecta.php
 * - Interfaz: Redirige a ../index.php en caso de fallo o ../facturas.php en éxito.
 * -----------------------------------------------------------------------------
 */

// Inicializa el entorno de sesiones para poder escribir datos de autenticación posteriormente
session_start();

// Inclusión del script de conexión al motor de bases de datos
require_once("../admin/funciones/conecta.php");

/**
 * Filtro de Método de Petición.
 * Garantiza que el script solo sea accesible mediante solicitudes POST (envío de formulario).
 * Cualquier intento de acceso directo vía URL (GET) es rechazado y redirigido.
 */
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: ../index.php");
    exit;
}

// Captura de credenciales removiendo espacios en blanco accidentales al inicio o final
$usuario  = trim($_POST['usuario']);
$password = trim($_POST['password']);

// Inicialización del controlador de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   1. CONSULTA SEGURA DE IDENTIDAD (SENTENCIAS PREPARADAS)
   ============================================================================= */
// Se busca al empleado por su alias/correo y se valida que no esté marcado como eliminado (borrado lógico)
$sql = "SELECT * FROM empleados 
        WHERE usuario = ? 
        AND eliminado = 0";

$stmt = $con->prepare($sql);
$stmt->bind_param("s", $usuario); // Inyección segura del parámetro string (Evita Inyección SQL)
$stmt->execute();

$resultado = $stmt->get_result();

/**
 * Control de Fallo A: Usuario Inexistente.
 * Si el conjunto de resultados devuelve 0 filas, se detiene el flujo.
 * Nota de Seguridad: El mensaje es genérico ("Acceso denegado") para no dar pistas a atacantes sobre qué nombres de usuario existen.
 */
if($resultado->num_rows == 0){
    echo "
    <script>
        alert('Acceso denegado');
        window.location='../index.php';
    </script>";
    exit;
}

// Extracción del arreglo asociativo con los datos del empleado
$empleado = $resultado->fetch_assoc();

/* =============================================================================
   2. VERIFICACIÓN CRIPTOGRÁFICA DE CONTRASEÑA
   ============================================================================= */
/**
 * password_verify comprueba que el string plano ($password) coincida con el hash
 * almacenado previamente en la base de datos (generalmente generado con bcrypt o argon2).
 * Control de Fallo B: Contraseña Incorrecta.
 */
if(!password_verify($password, $empleado['password'])){
    echo "
    <script>
        alert('Acceso denegado');
        window.location='../index.php';
    </script>";
    exit;
}

/* =============================================================================
   3. INICIALIZACIÓN DE SESIÓN CORPORATIVA (TOKEN DE ACCESO)
   ============================================================================= */
// Almacenamiento del identificador único de la llave primaria
$_SESSION['idUser'] = $empleado['id'];

// Concatenación del nombre completo para su despliegue estético en la barra de navegación o menús
$_SESSION['nombreUser'] = $empleado['nombres'] . ' ' . $empleado['apellidos'];

// Almacenamiento del alias o correo del usuario actual
$_SESSION['User'] = $empleado['usuario'];

/**
 * Redirección de Éxito.
 * Envía al usuario autenticado directamente al panel de control y consulta de facturas.
 */
header("Location: ../facturas.php");

// Finaliza inmediatamente el procesamiento del hilo actual
exit;
?>