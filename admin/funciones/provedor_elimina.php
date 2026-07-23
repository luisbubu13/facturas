<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES DE MUTACIÓN (DELETE)
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/provedor_elimina.php
 * @description: Controlador de eliminación lógica (Soft Delete). Cambia el
 * estado de la bandera 'eliminado' a 1 para ocultar al proveedor
 * de las vistas públicas sin destruir la integridad referencial.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php (Inclusión local)
 * -----------------------------------------------------------------------------
 */

// Inclusión del núcleo de conexión de forma segura
require_once("conecta.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   1. CAPTURA Y SANITIZACIÓN DEL PARÁMETRO DE ENTRADA
   ============================================================================= */
/**
 * Se extrae el ID utilizando intval() para asegurar que el valor sea estrictamente 
 * un número entero, neutralizando caracteres alfanuméricos sospechosos.
 */
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

/* =============================================================================
   2. EJECUCIÓN DE BAJA LÓGICA (SENTENCIA PREPARADA SEGURA)
   ============================================================================= */
if ($id > 0) {
    /**
     * REGLA DE INTEGRIDAD:
     * No se utiliza un "DELETE FROM" real para no romper facturas o registros históricos
     * vinculados a este proveedor. La bandera 'eliminado = 1' lo descarta del listado.
     */
    $sql = "UPDATE provedores 
            SET eliminado = 1 
            WHERE id = ?";

    $stmt = $con->prepare($sql);
    
    // Vinculación estricta del parámetro: i = integer
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $stmt->close();
}

/* =============================================================================
   3. REDIRECCIÓN LIMPIA (FLUX CONTROL)
   ============================================================================= */
// Al estar dentro de 'funciones/', se regresa un nivel en el árbol para ubicar la vista principal
header("Location: ../provedor_lista.php");
exit; // Asegura el fin del procesamiento del script