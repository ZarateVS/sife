<?php

namespace FacelecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag; // Excel
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

class SucursalController extends Controller
{
    /**
     * @Route("/Sucursal", name="Sucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function SucursalAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Sucursal")) {
                return $this->render('FacelecBundle:Catalogos/Generales/Sucursal:Sucursal.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerSucursales", name="ObtenerSucursales")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerSucursalesAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Sucursal")) {
                $params = [
                    'sucursales'=>$this->ObtenerSucursales()
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Catalogos/Generales/Sucursal:TablaSucursales.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ActivarDesactivarSucursal", name="ActivarDesactivarSucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ActivarDesactivarSucursalAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Sucursal")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $Userid = $session->get('Userid');
                    $idsuc = $_POST['idsuc'];
                    $valor = $_POST['valor'];
                    $suc = $_POST['suc'];
                    $cve = $_POST['cve'];
                    if ($this->ActivarDesactivarSucursal($idsuc, $valor, $suc, $Userid, $cve))
                        die("1");
                    else
                        die("2");
                } else return $this->redirect($this->generateUrl('Sucursal'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/AgregarSucursal", name="AgregarSucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function AgregarSucursalAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("Sucursal")) {
                $Userid = $session->get('Userid');
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if ($this->validarInfo($datos)) {
                        if (!$this->compararSucursalPorClave($datos['sucursal'])) {
                            if($this->AgregarSucursal($datos, $Userid))
                                die("1"); // Operación exitosa
                            else
                                die("2");
                        }
                        else {
                            die("3"); // Clave de sucursal existente y activa
                        }
                    }
                    else {
                        die("2"); // No sepudo realizar la operación
                    }
                }
                else {
                    $params = [
                        'clientes' => $this->ObtenerClientes($Userid)
                    ];
                    $array = array_merge($params, $session->get('menu'));
                    return $this->render('FacelecBundle:Catalogos/Generales/Sucursal:AgregarSucursal.html.twig', $array);
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ModificarSucursal", name="ModificarSucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ModificarSucursalAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("Sucursal")) {
                $Userid = $session->get('Userid');
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if (!isset($datos['id_sucursal'])) {
                        $id = $_POST['id'];
                        $params = [
                            'sucursales' => $this->ObtenerSucursal($id),
                            'clientes' => $this->ObtenerClientes($Userid),
                            'documentos' => $this->ObtenerDocumentos($id)
                        ];
                        $array = array_merge($params, $session->get('menu'));
                        return $this->render('FacelecBundle:Catalogos/Generales/Sucursal:ModificarSucursal.html.twig', $array);
                    }
                    else {
                        if ($this->validarInfo($datos)) {
                            if (!$this->compararSucursalPorClaveYId($datos['sucursal'], $datos['id_sucursal'])) {
                                if ($this->ActualizarSucursal($datos, $Userid))
                                    die("1"); // Operación completa
                                else
                                    die("2");
                            }
                            else {
                                die("3"); // Clave de sucursal existente y activa
                            }
                        }
                        else {
                            die("2"); // No se pudo realizar la operación
                        }
                    }
                } else return $this->redirect($this->generateUrl('Sucursal'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/Excel", name="Excel")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ExcelAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Sucursal")) {
                return $this->generarExcel();
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/boton", name="boton")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function botonAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Sucursal")) {
                return $this->render('FacelecBundle:Catalogos/Generales/Sucursal:btnExcel.html.twig');
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    public function ObtenerSucursales() {
        $conn = new Model();
        $sql = "SELECT id_sucursal, sucursal, nombre, apertura, activa FROM sucursales ORDER BY nombre";
        $result = $conn->query($sql);
        $sucursales = [];
        while ($row=$conn->fetch_array($result)) {
            $sucursales[] = array(
                'id_sucursal'=>$row['id_sucursal'],
                'sucursal'=>utf8_encode($row['sucursal']),
                'nombre'=>utf8_encode($row['nombre']),
                'apertura'=>$row['apertura'],
                'activa'=>$row['activa']
            );
        }
        $conn->close();
        return $sucursales;
    }
    
    public function ObtenerSucursal($id) {
        $conn = new Model();
        $sql = "SELECT id_sucursal, id_cliente, sucursal, nombre, apertura, activa, tiposucursal FROM sucursales WHERE id_sucursal=$id ORDER BY nombre";
        $result = $conn->query($sql);
        $sucursal = [];
        while ($row=$conn->fetch_array($result)) {
            $sucursal[] = array(
                'id_sucursal'=>$row['id_sucursal'],
                'id_cliente'=>$row['id_cliente'],
                'sucursal'=>utf8_encode($row['sucursal']),
                'nombre'=>utf8_encode($row['nombre']),
                'apertura'=>$row['apertura'],
                'activa'=>$row['activa'],
                'tiposucursal'=>$row['tiposucursal']
            );
        }
        $conn->close();
        return $sucursal;
    }
    
    public function ActivarDesactivarSucursal($idsuc, $valor, $suc, $Userid, $cve) {
        if ($valor == 1) {
            if ($this->compararSucursalPorClave($cve))
                return 0;
            $accion = "Activar sucursal";
            $detalle = "Activó la sucursal: $suc";
        }
        else {
            $accion = "Desactivar sucursal";
            $detalle = "Desactivó la sucursal: $suc";
        }
        $conn = new Model();
        $sql = "UPDATE sucursales SET activa=$valor WHERE id_sucursal=$idsuc";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0)
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        $conn->close();
        return $result;
    }
    
    public function ActualizarSucursal($datos, $Userid) {
        $conn = new Model();
        $sql_select = "SELECT razonsocial, sucursal, nombre, apertura, tiposucursal
                        FROM sucursales A INNER JOIN clientes B ON A.id_cliente=B.id_cliente
                        WHERE id_sucursal=".$datos['id_sucursal'].";";
        $result = $conn->query($sql_select);
        $rs = $this->ObtenerRS($datos['rs'], $conn);
        while ($row = $conn->fetch_array($result)) {
            $tipo1 = ($row['tiposucursal'] === "F") ? "Farmacia" : "Laboratorio";
            $tipo2 = ($datos['tipo'] === "F") ? "Farmacia" : "Laboratorio";
            $detalle="Actualizó la siguiente sucursal de: Sucursal: ".$row['sucursal'].", Nombre: ".utf8_encode($row['nombre']).", Tipo: $tipo1, Apertura: ".$row['apertura'].", Razón social: ".utf8_decode($row['razonsocial'])." --A Sucursal: ".$datos['sucursal'].", Nombre: ".$datos['nombre'].", Tipo: $tipo2, Apertura: ".$datos['apertura'].", Razón social: ".$rs;
        }
        $sql_update = "UPDATE sucursales SET id_cliente=".$datos['rs'].", sucursal='".$datos['sucursal']."', nombre='".addslashes($datos['nombre'])."', apertura='".$datos['apertura']."', tiposucursal='".$datos['tipo']."' WHERE Id_sucursal=".$datos['id_sucursal'];
        $result = $conn->query($sql_update);
        if ($conn->num_rows($result) > 0) {
            $accion = "Actualizar sucursal";
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function AgregarSucursal ($datos, $Userid) {
        $conn = new Model();
        $sql = "INSERT INTO sucursales (id_cliente, sucursal, nombre, activa, apertura, usr_crea, fch_crea, tiposucursal) VALUES (".$datos['rs'].", '".$datos['sucursal']."', '".addslashes($datos['nombre'])."', 1, '".$datos['apertura']."', $Userid, now(), '".$datos['tipo']."')";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            $accion = "Agregar sucursal";
            $detalle = "Agregó la sucursal: ".$datos['sucursal']."-".$datos['nombre']."";
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function ObtenerClientes($Userid) {
        $conn = new Model();
        $sql = "SELECT c.id_cliente, c.razonsocial FROM clientes c INNER JOIN sis_usrcli uc ON uc.id_cliente=c.id_cliente WHERE c.activo=1 AND uc.idUsr=$Userid ORDER BY razonsocial";
        $result = $conn->query($sql);
        while ($row=$conn->fetch_array($result)) {
            $clientes[] = array(
                'id_cliente' => $row['id_cliente'],
                'razonsocial' => utf8_encode($row['razonsocial'])
            );
        }
        $conn->close();
        return $clientes;
    }
    
    public function ObtenerDocumentos($id) {
        $conn = new Model();
        $sql = "SELECT * FROM documentos WHERE id_sucursal=$id GROUP BY id_sucursal";
        $result = $conn->query($sql);
        $docs = [];
        while ($row=$conn->fetch_array($result)) {
            $docs[] = $row;
        }
        $conn->close();
        return $docs;
    }
    
    public function ObtenerRS($id, $conn) {
        $sql = "SELECT razonsocial FROM clientes WHERE id_cliente=$id";
        $result = $conn->query($sql);
        while ($row=$conn->fetch_array($result)) {
            $rs = utf8_encode($row['razonsocial']);
        }
        return $rs;
    }
    
    public function validarInfo($datos) {
        $a = array_values($datos);
        
        // Validar campos vacios
        for ($i = 0; $i < sizeof($a); $i++) {
            if ($a[$i] === "" )
                return 0;
        }
        
        // Validar enteros
        if (!ctype_digit($a[0]))
            return 0;
        
        // Validar cadenas
        $cadenas = [$a[1], $a[2], $a[3], $a[4]];
        $longs = [6, 100, 1, 10];
        for ($i = 0; $i < sizeof($cadenas); $i++) {
            if (strlen($cadenas[$i]) > $longs[$i])
                return 0;   
        }
        
        // Validar apostrofe
        $sin_apostrofe = [$a[0], $a[1], $a[3], $a[4]];
        for ($i = 0; $i< sizeof($sin_apostrofe); $i++) {
            if (strpos($sin_apostrofe[$i],"'") !== false)
                return 0;
        }
        
        return 1;
    }
    
    public function cambiarFormatoFecha($fch){ // De -> YYYY-MM-DD - A -> DD/MM/YYYY
        return  preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2})/i','$3/$2/$1', $fch);
    }
    
    public function compararSucursalPorClave($clave) {
        $conn = new Model();
        $sql = "SELECT * FROM sucursales WHERE sucursal='$clave' AND activa=1;";
        $result = $conn->query($sql);
        $sucursal = "";
        while ($row = $conn->fetch_array($result)) {
            $sucursal = $row;
        }
        $conn->close();
        return $sucursal;
    }
    
    public function compararSucursalPorClaveYId($clave, $id) {
        $conn = new Model();
        $sql = "SELECT * FROM sucursales WHERE sucursal='$clave' AND activa=1 AND id_sucursal NOT IN ($id);";
        $result = $conn->query($sql);
        $sucursal = "";
        while ($row = $conn->fetch_array($result)) {
            $sucursal = $row;
        }
        $conn->close();
        return $sucursal;
    }
    
    public function registrarBitacora($accion, $detalle, $Userid, $conn) {
        $idmnu = $this->container->getParameter('Sucursal'); // El id del menu se encuentra definido en el archivo parameters.yml
        $sql = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '$detalle')";
        $conn->query($sql);
    }
    
    public function generarExcel() {
        // ask the service for a excel object
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator(gethostname())
            ->setLastModifiedBy(gethostname());

        $phpExcelObject->getActiveSheet()->setTitle('Sucursales');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        $celda = $phpExcelObject->getActiveSheet();

        $conn = new Model();
        $sql = "SELECT razonsocial, sucursal, nombre, apertura, activa, tiposucursal
                FROM sucursales A INNER JOIN clientes B ON A.id_cliente=B.id_cliente
                ORDER BY nombre";
        $result = $conn->query($sql);

        $celda->setCellValue('A1', 'Razón Social')
                ->setCellValue('B1', 'Sucursal')
                ->setCellValue('C1', 'Nombre')
                ->setCellValue('D1', 'Apertura')
                ->setCellValue('E1', 'Estatus')
                ->setCellValue('F1', 'Tipo');
        //$celda->getStyle('B2:E2')->getFont()->setSize(12);
        $phpExcelObject->getDefaultStyle()->getFont()->setName('Arial');
        $phpExcelObject->getDefaultStyle()->getFont()->setSize(10);

        $celda->getStyle('A1:F1')->getFont()->setBold(true);
        $celda->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        //$celda->getStyle('B2:E2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');

        $i = 2;
        while ($row = $conn->fetch_array($result)) {
            $estatus = ($row['activa'] == 1) ? "Activa" : "Inactiva";
            $color = ($row['activa'] == 1) ? 'EDFAE0' : 'FFE4E4';
            $tipo = ($row['tiposucursal'] === "F") ? "Farmacia" : "Laboratorio";
            
            $celda->setCellValue('A'.$i, utf8_encode($row['razonsocial']))
                ->setCellValue('B'.$i, $row['sucursal'])
                ->setCellValue('C'.$i, utf8_encode($row['nombre']))
                ->setCellValue('D'.$i, $row['apertura'])
                ->setCellValue('E'.$i, $estatus)
                ->setCellValue('F'.$i, $tipo);
            $celda->getStyle('A'.$i.':F'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            
            $celda->getStyle('A'.$i.':F'.$i)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $color)
                    )
                )
            );
            $i++;
        }

        foreach(range('A', 'F') as $column) {
            $celda->getColumnDimension($column)->setAutoSize(true);
        }

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Sucursales.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}