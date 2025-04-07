<?php
require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

// Activar mensajes de error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class TablaProductosMermas {
    public function mostrarTablaProductosMermas() {
        $item = null;
        $valor = null;
        $orden = "id";
        $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

        if (empty($productos)) {
            echo '{"data": []}';
            return;
        }

        $datosJson = '{
          "data": [';
        foreach ($productos as $key => $value) {
            $imagen = "<img src='" . $value["imagen"] . "' width='40px'>";
            if ($value["stock"] <= 10) {
                $stock = "<button class='btn btn-danger'>" . $value["stock"] . "</button>";
            } elseif ($value["stock"] > 10 && $value["stock"] <= 15) {
                $stock = "<button class='btn btn-warning'>" . $value["stock"] . "</button>";
            } else {
                $stock = "<button class='btn btn-success'>" . $value["stock"] . "</button>";
            }
            $botones = "<div class='btn-group'><button class='btn btn-primary seleccionarProducto' idProducto='" . $value["id"] . "' codigoProducto='" . $value["codigo"] . "' descripcionProducto='" . $value["descripcion"] . "'> Agregar</button></div>";

            $datosJson .= '[
                  "' . ($key + 1) . '",
                  "' . $imagen . '",
                  "' . $value["codigo"] . '",
                  "' . $value["descripcion"] . '",
                  "' . $value["talla"] . '",
                  "' . $stock . '",
                  "' . $botones . '"
                ],';
        }
        $datosJson = substr($datosJson, 0, -1); // Eliminar la última coma
        $datosJson .= '] 
         }';

        echo $datosJson;
    }
}

$activarProductosMermas = new TablaProductosMermas();
$activarProductosMermas->mostrarTablaProductosMermas();
?>
