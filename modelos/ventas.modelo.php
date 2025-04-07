<?php

require_once "conexion.php";

class ModeloVentas{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/

	static public function mdlMostrarVentas($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id ASC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}
		
		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	REGISTRO DE VENTA
	=============================================*/
	static public function mdlIngresarVenta($tabla, $datos) {
		try {
			$pdo = Conexion::conectar();
		} catch (Exception $e) {
			return "error";
		}
	
		$stmt = $pdo->prepare("INSERT INTO $tabla(codigo, id_vendedor, impuesto, neto, total) VALUES (:codigo, :id_vendedor, :impuesto, :neto, :total)");
	
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
	
		if ($stmt->execute()) {
			return $pdo->lastInsertId();
		} else {
			return "error";
		}
	
		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	EDITAR VENTA
	=============================================*/

	static public function mdlEditarVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET   id_vendedor = :id_vendedor,  impuesto = :impuesto, neto = :neto, total= :total WHERE codigo = :codigo");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/

	static public function mdlEliminarVenta($tabla, $datos){

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
	RANGO FECHAS
	=============================================*/	

	/*=============================================
	RANGO FECHAS
	=============================================*/	

	static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal) {
		$pdo = Conexion::conectar();
	
		if ($fechaInicial == null) {
			$stmt = $pdo->prepare("SELECT * FROM $tabla ORDER BY id ASC");
			$stmt->execute();
			return $stmt->fetchAll();
		} elseif ($fechaInicial == $fechaFinal) {
			$stmt = $pdo->prepare("SELECT * FROM $tabla WHERE fecha LIKE :fecha");
			$stmt->bindValue(":fecha", '%' . $fechaFinal . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll();
		} else {
			$fechaActual = new DateTime();
			$fechaActual->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");
	
			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");
	
			if ($fechaFinalMasUno == $fechaActualMasUno) {
				$stmt = $pdo->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN :fechaInicial AND :fechaFinalMasUno");
				$stmt->bindValue(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
				$stmt->bindValue(":fechaFinalMasUno", $fechaFinalMasUno, PDO::PARAM_STR);
			} else {
				$stmt = $pdo->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN :fechaInicial AND :fechaFinal");
				$stmt->bindValue(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
				$stmt->bindValue(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
			}
	
			$stmt->execute();
			return $stmt->fetchAll();
		}
	}
	
	/*=============================================
	SUMAR EL TOTAL DE VENTAS
	=============================================*/

	static public function mdlSumaTotalVentas($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT SUM(neto) as total FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}


	static public function mdlIngresarDetalleVenta($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_venta, id_producto, cantidad, precio_unitario) VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario)");

        $stmt->bindParam(":id_venta", $datos["id_venta"], PDO::PARAM_INT);
        $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
        $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return true;
        } else {
            // Mostrar errores SQL
            print_r($stmt->errorInfo());
            return false;
        }

        $stmt->close();
        $stmt = null;
    }
	static public function mdlMostrarDetallesVenta($tabla, $item, $valor) {
		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
		$stmt->bindParam(":" . $item, $valor, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	static public function mdlEliminarDetalleVenta($idVenta) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM detalle_venta WHERE id_venta = :id_venta");
            $stmt->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (Exception $e) {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }
	
}
	