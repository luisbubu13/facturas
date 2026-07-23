<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES ASÍNCRONOS
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/verifica_provedor.php
 * @description: API local que valida la existencia de un proveedor por nombre.
 * Soporta exclusión por ID para no colisionar en flujos de edición.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * -----------------------------------------------------------------------------
 */

// Inclusión del núcleo de conexión
require_once("conecta.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

// Captura y limpieza de las variables transmitidas por el método POST de AJAX
$nombre = trim($_POST['nombre']);
$id     = isset($_POST['id']) ? intval($_POST['id']) : 0;

/* =============================================================================
   1. CONSULTA DE VERIFICACIÓN CON SENTENCIA PREPARADA
   ============================================================================= */
/**
 * LÓGICA DE EXCLUSIÓN CRUCIAL (id != ?):
 * - En ALTA: El ID viaja como 0, por lo tanto busca el nombre en toda la tabla.
 * - En EDICIÓN: El ID viaja con el valor del registro actual. Esto evita que la 
 * consulta dé un "falso positivo" al detectar su propio nombre sin cambios.
 */
$sql = "SELECT id 
        FROM provedores 
        WHERE nombre = ? 
        AND id != ? 
        AND eliminado = 0";

$stmt = $con->prepare($sql);
$stmt->bind_param("si", $nombre, $id);
$stmt->execute();

$resultado = $stmt->get_result();

/* =============================================================================
   2. SALIDA IMPRESA (RESPUESTA HACIA AJAX)
   ============================================================================= */
/**
 * Imprime un código binario plano (texto plano) que lee el bloque 'success' de jQuery:
 * 1 = El proveedor ya existe (Bloquear formulario).
 * 0 = El proveedor está disponible (Permitir registro).
 */
echo ($resultado->num_rows > 0) ? 1 : 0;