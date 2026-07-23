<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/provedor_edita.php
 * @description: Interfaz y controlador unificado para la modificación (UPDATE)
 * de datos de proveedores. Controla la persistencia segura de
 * campos de texto y estados binarios (bandera IRS).
 * @dependencies:
 * - Config/Conexión: funciones/conecta.php
 * - Seguridad: funciones/valida_sesion.php
 * -----------------------------------------------------------------------------
 */

// Inclusión de dependencias del núcleo del sistema
require_once("funciones/conecta.php");
require_once("funciones/valida_sesion.php");

// Inicialización del puntero de conexión a la Base de Datos
$con = conecta();

/* =============================================================================
   1. CAPTURA Y VALIDACIÓN DEL IDENTIFICADOR (MÉTODO HTTP GET)
   ============================================================================= */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cláusula de salvaguarda primaria: Si el ID es inválido, aborta e impide el renderizado
if($id <= 0){
    header("Location: provedor_lista.php");
    exit;
}

/* =============================================================================
   2. SECCIÓN DE ACTUALIZACIÓN (PROCESAMIENTO DE PETICIÓN POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Captura, tipado explícito y sanitización elemental de las variables recibidas
    $id     = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $irs    = intval($_POST['irs']); // Recibe 1 (Sí) o 0 (No)

    /* -------------------------------------------------------------------------
       EJECUCIÓN DEL UPDATE MEDIANTE SENTENCIA PREPARADA
       ------------------------------------------------------------------------- */
    $sql = "UPDATE provedores
            SET nombre = ?,
                irs = ?
            WHERE id = ?";

    $stmt = $con->prepare($sql);
    
    // Vinculación de parámetros estrictos: s = string (nombre), i = integer (irs), i = integer (id)
    $stmt->bind_param("sii", $nombre, $irs, $id);

    if($stmt->execute()){
        // Redirección limpia al listado maestro si la ejecución fue exitosa
        header("Location: provedor_lista.php");
        exit;
    }
}

/* =============================================================================
   3. EXTRACCIÓN DE DATOS DE ORIGEN PARA PRECARGA (MÉTODO GET)
   ============================================================================= */
// Consulta el registro vigente del proveedor para inyectar sus valores actuales en los inputs
$sql = "SELECT *
        FROM provedores
        WHERE id = ?
        AND eliminado = 0";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

// Cláusula de salvaguarda secundaria: Si el proveedor sufrió un borrado lógico o no existe, redirige
if($resultado->num_rows == 0){
    header("Location: provedor_lista.php");
    exit;
}

$proveedor = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar proveedor</title>

    <link rel="stylesheet" href="css/provedores/provedores_alta.css">
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/provedor_alta.js"></script>
</head>

<body>

    <div class="contenedor">

        <h2>Editar proveedor</h2>

        <div class="barra">
            <a href="provedor_lista.php" class="btnRegresar">
                &larr; Regresar
            </a>
        </div>

        <form id="formProveedor" method="POST">

            <input
                type="hidden"
                name="id"
                id="id"
                value="<?= $proveedor['id']; ?>">

            <label for="nombre">
                Nombre del proveedor
            </label>
            <input
                type="text"
                name="nombre"
                id="nombre"
                maxlength="255"
                autocomplete="off"
                value="<?= htmlspecialchars($proveedor['nombre']); ?>"> <div id="mensajeNombre"></div>

            <label for="irs">
                IRS
            </label>
            <select name="irs" id="irs">

                <option value="">
                    Seleccionar
                </option>

                <option
                    value="1"
                    <?= ($proveedor['irs'] == 1) ? 'selected' : ''; ?>>
                    Sí
                </option>

                <option
                    value="0"
                    <?= ($proveedor['irs'] == 0) ? 'selected' : ''; ?>>
                    No
                </option>

            </select>

            <button type="submit">
                Guardar cambios
            </button>

        </form>

    </div>

</body>

</html>