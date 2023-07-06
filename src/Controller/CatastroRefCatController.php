<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CatastroRefCatController extends AbstractController
{
    /**
     * @Route("/catastro/ref/cat", name="app_catastro_ref_cat")
     */


    public function consultaDnprcAction(): Response
    {
        // Parámetros de entrada
        $provincia = ''; // Opcional
        $municipio = ''; // Obligatorio si se pone provincia sino es opcional
        $refCat = '9801614YH1590B0008TT'; // Obligatorio, es un codigo de numeros y letras, pueden ser 7/14/18 o 20
        
        
        
        // Verificar la longitud de $refCat
        if (strlen($refCat) === 7 ||strlen($refCat) === 14 || strlen($refCat) === 18 || strlen($refCat) === 20) {
            // La longitud es válida, continuar con el procesamiento
            // ...
        } else {
            // La longitud no es válida, mostrar un mensaje de error
            echo "Longitud Inválida, por favor verifique";
        }

// ----------- PRUEBAS CON VIVIENDAS -------------//                                               // ------ PRUEBAS CON FINCAS 14 CARACTERES ------------ //
        // 9801614YH1590B0008TT  probado ok                                                              1861951VK4616B
        // 001005700VP10B0001MH  probado ok
        // 8729703YJ1782H0010KD  probado ok
        // 7099607UK8279N0001PF  probado ok




        // Construir los datos de la solicitud en formato de cadena de consulta
        $requestData = http_build_query([
            'Provincia' => $provincia,
            'Municipio' => $municipio,
            'RC' => $refCat,
        ]);

        // Inicializar la sesión cURL
        $ch = curl_init();

        // Establecer la configuración de la solicitud cURL
        curl_setopt($ch, CURLOPT_URL, 'http://ovc.catastro.meh.es/ovcservweb/OVCSWLocalizacionRC/OVCCallejero.asmx/Consulta_DNPRC');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Realizar la solicitud cURL y obtener la respuesta
        $response = curl_exec($ch);

        // Cerrar la sesión cURL
        curl_close($ch);


        // Procesar la respuesta
        $xml = simplexml_load_string($response);
        $xml->registerXPathNamespace('ns', 'http://www.catastro.meh.es/');

        // -------------------------------------------------- Obtener los valores del XML -------------------------------------------------------------------------------     
        $numInmuebles = (string) $xml->xpath('//ns:cudnp')[0];
        $numUnidadesConstructivas = (string) $xml->xpath('//ns:cn')[0];
        $numSubparcelas = (string) $xml->xpath('//ns:cucul')[0];
        $tipoBienInmueble = (string) $xml->xpath('//ns:cucul')[0];
        $pc1 = (string) $xml->xpath('//ns:pc1')[0];
        $pc2 = (string) $xml->xpath('//ns:pc2')[0];
        $car = (string) $xml->xpath('//ns:car')[0];
        $cc1 = (string) $xml->xpath('//ns:cc1')[0];
        $cc2 = (string) $xml->xpath('//ns:cc2')[0];
        $referenciaCatastral = $pc1 . $pc2 . $car . $cc1 . $cc2; // Construir la referencia catastral completa
        $codigoProvinciaINE = (string) $xml->xpath('//ns:loine/ns:cp')[0];
        $codigoMunicipioINE = (string) $xml->xpath('//ns:loine/ns:cm')[0];
        $codigoMunicipioDGC = (string) $xml->xpath('//ns:cmc')[0];
        $nombreProvincia = (string) $xml->xpath('//ns:np')[0];
        $nombreMunicipio = (string) $xml->xpath('//ns:nm')[0];
        // Capturar los valores de localizacion Urbana
        $codigoVia = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:cv')[0] ?? null) ?: null;
        $tipoVia = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:tv')[0] ?? null) ?: null;
        $nombreVia = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:nv')[0] ?? null) ?: null;
        $primerNumeroNodes = $xml->xpath('//ns:locs/ns:lous/ns:lourb/ns:dir/ns:pnp');
        $primerNumero = !empty($primerNumeroNodes) ? (string) $primerNumeroNodes[0] : null;
        $primerLetra = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:plp')[0] ?? null) ?: null;
        $segundoNumero = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:snp')[0] ?? null) ?: null;
        $segundaLetra = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:slp')[0] ?? null) ?: null;
        $km = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:km')[0] ?? null) ?: null;
        $bloque = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:loint/ns:bq')[0] ?? null) ?: null;
        $escalera = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:loint/ns:es')[0] ?? null) ?: null;
        $planta = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:loint/ns:pt')[0] ?? null) ?: null;
        $puerta = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:loint/ns:pu')[0] ?? null) ?: null;
        $codPostal = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dp')[0] ?? null) ?: null;
        $distMunicipal = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dm')[0] ?? null) ?: null;
        // Capturar los valores de localizacion rustica
        $codigoMunicipioAgregado = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:cma')[0] ?? null) ?: null;
        $codigoZonaConcentracion = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:czc')[0] ?? null) ?: null;
        $codigoPoligono = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:cpp/ns:cpo')[0] ?? null) ?: null;
        $codigoParcela = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:cpp/ns:cpa')[0] ?? null) ?: null;
        $nombreParaje = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:npa')[0] ?? null) ?: null;
        $codigoParaje = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:cpaj')[0] ?? null) ?: null;
        //--------------------------------------------------  LOCALIZACIONES ADICIONALES  ---------------------------------------------------------------------
        // Capturar los valores de localizacion Urbana ADICIONAL
        $codigoViaAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:cv')[0] ?? null) ?: null;
        $tipoViaAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:tv')[0] ?? null) ?: null;
        $nombreViaAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:nv')[0] ?? null) ?: null;
        $primerNumeroAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:pnp')[0] ?? null) ?: null;
        $primerLetraAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:plp')[0] ?? null) ?: null;
        $segundoNumeroAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:snp')[0] ?? null) ?: null;
        $segundaLetraAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:slp')[0] ?? null) ?: null;
        $kmAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:km')[0] ?? null) ?: null;
        $bloqueAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:loint/ns:bq')[0] ?? null) ?: null;
        $escaleraAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:loint/ns:es')[0] ?? null) ?: null;
        $plantaAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:loint/ns:pt')[0] ?? null) ?: null;
        $puertaAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:loint/ns:pu')[0] ?? null) ?: null;
        $codPostalAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dp')[0] ?? null) ?: null;
        $distMunicipalAd = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dm')[0] ?? null) ?: null;
        // Capturar los valores de localizacion rustica ADICIONAL
        $codigoMunicipioAgregadoAd = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:cma')[0] ?? null) ?: null;
        $codigoZonaConcentracionAd = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:czc')[0] ?? null) ?: null;
        $codigoPoligonoAd = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:cpp/ns:cpo')[0] ?? null) ?: null;
        $codigoParcelaAd = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:cpp/ns:cpa')[0] ?? null) ?: null;
        $nombreParajeAd = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:npa')[0] ?? null) ?: null;
        $codigoParajeAd = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:cpaj')[0] ?? null) ?: null;
        //-------------------------------------------------- FIN LOCALIZACIONES ADICIONALES  ---------------------------------------------------------------------
        $locParcela = (string) ($xml->xpath('//ns:ldt')[0] ?? null) ?: null;
        $usoPrincipal = (string) ($xml->xpath('//ns:debi/ns:luso')[0] ?? null) ?: null;
        $superficieContruida = (string) ($xml->xpath('//ns:debi/ns:sfc')[0] ?? null) ?: null;
        $participacion = (string) ($xml->xpath('//ns:debi/ns:cpt')[0] ?? null) ?: null;
        $AnioConstruccion = (string) ($xml->xpath('//ns:debi/ns:ant')[0] ?? null) ?: null;
        //--------------------------------------------------- CONSTRUCCION ----------------------------------------------------------------------------------------
        $lcons = $xml->xpath('//ns:lcons/ns:cons');
        $consData = [];

        foreach ($lcons as $cons) {
            $usoUnidadConstructiva = (string) $cons->lcd;
            $escalera = isset($cons->dt->lourb->loint->es) ? (string) $cons->dt->lourb->loint->es : '';
            $planta = isset($cons->dt->lourb->loint->pt) ? (string) $cons->dt->lourb->loint->pt : '';
            $puerta = isset($cons->dt->lourb->loint->pu) ? (string) $cons->dt->lourb->loint->pu : '';
            $superficie = isset($cons->dfcons->stl) ? (string) $cons->dfcons->stl : '';

            $consData[] = [
                'Uso Principal de la Unidad' => $usoUnidadConstructiva,
                'Escalera' => $escalera,
                'Planta' => $planta,
                'Puerta' => $puerta,
                'Superficie m2' => $superficie,
            ];
        }

        //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

        // Construir el arreglo de respuesta JSON
        $responseData = [
            'Num Inmuebles' => $numInmuebles,
            'Num Unidades Constructivas' => $numUnidadesConstructivas,
            'Num Subparcelas' => $numSubparcelas,
            'Tipo de Bien Inmueble' => $tipoBienInmueble,
            'Referencia Catastral' => $referenciaCatastral,
            'Codigo Provincia INE' => $codigoProvinciaINE,
            'Codigo Municipio INE' => $codigoMunicipioINE,
            'CodigoMunicipioDGC' => $codigoMunicipioDGC,
            'NombreProvincia' => $nombreProvincia,
            'NombreMunicipio' => $nombreMunicipio,
            'Localizacion Urbana' => [
                'Codigo de Via' => $codigoVia,
                'Tipo de Via' => $tipoVia,
                'Nombre de Via' => $nombreVia,
                'Primer Numero' => $primerNumero,
                'Primer Letra' => $primerLetra,
                'segundo Numero' => $segundoNumero,
                'segunda Letra' => $segundaLetra,
                'Km' => $km,
                'Bloque' => $bloque,
                'Escalera' => $escalera,
                'Planta' => $planta,
                'Puerta' => $puerta,
                'Codigo Postal' => $codPostal,
                'Distrito Municipal' => $distMunicipal,
            ],
            'Localizacion Rustico' => [
                'Codigo Municipio' => $codigoMunicipioAgregado,
                'Codigo Zona' => $codigoZonaConcentracion,
                'Codigo Poligono' => $codigoPoligono,
                'Codigo Parcela' => $codigoParcela,
                'Nombre de Paraje' => $nombreParaje,
                'Codigo de Paraje' => $codigoParaje,
            ],
            'Localizacion Urbana ADICIONAL' => [
                'Codigo de Via' => $codigoViaAd,
                'Tipo de Via' => $tipoViaAd,
                'Nombre de Via' => $nombreViaAd,
                'Primer Numero' => $primerNumeroAd,
                'Primer Letra' => $primerLetraAd,
                'segundo Numero' => $segundoNumeroAd,
                'segunda Letra' => $segundaLetraAd,
                'Km' => $kmAd,
                'Bloque' => $bloqueAd,
                'Escalera' => $escaleraAd,
                'Planta' => $plantaAd,
                'Puerta' => $puertaAd,
                'Codigo Postal' => $codPostalAd,
                'Distrito Municipal' => $distMunicipalAd,
            ],
            'Localizacion Rustico ADICIONAL' => [
                'Codigo Municipio' => $codigoMunicipioAgregadoAd,
                'Codigo Zona' => $codigoZonaConcentracionAd,
                'Codigo Poligono' => $codigoPoligonoAd,
                'Codigo Parcela' => $codigoParcelaAd,
                'Nombre de Paraje' => $nombreParajeAd,
                'Codigo de Paraje' => $codigoParajeAd,
            ],
            'Direccion Completa' => $locParcela,
            'Uso Principal' => $usoPrincipal,
            'Superficie Construida' => $superficieContruida,
            'Coeficiente de Participacion' => $participacion,
            'Fecha de Construccion' => $AnioConstruccion,

            $responseData['lcons'] = $consData,

        ];
        // -----------------------------------------------------------------------------------------------------------------------------------------------------------------------

        // Devolver la respuesta en formato JSON
        return $this->json($responseData, 200, [], [
            'json_encode_options' => JSON_PRETTY_PRINT
        ]);
    }
}
