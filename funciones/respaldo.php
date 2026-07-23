<?php

require_once("../admin/funciones/conecta.php");
require_once("valida_sesion.php");

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/

$base = "facturas";
$usuario = "root";
$password = "";

/*
| Ruta donde está mysqldump
| Cambia esta ruta si tu XAMPP está instalado en otro lugar.
*/

$mysqldump = "C:\\xampp\\mysql\\bin\\mysqldump.exe";

/*
| Archivo destino
*/

$archivo = dirname(__DIR__) . "/admin/facturas.sql";

/*
|--------------------------------------------------------------------------
| COMANDO
|--------------------------------------------------------------------------
*/

$comando =
    "\"$mysqldump\" " .
    "--user=$usuario " .
    "--password=$password " .
    "$base > \"$archivo\"";

/*
|--------------------------------------------------------------------------
| EJECUTAR
|--------------------------------------------------------------------------
*/

exec($comando, $salida, $resultado);

if($resultado == 0){

    echo "
    <script>

        alert('Copia de seguridad creada correctamente.');

        location.href='../facturas.php';

    </script>";

}else{

    echo "
    <script>

        alert('No fue posible generar la copia de seguridad.');

        location.href='../facturas.php';

    </script>";
}
?>