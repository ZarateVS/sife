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

class EstadoController extends Controller
{
    /**
     * @Route("/Estado", name="Estado")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function EstadoAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Estado")) {
                return $this->render('FacelecBundle:Catalogos/Generales/Estado:Estado.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerEstados", name="ObtenerEstados")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerEstadosAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Estado")) {
                $params = [
                    'estados'=>$this->ObtenerEstados()
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Catalogos/Generales/Estado:TablaEstados.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ActivarDesactivarEstado", name="ActivarDesactivarEstado")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ActivarDesactivarEstadoAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Estado")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $Userid = $session->get('Userid');
                    $id_edo = $_POST['id_edo'];
                    $nom_edo = $_POST['nom_edo'];
                    $valor = $_POST['valor'];
                    if ($this->ActivarDesactivarEstado($id_edo, $nom_edo, $valor, $Userid))
                        die("1");
                    else
                        die("2");
                } else return $this->redirect($this->generateUrl('Estado'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/AgregarEstado", name="AgregarEstado")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function AgregarEstadoAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("Estado")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if ($this->validarInfo($datos)) {
                        if (!$this->validarNombreEstado($datos)) {
                            $Userid = $session->get('Userid');
                            if ($this->AgregarEstado($datos, $Userid))
                                die("1"); // Operación completa
                            else
                                die("2"); 
                        }
                        else {
                            die("3"); // Nombre existente
                        }
                    }
                    else {
                        die("2"); // No se pudo realizar la operación
                    }
                }
                else {
                    return $this->render('FacelecBundle:Catalogos/Generales/Estado:AgregarEstado.html.twig', $session->get('menu'));
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ModificarEstado", name="ModificarEstado")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ModificarEstadoAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("Estado")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if (!isset($datos['id_estado'])) {
                        $id = $_POST['id'];
                        $params = [
                            'estados'=>$this->ObtenerEstado($id)
                        ];
                        $array = array_merge($params, $session->get('menu'));
                        return $this->render('FacelecBundle:Catalogos/Generales/Estado:ModificarEstado.html.twig', $array);
                    }
                    else {
                        $Userid = $session->get('Userid');
                        if ($this->validarInfo($datos)) {
                            if (!$this->CompararEstadoPorNombre($datos)) {
                                if ($this->ActualizarEstado($datos, $Userid))
                                    die("1"); // Operación completa
                                else
                                    die("2"); // No se pudo realizar la operación
                            }
                            else {
                                die("3"); // Nombre existente
                            }
                        }
                        else {
                            die("2");
                        }
                    }
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    public function ObtenerEstados() {
        $conn = new Model();
        $sql = "SELECT id_estado, estado, activo FROM estado WHERE id_estado !=0;";
        $result=$conn->query($sql);
        while ($row=$conn->fetch_array($result)) {
            $estados[] = array(
                'id_estado'=>$row['id_estado'],
                'estado'=>utf8_encode($row['estado']),
                'activo'=>$row['activo']
            );
        }
        $conn->close();
        return $estados;
    }
    
    public function ObtenerEstado($id) {
        $conn = new Model();
        $sql = "SELECT id_estado, estado FROM estado WHERE id_estado=$id;";
        $result=$conn->query($sql);
        while ($row=$conn->fetch_array($result)) {
            $estado[] = array(
                'id_estado'=>$row['id_estado'],
                'estado'=>utf8_encode($row['estado'])
            );
        }
        $conn->close();
        return $estado;
    }
    
    public function ActivarDesactivarEstado($id_edo, $nom_edo, $valor, $Userid) {
        $conn = new Model();
        $sql = "UPDATE estado SET activo=$valor WHERE id_estado=$id_edo;";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            if ($valor == 1) {
                $accion = "Activar estado";
                $detalle = "Activó el estado: $nom_edo";
            }
            else {
                $accion = "Desactivar estado";
                $detalle = "Desactivó el estado: $nom_edo";
            }
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function ActualizarEstado($datos, $Userid) {
        $conn = new Model();
        $sql = "UPDATE estado SET estado='".$datos['nombre']."' WHERE id_estado=".$datos['id_estado'].";";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            $accion = "Actualizar estado";
            $detalle = "Actualizó el siguiente estado de: Nombre: ".$datos['nom']." A Nombre: ".$datos['nombre'];
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function AgregarEstado($datos, $Userid) {
        $conn = new Model();
        $sql = "INSERT INTO estado (estado, activo, usrC, fchC) VALUES ('".$datos['nombre']."', 1, $Userid, now());";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            $accion = "Agregar estado";
            $detalle = "Agregó el estado: ".$datos['nombre'];
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function validarNombreEstado($datos) {
        $conn = new Model();
        $sql = "SELECT * FROM estado WHERE estado='".$datos['nombre']."'";
        $result = $conn->query($sql);
        $estado = "";
        while ($row = $conn->fetch_array($result)) {
            $estado = $row;
        }
        $conn->close();
        return $estado;
    }
    
    public function CompararEstadoPorNombre($datos) {
        $conn = new Model();
        $sql = "SELECT * FROM estado WHERE estado LIKE '".$datos['nombre']."' AND id_estado NOT IN (".$datos['id_estado'].")";
        $result = $conn->query($sql);
        $estado = "";
        while ($row = $conn->fetch_array($result)) {
            $estado = $row;
        }
        $conn->close();
        return $estado;
    }
    
    public function validarInfo($datos) {
        // Validar campos vacios
        if (!isset($datos['nombre']))
            return 0;
        
        if ($datos['nombre'] === "")
            return 0;
        
        // Validar id_estado en caso de actualizar estado
        if (isset($datos['id_estado']) && $datos['id_estado'] != "") {
            if (!ctype_digit($datos['id_estado']))
                return 0;
        }
        
        // Validar cadena: nombre
        if (strlen($datos['nombre']) > 50)
            return 0;
        
        // Validar apostrofe
        if (strpos($datos['nombre'],"'") !== false)
            return 0;
        
        return 1;
    }
    
    public function registrarBitacora($accion, $detalle, $Userid, $conn) {
        $idmnu = $this->container->getParameter('Estado');
        $sql = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '$detalle')";
        $conn->query($sql);
    }
}