<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES ASÍNCRONOS
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/verificar_empleado.php
 * @description: API local de validación que dictamina la disponibilidad de un
 * nombre de usuario (login nickname) dentro de la tabla de empleados.
 * Soporta exclusión de ID para flujos de edición.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php (Inclusión local)
 * -----------------------------------------------------------------------------
 */

// Inclusión del núcleo de conexión a la base de datos del sistema
require_once("conecta.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

// Captura y remoción de espacios en blanco en la credencial enviada por AJAX
$usuario = trim($_POST['usuario']);

// Captura opcional del ID para discriminar procesos de Edición frente a procesos de Alta
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

/* =============================================================================
   1. EVALUACIÓN DE DISPONIBILIDAD MEDIANTE SENTENCIA PREPARADA
   ============================================================================= */
/**
 * REGLA DE EXCLUSIÓN PARA LOGIN SEGURO:
 * Comprueba que el nickname de acceso no pertenezca a ningún otro empleado activo
 * (eliminado = 0). Si '$id' es mayor a 0 (Modo Edición), la cláusula 'AND id != ?' 
 * le indica a MySQL que ignore el registro actual del usuario para que pueda
 * guardar otros cambios en su perfil sin modificar su alias obligatorio.
 */
$sql = "SELECT id
        FROM empleados
        WHERE usuario = ?
        AND id != ?
        AND eliminado = 0";

$stmt = $con->prepare($sql);

// Enlace parametrizado estricto: s = string (alias del usuario), i = integer (ID de fila)
$stmt->bind_param("si", $usuario, $id);
$stmt->execute();

$resultado = $stmt->get_result();

/* =============================================================================
   2. EMISIÓN DE LA RESPUESTA DE CONTROL (TEXT RESPONSE)
   ============================================================================= */
/**
 * Imprime un código de estado binario plano legible por la rutina de promesas en JS:
 * 1 = El nombre de usuario ya está tomado (Muestra advertencia y bloquea envío).
 * 0 = El nombre de usuario está completamente libre (Permite continuar).
 */
echo ($resultado->num_rows > 0) ? 1 : 0;