<?php

// Incluye los archivos de controladores y modelos necesarios para el funcionamiento del código.
require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

// Define la clase AjaxProductos que maneja las solicitudes AJAX relacionadas con productos.
class AjaxProductos{

  /*=============================================
  GENERAR CÓDIGO A PARTIR DE ID CATEGORIA
  =============================================*/
  // Propiedad pública para almacenar el ID de la categoría.
  public $idCategoria;

  // Método para generar un código de producto basado en el ID de la categoría.
  public function ajaxCrearCodigoProducto(){

    // Define el campo de la base de datos que se utilizará para la consulta.
    $item = "id_categoria";
    // Asigna el valor de la categoría recibido desde el frontend.
    $valor = $this->idCategoria;
    // Define el orden en que se deben recuperar los productos.
    $orden = "id";

    // Llama al método estático del controlador de productos para obtener los productos.
    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

    // Devuelve la respuesta en formato JSON.
    echo json_encode($respuesta);

  }


  /*=============================================
  EDITAR PRODUCTO
  =============================================*/ 

  // Propiedades públicas para almacenar el ID del producto, un indicador para traer todos los productos y el nombre del producto.
  public $idProducto;
  public $traerProductos;
  public $nombreProducto;

  // Método para editar o recuperar información de un producto.
  public function ajaxEditarProducto(){

    // Verifica si se debe traer todos los productos.
    if($this->traerProductos == "ok"){

      // Define los valores nulos para traer todos los productos.
      $item = null;
      $valor = null;
      $orden = "id";

      // Llama al método estático del controlador de productos para obtener todos los productos.
      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

      // Devuelve la respuesta en formato JSON.
      echo json_encode($respuesta);


    // Verifica si se proporcionó un nombre de producto para buscar.
    }else if($this->nombreProducto != ""){

      // Define el campo de la base de datos que se utilizará para la consulta.
      $item = "descripcion";
      // Asigna el valor del nombre del producto recibido desde el frontend.
      $valor = $this->nombreProducto;
      // Define el orden en que se deben recuperar los productos.
      $orden = "id";

      // Llama al método estático del controlador de productos para obtener los productos que coincidan con el nombre.
      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

      // Devuelve la respuesta en formato JSON.
      echo json_encode($respuesta);

    // Si no se proporcionó un nombre de producto, se busca por ID.
    }else{

      // Define el campo de la base de datos que se utilizará para la consulta.
      $item = "id";
      // Asigna el valor del ID del producto recibido desde el frontend.
      $valor = $this->idProducto;
      // Define el orden en que se deben recuperar los productos.
      $orden = "id";

      // Llama al método estático del controlador de productos para obtener el producto con el ID especificado.
      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

      // Devuelve la respuesta en formato JSON.
      echo json_encode($respuesta);

    }

  }

}

/*=============================================
GENERAR CÓDIGO A PARTIR DE ID CATEGORIA
=============================================*/	

// Verifica si se ha enviado un ID de categoría a través de POST.
if(isset($_POST["idCategoria"])){

  // Crea una instancia de la clase AjaxProductos.
  $codigoProducto = new AjaxProductos();
  // Asigna el ID de la categoría recibido desde el frontend a la propiedad de la clase.
  $codigoProducto -> idCategoria = $_POST["idCategoria"];
  // Llama al método para generar el código del producto basado en el ID de la categoría.
  $codigoProducto -> ajaxCrearCodigoProducto();

}

/*=============================================
EDITAR PRODUCTO
=============================================*/ 

// Verifica si se ha enviado un ID de producto a través de POST.
if(isset($_POST["idProducto"])){

  // Crea una instancia de la clase AjaxProductos.
  $editarProducto = new AjaxProductos();
  // Asigna el ID del producto recibido desde el frontend a la propiedad de la clase.
  $editarProducto -> idProducto = $_POST["idProducto"];
  // Llama al método para editar o recuperar información del producto.
  $editarProducto -> ajaxEditarProducto();

}

/*=============================================
TRAER PRODUCTO
=============================================*/ 

// Verifica si se ha enviado una solicitud para traer todos los productos a través de POST.
if(isset($_POST["traerProductos"])){

  // Crea una instancia de la clase AjaxProductos.
  $traerProductos = new AjaxProductos();
  // Asigna el valor recibido desde el frontend a la propiedad de la clase.
  $traerProductos -> traerProductos = $_POST["traerProductos"];
  // Llama al método para traer todos los productos.
  $traerProductos -> ajaxEditarProducto();

}

/*=============================================
TRAER PRODUCTO
=============================================*/ 

// Verifica si se ha enviado un nombre de producto a través de POST.
if(isset($_POST["nombreProducto"])){

  // Crea una instancia de la clase AjaxProductos.
  $traerProductos = new AjaxProductos();
  // Asigna el nombre del producto recibido desde el frontend a la propiedad de la clase.
  $traerProductos -> nombreProducto = $_POST["nombreProducto"];
  // Llama al método para buscar productos por nombre.
  $traerProductos -> ajaxEditarProducto();

}

