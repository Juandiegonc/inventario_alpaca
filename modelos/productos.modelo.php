<?php

require_once "conexion.php";

class ModeloProductos{

	/*=============================================
	MOSTRAR PRODUCTOS
	=============================================*/

	static public function mdlMostrarProductos($tabla, $item, $valor, $orden){
		if($item != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");
			$stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch();
			
		} else {
			// Si $orden es null, no usamos ORDER BY
			if ($orden) {
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY $orden DESC");
			} else {
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
			}
			$stmt->execute();
			return $stmt->fetchAll();
		}
		$stmt->close();
		$stmt = null;
	}
	

	/*=============================================
	REGISTRO DE PRODUCTO
	=============================================*/
static public function mdlIngresarProducto($tabla, $datos){

    $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_categoria, codigo, descripcion, ubicacion, talla, imagen, stock, precio_produccion, precio_venta) VALUES (:id_categoria, :codigo, :descripcion, :ubicacion, :talla, :imagen, :stock, :precio_produccion, :precio_venta)");

    $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
    $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
    $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
	$stmt->bindParam(":ubicacion", $datos["ubicacion"], PDO::PARAM_STR);
    $stmt->bindParam(":talla", $datos["talla"], PDO::PARAM_STR); // Asegúrate de que este parámetro esté presente en $datos
    $stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
    $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
    $stmt->bindParam(":precio_produccion", $datos["precio_produccion"], PDO::PARAM_STR);
    $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);

    if($stmt->execute()){
        return "ok";
    }else{
        return "error";
    }

    $stmt->close();
    $stmt = null;
}

/*=============================================
	EDITAR PRODUCTO
	=============================================*/

	static public function mdlEditarProducto($tabla, $datos){
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET id_categoria = :id_categoria, descripcion = :descripcion , ubicacion = :ubicacion, talla = :talla, imagen = :imagen, stock = :stock, precio_produccion = :precio_produccion, precio_venta = :precio_venta WHERE codigo = :codigo");
	
		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":ubicacion", $datos["ubicacion"], PDO::PARAM_STR);
		$stmt->bindParam(":talla", $datos["talla"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_produccion", $datos["precio_produccion"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
	
		if($stmt->execute()){
			return "ok";
		}else{
			return "error";
		}
	
		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	BORRAR PRODUCTO
	=============================================*/

	static public function mdlEliminarProducto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR PRODUCTO
	=============================================*/

	static public function mdlActualizarProducto($tabla, $item1, $valor1, $valor){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");

		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $valor, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR SUMA VENTAS
	=============================================*/	

	static public function mdlMostrarSumaVentas($tabla){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(ventas) as total FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}
	
		static public function mdlActualizarStockProducto($tabla, $idProducto, $nuevoStock) {
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET stock = :stock WHERE id = :id");
		$stmt->bindParam(":stock", $nuevoStock, PDO::PARAM_INT);
		$stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);
	
		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
	
		$stmt->close();
		$stmt = null;
	}
	
	/*=============================================
RANGO FECHAS PARA AGREGACIONES
=============================================*/
static public function mdlRangoFechasProductos($fechaInicial, $fechaFinal) {
    $stmt = Conexion::conectar()->prepare("
        SELECT 
            p.id,
            c.nombre AS categoria_nombre,
            p.codigo,
            p.descripcion,
            p.ubicacion
            p.stock,
            p.precio_produccion,
            p.precio_venta,
            p.ventas,
            p.fecha,
            p.talla
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id
        WHERE p.fecha BETWEEN :fechaInicial AND :fechaFinal
    ");
    $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
    $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll();
}

static public function mdlContarProductos($tabla) {
    $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM $tabla");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)["total"];
}
/*=============================================
MOSTRAR PRODUCTOS CON BAJO STOCK POR CATEGORÍA
=============================================*/
static public function mdlMostrarBajoStockPorCategoria(){
    $stmt = Conexion::conectar()->prepare("
        SELECT c.categoria AS categoria, p.codigo, p.descripcion, p.stock, p.ubicacion, p.talla
        FROM productos p
        JOIN categorias c ON p.id_categoria = c.id
        WHERE p.stock < 12
        ORDER BY c.categoria ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

}