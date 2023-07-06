<?php

namespace App\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CatastroWebController extends AbstractController
{
    /**
     * @Route("/catastro/web", name="app_catastro_web")
     */

     
    public function interactWithWebsite()
    {
        // Datos
        $url = 'https://www1.sedecatastro.gob.es/cycbieninmueble/ovcbusqueda.aspx';
        $referenciaCatastral = '7932704VG4173B0001GE';
        //9801614YH1590B0008TT
        //001005700VP10B0001MH
        //3788601NF3638N0006BS
        //17341040UF7613S0038RX
        //8729703YJ1782H0010KD
        //7099607UK8279N0001PF
        //7932704VG4173B0001GE

        // Crear cliente HTTP
        $client = HttpClient::create();

        // Hacer solicitud GET inicial
        $response = $client->request('GET', $url);
        $html = $response->getContent();

        // Crear un objeto Crawler a partir del HTML y la URL
        $crawler = new Crawler($html, $url);

        // Encontrar el formulario por su ID
        $form = $crawler->filter('form#aspnetForm')->form();

        // Establecer el valor del campo "Referencia catastral"
        $form['ctl00$Contenido$txtRC2']->setValue($referenciaCatastral);

        // Obtener la URL de acción del formulario
        $actionUrl = $form->getUri();
       

        // Enviar el formulario
        $response = $client->request('POST', $actionUrl, [
            'body' => $form->getPhpValues(),           
        ]);

        
        
        // Obtener la URL de redireccionamiento
        //$redirectUrl = $response->getInfo('url');
        $redirectUrl = 'https://www1.sedecatastro.gob.es/cycbieninmueble/OVCConCiud.aspx?UrbRus=U&RefC=' . $referenciaCatastral . '&esBice=&RCBice1=&RCBice2=&DenoBice=&from=OVCBusqueda&pest=rc&RCCompleta=' . $referenciaCatastral . '&final=&del=18&mun=900';

        // Hacer solicitud GET al URL de redireccionamiento
        $newResponse = $client->request('GET', $redirectUrl);
        $newHtml = $newResponse->getContent();

        // Crear un objeto Crawler para el resultado HTML
        $crawler = new Crawler($newHtml, $redirectUrl);


        // Capturar los datos requeridos
        $datos1 = trim($crawler->filter('div.panel-heading.amarillo')->eq(0)->text());
        $referenciaCatastral = $referenciaCatastral;
        $localizacion = trim($crawler->filter('#ctl00_Contenido_tblInmueble .form-group:nth-child(2) .col-md-8 label')->text());
        $clase = trim($crawler->filter('#ctl00_Contenido_tblInmueble .form-group:nth-child(3) .col-md-8 label')->text());
        $usoPrincipal = trim($crawler->filter('#ctl00_Contenido_tblInmueble .form-group:nth-child(4) .col-md-8 label')->text());
        $superficieConstruida = trim($crawler->filter('#ctl00_Contenido_tblInmueble .form-group:nth-child(5) .col-md-8 label')->text());
        $anioContruccion = trim($crawler->filter('#ctl00_Contenido_tblInmueble .form-group:nth-child(6) .col-md-8 label')->text());
        // Capturar los datos requeridos del segundo div.panel-heading.amarillo
        $datos2 = trim($crawler->filter('div.panel-heading.amarillo')->eq(1)->text());
        $tipoParcela = trim($crawler->filter('#ctl00_Contenido_tblFinca .form-group:first-child span.control-label.black')->text());
        $superficieGrafica = trim($crawler->filter('#ctl00_Contenido_tblFinca .form-group:nth-child(3) .col-md-9 label')->text());
        // Capturar los datos requeridos del tercer div.panel-heading.amarillo
        $datos3 = trim($crawler->filter('div.panel-heading.amarillo')->eq(2)->text());

        // Capturar los datos requeridos del tercer div.panel-heading.amarillo
        $datos3 = trim($crawler->filter('div.panel-heading.amarillo')->eq(2)->text());

        // Capturar los datos requeridos del tercer div.panel-heading.amarillo
        $datos3 = trim($crawler->filter('div.panel-heading.amarillo')->eq(2)->text());

        $usosPrincipales = [];
        $crawler->filter('#ctl00_Contenido_tblLocales tr')->slice(1)->each(function ($row) use (&$usosPrincipales) {
            $usoPrincipal = trim($row->filter('td:nth-child(1) span')->text());
            $escalera = trim($row->filter('td:nth-child(2) span')->text());
            $planta = trim($row->filter('td:nth-child(3) span')->text());
            $puerta = trim($row->filter('td:nth-child(4) span')->text());
            $superficieM2 = trim($row->filter('td:nth-child(5) span')->text());
            $tipoReforma = trim($row->filter('td:nth-child(6) span')->text());
            $fechaReforma = trim($row->filter('td:nth-child(7) span')->text());

            $usosPrincipales[] = [
                'Uso Principal' => $usoPrincipal,
                'Escalera' => $escalera,
                'Planta' => $planta,
                'Puerta' => $puerta,
                'Superficie m2' => $superficieM2,
                'Tipo de Reforma' => $tipoReforma,
                'Fecha de Reforma' => $fechaReforma,
            ];
        });



        // Crear un array con los valores capturados
        $data = [
            'Contenido' => $datos1,
            'referenciaCatastral' => $referenciaCatastral,
            'Localizacion' => $localizacion,
            'Clase' => $clase,
            'Uso Principal' => $usoPrincipal,
            'Superficie Construida' => $superficieConstruida,
            'Fecha de Construccion' => $anioContruccion,
            'Otros Datos' => $datos2,
            'Tipo' => $tipoParcela,
            'Superficie Grafica' => $superficieGrafica,
            'Datos Construccion' => $datos3,
            'Detalles' => $usosPrincipales,

        ];

        // Codificar el array como JSON con los caracteres acentuados corregidos
        $jsonResponse = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        // Crear la respuesta HTTP con el contenido JSON formateado
        $response = new Response($jsonResponse);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
