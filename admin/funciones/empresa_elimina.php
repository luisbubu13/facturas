<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES DE MUTACIÓN (DELETE)
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/empresa_elimina.php
 * @description: Controlador de eliminación lógica (Soft Delete) para el catálogo
 * de empresas. Modifica el estado de la bandera 'eliminado' a 1
 * para ocultar el registro de las consultas operativas ordinarias.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php (Inclusión local)
 * -----------------------------------------------------------------------------
 */

// Inclusión del módulo de abstracción de datos de forma segura
require_once("conecta.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   1. CAPTURA Y SANITIZACIÓN DEL IDENTIFICADOR DE ENTRADA
   ============================================================================= */
/**
 * Se extrae la clave primaria utilizando intval() para forzar un tipado entero estricto.
 * Esto neutraliza cualquier cadena alfanumérica o caracteres especiales maliciosos.
 */
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

/* =============================================================================
   2. PROCESAMIENTO DE BAJA LÓGICA (SENTENCIA PREPARADA SEGURA)
   ============================================================================= */
if ($id > 0) {
    /**
     * SALVAGUARDA DE HISTORIAL FINANCIERO:
     * No se ejecuta una sentencia "DELETE FROM" física para salvaguardar la integridad
     * referencial de las facturas e inventario de autobuses vinculados a esta organización.
     */
    $sql = "UPDATE empresa 
            SET eliminado = 1 
            WHERE id = ?";

    $stmt = $con->prepare($sql);
    
    // Vinculación explícita del parámetro tipado: i = integer
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Cierre de buffers del statement
    $stmt->close();
}

/* =============================================================================
   3. DESVIO DE FLUX DE RETORNO (REDIRECT)
   ============================================================================= */
/**
 * Sube un nivel en el árbol de directorios ('../') para retornar limpiamente
 * al listado maestro de visualización de empresas corporativas.
 */
header("Location: ../empresas_lista.php");
exit; // Conclusión segura del hilo de ejecución