<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES ASÍNCRONOS
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/verificar_empresa.php
 * @description: API local que dictamina la existencia de una empresa mediante
 * su nombre comercial o razón social. Previene colisiones 
 * en procesos de inserción y actualización.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php (Inclusión local)
 * -----------------------------------------------------------------------------
 */

// Inclusión del núcleo de conexión a bases de datos corporativas
require_once("conecta.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

// Captura y remoción de espacios residuales de las variables transmitidas por el cliente
$nombre = trim($_POST['nombre']);

// Discriminador de flujo: Captura la llave primaria si proviene de un entorno de edición
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

/* =============================================================================
   1. EVALUACIÓN DE UNICIDAD MEDIANTE SENTENCIA PREPARADA
   ============================================================================= */
/**
 * ALGORITMO DE FILTRADO:
 * Evalúa si el 'nombre' ya está tomado por otra fila activa (eliminado = 0).
 * Si '$id' es mayor a 0, la cláusula 'AND id != ?' aísla el registro bajo 
 * edición para que la base de datos no lo marque como duplicado de sí mismo.
 */
$sql = "SELECT id
        FROM empresa
        WHERE nombre = ?
        AND id != ?
        AND eliminado = 0";

$stmt = $con->prepare($sql);

// Enlace estricto de parámetros: s = string (nombre de empresa), i = integer (ID de fila)
$stmt->bind_param("si", $nombre, $id);
$stmt->execute();

$resultado = $stmt->get_result();

/* =============================================================================
   2. RETORNO DE CÓDIGO DE ESTADO (HTTP RESPONSE)
   ============================================================================= */
/**
 * Emite una bandera numérica binaria en texto plano procesable por 'empresa_alta.js':
 * 1 = Nombre duplicado o no disponible.
 * 0 = Nombre libre y apto para ser registrado.
 */
echo ($resultado->num_rows > 0) ? 1 : 0;