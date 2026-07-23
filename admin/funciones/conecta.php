<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - ARCHIVO DE CONFIGURACIÓN DEL NÚCLEO
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/conecta.php
 * @description: Centraliza las credenciales de acceso al motor de Base de Datos,
 * configura la zona horaria del servidor de la aplicación y 
 * expone la rutina global de conexión (MySQLi Object).
 * @used_by: Utilizado de forma transversal por todos los controladores, 
 * scripts AJAX y archivos CRUD del sistema.
 * -----------------------------------------------------------------------------
 */

// Sincroniza el reloj del servidor PHP para que las funciones de fecha y hora cuadren con México
date_default_timezone_set('America/Mexico_City');

/* =============================================================================
   1. DEFINICIÓN DE CONSTANTES GLOBLALES DE ACCESO (ENTORNO LOCAL)
   ============================================================================= */
define("HOST",    'db'); // Dirección loopback y puerto por defecto de MySQL/MariaDB
define("BD",      'facturas');       // Nombre de la base de datos relacional del sistema
define("USER_BD", 'root');           // Identificador del usuario Administrador del motor
define("PASS_BD", 'root');               // Contraseña asociada (Vía de desarrollo local sin clave)

/* =============================================================================
   2. FUNCIÓN CONSTRUCTORA DEL PUNTERO DE CONEXIÓN
   ============================================================================= */
/**
 * Genera una nueva instancia del controlador nativo de PHP para MySQL.
 * * @return mysqli Objeto con el canal de comunicación abierto y listo para procesar queries.
 */
function conecta(){
    
    // Inicialización del objeto de conexión mediante la API Orientada a Objetos de MySQLi
    $con = new mysqli(HOST, USER_BD, PASS_BD, BD);
    
    /* Opcional para depuración en desarrollo:
       Si la conexión fallara por credenciales incorrectas, puedes descomentar las líneas inferiores:
       
       if ($con->connect_error) {
           die("Error crítico de conexión: " . $con->connect_error);
       }
    */
    
    // Forzado de codificación UTF-8 para evitar caracteres extraños o errores de acentuación en la BD
    $con->set_charset("utf8mb4");
    
    // Retorna el descriptor de conexión listo para ser capturado por la variable $con de tus scripts
    return $con;
}
?>