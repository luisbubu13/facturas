<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: funciones/eliminar_respaldo.php
 * @description: Elimina respaldos de base de datos almacenados en admin/backups.
 * -----------------------------------------------------------------------------
 */


require_once("../admin/funciones/conecta.php");
require_once("valida_sesion.php");


/*
|--------------------------------------------------------------------------
| VALIDAR ARCHIVO RECIBIDO
|--------------------------------------------------------------------------
*/

if(!isset($_GET['archivo']) || $_GET['archivo'] == ''){

    header("Location: ../respaldos.php?error=archivo");
    exit;

}


$archivo = basename($_GET['archivo']);


/*
|--------------------------------------------------------------------------
| RUTA REAL DEL BACKUP
|--------------------------------------------------------------------------
*/

$carpeta = dirname(__DIR__) . "/admin/backups";


$ruta = $carpeta . "/" . $archivo;


/*
|--------------------------------------------------------------------------
| VALIDAR EXTENSIÓN
|--------------------------------------------------------------------------
*/

if(pathinfo($archivo, PATHINFO_EXTENSION) != "gz"){

    header("Location: ../respaldos.php?error=formato");
    exit;

}


/*
|--------------------------------------------------------------------------
| VALIDAR EXISTENCIA Y ELIMINAR
|--------------------------------------------------------------------------
*/

if(file_exists($ruta)){

    unlink($ruta);

    header("Location: ../respaldos.php?eliminado=1");
    exit;

}else{

    header("Location: ../respaldos.php?error=noexiste");
    exit;

}

?>