<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/funciones/factura_accion.php
 * @description: Controlador API encargado de realizar actualizaciones de estado
 * por lote (Bulk Actions) sobre los registros de facturas. 
 * Responde peticiones asíncronas (AJAX) en formato JSON.
 * @dependencies: 
 * - Config/Conexión: ../admin/funciones/conecta.php
 * - Seguridad: valida_sesion.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de archivos base para conectividad y validación de autenticación
require_once("../admin/funciones/conecta.php");
require_once("valida_sesion.php");

// Forzar la cabecera HTTP para indicarle al cliente que la respuesta es un objeto JSON
header('Content-Type: application/json');

// Inicialización de la conexión con el motor de base de datos
$con = conecta();

// Recuperación de parámetros POST utilizando el operador de fusión nula (??)
$accion = $_POST['accion'] ?? '';
$ids    = $_POST['ids'] ?? [];

// Cláusula de salvaguarda: Valida que la acción exista y que el arreglo de IDs no esté vacío
if($accion == '' || empty($ids)){
    echo json_encode([
        "ok" => false,
        "msg" => "Datos incompletos"
    ]);
    exit; // Finaliza la ejecución inmediatamente para evitar respuestas corruptas
}

/* =============================================================================
   CONSTRUCCIÓN DINÁMICA DE LA CLÁUSULA "IN" PARA SENTENCIAS PREPARADAS
   ============================================================================= */
// Convierte todos los elementos del arreglo a enteros estrictos (Filtro de seguridad)
$ids = array_map('intval', $ids);

// Genera una cadena de signos de interrogación (?,?,?) del tamaño exacto del arreglo recibido
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// Captura de fecha y hora del sistema para auditorías de transacciones
$fecha = date('Y-m-d H:i:s');

/* =============================================================================
   MÁQUINA DE ESTADOS - ENRUTAMIENTO DE ACCIONES POR LOTE
   ============================================================================= */
if($accion == 'entregado'){

    $estado = 2; // Código de estado asignado para facturas entregadas

    // El query actualiza tanto el estado como la fecha definitiva de entrega
    $sql = "UPDATE factura 
            SET estado = ?, 
                fecha_entregado = ? 
            WHERE id IN ($placeholders)";

    // Define los tipos de datos de los parámetros ("i"=entero, "s"=string) seguidos de los enteros de los IDs
    $types = "is" . str_repeat('i', count($ids));
    
    // Fusiona las variables de control junto con el array estructurado de IDs
    $params = array_merge([$estado, $fecha], $ids);

}
elseif($accion == 'cancelado'){

    $estado = 3; // Código de estado asignado para la cancelación lógica

    $sql = "UPDATE factura 
            SET estado = ? 
            WHERE id IN ($placeholders)";

    $types = "i" . str_repeat('i', count($ids));
    $params = array_merge([$estado], $ids);

}
elseif($accion == 'imprimir'){

    $estado = 1; // Código de estado asignado al pasar a "En proceso" tras su impresión

    $sql = "UPDATE factura 
            SET estado = ? 
            WHERE id IN ($placeholders)";

    $types = "i" . str_repeat('i', count($ids));
    $params = array_merge([$estado], $ids);

}
else{
    // Manejo de error en caso de recibir una bandera no contemplada en las reglas del negocio
    echo json_encode([
        "ok" => false,
        "msg" => "Acción inválida"
    ]);
    exit;
}

/* =============================================================================
   PREPARACIÓN, VINCULACIÓN Y EJECUCIÓN SEGURA (ANTI INYECCIÓN SQL)
   ============================================================================= */
$stmt = $con->prepare($sql);

/**
 * El operador de desempaquetado (...) expande el arreglo de parámetros dinámicos
 * para inyectarlos secuencialmente dentro del método bind_param.
 */
$stmt->bind_param($types, ...$params);

// Ejecución de la sentencia en la base de datos y retorno del resultado en formato JSON
if($stmt->execute()){
    echo json_encode(["ok" => true]);
} else {
    echo json_encode([
        "ok" => false,
        "msg" => "Error al actualizar"
    ]);
}
?>