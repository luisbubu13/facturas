<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PUERTA DE ENTRADA (LOGIN)
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/index.php
 * @description: Pantalla de autenticación para el personal administrativo.
 * Intercepta sesiones activas para evitar re-logins innecesarios
 * y sirve el formulario de captura de credenciales hacia el validador.
 * @dependencies:
 * - Controlador de Autenticación: funciones/validar_login.php
 * - Vista Destino: bienvenido.php
 * -----------------------------------------------------------------------------
 */

// Inicializa o reanuda la sesión basada en el identificador de sesión actual (Cookie PHPSESSID)
session_start();

/* =============================================================================
   1. CONTROL DE REDIRECCIÓN AUTOMÁTICA (MÉTODO HTTP GET)
   ============================================================================= */
/**
 * REGLA DE SEGURIDAD PERIMETRAL:
 * Si el índice de sesión 'id' ya se encuentra persistido, significa que el usuario
 * superó exitosamente el login con anterioridad. Se le redirige inmediatamente
 * al Dashboard para optimizar la experiencia de usuario y resguardar la ruta.
 */
if(isset($_SESSION['id'])){
    header("Location: bienvenido.php");
    exit; // Frena la lectura del script previniendo el renderizado innecesario del HTML inferior
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Facturas Admin</title>

    <link rel="icon" href="fotos/logo.png" type="image/jpg">
    
    <link rel="stylesheet" href="css/login.css">
</head>

<body>

    <div class="contenedor">

        <h2>Sistema de Facturas</h2>

        <form action="funciones/validar_login.php" method="POST">

            <label>Usuario</label>
            <input
                type="text"
                name="usuario"
                autocomplete="off"
                required>

            <label>Password</label>
            <input
                type="password"
                name="password"
                required>

            <button type="submit">
                Ingresar
            </button>

        </form>

    </div>

</body>

</html>