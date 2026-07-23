<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/bienvenido.php
 * @description: Pantalla de aterrizaje (Dashboard / Home Interno) del módulo 
 * administrativo. Muestra un saludo personalizado al usuario 
 * autenticado tras superar el control perimetral de acceso.
 * @dependencies:
 * - Seguridad: funciones/valida_sesion.php
 * - Componentes: menu.php (Menú de navegación global del backoffice)
 * - CSS: css/bienvenido.css, css/menu.css
 * -----------------------------------------------------------------------------
 */

// MEDIDA PERIMETRAL DE SEGURIDAD
// Evalúa la sesión del operador. Si es anónima, aborta el script y redirige al login corporativo.
require_once("funciones/valida_sesion.php");

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Bienvenido</title>

    <link rel="stylesheet" href="css/bienvenido.css">
    <link rel="stylesheet" href="css/menu.css">
    
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />
</head>

<body>

    <div class="contenedor">

        <?php include("menu.php"); ?>

        <div class="contenido">

            <h1>Bienvenido</h1>

            <h2>
                <?= htmlspecialchars($_SESSION['nombre']); ?>
            </h2>

        </div>

    </div>

</body>

</html>