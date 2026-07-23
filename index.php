<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/index.php
 * @description: Punto de entrada principal y formulario de Login para usuarios 
 *               generales (compañeros de otras áreas).
 * @dependencies: 
 *   - CSS: css/login.css
 *   - Imagen: admin/fotos/logo.png
 *   - Acción: funciones/validar_login.php (Procesa las credenciales)
 * -----------------------------------------------------------------------------
 */

// Inicializa o reanuda la sesión del usuario en el servidor
session_start();

/**
 * CONTROL DE ACCESO
 * Si la variable de sesión 'id' ya está definida, significa que el usuario
 * ya está autenticado. Por lo tanto, se le redirige automáticamente a la 
 * página principal de facturas para evitar que vuelva a loguearse.
 */
if(isset($_SESSION['id'])){
    header("Location: facturas.php");
    exit; // Detiene la ejecución del script para asegurar la redirección
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Facturas</title>
    
    <!-- Icono de la pestaña del navegador (Favicon) -->
    <link rel="icon" href="admin/fotos/logo.png" type="image/jpg">
    
    <!-- Estilos específicos para la interfaz de inicio de sesión -->
    <link rel="stylesheet" href="css/login.css">
</head>

<body>

    <!-- Contenedor central del formulario de acceso -->
    <div class="contenedor">

        <h2>Sistema de Facturas</h2>

        <!-- 
          Formulario de Autenticación
          - Método POST: Para enviar las credenciales de forma segura en el cuerpo de la petición.
          - Acción: Envía los datos al script 'validar_login.php' dentro de la carpeta funciones.
        -->
        <form action="funciones/validar_login.php" method="POST">

            <!-- Campo para el nombre de usuario -->
            <label>Usuario</label>
            <input
                type="text"
                name="usuario"
                autocomplete="off" 
                required>          <!-- Evita que el navegador sugiera usuarios anteriores por seguridad / Validación nativa: el campo no puede ir vacío -->

            <!-- Campo para la contraseña -->
            <label>Password</label>
            <input
                type="password"
                name="password"
                required>          <!-- Validación nativa: el campo no puede ir vacío -->

            <!-- Botón de envío del formulario -->
            <button type="submit">
                Ingresar
            </button>

        </form>

    </div>

</body>

</html>