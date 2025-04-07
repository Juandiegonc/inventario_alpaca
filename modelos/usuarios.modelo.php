<?php

require_once "conexion.php";

class ModeloUsuarios{

	/*=============================================
	MOSTRAR USUARIOS
	=============================================*/

	static public function mdlMostrarUsuarios($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}
		

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	REGISTRO DE USUARIO
	=============================================*/

	static public function mdlIngresarUsuario($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, usuario, password, correo, perfil, foto) VALUES (:nombre, :usuario, :password, :correo, :perfil, :foto)");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
		$stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
		$stmt->bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
		$stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
		$stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";	

		}else{

			return "error";
		
		}

		$stmt->close();
		
		$stmt = null;

	}

	/*=============================================
	EDITAR USUARIO
	=============================================*/

	static public function mdlEditarUsuario($tabla, $datos){
	
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, password = :password, correo = :correo, perfil = :perfil, foto = :foto WHERE usuario = :usuario");

		$stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt -> bindParam(":password", $datos["password"], PDO::PARAM_STR);
		$stmt -> bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
		$stmt -> bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
		$stmt -> bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
		$stmt -> bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR USUARIO
	=============================================*/

	static public function mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");

		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":".$item2, $valor2, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	BORRAR USUARIO
	=============================================*/

	static public function mdlBorrarUsuario($tabla, $datos){

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
GUARDAR TOKEN EN LA BASE DE DATOS
=============================================*/
static public function mdlGuardarToken($tabla, $datos){
    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET token = :token, token_expiracion = :token_expiracion WHERE id = :id");
    $stmt->bindParam(":token", $datos["token"], PDO::PARAM_STR);
    $stmt->bindParam(":token_expiracion", $datos["token_expiracion"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

    if($stmt->execute()){
        return "ok";
    } else {
        return "error";
    }
}
 /*=============================================
    VERIFICAR TOKEN EN LA BASE DE DATOS
    =============================================*/
    static public function mdlVerificarToken($tabla, $token){
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE token = :token");
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(); // Devuelve los datos del usuario asociado al token
    }

  /*=============================================
ACTUALIZAR CONTRASEÑA TEMPORAL EN LA BASE DE DATOS
=============================================*/
static public function mdlActualizarContrasenaTemporal($tabla, $datos){
    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET password = :password WHERE id = :id");
    $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

    if($stmt->execute()){
        return "ok";
    } else {
        return "error";
    }
}
/*=============================================
OBTENER USUARIOS POR ROLES
=============================================*/
static public function mdlObtenerUsuariosPorRoles($tabla, $roles) {
    // Contar la cantidad de roles proporcionados
    $cantidadRoles = count($roles);

    // Crear placeholders para la consulta SQL (?, ?, ...)
    $placeholders = implode(',', array_fill(0, $cantidadRoles, '?'));

    // Preparar la consulta SQL
    $stmt = Conexion::conectar()->prepare("
        SELECT id, nombre, correo, perfil 
        FROM $tabla 
        WHERE perfil IN ($placeholders)
    ");

    // Ejecutar la consulta con los valores de los roles
    $stmt->execute($roles);

    // Obtener los resultados
    return $stmt->fetchAll();
}
}