<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Movimiento
 *
 * @author walter
 */
class movimientosController extends Controller{

    private $_ajax;

    public function __construct() {
        parent::__construct();
         $this->_ajax  = $this->loadModel('movimientos');
    }

    public function index() {
        $this->_view->titulo = 'Titulo';
        $this->_view->renderizar('index');
    }

    public function getSaldo() {
        $socioModel = $this->loadModel('socios');
        $socio = $socioModel->getById($_POST['id']);
        $retorno = $this->_ajax->getMovimientosSocio($socio);
        foreach($retorno as $valor) {
            $valor->fecha_computo = date('d/m/Y', strtotime($valor->fecha_computo));
        }

        if(!$retorno) {
           $retorno = array('fecha_computo' => '', 'importe' => 0);
        }
        echo json_encode($retorno);
    }

    public function guardar() {

    }

    public function pagar() {
        $this->_view->titulo = 'Titulo';
        $this->_view->renderizar('pagar');
    }

    public function ingresarPago() {

        $mov = $this->_ajax->buildMovimiento();
        $modeloSocio = $this->loadModel('socios');
        $mov->setSocio($modeloSocio->getById(filter_input(INPUT_POST ,'id', FILTER_SANITIZE_STRING)));
        $mov->setImporte(-filter_input(INPUT_POST ,'importe', FILTER_SANITIZE_NUMBER_FLOAT));
        $ingreso = filter_input(INPUT_POST ,'fecha', FILTER_SANITIZE_STRING);
        $ingreso = $this->cambiarfecha_mysql($ingreso);
        $mes = date('m',  strtotime($ingreso));
        $anio = date('Y', strtotime($ingreso));
        $mov->setFecha($ingreso);
        $mov->setMes($mes);
        $mov->setAnio($anio);
        $this->_ajax->save($mov);
        $retorno = array();
        $consulta = $this->_ajax->getLasts($mov->getFecha(),6);

        if(!$consulta) {
            $retorno [] = array('nombre' => 'Sin', 'apellido' => 'Resultados');
        } else {
            foreach($consulta as $valor) {
                $socio = $modeloSocio->getById($valor->id_socio_fk);
                $retorno[] = array('id' => $socio->getId()  , 'nombre' => $socio->__toString(),
                    'importe' => abs($valor->importe));
            }


        }
      //  print_r($mov);
        echo json_encode($retorno);

    }


    public function preprint() {
        $this->_view->titulo = 'Titulo';
        $this->_view->renderizar('formprint');
    }

    public function imprimir() {
        $mes = filter_input(INPUT_POST, 'mes', FILTER_SANITIZE_NUMBER_INT);
        $anio = filter_input(INPUT_POST, 'anio', FILTER_SANITIZE_NUMBER_INT);

        $modelo = $this->loadModel('movimientos');
        $modelSocios = $this->loadModel('socios');
        $row = $modelo->getMes($anio, $mes);
        $registros = count($row);
        $paginas = $registros / 4;
        //echo $paginas . ' ' . $registros;
        $this->getLibrary('fpdf');
        $pdf=new FPDF();
        $pdf->AliasNbPages();
        $pdf->SetTopMargin(5);
        $pdf->SetFont('Arial','',8);

        $pos_y  =  12;
        $it = 0;
        for($i = 0; $i < $paginas; $i++) {
            $pdf->AddPage();
            for($j = 0; $j < 4; $j++) {
                    if(!($it < $registros))
                        break;
                    $socio = $modelSocios->getById($row[$it]->id_socio_fk);
                    $pdf->SetXY(26,$pos_y);
                    $pdf->Cell(50,4,$socio->getId(),0,0);
                    $pdf->SetXY(58,$pos_y);
                    $pdf->Cell(50,4,$row[$it]->mes.'/'.$row[$it]->anio,0,0);
                    $pdf->SetXY(86,$pos_y);
                    $pdf->Cell(50,4,$socio->getId(),0,0);
                    $pdf->SetXY(121,$pos_y);
                    $pdf->Cell(50,4,$row[$it]->mes.'/'.$row[$it]->anio,0,0);
                    $pdf->SetXY(8,$pos_y + 9);
                    $pdf->Cell(80,4,  utf8_decode(utf8_decode($socio->__toString())),0,0,'C');
                    $pdf->SetXY(83,$pos_y + 9);
                    $pdf->Cell(90,4,  utf8_decode($socio->__toString()),0,0,'C');
                    $pdf->SetXY(8,$pos_y + 18);
                    $pdf->Cell(80,4, utf8_decode(utf8_decode($socio->getDomicilio())),0,0,'C');
                    $pdf->SetXY(83,$pos_y + 18);
                    $pdf->Cell(90,4,  utf8_decode(utf8_decode($socio->getDomicilio())),0,0,'C');
                    $pdf->SetXY(178,$pos_y + 18);
                    $pdf->Cell(50,4,  substr($socio->getCategoria()->getNombre(),0,1),0,0);
                    $pdf->SetXY(26,$pos_y + 27);
                    $pdf->Cell(50,4,  substr($socio->getCategoria()->getNombre(),0,1),0,0);
                    $pdf->SetXY(59,$pos_y + 27);
                    $pdf->Cell(50,4, $row[$it]->importe, 0,0);
                    $pdf->SetXY(108,$pos_y + 27);
                    $pdf->Cell(50,4, $row[$it]->importe, 0,0);
                    // $pdf->SetXY(86, $pos_y + 36);
                    // $pdf->Cell(90,4, 'La Cantina, Servicio Delivery 4456 9026', 0,0);
                    $pos_y+=74;
                    $pdf->SetY($pos_y);

                    $it++;

            }

            $pos_y = 12;

        //$pdf->SetFillColor(236,235,236);



        }

        $pdf->Output();

    }

