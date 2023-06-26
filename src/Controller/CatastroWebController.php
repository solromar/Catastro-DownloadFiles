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
        $redirectUrl = 'https://www1.sedecatastro.gob.es/cycbieninmueble/OVCConCiud.aspx?UrbRus=U&RefC=' . $referenciaCatastral . '&esBice=&RCBice1=&RCBice2=&DenoBice=&from=OVCBusqueda&pest=rc&RCCompleta=' . $referenciaCatastral . '&final=&del=18&mun=900';;

        // Hacer solicitud GET al URL de redireccionamiento
        $response = $client->request('GET', $redirectUrl);
        $html = $response->getContent();

        // Crear un objeto Crawler para el resultado HTML
        $crawler = new Crawler($html, $redirectUrl);

        // Encontrar el enlace de "Imprimir Datos"
        $imprimirDatosLink = $crawler->filter('a#BImprimirDatos');
        $imprimirDatosUrl = $imprimirDatosLink->attr('href');

        // Construir la URL completa del enlace "Imprimir Datos"
        $imprimirDatosUrl = 'https://www1.sedecatastro.gob.es/cycbieninmueble/' . $imprimirDatosUrl;

        // Hacer solicitud GET a la URL de "Imprimir Datos"
        $response = $client->request('GET', $imprimirDatosUrl);
        $fileContent = $response->getContent();

        //------------------------------------------  GUARDAR EL ARCHIVO PDF ---------------------------------------------------------------

        $directoryPath = '/app/public/ficheros/';
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        $filePath = $directoryPath . '_' . $referenciaCatastral . '.pdf';

        // Verificar si el archivo ya existe
        if (!file_exists($filePath)) {
            // Guardar el archivo solo si no existe previamente
            file_put_contents($filePath, $fileContent);



            // Obtener el contenido de la respuesta
            $resultHtml = $response->getContent();
        }
        return $this->json('Archivo descargado exitosamente');
    }
}
