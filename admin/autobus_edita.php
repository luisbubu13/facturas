<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/autobus_editar.php
 * @description: Formulario y procesador lógico para la edición de autobuses.
 * Detecta el tipo de petición HTTP (GET para pintar la interfaz, 
 * POST para persistir cambios) y previene duplicidad de unidades.
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * - Componentes: menu.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias del núcleo
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   1. CAPTURA DE PARÁMETRO DE ENTRADA (MÉTODO GET)
   ============================================================================= */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cláusula de salvaguarda primaria: Si el ID es inválido, aborta y regresa a la lista
if($id <= 0){
    header("Location: autobuses_lista.php");
    exit;
}

/* =============================================================================
   2. SECCIÓN DE ACTUALIZACIÓN (PROCESAMIENTO DE PETICIÓN POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Captura y sanitización estricta de las variables enviadas por el formulario
    $id         = intval($_POST['id']);
    $unidad     = trim($_POST['unidad']);
    $id_empresa = intval($_POST['id_empresa']);

    // Validación en el servidor: Campos obligatorios vacíos o nulos
    if($unidad == "" || $id_empresa <= 0){
        echo "
        <script>
            alert('Todos los campos son obligatorios');
            history.back(); // Regresa al usuario al estado anterior del formulario sin borrar datos
        </script>";
        exit;
    }

    /**
     * REGLA DE NEGOCIO: Validación de Duplicados Excluyente.
     * Verifica si el nuevo nombre de la unidad ya existe en otro registro diferente al actual.
     * La cláusula 'id != ?' evita que el sistema se bloquee a sí mismo al guardar sin cambiar la unidad.
     */
    $sql = "SELECT id FROM autobus 
            WHERE unidad = ? 
            AND id != ? 
            AND eliminado = 0";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $unidad, $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){
        echo "
        <script>
            alert('La unidad ya existe');
            history.back();
        </script>";
        exit;
    }

    /* -------------------------------------------------------------------------
       EJECUCIÓN DEL CAMBIO (OPERACIÓN UPDATE)
       ------------------------------------------------------------------------- */
    $sql = "UPDATE autobus 
            SET unidad = ?, 
                id_empresa = ? 
            WHERE id = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("sii", $unidad, $id_empresa, $id);
    $stmt->execute();

    // Redirección de éxito
    header("Location: autobuses_lista.php");
    exit;
}

/* =============================================================================
   3. EXTRACCIÓN DE DATOS DE ORIGEN PARA RENDERIZADO
   ============================================================================= */
// Consulta el estado actual del registro del autobús para precargar el formulario
$sql = "SELECT * FROM autobus WHERE id = ? AND eliminado = 0";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

// Cláusula de salvaguarda secundaria: Si el ID no existe o está eliminado, redirige
if($resultado->num_rows == 0){
    header("Location: autobuses_lista.php");
    exit;
}

$autobus = $resultado->fetch_assoc();

// Consulta para poblar el listado desplegable de empresas asociables
$sql = "SELECT id, nombre FROM empresa WHERE eliminado = 0 ORDER BY nombre";
$empresas = $con->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar autobús</title>

    <link rel="stylesheet" href="css/autobuses/autobus_alta.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/autobus_alta.js"></script>
</head>

<body>

<div class="contenedor">

    <?php include("menu.php"); ?>

    <h2>Editar autobús</h2>

    <div class="barra">
        <a href="autobuses_lista.php" class="btnRegresar">&larr; Regresar</a>
    </div>

    <form id="formAutobus" method="POST">

        <input 
            type="hidden" 
            name="id" 
            id="id" 
            value="<?= $autobus['id']; ?>">

        <label>Unidad</label>
        <input 
            type="text" 
            name="unidad" 
            id="unidad" 
            value="<?= htmlspecialchars($autobus['unidad']); ?>">

        <div id="mensajeAutobus"></div>

        <label>Empresa</label>
        <select name="id_empresa" id="id_empresa">
            <option value="">Seleccione una empresa</option>

            <?php while($empresa = $empresas->fetch_assoc()){ ?>
                <option 
                    value="<?= $empresa['id']; ?>" 
                    <?= ($empresa['id'] == $autobus['id_empresa']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($empresa['nombre']); ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit">Guardar cambios</button>

    </form>

</div>

</body>
</html>