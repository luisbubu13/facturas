<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES ASÍNCRONOS
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/verificar_autobuses.php
 * @description: API local de validación que dictamina la disponibilidad de un
 * número de unidad vehicular dentro de la tabla de autobuses.
 * Garantiza que no existan registros duplicados activos.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php (Inclusión local)
 * -----------------------------------------------------------------------------
 */

// Inclusión del núcleo de conexión a la base de datos del sistema
require_once("conecta.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

// Captura y remoción de espacios residuales del identificador alfanumérico de la unidad
$unidad = trim($_POST['unidad']);

// Captura e inicialización de la clave primaria para discriminar procesos de Edición
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

/* =============================================================================
   1. EVALUACIÓN DE DISPONIBILIDAD MEDIANTE SENTENCIA PREPARADA
   ============================================================================= */
/**
 * CONTROL DE INVENTARIO LOGÍSTICO:
 * Verifica si el código identificador de la 'unidad' ya fue asignado a otro autobús activo
 * (eliminado = 0). Si '$id' es mayor a 0 (Modo Edición), la cláusula 'AND id != ?'
 * le indica a MySQL que ignore el registro actual de la unidad para que el operador
 * pueda actualizar otros parámetros (como la empresa vinculada) sin conflictos.
 */
$sql = "SELECT id
        FROM autobus
        WHERE unidad = ?
        AND id != ?
        AND eliminado = 0";

$stmt = $con->prepare($sql);

// Enlace parametrizado estricto: s = string (código de unidad), i = integer (ID del registro)
$stmt->bind_param("si", $unidad, $id);
$stmt->execute();

$resultado = $stmt->get_result();

/* =============================================================================
   2. RETORNO DEL CÓDIGO DE CONTROL (TEXT RESPONSE)
   ============================================================================= */
/**
 * Imprime una bandera binaria en texto plano consumible por 'autobus_alta.js':
 * 1 = El número de unidad ya está asignado (Se despliega alerta en pantalla).
 * 0 = Unidad libre y disponible para el registro.
 */
echo ($resultado->num_rows > 0) ? 1 : 0;
?>