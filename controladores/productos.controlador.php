<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class ControladorProductos{

	/*=============================================
	MOSTRAR PRODUCTOS
	=============================================*/

	static public function ctrMostrarProductos($item, $valor, $orden){

		$tabla = "productos";

		$respuesta = ModeloProductos::mdlMostrarProductos($tabla, $item, $valor, $orden);

		return $respuesta;

	}

	    /*=============================================
    CREAR PRODUCTO
    =============================================*/
    static public function ctrCrearProducto(){
        if(isset($_POST["nuevaDescripcion"])){
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaDescripcion"]) &&
            preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevaUbicacion"]) &&
               preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ]+$/', $_POST["nuevaTalla"]) &&
               preg_match('/^[0-9]+$/', $_POST["nuevoStock"]) &&    
               preg_match('/^[0-9.]+$/', $_POST["nuevoPrecioProduccion"]) &&
               preg_match('/^[0-9.]+$/', $_POST["nuevoPrecioVenta"])){

                /*=============================================
                VALIDAR IMAGEN
                =============================================*/
                $ruta = "vistas/img/productos/default/anonymous.png";

                if(isset($_FILES["nuevaImagen"]["tmp_name"]) && !empty($_FILES["nuevaImagen"]["tmp_name"])){
                    list($ancho, $alto) = getimagesize($_FILES["nuevaImagen"]["tmp_name"]);

                    $nuevoAncho = 500;
                    $nuevoAlto = 500;

                    /*=============================================
                    CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA FOTO DEL USUARIO
                    =============================================*/
                    $directorio = "vistas/img/productos/".$_POST["nuevoCodigo"];

                    mkdir($directorio, 0755);

                    /*=============================================
                    DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
                    =============================================*/
                    if($_FILES["nuevaImagen"]["type"] == "image/jpeg"){
                        $aleatorio = mt_rand(100,999);
                        $ruta = "vistas/img/productos/".$_POST["nuevoCodigo"]."/".$aleatorio.".jpg";
                        $origen = imagecreatefromjpeg($_FILES["nuevaImagen"]["tmp_name"]);                        
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $ruta);
                    }

                    if($_FILES["nuevaImagen"]["type"] == "image/png"){
                        $aleatorio = mt_rand(100,999);
                        $ruta = "vistas/img/productos/".$_POST["nuevoCodigo"]."/".$aleatorio.".png";
                        $origen = imagecreatefrompng($_FILES["nuevaImagen"]["tmp_name"]);                        
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $ruta);
                    }
                }

                $tabla = "productos";
                $datos = array(
                    "id_categoria" => $_POST["nuevaCategoria"],
                    "codigo" => $_POST["nuevoCodigo"],
                    "descripcion" => $_POST["nuevaDescripcion"],
                    "ubicacion" => $_POST["nuevaUbicacion"],
                    "talla" => $_POST["nuevaTalla"],
                    "stock" => $_POST["nuevoStock"],
                    "precio_produccion" => $_POST["nuevoPrecioProduccion"],
                    "precio_venta" => $_POST["nuevoPrecioVenta"],
                    "imagen" => $ruta
                );

                $respuesta = ModeloProductos::mdlIngresarProducto($tabla, $datos);

                if($respuesta == "ok"){
                    echo '<script>
                        swal({
                            type: "success",
                            title: "El producto ha sido guardado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "productos";
                            }
                        });
                    </script>';
                }
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡El producto no puede ir con los campos vacíos o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "productos";
                        }
                    });
                </script>';
            }
        }
    }

	/*=============================================
	EDITAR PRODUCTO
	=============================================*/

	static public function ctrEditarProducto(){
		if(isset($_POST["editarDescripcion"])){
			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarDescripcion"]) &&
			preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarUbicacion"]) &&
			   preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarTalla"]) &&
			   preg_match('/^[0-9]+$/', $_POST["editarStock"]) &&    
			   preg_match('/^[0-9.]+$/', $_POST["editarPrecioProduccion"]) &&
			   preg_match('/^[0-9.]+$/', $_POST["editarPrecioVenta"])){
	
				/*=============================================
				VALIDAR IMAGEN
				=============================================*/
				$ruta = $_POST["imagenActual"]; // Mantener la imagen actual por defecto
	
				if(isset($_FILES["editarImagen"]["tmp_name"]) && !empty($_FILES["editarImagen"]["tmp_name"])){
					list($ancho, $alto) = getimagesize($_FILES["editarImagen"]["tmp_name"]);
					$nuevoAncho = 500;
					$nuevoAlto = 500;
	
					// Crear directorio si no existe
					$directorio = "vistas/img/productos/".$_POST["editarCodigo"];
					if(!file_exists($directorio)){
						mkdir($directorio, 0755);
					}
	
					// Eliminar imagen anterior
					if(!empty($_POST["imagenActual"]) && 
					   $_POST["imagenActual"] != "vistas/img/productos/default/anonymous.png" &&
					   file_exists($_POST["imagenActual"])){
						unlink($_POST["imagenActual"]);
					}
	
					// Procesar nueva imagen
					if($_FILES["editarImagen"]["type"] == "image/jpeg"){
						$aleatorio = mt_rand(100,999);
						$ruta = "vistas/img/productos/".$_POST["editarCodigo"]."/".$aleatorio.".jpg";
						$origen = imagecreatefromjpeg($_FILES["editarImagen"]["tmp_name"]);
						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
						imagejpeg($destino, $ruta);
					}
	
					if($_FILES["editarImagen"]["type"] == "image/png"){
						$aleatorio = mt_rand(100,999);
						$ruta = "vistas/img/productos/".$_POST["editarCodigo"]."/".$aleatorio.".png";
						$origen = imagecreatefrompng($_FILES["editarImagen"]["tmp_name"]);
						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
						imagepng($destino, $ruta);
					}
				}
	
				$tabla = "productos";
				$datos = array(
					"id_categoria" => $_POST["editarCategoria"],
					"codigo" => $_POST["editarCodigo"],
					"descripcion" => $_POST["editarDescripcion"],
					"ubicacion" => $_POST["editarUbicacion"],
					"talla" => $_POST["editarTalla"],
					"stock" => $_POST["editarStock"],
					"precio_produccion" => $_POST["editarPrecioProduccion"],
					"precio_venta" => $_POST["editarPrecioVenta"],
					"imagen" => $ruta
				);
	
				$respuesta = ModeloProductos::mdlEditarProducto($tabla, $datos);
	
				if($respuesta == "ok"){
					echo '<script>
						swal({
							type: "success",
							title: "El producto ha sido editado correctamente",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "productos";
							}
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El producto no puede ir con los campos vacíos o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "productos";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	BORRAR PRODUCTO
	=============================================*/
	static public function ctrEliminarProducto(){

		if(isset($_GET["idProducto"])){

			$tabla ="productos";
			$datos = $_GET["idProducto"];

			if($_GET["imagen"] != "" && $_GET["imagen"] != "vistas/img/productos/default/anonymous.png"){

				unlink($_GET["imagen"]);
				rmdir('vistas/img/productos/'.$_GET["codigo"]);

			}

			$respuesta = ModeloProductos::mdlEliminarProducto($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "El producto ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "productos";

								}
							})

				</script>';

			}		
		}


	}

	/*=============================================
	MOSTRAR SUMA VENTAS
	=============================================*/

	static public function ctrMostrarSumaVentas(){

		$tabla = "productos";

		$respuesta = ModeloProductos::mdlMostrarSumaVentas($tabla);

		return $respuesta;

	}
	/*=============================================
	DESCARGAR EXCEL 
	=============================================*/

	public static function ctrDescargarReporteAgregaciones($fechaInicial, $fechaFinal) {
        // Obtener datos de productos
        $tabla = "productos";
        if ($fechaInicial && $fechaFinal) {
            // Obtener productos dentro del rango de fechas
            $productos = ModeloProductos::mdlRangoFechasProductos($tabla, $fechaInicial, $fechaFinal);
        } else {
            // Obtener todos los productos
            $item = null;
            $valor = null;
            $orden = "id";
            $productos = ModeloProductos::mdlMostrarProductos($tabla, $item, $valor, $orden);
        }

        // Crear nuevo documento Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar encabezados
        $sheet->setCellValue('A1', 'TIPO MOVIMIENTO');
        $sheet->setCellValue('B1', 'CANTIDAD');
        $sheet->setCellValue('C1', 'PRODUCTOS');
        $sheet->setCellValue('D1', 'UBICACIÓN');
        // $sheet->setCellValue('E1', 'TALLA');
        $sheet->setCellValue('E1', 'FECHA');

        // Aplicar estilos a los encabezados (negritas)
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Llenar la hoja con los datos
        $row = 2; // Comenzar desde la fila 2 (debajo de los encabezados)
        foreach ($productos as $producto) {
            // Escribir los datos en el archivo Excel
            $sheet->setCellValue('A' . $row, 'Registro');
            $sheet->setCellValue('B' . $row, $producto["stock"]); // Usar stock como cantidad
            $sheet->setCellValue('C' . $row, $producto["codigo"]);
            $sheet->setCellValue('D' . $row, $producto["ubicacion"]);
            // $sheet->setCellValue('E' . $row, $producto["talla"]);
            $sheet->setCellValue('E' . $row, ($producto["fecha"]));
            $row++;
        }

        // Establecer el nombre del archivo
        $fileName = "Reporte_Agregaciones_" . date('Y-m-d') . ".xlsx";

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
DESCARGAR EXCEL DE PRODUCTOS CON BAJO STOCK
=============================================*/
public static function ctrDescargarReporteBajoStock() {
    $tabla = "productos";
    $productosBajoStock = ModeloProductos::mdlMostrarBajoStockPorCategoria();

    // Generar el archivo Excel
    $Name = "Reporte_Bajo_Stock_" . date('Y-m-d') . '.xls';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $Name . '"');
    echo utf8_decode("
        <table border='1'>
            <tr>
                <td style='font-weight:bold;'>CÓDIGO</td>
                <td style='font-weight:bold;'>CATEGORÍA</td>
                <td style='font-weight:bold;'>PRODUCTO</td>
                <td style='font-weight:bold;'>STOCK ACTUAL</td>
                <td style='font-weight:bold;'>UBICACIÓN</td>
                <td style='font-weight:bold;'>TALLA</td>
            </tr>
    ");
    foreach ($productosBajoStock as $producto) {
        echo utf8_decode("
            <tr>
                <td>" . $producto["codigo"] . "</td>
                <td>" . $producto["categoria"] . "</td>
                <td>" . $producto["descripcion"] . "</td>
                <td>" . $producto["stock"] . "</td>
                <td>" . $producto["ubicacion"] . "</td>
                <td>" . $producto["talla"] . "</td>
            </tr>
        ");
    }
    echo "</table>";
}

}