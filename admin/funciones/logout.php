<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - CONTROLADORES DE AUTENTICACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/logout.php
 * @description: Controlador de cierre de sesión seguro. Elimina las variables
 * globales del entorno actual en el servidor y redirige
 * al operador hacia la pantalla de acceso perimetral.
 * @dependencies:
 * - Origen: Invocado globalmente por el componente 'menu.php'.
 * - Destino: ../index.php (Pantalla de Login)
 * -----------------------------------------------------------------------------
 */

// Inicializa o reanuda la sesión vigente para poder manipular sus variables activas
session_start();

/* =============================================================================
   1. REVOCACIÓN DE CREDENCIALES (DESASIGNACIÓN DE VARIABLES)
   ============================================================================= */
/**
 * Se utiliza la función unset() sobre cada uno de los índices creados durante
 * el inicio de sesión exitoso en 'validar_login.php'. Esto invalida cualquier
 * validación posterior del middleware 'valida_sesion.php'.
 */
unset($_SESSION['id']);      // Elimina la Llave Primaria del empleado
unset($_SESSION['nombre']);  // Elimina el nombre completo registrado
unset($_SESSION['usuario']); // Elimina el alias/nickname de acceso

/* Opcional complementario (Buena práctica): 
   Si se deseara borrar por completo toda la estructura del archivo físico 
   de la sesión en el servidor, se podría implementar: session_destroy();
*/

/* =============================================================================
   2. REDIRECCIÓN DE RETORNO Y CIERRE SEGURÓ
   ============================================================================= */
/**
 * Al encontrarse este script dentro de la carpeta 'funciones/', se sube un nivel 
 * en el árbol de directorios ('../') para apuntar correctamente al archivo 'index.php'
 * ubicado en la raíz del entorno administrativo.
 */
header("Location: ../index.php");

// Frena de forma estricta la ejecución del hilo del backend
exit;