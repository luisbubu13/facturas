<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/autobus_alta.php
 * @description: Interfaz y controlador para el registro (inserción) de nuevos
 * autobuses en el sistema. Realiza validaciones de duplicados 
 * y sanitización de datos antes de mutar la base de datos.
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
   1. CONSULTA DE CATÁLOGOS SECUNDARIOS (DATASET PARA SELECT)
   ============================================================================= */
// Recupera las empresas activas para construir dinámicamente las opciones del formulario
$sql = "SELECT id, nombre
        FROM empresa
        WHERE eliminado = 0
        ORDER BY nombre";

$empresas = $con->query($sql);

/* =============================================================================
   2. PROCESAMIENTO DE TRANSACCIÓN (MÉTODO HTTP POST)
   ============================================================================= */
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Captura y tipificación estricta de variables recibidas desde el DOM
    $unidad     = trim($_POST['unidad']);
    $id_empresa = intval($_POST['id_empresa']);

    // Cláusula de salvaguarda en servidor: Deniega campos vacíos o llaves foráneas en cero
    if($unidad == "" || $id_empresa <= 0){
        echo "
        <script>
            alert('Todos los campos son obligatorios');
            history.back(); // Regresa al operador al formulario conservando sus datos intactos
        </script>";
        exit;
    }

    /**
     * REGLA DE NEGOCIO: Verificación estricta de Unicidad.
     * Busca colisiones de datos comprobando si el identificador de la unidad ya
     * se encuentra registrado dentro de una fila vigente (eliminado = 0).
     */
    $sql = "SELECT id
            FROM autobus
            WHERE unidad = ?
            AND eliminado = 0";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $unidad); // Enlace parametrizado seguro contra inyecciones SQL
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
       EJECUCIÓN DE LA INSERCIÓN (OPERACIÓN INSERT)
       ------------------------------------------------------------------------- */
    $sql = "INSERT INTO autobus (unidad, id_empresa) VALUES (?, ?)";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $unidad, $id_empresa); // s = string, i = integer
    $stmt->execute();

    // Redirección limpia al catálogo principal tras la inserción exitosa
    header("Location: autobuses_lista.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Alta de autobús</title>

    <link rel="stylesheet" href="css/autobuses/autobus_alta.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="icon" href="fotos/logo.png" type="image/jpg" />

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/autobus_alta.js"></script>
</head>

<body>

<div class="contenedor">

    <?php include("menu.php"); ?>

    <h2>Alta de autobús</h2>

    <div class="barra">
        <a href="autobuses_lista.php" class="btnRegresar">&larr; Regresar</a>
    </div>

    <form id="formAutobus" method="POST">

        <label>Unidad</label>
        <input 
            type="text" 
            name="unidad" 
            id="unidad" 
            autocomplete="off" 
            required>

        <div id="mensajeAutobus"></div>

        <label>Empresa</label>
        <select name="id_empresa" id="id_empresa" required>
            <option value="">Seleccione una empresa</option>

            <?php while($empresa = $empresas->fetch_assoc()){ ?>
                <option value="<?= $empresa['id']; ?>">
                    <?= htmlspecialchars($empresa['nombre']); ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit">Registrar</button>

    </form>

</div>

</body>
</html>