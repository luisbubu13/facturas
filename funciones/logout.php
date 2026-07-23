<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/funciones/logout.php
 * @description: Controlador de cierre de sesión. Remueve de manera segura las
 * credenciales y tokens de acceso almacenados en la superglobal 
 * $_SESSION, destruyendo el estado de autenticación en el servidor.
 * @dependencies: Ninguna. Redirige al script raíz de inicio de sesión.
 * -----------------------------------------------------------------------------
 */

// Inicializa o reanuda la sesión existente basada en la cookie de sesión (PHPSESSID)
session_start();

/**
 * Liberación selectiva de las variables de sesión del usuario.
 * Se utiliza 'unset' para remover específicamente los datos de identidad del vector global,
 * permitiendo que otras posibles configuraciones de sesión del sitio permanezcan intactas.
 */
unset($_SESSION['idUser']);     // Remueve el identificador único del usuario en la Base de Datos
unset($_SESSION['nombreUser']); // Remueve el nombre legible del operador expuesto en la interfaz
unset($_SESSION['User']);       // Remueve el correo electrónico, rol o alias de autenticación

/**
 * Redirección HTTP hacia la pantalla de inicio (Login).
 * Al usar la ruta relativa '../index.php', el script saca al usuario del subdirectorio 
 * de funciones de facturas y lo posiciona en la raíz pública del sistema.
 */
header("Location: ../index.php");

// Finaliza inmediatamente el procesamiento del hilo actual para evitar inyecciones de código post-redirección
exit;
?>