    public function imprimir140() {
        $mes = filter_input(INPUT_POST, 'mes', FILTER_SANITIZE_NUMBER_INT);
        $anio = filter_input(INPUT_POST, 'anio', FILTER_SANITIZE_NUMBER_INT);

        $modelo = $this->loadModel('movimientos');
        $modelSocios = $this->loadModel('socios');
        $row = $modelo->getMes($anio, $mes);
        $registros = count($row);
        $paginas = $registros / 4;
        //echo $paginas . ' ' . $registros;
        $this->getLibrary('fpdf');
        $pdf=new FPDF();
        $pdf->AliasNbPages();
        $pdf->SetTopMargin(5);
        $pdf->SetFont('Arial','',8);

        $pos_y  =  11;
        $it = 0;
        for($i = 0; $i < $paginas; $i++) {
            $pdf->AddPage();
            for($j = 0; $j < 4; $j++) {
                    if(!($it < $registros))
                        break;
                    $socio = $modelSocios->getById($row[$it]->id_socio_fk);
                    $pdf->SetXY(23,$pos_y);
                    $pdf->Cell(50,4,$socio->getId(),0,0);
                    $pdf->SetXY(55,$pos_y);
                    $pdf->Cell(50,4,$row[$it]->mes.'/'.$row[$it]->anio,0,0);
                    $pdf->SetXY(83,$pos_y);
                    $pdf->Cell(50,4,$socio->getId(),0,0);
                    $pdf->SetXY(118,$pos_y);
                    $pdf->Cell(50,4,$row[$it]->mes.'/'.$row[$it]->anio,0,0);
                    $pdf->SetXY(5,$pos_y + 9);
                    $pdf->Cell(80,4,  utf8_decode(utf8_decode($socio->__toString())),0,0,'C');
                    $pdf->SetXY(80,$pos_y + 9);
                    $pdf->Cell(90,4,  utf8_decode(utf8_decode($socio->__toString())),0,0,'C');
                    $pdf->SetXY(5,$pos_y + 18);
                    $pdf->Cell(80,4, utf8_decode(utf8_decode($socio->getDomicilio())),0,0,'C');
                    $pdf->SetXY(80,$pos_y + 18);
                    $pdf->Cell(90,4,  utf8_decode(utf8_decode($socio->getDomicilio())),0,0,'C');
                    $pdf->SetXY(175,$pos_y + 18);
                    $pdf->Cell(50,4,  substr($socio->getCategoria()->getNombre(),0,1),0,0);
                    $pdf->SetXY(23,$pos_y + 27);
                    $pdf->Cell(50,4,  substr($socio->getCategoria()->getNombre(),0,1),0,0);
                    $pdf->SetXY(56,$pos_y + 27);
                    $pdf->Cell(50,4, $row[$it]->importe, 0,0);
                    $pdf->SetXY(105,$pos_y + 27);
                    $pdf->Cell(50,4, $row[$it]->importe, 0,0);
                    $pos_y+=74;
                    $pdf->SetY($pos_y);

                    $it++;

            }

            $pos_y = 11;

        //$pdf->SetFillColor(236,235,236);



        }

        $pdf->Output();

    }


    public function generar() {
        $this->_view->titulo = 'Titulo';
        $this->_view->renderizar('generar');
    }

    public function generacuota() {
        $mes = filter_input(INPUT_POST, 'mes', FILTER_SANITIZE_NUMBER_INT);
        $anio = filter_input(INPUT_POST, 'anio', FILTER_SANITIZE_NUMBER_INT);
        $this->_view->mes = $mes;
        $this->_view->anio = $anio;

        $this->_view->renderizar('generacuota');
    }

