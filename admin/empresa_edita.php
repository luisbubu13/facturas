<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/empresa_edita.php
 * @description: Interfaz y controlador unificado para la modificación (UPDATE)
 * de empresas. Evalúa colisiones de nombres e inyecta de forma
 * segura las variables preexistentes en los inputs del DOM.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - Componentes: menu.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias del núcleo del sistema
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   1. CAPTURA DEL IDENTIFICADOR (MÉTODO HTTP GET)
   ============================================================================= */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cláusula de salvaguarda primaria: Si el ID es inválido o nulo, redirige al catálogo
if($id <= 0){
    header("Location: empresas_lista.php");
    exit;
}

/* =============================================================================
   2. PROCESAMIENTO DE LA ACTUALIZACIÓN (MÉTODO HTTP POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Captura y sanitización de los datos enviados por el cliente
    $id     = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);

    // Validación perimetral en servidor: Restricción de campo obligatorio vacío
    if($nombre == ""){
        echo "
        <script>
            alert('Ingrese el nombre de la empresa');
            history.back(); // Retorna al operador al formulario manteniendo los estados intactos
        </script>";
        exit;
    }

    /**
     * REGLA DE NEGOCIO: Control de duplicidad excluyendo el registro actual.
     * Busca coincidencias exactas del nombre ingresado con otras empresas vigentes.
     * La condicional 'id != ?' permite guardar sin modificar el nombre original de esta fila.
     */
    $sql = "SELECT id FROM empresa 
            WHERE nombre = ? 
            AND id != ? 
            AND eliminado = 0";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $nombre, $id); // Enlace parametrizado seguro contra Inyecciones SQL
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){
        echo "
        <script>
            alert('La empresa ya existe');
            history.back();
        </script>";
        exit;
    }

    /* -------------------------------------------------------------------------
       EJECUCIÓN DEL UPDATE EN LA BASE DE DATOS
       ------------------------------------------------------------------------- */
    $sql = "UPDATE empresa SET nombre = ? WHERE id = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $nombre, $id);
    $stmt->execute();

    // Redirección limpia tras la actualización exitosa
    header("Location: empresas_lista.php");
    exit;
}

/* =============================================================================
   3. EXTRACCIÓN DE DATOS PARA RENDERIZADO (PRECARGA DEL FORMULARIO)
   ============================================================================= */
// Consulta el registro vigente de la empresa para inyectar sus valores actuales en la vista
$sql = "SELECT * FROM empresa WHERE id = ? AND eliminado = 0";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

// Cláusula de salvaguarda secundaria: Si la empresa no existe o sufrió un borrado lógico, aborta
if($resultado->num_rows == 0){
    header("Location: empresas_lista.php");
    exit;
}

$empresa = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar empresa</title>

    <link rel="stylesheet" href="css/empresas/empresa_alta.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/empresa_alta.js"></script>
</head>

<body>

<div class="contenedor">

    <?php include("menu.php"); ?>

    <h2>Editar empresa</h2>

    <div class="barra">
        <a href="empresas_lista.php" class="btnRegresar">&larr; Regresar</a>
    </div>

    <form id="formEmpresa" method="POST">

        <input
            type="hidden"
            name="id"
            id="id"
            value="<?= $empresa['id']; ?>">

        <label>Nombre de la empresa</label>
        <input
            type="text"
            name="nombre"
            id="nombre"
            autocomplete="off"
            value="<?= htmlspecialchars($empresa['nombre']); ?>"> <div id="mensajeEmpresa"></div>

        <button type="submit">Guardar cambios</button>

    </form>

</div>

</body>
</html>