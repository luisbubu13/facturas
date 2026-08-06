<?php

require_once("../admin/funciones/conecta.php");
require_once("valida_sesion.php");

/*=========================================================
=            BUSCAR AUTOMÁTICAMENTE EL DUMP               =
=========================================================*/

$dump = trim(shell_exec("command -v mariadb-dump"));

if($dump == ""){
    $dump = trim(shell_exec("command -v mysqldump"));
}

if($dump == ""){
    header("Location: ../respaldos.php?error=1");
    exit;
}

/*=========================================================
=            DATOS DE CONEXIÓN                            =
=========================================================*/

$host     = HOST;
$base     = BD;
$usuario  = USER_BD;
$password = PASS_BD;

/*=========================================================
=            CARPETA DE RESPALDOS                         =
=========================================================*/

$carpeta = dirname(__DIR__) . "/admin/backups";

if(!is_dir($carpeta)){
    mkdir($carpeta, 0777, true);
}

/*=========================================================
=            NOMBRE DEL ARCHIVO                           =
=========================================================*/

$fecha = date("Y-m-d_H-i-s");

$sqlFile = $carpeta . "/facturas_" . $fecha . ".sql";
$gzFile  = $sqlFile . ".gz";

/*=========================================================
=            GENERAR RESPALDO                             =
=========================================================*/

$comando =
    escapeshellcmd($dump) .
    " --host=" . escapeshellarg($host) .
    " --user=" . escapeshellarg($usuario) .
    " --password=" . escapeshellarg($password) .
    " --single-transaction" .
    " --routines" .
    " --events" .
    " --triggers" .
    " --default-character-set=utf8mb4" .
    " " . escapeshellarg($base) .
    " > " . escapeshellarg($sqlFile) .
    " 2>&1";

exec($comando, $salida, $resultado);

if($resultado !== 0){

    file_put_contents(
        $carpeta . "/error_respaldo.log",
        implode(PHP_EOL, $salida)
    );

    if(file_exists($sqlFile)){
        unlink($sqlFile);
    }

    header("Location: ../respaldos.php?error=1");
    exit;
}

/*=========================================================
=            COMPRIMIR (.GZ)                              =
=========================================================*/

$origen = fopen($sqlFile, "rb");

if(!$origen){

    header("Location: ../respaldos.php?error=2");
    exit;
}

$destino = gzopen($gzFile, "wb9");

while(!feof($origen)){

    gzwrite(
        $destino,
        fread($origen, 1024 * 512)
    );
}

fclose($origen);
gzclose($destino);

/*=========================================================
=            ELIMINAR SQL TEMPORAL                        =
=========================================================*/

unlink($sqlFile);

/*=========================================================
=            CONSERVAR SOLO LOS ÚLTIMOS 30                =
=========================================================*/

$archivos = glob($carpeta . "/facturas_*.sql.gz");

if(count($archivos) > 30){

    usort($archivos, function($a, $b){

        return filemtime($b) - filemtime($a);

    });

    $viejos = array_slice($archivos, 30);

    foreach($viejos as $archivo){

        @unlink($archivo);

    }
}

/*=========================================================
=            REDIRECCIÓN                                  =
=========================================================*/

header("Location: ../respaldos.php?ok=1");
exit;