    public function confirmar($mes, $anio) {
        $model = $this->loadModel('movimientos');
        $result = $model->verificarMes($mes, $anio);
        if($result) {
            $modelSocios = $this->loadModel('socios');
            $listado = $modelSocios->getHabilitados();
            $model->generarMes($listado, $mes, $anio);
            $this->_view->mensaje = 'Mes generado con &eacute;xito';
            $this->_view->renderizar('generado');
        } else {
            $this->_view->mensaje = 'El mes ya est&aacute; generado';
            $this->_view->renderizar('generado');
        }


    }

    public function eliminar() {
        $mov = $this->_ajax->getById($_POST['id']);
        $this->_ajax->delete($mov);

        $retorno = $this->_ajax->getMovimientosSocio($mov->getSocio());
        foreach($retorno as $valor) {
            $valor->fecha_computo = date('d/m/Y', strtotime($valor->fecha_computo));
        }

        if(!$retorno) {
           $retorno = array('fecha_computo' => '', 'importe' => 0);
        }
        echo json_encode($retorno);
    }

    public function totales() {
        $this->_view->renderizar('totales');
    }

    public function getTotales() {
        $fecha = $this->cambiarfecha_mysql($_POST['fecha']);
        $retorno = $this->_ajax->getTotales($fecha);
        echo json_encode($retorno);
    }

    public function listarec($mes, $anio) {

        $modelo = $this->loadModel('movimientos');
        $modelSocios = $this->loadModel('socios');
        $row = $modelo->getMes($anio, $mes);
        $registros = count($row);
        $paginas = ceil($registros / 45);
        $this->getLibrary('fpdf');
        $pdf=new FPDF();
        $pdf->AliasNbPages();
        $pdf->SetTopMargin(5);
        $pdf->SetFont('Arial','B',14);
        $pos_y  =   13;
        $pdf->AddPage();
        $pdf->SetXY(20,$pos_y);
        $pdf->Cell(0,8,utf8_decode('Listado de emisión de recibos'),0,0,'C');

        $pos_y = 25;
        $it = 0;
        for($i = 0; $i < $paginas; $i++) {

            $pdf->SetFont('Arial','B',8);
            $pdf->SetXY(20,$pos_y);
            $pdf->Cell(10,4,'Nro.',0,0);
            $pdf->SetXY(30,$pos_y);
            $pdf->Cell(50,4,'Nombre',0,0);
            $pdf->SetXY(90,$pos_y);
            $pdf->Cell(65,4,'Domicilio',0,0);
            $pdf->SetXY(160,$pos_y);
            $pdf->Cell(10,4,'Cat',0,0);
            $pdf->SetXY(170,$pos_y);
            $pdf->Cell(50,4,'Importe',0,0);
            //$pos_y+=5;
            $pdf->SetFont('Arial','',8);
            $pos_y = 28;
            $pdf->SetY($pos_y);
            for($j = 0; $j < 45; $j++) {
                    if(!($it < $registros))
                        break;
                        $socio = $modelSocios->getById($row[$it]->id_socio_fk);
                        $pdf->SetXY(20,$pos_y);
                        $pdf->Cell(10,4,$socio->getId(),0,0);
                        $pdf->SetXY(30,$pos_y);
                        $pdf->Cell(50,4,utf8_decode(utf8_decode($socio->__toString())),0,0);
                        $pdf->SetXY(90,$pos_y);
                        $pdf->Cell(65,4,utf8_decode(utf8_decode($socio->getDomicilio())),0,0);
                        $pdf->SetXY(160,$pos_y);
                        $pdf->Cell(10,4,substr($socio->getCategoria()->__toString(),0,1),0,0);
                        $pdf->SetXY(170,$pos_y);
                        $pdf->Cell(50,4,$row[$it]->importe,0,0);
                        $pos_y+=5;
                        $pdf->SetY($pos_y);
                        $it++;

                }

                $pdf->SetY($pos_y + 10);
                $pdf->SetFont('Arial','I',8);
                $pdf->Cell(0,10,utf8_decode('Página ').$pdf->PageNo().' de {nb}',0,0,'C');

                if($pdf->PageNo() < $paginas) {
                      $pos_y = 25;
                      $pdf->AddPage();
                } else {
                    $pdf->SetY($pos_y + 20);
                    $tot = json_decode($this->getTotalesE($mes, $anio));
                    $totales = $tot[0]->importe+$tot[1]->importe;

                    $pdf->Cell(0,10, utf8_decode('Total Activos: ').$tot[0]->importe, 0, 0, 'C');
                    $pdf->SetY($pos_y + 25);
                    $pdf->Cell(0,10, utf8_decode('Total Cadetes: ').$tot[1]->importe, 0, 0, 'C');
                    $pdf->SetY($pos_y + 35);
                    $pdf->Cell(0,10, utf8_decode('Total General: ').$totales, 0, 0, 'C');

                }


            }

            $pdf->Output();



    }

    private function getTotalesE($mes, $anio) {

        $retorno = $this->_ajax->getTotales($anio.'-'.$mes.'-01');
        return json_encode($retorno);
    }
}
