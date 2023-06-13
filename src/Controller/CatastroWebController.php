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

        // Crear el cliente HTTP
        $client = HttpClient::create();

        // Realizar una solicitud GET para obtener el HTML de la página web
        $response = $client->request('GET', $url);
        $html = $response->getContent();


        // Crear un objeto Crawler a partir del HTML 
        $crawler = new Crawler($html);


        // Encontrar el elemento <input> de referencia catastral
        $inputElement = $crawler->filter('input[name="ctl00$Contenido$txtRC2"][type="text"]')->first();
        
        // Establecer el valor de $referenciaCatastral en el atributo 'value' del elemento <input>
        $inputElement->attr('value', $referenciaCatastral);

        // Obtener el formulario asociado al elemento <input>
        $form = $inputElement->parents()->filter('form')->first()->form();



        // Encontrar el formulario por su ID
        //$form = $crawler->filter('#refcat2')->form();


        // Establecer el valor del campo "Referencia Catastral"
        //$form['ctl00_Contenido_txtRC2'] = $referenciaCatastral;

        // Obtener la URL de acción del formulario
        $actionUrl = $form->getUri();

        // Enviar el formulario
        $response = $client->request('POST', $actionUrl, [
            'body' => $form->getValues(),
        ]);
        /*
        //Obtiene la URL donde se abre luego de establecer el valor de $referenciaCatastral
        $client = HttpClient::create();
        $iframeUrl = $url = 'https://www1.sedecatastro.gob.es/cycbieninmueble/OVCConCiud.aspx?UrbRus=U&RefC= . $referenciaCatastral . &esBice=&RCBice1=&RCBice2=&DenoBice=&from=OVCBusqueda&pest=rc&RCCompleta= .$referenciaCatastral . &final=&del=18&mun=900';

        // Realizar una solicitud GET al URL del iframe        
        $response = $client->request('GET', $iframeUrl);
        

        // Obtener el contenido del archivo
        $fileContent = $response->getContent();


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

*/
        return $this->json("Archivo descargado exitosamente");
    }
}
