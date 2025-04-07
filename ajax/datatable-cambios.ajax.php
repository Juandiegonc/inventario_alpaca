<?php
require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class TablaCambios {
    public function mostrarTablaCambios() {
        $item = null;
        $valor = null;
        $productos = ControladorProductos::ctrMostrarProductos($item, $valor, "id");

        if(!is_array($productos)) {
            return json_encode(array("data" => array()));
        }

        $data = array();

        foreach($productos as $key => $producto) {
            $tipo = isset($_GET["tipo"]) ? $_GET["tipo"] : "devolver";
            $boton = "<button class='btn btn-primary agregar" . 
                     ($tipo == "devolver" ? "ProductoDevolver" : "ProductoEntregar") . 
                     "' idProducto='".$producto["id"]."'>+</button>";
            
            $imagen = "<img src='".$producto["imagen"]."' width='40px'>";

            $data[] = array(
                ($key + 1),
                $imagen,
                $producto["codigo"],
                $producto["descripcion"],
                $producto["talla"],
                $producto["stock"],
                $boton
            );
        }

        $response = array(
            "data" => $data
        );

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}

// Asegurarse de enviar los headers correctos
header('Content-Type: application/json');

// Instanciar y mostrar la tabla
$mostrarTabla = new TablaCambios();
echo $mostrarTabla->mostrarTablaCambios();