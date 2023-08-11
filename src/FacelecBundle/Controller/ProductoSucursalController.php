<?php

namespace FacelecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use FacelecBundle\Controller\GenerarMenuController;
use FacelecBundle\Model\ModelCorporativo;
use FacelecBundle\Model\Model;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProductoSucursalController extends Controller
{
    /**
     * Muestra el Catalogo de Productos
     * Realiza:
     * 1.- Comprueba si el usuario esta logeado en el sistema y si tiene permiso de acceso al catalogo
     * 2.- Muestra la vista CatalogoProductos que contiene la tabla con la información de todos los productos
     * @Route("/ProductosSucursales", name="ProductosSucursales")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ProductosSucursalesAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("ProductosSucursales")) {
                return $this->render('FacelecBundle:Catalogos/Generales/ProductoSucursal:ProductosSucursales.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * Obtiene los productos de la BD
     * Realiza:
     * 1.- Comprueba si el usuario esta logeado en el sistema y si tiene permiso de acceso al catálogo.
     * 2.- Almacena la información de todos los productos en el arreglo $params en la clave 'productos' mediante la función ObetenerProductos().
     * 3.- Fusiona el arreglo $params con la variable de sesión 'menu' en $array.
     * 4.- Envía el arreglo a la vista TablaProductos para llenar la tabla.
     * @Route("/ObtenerProductos", name="ObtenerProductos")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerProductosAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("ProductosSucursales")) {
                $params = [ 'productos'=>$this->ObtenerProductos() ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Catalogos/Generales/ProductoSucursal:TablaProductosSucursales.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }

    /**
     * Activa/Desactiva un producto de la BD
     * 1.- Comprueba si el usuario esta logeado en el sistema y si tiene permiso de acceso al catálogo.
     * 2.- Comprueba si el método de petición es POST.
     * 3.- Si es POST establece las variables que contienen el id del usuario, id y nombre del producto a ser activado/desactivado y el valor que indica esta acción (1=activar, 0=desactivar).
     * 4.- Si no es POST muestra la vista CatalogoProductos.
     * @Route("/ActivarDesactivarProducto", name="ActivarDesactivarProducto")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ActivarDesactivarProductoAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("ProductosSucursales")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $Userid = $session->get('Userid');
                    $idprod = $_POST['idprod'];
                    $nomprod = $_POST['nomprod'];
                    $valor = $_POST['valor'];
                    if ($this->ActivarDesactivarProducto($idprod, $nomprod, $valor, $Userid))
                        die('1');
                } else return $this->redirect($this->generateUrl('ProductosSucursales'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * Agrega un nuevo producto a la BD
     * 1.- Comprueba si el usuario esta logeado en el sistema y si tiene permiso de acceso al catálogo.
     * 2.- Comprueba si el método de petición es POST.
     * 3.- Si el método es POST, crea un objeto de la clase DefaultController y ejecuta su función getData(), la cual obtinee la información y la almacena en $datos.
     * 4.- Comprueba si el código y nombre del nuevo producto no existen en la BD, mediante la función Comparar_producto_por_id_y_nombre().
     * 5.- Valida la longitud del código, nombre y unidad del nuevo producto.
     * 6.- Agrega el nuevo producto mediante la función AgregarProducto().
     * 7.- Si el método de petición no es POST, muestra la vista AgregarProducto.
     * @Route("/AgregarProducto", name="AgregarProducto")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function AgregarProductoAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("ProductosSucursales")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if (!$this->Comparar_producto_por_id_y_nombre($datos)) {
                        if ($this->validarInfo($datos)) {
                            $Userid = $session->get('Userid');
                            if ($this->AgregarProducto($datos, $Userid))
                                die("1");
                            else
                                die("3");
                        }
                        else
                            die("3");
                    }
                    else
                        die("2");
                }
                $params = [
                    'cves_prodserv'=>$this->ObtenerClavesProductosServicios(),
                    'cves_unidad'=>$this->ObtenerClavesUnidad()
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Catalogos/Generales/ProductoSucursal:AgregarProductoSucursal.html.twig', $array);
            } else return $this->redirect($this->generateUrl('Alerta'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * Muestra la vista para edición de un producto / Ejecuta la actualización de un producto.
     * 1.- Comprueba si el usuario esta logeado en el sistema y si tiene permiso de acceso al catálogo.
     * 2.- Si el método de petición es POST:
     *   2.1.- Se obtiene la información que se recibe desde la vista, mediante la función getData() y se almacena en $datos.
     *   2.2.- Si no esta definida la variable 'codigo' dentro de $datos, se obtiene la información de un producto mediante su id con la función ObtenerInfoProducto() y muestra la vista ModificarProducto.
     *   2.3.- Si esta definida 'codigo', se valida la longitud de los datos y además se valida que el código y el nombre no se dupliquen, para luego ejecutar la actualización mediante la función ModificarProducto().
     * 3.- Si el método no es POST, regresa a la vista CatalogoProductos.
     * @Route("/ModificarProducto", name="ModificarProducto")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ModificarProductoAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has('ProductosSucursales')) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if (!isset($datos['codigo'])) {// Si no esta definida la variable $datos['codigo'], carga el formulario con la informacion del producto
                        $idprod = $_POST['id'];
                        $params = [
                            'InfoProducto'=>$this->ObtenerInfoProducto($idprod),
                            'cves_prodserv'=>$this->ObtenerClavesProductosServicios(),
                            'cves_unidad'=>$this->ObtenerClavesUnidad()
                        ];
                        $array = array_merge($params, $session->get('menu'));
                        return $this->render('FacelecBundle:Catalogos/Generales/ProductoSucursal:ModificarProductoSucursal.html.twig', $array);
                    }
                    else {
                        if($this->validarInfo($datos)) {
                            if ($datos["codigo"] != $datos["codigo_actual"] && $datos["nombre"] == $datos["nombre_actual"]) {
                                if(!$this->Compararproductoporid($datos)) {
                                    $Userid = $session->get('Userid');
                                    if($this->ModificarProducto($datos, $Userid))
                                        die("1");
                                }
                                else
                                    die("Código");
                            }
                            if ($datos["codigo"] == $datos["codigo_actual"] && $datos["nombre"] != $datos["nombre_actual"]) {
                                if(!$this->Compararproductopornombre($datos)) {
                                    $Userid = $session->get('Userid');
                                    if($this->ModificarProducto($datos, $Userid))
                                        die("1");
                                }
                                else
                                    die("Nombre");
                            }
                            if ($datos["codigo"] != $datos["codigo_actual"] && $datos["nombre"] != $datos["nombre_actual"]) {
                                if(!$this->Comparar_producto_por_id_y_nombre($datos)) {
                                    $Userid = $session->get('Userid');
                                    if($this->ModificarProducto($datos, $Userid))
                                        die("1");
                                }
                                else
                                    die("Código o nombre");
                            }
                            $Userid = $session->get('Userid');
                            if($this->ModificarProducto($datos, $Userid))
                                die("1");
                        }
                        else
                            die("3");
                    }
                }
                else return $this->redirect($this->generateUrl('ProductosSucursales'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    
    /**
     * @Route("/LlenarSelectsProductosSucursales", name="LlenarSelectsProductosSucursales")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function LlenarSelectsProductosSucursalesAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("ProductosSucursales")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $params = [ 'cves_prodserv'=>$this->ObtenerClavesProductosServicios() ];
                    return $this->render('FacelecBundle:Catalogos/Generales/ProductoSucursal:SelectPS.html.twig', $params);
                }
                else return $this->redirect($this->generateUrl('ProductosSucursales'));
            } else return $this->redirect($this->generateUrl('Alerta'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /* ------------------FUNCIONES-------------------- */
    
    /**
     * Agrega un nuevo producto.
     * Registra en la tabla sis_bitacora la accion y el nombre del producto insertado
     */   
    public function AgregarProducto ($datos, $Userid) {
        $conn = new Model();
        $sql = "INSERT INTO cat_producto (Id_Producto, Nombre, Precio, IVA, unidad, activo, usrC, fchC, claveProdServ, claveUnidad) VALUES ('".$datos['codigo']."', '".$datos['nombre']."', ".$datos['precio'].", ".$datos['iva'].", '".$datos['unidad']."', 1, $Userid, now(), '".$datos['cve_prodserv']."', '".$datos['cve_unidad']."')";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            $accion = "Agregar producto sucursal";
            $detalle = "Agregó el producto sucursal: ".$datos['nombre'];
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    /**
    * Actualiza un producto.
    * Ejecuta el primer Select para almacenar la información del producto, previa a la actualización.
    * Ejecuta el Update para actualizar el producto.
    * Almacena la información del producto antes y después de realizar la actualización.
    * @return $result
    */
    public function ModificarProducto ($datos, $Userid) {
        $conn = new Model();
        $sql_select = "SELECT * FROM cat_producto WHERE Id_Producto='".$datos['codigo_actual']."'";
        $result_select = $conn->query($sql_select);
        while ($row = $conn->fetch_array($result_select)) {
            $detalle = "Actualizó el siguiente producto sucursal de: Id_Producto: ".$row['Id_Producto'].", Nombre: ".utf8_encode($row['Nombre']).", Precio: ".$row['Precio'].", IVA: ".$row['IVA'].", Unidad: ".$row['unidad'].", ClaveProdServ: ".$row['claveProdServ'].", ClaveUnidad: ".$row['claveUnidad']." -- A: Id_Producto: ".$datos['codigo'].", Nombre: ".$datos['nombre'].", Precio: ".$datos['precio'].", IVA: ".$datos['iva'].", Unidad: ".$datos['unidad'].", ClaveProdServ: ".$datos['cve_prodserv'].", ClaveUnidad: ".$datos['cve_unidad'].";";
        }
        $sql_update = "UPDATE cat_producto SET Id_Producto='".$datos['codigo']."', Nombre='".$datos['nombre']."', Precio=".$datos['precio'].", IVA=".$datos['iva'].", unidad='".$datos['unidad']."', claveProdServ='".$datos['cve_prodserv']."', claveUnidad='".$datos['cve_unidad']."' WHERE Id_Producto='".$datos['codigo_actual']."'";
        $result_update = $conn->query($sql_update);
        if ($conn->num_rows($result_update) > 0) {
            $accion = "Actualizar producto sucursal";
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result_update;
    }
    
    /**
     * Obtiene la información de los prodcutos de la BD
     * @return $productos
     */
    public function ObtenerProductos() {
        $conn = new Model();
        $sql = "SELECT * FROM cat_producto ORDER BY Id_Producto";
        $productos = $this->setCodingUtf8($sql, $conn, 0);
        /*$result = $conn->query($sql);
        while ($row = $conn->fetch_array($result)) {
            $productos[] = array(
                'Id_Producto'=>$row['Id_Producto'],
                'Nombre'=>utf8_encode($row['Nombre']),
                'Precio'=>$row['Precio'],
                'IVA'=>$row['IVA'],
                'activo'=>$row['activo']
            );
        }*/
        $conn->close();
        return $productos;
    }
    
    /**
     * Obtiene la información de un prodcuto de la BD
     * @return $producto
     */
    public function ObtenerInfoProducto($idprod) {
        $conn = new Model();
        $sql = "SELECT * FROM cat_producto WHERE Id_Producto=$idprod";
        $producto = $this->setCodingUtf8($sql, $conn, 0);
        /*$result = $conn->query($sql);
        $producto = [];
        while($row = $conn->fetch_array($result)) {
            $producto [] = array(
                'Id_Producto'=>$row['Id_Producto'],
                'Nombre'=>utf8_encode($row['Nombre']),
                'Precio'=>$row['Precio'],
                'IVA'=>$row['IVA'],
                'unidad'=>$row['unidad'],
                'activo'=>$row['activo'],
                'claveProdServ'=>$row['claveProdServ'],
                'claveUnidad'=>$row['claveUnidad']
            );
        }*/
        $conn->close();
        return $producto;
    }
    
    /**
     * Activa/Desactiva un producto
     * Verifica si el valor de la variable $valor es igual a 1 (activar) o a 0 (desactivar), el cual indica la acción
     * @return $result
     */
    public function ActivarDesactivarProducto($idprod, $nomprod, $valor, $Userid) {
        $conn = new Model();
        $sql = "UPDATE cat_producto SET activo=$valor WHERE Id_Producto=$idprod";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            if ($valor == 1) {
                $accion = "Activar producto sucursal";
                $detalle = "Activó el producto sucursal: $nomprod";
            }
            else {
                $accion = "Desactivar producto sucursal";
                $detalle = "Desactivó el producto sucursal: $nomprod";
            }
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    /**
     * Verifica si el id o el nombre existen en la BD
     * @return $producto
     */    
    public function Comparar_producto_por_id_y_nombre($datos) {
        $conn = new Model();
        $sql = "SELECT * FROM cat_producto WHERE Id_Producto='".$datos['codigo']."' OR Nombre='".$datos['nombre']."'";
        $result = $conn->query($sql);
        $producto = [];
        while ($row = $conn->fetch_array($result)) {
            $producto[] = $row;
        }
        $conn->close();
        return $producto;
    }
    
    /**
     * Verifica si el id existe en la BD.
     * @return $producto
     */    
    public function Compararproductoporid ($datos) {
        $conn = new Model();
        $sql = "SELECT * FROM cat_producto WHERE Id_Producto LIKE '".$datos['codigo']."' AND Nombre NOT IN ('".$datos['nombre']."')";
        $result = $conn->query($sql);
        $producto = [];
        while ($row = $conn->fetch_array($result)) {
            $producto[] = $row;
        }
        $conn->close();
        return $producto;
    }
    
    /**
     * Verifica si el nombre existe en la BD.
     * @return $producto
     */    
    public function Compararproductopornombre ($datos) {
        $conn = new Model();
        $sql = "SELECT * FROM cat_producto WHERE Nombre LIKE '".$datos['nombre']."' AND Id_Producto NOT IN ('".$datos['codigo']."')";
        $result = $conn->query($sql);
        $producto = [];
        while ($row = $conn->fetch_array($result)) {
            $producto[] = $row;
        }
        $conn->close();
        return $producto;
    }
    
    public function ObtenerClavesUnidad () {
        $conn = new Model();
        $sql= "SELECT claveUnidad, nombre FROM c_claveunidad ORDER BY nombre";
        $cves_unidad = $this->setCodingUtf8($sql, $conn, 0);
        $conn->close();
        return $cves_unidad;
    }
    
    public function ObtenerClavesProductosServicios () {
        $conn = new Model();
        $sql= "SELECT claveProdServ, descripcion FROM c_claveprodserv ORDER BY descripcion LIMIT 0, 100";
        $cves_prodserv = $this->setCodingUtf8($sql, $conn, 0);
        $conn->close();
        return $cves_prodserv;
    }
    
    public function registrarBitacora($accion, $detalle, $Userid, $conn) {
        $idmnu = $this->container->getParameter('ProductosSucursales');
        $sql = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '$detalle')";
        $conn->query($sql);
    }
    
    /**
     * Valida la longitud del codigo, nombre y unidad del producto
     */ 
    public function ValidarLongitud ($datos) {
        if (strlen($datos['codigo']) > 50 or strlen($datos['nombre']) > 180 or strlen($datos['unidad']) > 50 or strlen($datos['cve_prodserv']) > 10 or strlen($datos['cve_unidad']) > 10)
            return 0;
        return 1;
    }
    
    public function ValidarDecimal($valor) {
        if ($valor != ""){ // Si es diferente de vacio
            if (is_numeric($valor)) { // Si es un valor numérico
                if(strpos($valor,".") !== false) { // Si hay un punto
                    $num = explode(".", $valor);
                    $entero = (int)$num[0];
                    $decimal = (int)$num[1];
                    if (strlen($entero) <= 12 && $entero >= 0 && strlen($decimal) <= 2 && $decimal >= 0)
                        return 1; // Valido 1
                    else
                        return 0; // No Valido 0
                }
                else
                    if (strlen($valor) <= 15 && (int)$valor > 0)
                        return 1; // Valido 1
                    else
                        return 0; // No Valido 0
            }
            else {
                return 0; // No Valido 0
            }
        }
        else
            return 0; // No Valido 0
    }
    
    public function validarInfo($datos) {
        
        if (!$this->ValidarDecimal($datos['precio']))
            return 0;
        
        if (!$this->ValidarLongitud($datos))
            return 0;
        
        return 1;
    }
    
    public function setCodingUtf8($sql, $conn, $format) {
        $r = $conn->query($sql);
        $array = [];
        while ($row = $conn->fetch_array($r)) {
            $keys = array_keys($row);
            for ($i = 0; $i < sizeof($row); $i++) {
                $a[$keys[$i]] = utf8_encode($row[$keys[$i]]);
            }
            $array [] = $a;
        }
        
        if ($format == 1)
            return $a;
        else
            return $array;
    }
}