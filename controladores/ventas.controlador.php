<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControladorVentas {
    /*=============================================
    MOSTRAR VENTAS
    =============================================*/
    static public function ctrMostrarVentas($item, $valor) {
        $tabla = "ventas";
        $respuesta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    CREAR VENTA
    =============================================*/
    static public function ctrCrearVenta() {
        if (isset($_POST["nuevaVenta"])) {
            // Validar que haya productos en la lista
            if ($_POST["listaProductos"] == "") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "La venta no se ha ejecutado si no hay productos",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "ventas";
                        }
                    });
                </script>';
                return;
            }

            // Decodificar la lista de productos
            $listaProductos = json_decode($_POST["listaProductos"], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al decodificar la lista de productos",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "ventas";
                        }
                    });
                </script>';
                return;
            }

            // Registrar la venta en la tabla `ventas`
            date_default_timezone_set('America/Lima');
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');

            $tabla = "ventas";
            $datos = array(
                "id_vendedor" => $_POST["idVendedor"],
                "codigo" => $_POST["nuevaVenta"],
                "impuesto" => $_POST["nuevoPrecioImpuesto"],
                "neto" => $_POST["nuevoPrecioNeto"],
                "total" => $_POST["totalVenta"]
            );

            $idVenta = ModeloVentas::mdlIngresarVenta($tabla, $datos); // Recuperar el ID de la venta

            if ($idVenta === "error") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Hubo un problema al registrar la venta",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "ventas";
                        }
                    });
                </script>';
                return;
            }

            // Registrar los detalles de la venta
            $detallesInsertados = true; // Bandera para verificar si todos los detalles se insertaron correctamente

            foreach ($listaProductos as $key => $value) {
                $datosDetalle = array(
                    "id_venta" => $idVenta,
                    "id_producto" => $value["id"],
                    "cantidad" => $value["cantidad"],
                    "precio_unitario" => $value["precio"]
                );

                // Validar datos antes de insertar
                if (!is_numeric($datosDetalle["id_venta"]) || !is_numeric($datosDetalle["id_producto"]) ||
                    !is_numeric($datosDetalle["cantidad"]) || !is_numeric($datosDetalle["precio_unitario"])) {
                    $detallesInsertados = false;
                    continue;
                }

                // Actualizar el stock del producto
                $tablaProductos = "productos";
                $item = "id";
                $valorProducto = $value["id"];
                $orden = "id";

                $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valorProducto, $orden);

                if ($traerProducto) {
                    // Reducir el stock
                    $item1b = "stock";
                    $valor1b = $traerProducto["stock"] - $value["cantidad"];
                    ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valorProducto);

                    // Aumentar las ventas
                    $item1a = "ventas";
                    $valor1a = $traerProducto["ventas"] + $value["cantidad"];
                    ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valorProducto);
                }

                // Insertar el detalle de la venta
                $respuesta = ModeloVentas::mdlIngresarDetalleVenta("detalle_venta", $datosDetalle);

                if (!$respuesta) {
                    $detallesInsertados = false;
                }
            }

            // Mostrar alerta de éxito o error
            if ($detallesInsertados) {
                echo '<script>
                    swal({
                        type: "success",
                        title: "La venta ha sido registrada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "ventas";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Hubo un problema al registrar los detalles de la venta",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "ventas";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    EDITAR VENTA
    =============================================*/
    static public function ctrEditarVenta() {
        if (isset($_POST["editarVenta"])) {
            // Obtener la venta actual
            $tabla = "ventas";
            $item = "codigo";
            $valor = $_POST["editarVenta"];
            $traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

            // Obtener los productos actuales desde la tabla detalle_venta
            $tablaDetalle = "detalle_venta";
            $itemDetalle = "id_venta";
            $valorDetalle = $traerVenta["id"];
            $detallesActuales = ModeloVentas::mdlMostrarDetallesVenta($tablaDetalle, $itemDetalle, $valorDetalle);

            // Restaurar el stock y reducir las ventas de los productos actuales
            foreach ($detallesActuales as $detalle) {
                $tablaProductos = "productos";
                $item = "id";
                $valorProducto = $detalle["id_producto"];
                $orden = "id";

                $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valorProducto, $orden);

                if ($traerProducto) {
                    // Restaurar el stock
                    $item1b = "stock";
                    $valor1b = $traerProducto["stock"] + $detalle["cantidad"];
                    ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valorProducto);

                    // Reducir las ventas
                    $item1a = "ventas";
                    $valor1a = $traerProducto["ventas"] - $detalle["cantidad"];
                    ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valorProducto);
                }
            }

            // Eliminar los detalles actuales de la venta
            ModeloVentas::mdlEliminarDetalleVenta($traerVenta["id"]);

            // Procesar los nuevos productos
            $listaProductos = json_decode($_POST["listaProductos"], true);

            foreach ($listaProductos as $key => $value) {
                $tablaProductos = "productos";
                $item = "id";
                $valorProducto = $value["id"];
                $orden = "id";

                $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valorProducto, $orden);

                if ($traerProducto) {
                    // Reducir el stock
                    $item1b = "stock";
                    $valor1b = $traerProducto["stock"] - $value["cantidad"];
                    ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valorProducto);

                    // Aumentar las ventas
                    $item1a = "ventas";
                    $valor1a = $traerProducto["ventas"] + $value["cantidad"];
                    ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valorProducto);
                }

                // Insertar los nuevos detalles de la venta
                $datosDetalle = array(
                    "id_venta" => $traerVenta["id"],
                    "id_producto" => $value["id"],
                    "cantidad" => $value["cantidad"],
                    "precio_unitario" => $value["precio"]
                );

                ModeloVentas::mdlIngresarDetalleVenta("detalle_venta", $datosDetalle);
            }

            // Guardar cambios de la venta
            $datos = array(
                "id_vendedor" => $_POST["idVendedor"],
                "codigo" => $_POST["editarVenta"],
                "impuesto" => $_POST["nuevoPrecioImpuesto"],
                "neto" => $_POST["nuevoPrecioNeto"],
                "total" => $_POST["totalVenta"]
            );

            $respuesta = ModeloVentas::mdlEditarVenta($tabla, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "La venta ha sido editada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "ventas";
                        }
                    });
                </script>';
            }
        }
    }

     /*=============================================
    ELIMINAR VENTA
    =============================================*/
    static public function ctrEliminarVenta() {
        if (isset($_GET["idVenta"])) {
            $tabla = "ventas";
            $item = "id";
            $valor = $_GET["idVenta"];
            $traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

            // Obtener los productos desde la tabla detalle_venta
            $tablaDetalle = "detalle_venta";
            $itemDetalle = "id_venta";
            $valorDetalle = $_GET["idVenta"];
            $detallesVenta = ModeloVentas::mdlMostrarDetallesVenta($tablaDetalle, $itemDetalle, $valorDetalle);

            // Restaurar el stock y reducir las ventas de los productos
            foreach ($detallesVenta as $detalle) {
                $tablaProductos = "productos";
                $item = "id";
                $valorProducto = $detalle["id_producto"];
                $orden = "id";

                $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valorProducto, $orden);

                if ($traerProducto) {
                    // Restaurar el stock
                    $item1b = "stock";
                    $valor1b = $traerProducto["stock"] + $detalle["cantidad"];
                    ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valorProducto);

                    // Reducir las ventas
                    $item1a = "ventas";
                    $valor1a = $traerProducto["ventas"] - $detalle["cantidad"];
                    ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valorProducto);
                }
            }

            // Eliminar los detalles de la venta
            $eliminarDetalles = ModeloVentas::mdlEliminarDetalleVenta($_GET["idVenta"]);

            if ($eliminarDetalles !== "ok") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Hubo un problema al eliminar los detalles de la venta",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "ventas";
                        }
                    });
                </script>';
                return;
            }

            // Eliminar la venta principal
            $respuesta = ModeloVentas::mdlEliminarVenta($tabla, $_GET["idVenta"]);

            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "La venta ha sido borrada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "ventas";
                        }
                    });
                </script>';
            }
        }
    }
    private static function obtenerDatosVentas() {
        // Obtener todas las ventas
        $ventas = ModeloVentas::mdlMostrarVentas("ventas", null, null);

        // Array para almacenar los datos finales
        $datosFinales = [];

        foreach ($ventas as $venta) {
            // Obtener el nombre del vendedor
            $vendedor = ModeloVentas::mdlMostrarVentas("usuarios", "id", $venta["id_vendedor"]);

            // Obtener los detalles de los productos vendidos
            $detallesVenta = ModeloVentas::mdlMostrarDetallesVenta("detalle_venta", "id_venta", $venta["id"]);

            foreach ($detallesVenta as $detalle) {
                // Obtener el código del producto
                $producto = ModeloVentas::mdlMostrarVentas("productos", "id", $detalle["id_producto"]);

                // Agregar los datos al array final
                $datosFinales[] = [
                    "codigo_venta" => $venta["codigo"],
                    "codigo_producto" => $producto["codigo"] ?? "Sin código",
                    "nombre_vendedor" => $vendedor["nombre"] ?? "Desconocido",
                    "impuesto" => $venta["impuesto"],
                    "neto" => $venta["neto"],
                    "total" => $venta["total"],
                    "fecha" => $venta["fecha"]
                ];
            }
        }

        return $datosFinales;
    }


    /*=============================================
    DESCARGAR REPORTE DE VENTAS
    =============================================*/
    static public function ctrDescargarReporteVentas() {
        // Obtener datos de ventas con detalles de productos y vendedores
        $ventas = self::obtenerDatosVentas();

        // Crear nuevo documento Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar encabezados
        $sheet->setCellValue('A1', 'CÓDIGO VENTA');
        $sheet->setCellValue('B1', 'CÓDIGO PRODUCTO');
        $sheet->setCellValue('C1', 'VENDEDOR');
        $sheet->setCellValue('D1', 'IMPUESTO');
        $sheet->setCellValue('E1', 'NETO');
        $sheet->setCellValue('F1', 'TOTAL');
        $sheet->setCellValue('G1', 'FECHA');

        // Aplicar estilos a los encabezados (negritas)
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        // Llenar la hoja con los datos
        $row = 2; // Comenzar desde la fila 2 (debajo de los encabezados)
        foreach ($ventas as $venta) {
            $sheet->setCellValue('A' . $row, $venta["codigo_venta"]);
            $sheet->setCellValue('B' . $row, $venta["codigo_producto"]);
            $sheet->setCellValue('C' . $row, $venta["nombre_vendedor"]);
            $sheet->setCellValue('D' . $row, 'S/ ' . number_format($venta["impuesto"], 2));
            $sheet->setCellValue('E' . $row, 'S/ ' . number_format($venta["neto"], 2));
            $sheet->setCellValue('F' . $row, 'S/ ' . number_format($venta["total"], 2));
            $sheet->setCellValue('G' . $row, ($venta["fecha"]));
            $row++;
        }

        // Establecer el nombre del archivo
        $fileName = "Reporte_Ventas_" . date('Y-m-d') . ".xlsx";

        // Configurar las cabeceras HTTP para descargar el archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        // Guardar el archivo en la salida del navegador
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /*=============================================
    RANGO DE FECHAS PARA REPORTES
    =============================================*/
    static public function ctrRangoFechasVentas($fechaInicial, $fechaFinal) {
        $tabla = "ventas";
        $respuesta = ModeloVentas::mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal);
        return $respuesta;
    }
	static public function ctrMostrarDetallesVenta($item, $valor) {
		$tabla = "detalle_venta";
		$respuesta = ModeloVentas::mdlMostrarDetallesVenta($tabla, $item, $valor);
		return $respuesta;
	}
	public static function ctrSumaTotalVentas(){
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlSumaTotalVentas($tabla);
		return $respuesta;
	}
	public static function ctrContarProductos() {
        return ModeloProductos::mdlContarProductos("productos");
    }

    public static function ctrContarCategorias() {
        return ModeloCategorias::mdlContarCategorias("categorias");
    }
}
