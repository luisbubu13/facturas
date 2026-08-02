<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: respaldos.php
 * @description: Panel administrativo para gestionar copias de seguridad.
 * Permite crear, visualizar, descargar y eliminar respaldos de la BD.
 * -----------------------------------------------------------------------------
 */

date_default_timezone_set('America/Mexico_City');
require_once("funciones/valida_sesion.php");


/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/

$carpeta = __DIR__ . "/admin/backups";


/*
|--------------------------------------------------------------------------
| OBTENER RESPALDOS
|--------------------------------------------------------------------------
*/

$respaldos = [];


if(is_dir($carpeta)){

    $archivos = glob($carpeta . "/*.sql.gz");


    if($archivos){

        usort($archivos, function($a,$b){

            return filemtime($b) - filemtime($a);

        });


        foreach($archivos as $archivo){

            $respaldos[] = [

                "nombre" => basename($archivo),

                "fecha" => date(
                    "d/m/Y H:i:s",
                    filectime($archivo)
                ),

                "tamano" => round(
                    filesize($archivo) / 1024 / 1024,
                    2
                )

            ];

        }

    }

}

?>


<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Respaldos</title>

<link rel="icon" href="admin/fotos/logo.png">

<link rel="stylesheet" href="css/respaldos.css">

</head>


<body>


<div class="contenedor">


    <div class="cabecera">


        <img
            src="admin/fotos/logo.png"
            class="logo">


        <h1>
            Copias de Seguridad
        </h1>


    </div>



    <?php if(isset($_GET['ok'])){ ?>

        <div class="mensaje ok">
            Respaldo creado correctamente.
        </div>

    <?php } ?>


    <?php if(isset($_GET['eliminado'])){ ?>

        <div class="mensaje ok">
            Respaldo eliminado correctamente.
        </div>

    <?php } ?>


    <?php if(isset($_GET['error'])){ ?>

        <div class="mensaje error">
            Ocurrió un problema con la operación.
        </div>

    <?php } ?>



    <div class="acciones">


        <a
            href="funciones/respaldo.php"
            class="btn">

            Crear respaldo

        </a>


        <a
            href="facturas.php"
            class="btn">

            Regresar

        </a>


    </div>




    <div class="tabla">


        <div class="encabezado">


            <div>
                Archivo
            </div>


            <div>
                Fecha
            </div>


            <div>
                Tamaño
            </div>


            <div>
                Acciones
            </div>


        </div>




        <?php if(count($respaldos) > 0){ ?>


            <?php foreach($respaldos as $res){ ?>


                <div class="fila">


                    <div>
                        <?= htmlspecialchars($res['nombre']) ?>
                    </div>


                    <div>
                        <?= $res['fecha'] ?>
                    </div>


                    <div>
                        <?= $res['tamano'] ?> MB
                    </div>


                    <div class="accionesFila">


                        <a
                            href="admin/backups/<?= urlencode($res['nombre']) ?>"
                            class="descargar">

                            Descargar

                        </a>



                        <a
                            href="funciones/eliminar_respaldo.php?archivo=<?= urlencode($res['nombre']) ?>"
                            class="eliminar"
                            onclick="return confirm('¿Deseas eliminar este respaldo?');">

                            Eliminar

                        </a>


                    </div>


                </div>


            <?php } ?>


        <?php }else{ ?>


            <div class="vacio">

                No existen respaldos registrados.

            </div>


        <?php } ?>


    </div>



</div>


</body>

</html>