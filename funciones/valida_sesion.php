<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/funciones/valida_sesion.php
 * @description: Middleware / Guardián de Autenticación. Comprueba de forma
 * estricta la existencia de una sesión de usuario activa en el
 * servidor. Si la validación falla, restringe el acceso y
 * redirige al inicio público del sistema (index.php).
 * @dependencies: Se debe incluir mediante 'require_once' en la primera línea
 * de cualquier archivo PHP que requiera protección.
 * -----------------------------------------------------------------------------
 */

/**
 * Inicializa o reanuda la sesión actual del entorno.
 * Es una instrucción obligatoria para poder inspeccionar los valores mapeados
 * dentro de la superglobal $_SESSION.
 */
session_start();

/**
 * Cláusula de Intercepción y Bloqueo.
 * Evalúa si la variable identificadora del usuario ('idUser') NO ha sido declarada.
 * De ser el caso, determina que la petición es anónima o que la sesión ha expirado.
 */
if(!isset($_SESSION['idUser'])){

    /**
     * Redirección Forzada.
     * Envía una cabecera HTTP de redirección para mandar al navegador del intruso
     * de vuelta al formulario de inicio de sesión (index.php).
     */
    header("Location: index.php");

    /**
     * Terminación Inmediata del Script.
     * Mata el hilo de ejecución en el servidor. Esto es de vital importancia para
     * evitar que, ante un bypass de cabeceras en el cliente, el código posterior 
     * (consultas SQL, HTML confidencial) llegue a procesarse o exponerse.
     */
    exit;
}
?>