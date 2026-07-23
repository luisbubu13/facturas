<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES DE MUTACIÓN (DELETE)
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/empleados_elimina.php
 * @description: Controlador de baja lógica (Soft Delete) para el catálogo de
 * empleados. Altera el estado de la bandera 'eliminado' a 1 
 * para restringir su acceso al sistema y excluirlo de los listados.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php (Inclusión local)
 * -----------------------------------------------------------------------------
 */

// Inclusión del componente de abstracción de datos de forma segura
require_once("conecta.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   1. CAPTURA Y SANITIZACIÓN DEL IDENTIFICADOR DE ENTRADA
   ============================================================================= */
/**
 * Se extrae la clave primaria utilizando intval() para garantizar un tipo de dato
 * estrictamente numérico, anulando cadenas de texto o caracteres maliciosos.
 */
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

/* =============================================================================
   2. PROCESAMIENTO DE BAJA LÓGICA (SENTENCIA PREPARADA SEGURA)
   ============================================================================= */
if ($id > 0) {
    /**
     * REGLA DE SEGURIDAD HISTÓRICA:
     * No se utiliza una instrucción "DELETE FROM" física para resguardar la integridad
     * referencial y auditoría de los registros de facturación o bitácoras que el
     * empleado haya manipulado en el pasado.
     */
    $sql = "UPDATE empleados 
            SET eliminado = 1 
            WHERE id = ?";

    $stmt = $con->prepare($sql);
    
    // Vinculación explícita del parámetro tipado: i = integer
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Liberación de memoria del statement
    $stmt->close();
}

/* =============================================================================
   3. REDIRECCIÓN DE RETORNO (REDIRECT)
   ============================================================================= */
/**
 * Sube un nivel en el árbol de directorios ('../') para redirigir de forma
 * limpia al operador de vuelta al listado maestro de administración de personal.
 */
header("Location: ../empleados_lista.php");
exit; // Conclusión definitiva del script