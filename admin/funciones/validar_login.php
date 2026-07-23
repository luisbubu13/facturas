<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES DE AUTENTICACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/validar_login.php
 * @description: Controlador de seguridad perimetral. Valida las credenciales
 * de acceso de los empleados, restringe el ingreso por jerarquía
 * de puestos (Puesto = 2 para Admin) y levanta la sesión global.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php (Inclusión local)
 * - Origen HTTP: ../index.php (Formulario de Login)
 * - Destino Exitoso: ../bienvenido.php (Dashboard Principal)
 * -----------------------------------------------------------------------------
 */

// Inicializa o reanuda la sesión web basada en cookies de sesión seguras
session_start();

// Inclusión del núcleo de conexión a la base de datos
require_once("conecta.php");

/* =============================================================================
   1. PROTECCIÓN DE ENTRADA AL CONTROLADOR (MÉTODO HTTP)
   ============================================================================= */
/**
 * Salvaguarda perimetral: Si un agente externo intenta invocar este script directamente 
 * a través de la barra de direcciones (GET), se le deniega el procesamiento y 
 * se le redirige inmediatamente a la raíz del formulario.
 */
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: ../index.php");
    exit;
}

// Captura de datos eliminando espacios en blanco accidentales o maliciosos
$usuario  = trim($_POST['usuario']);
$password = trim($_POST['password']);

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   2. EVALUACIÓN Y FILTRADO DE ROLES DE USUARIO (ETAPA 1)
   ============================================================================= */
/**
 * REGLA DE NEGOCIO ESTRICTA (puesto = 2):
 * Filtra únicamente empleados activos (eliminado = 0) que tengan asignado el 
 * rol administrativo o gerencial superior (puesto = 2). Los empleados normales 
 * no son admitidos por esta consulta, disparando la cláusula de rechazo.
 */
$sql = "SELECT *
        FROM empleados
        WHERE usuario = ?
        AND eliminado = 0 
        AND puesto = 2";

$stmt = $con->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();

$resultado = $stmt->get_result();

// Cláusula de rechazo 1: El usuario no existe, está dado de baja o no tiene el rol de puesto correcto.
if($resultado->num_rows == 0){
    echo "
    <script>
        alert('Acceso denegado');
        window.location='../index.php';
    </script>";
    exit;
}

// Extracción del mapa asociativo de los datos del empleado administrativo
$empleado = $resultado->fetch_assoc();

/* =============================================================================
   3. VERIFICACIÓN CRIPTOGRÁFICA DE CONTRASENA (ETAPA 2)
   ============================================================================= */
/**
 * EVALUACIÓN NATIVA CON BCRYPT:
 * Compara la contraseña limpia provista en el input con la cadena Hash ($2y$10$...) 
 * persistida en la base de datos de manera matemática y asíncrona.
 */
if(!password_verify($password, $empleado['password'])){
    // Cláusula de rechazo 2: La contraseña introducida es incorrecta.
    echo "
    <script>
        alert('Acceso denegado');
        window.location='../index.php';
    </script>";
    exit;
}

/* =============================================================================
   4. ESTABLECIMIENTO DE VARIABLES DE SESIÓN (PERSISTENCIA GLOBAL)
   ============================================================================= */
/**
 * Una vez superados los filtros criptográficos y de rol de puesto, se procede a 
 * poblar la súper global $_SESSION en el servidor para autorizar el acceso perimetral.
 */
$_SESSION['id']      = $empleado['id'];       // Llave Primaria utilizada para el rastreo de transacciones
$_SESSION['nombre']  = $empleado['nombres'] . ' ' . $empleado['apellidos']; // Nombre completo del operador
$_SESSION['usuario'] = $empleado['usuario'];  // Alias identificador único de sesión

// Redirección limpia hacia el entorno de bienvenida (Dashboard de Administración)
header("Location: ../bienvenido.php");
exit; // Cierre de ejecución seguro