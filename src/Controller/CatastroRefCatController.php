<?php

namespace App\Controller;



use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CatastroRefCatController extends AbstractController
{
    /**
     * @Route("/catastro/ref/cat", name="app_catastro_ref_cat")
     */
    public function consultaDnprcAction()
    {
        $payload = [
            'referenciaCatastral' => null,
            'codigoProvinciaIne' => null,
            'codigoMunicipioIne' => null,
            'codigoMunicipioDgc' => null,
            'nombreProvincia' => null,
            'nombreMunicipio' => null,
            'numInmuebles' => null,
            'numUnidadesConstructivas' => null,
            'numSubparcelas' => null,
            'tipoBienInmueble' => null,
            'localizacionUrbana' =>  [
                'codigoVia' => null,
                'tipoVia' => null,
                'nombreVia' => null,
                'primerNumero' => null,
                'primerLetra' => null,
                'segundoNumero' => null,
                'segundaLetra' => null,
                'km' => null,
                'bloque' => null,
                'escalera' => null,
                'planta' => null,
                'puerta' => null,
                'codigoPostal' => null,
                'distritoMunicipal' => null,
            ],
            'localizacionRustico' =>  [
                'codigoMunicipio' => null,
                'codigoZona' => null,
                'codigoPoligono' => null,
                'codigoParcela' => null,
                'nombreParaje' => null,
                'codigoParaje' => null,
            ],
            'localizacionUrbanaAdicional' =>  [
                'codigoVia' => null,
                'tipoVia' => null,
                'nombreVia' => null,
                'primerNumero' => null,
                'primerLetra' => null,
                'segundoNumero' => null,
                'segundaLetra' => null,
                'km' => null,
                'bloque' => null,
                'escalera' => null,
                'planta' => null,
                'puerta' => null,
                'codigoPostal' => null,
                'distritoMunicipal' => null,
            ],
            'localizacionRusticoAdicional' =>  [
                'codigoMunicipio' => null,
                'codigoZona' => null,
                'codigoPoligono' => null,
                'codigoParcela' => null,
                'nombreParaje' => null,
                'codigoParaje' => null,
            ],
            'direccionCompleta' => null,
            'usoPrincipal' => null,
            'superficieConstruida' => null,
            'coeficienteParticipacion' => null,
            'fechaConstruccion' => null,
            'listadoConstrucciones' => null,
            'listadoInmuebles' => null
        ];

        $result = [];
        $provincia = ''; // Opcional
        $municipio = ''; // Obligatorio si se pone provincia sino es opcional
        $refCat = '1861951VK4616B'; // Obligatorio, es un codigo de numeros y letras, pueden ser 7/14/18 o 20
//1861951VK4616B      //9801614YH1590B0008TT
        $requestData = [
            'Provincia' => $provincia,
            'Municipio' => $municipio,
            'RC' => $refCat,
        ];

        // Crear un nuevo cliente Guzzle
        $client = new \GuzzleHttp\Client();

        try {
            // Realizar la solicitud POST
            $response = $client->request('POST', 'http://ovc.catastro.meh.es/ovcservweb/OVCSWLocalizacionRC/OVCCallejero.asmx/Consulta_DNPRC', [
                'form_params' => $requestData
            ]);

            // Procesar la respuesta
            $body = $response->getBody();
            $xml = simplexml_load_string($body);
            $xml->registerXPathNamespace('ns', 'http://www.catastro.meh.es/');
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // Tratar la excepción como se prefiera
            dd($e->getMessage());
        }

        if (strlen($refCat) === 20) {
            $pc1 = (string) $xml->xpath('//ns:pc1')[0];
            $pc2 = (string) $xml->xpath('//ns:pc2')[0];
            $car = (string) $xml->xpath('//ns:car')[0];
            $cc1 = (string) $xml->xpath('//ns:cc1')[0];
            $cc2 = (string) $xml->xpath('//ns:cc2')[0];
            $primerNumeroNodes = $xml->xpath('//ns:locs/ns:lous/ns:lourb/ns:dir/ns:pnp');
            $lcons = $xml->xpath('//ns:lcons/ns:cons');

            $consData = [];
            foreach ($lcons as $cons) {
                $usoUnidadConstructiva = (string) $cons->lcd;
                $escalera = isset($cons->dt->lourb->loint->es) ? (string) $cons->dt->lourb->loint->es : '';
                $planta = isset($cons->dt->lourb->loint->pt) ? (string) $cons->dt->lourb->loint->pt : '';
                $puerta = isset($cons->dt->lourb->loint->pu) ? (string) $cons->dt->lourb->loint->pu : '';
                $superficie = isset($cons->dfcons->stl) ? (string) $cons->dfcons->stl : '';

                $consData[] = [
                    'usoPrincipalUnidad' => $usoUnidadConstructiva,
                    'escalera' => $escalera,
                    'planta' => $planta,
                    'puerta' => $puerta,
                    'superficie m2' => $superficie,
                ];
            }

            //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
            $payload['referenciaCatastral'] = $pc1 . $pc2 . $car . $cc1 . $cc2;
            $payload['codigoProvinciaIne'] = (string) $xml->xpath('//ns:loine/ns:cp')[0];
            $payload['codigoMunicipioIne'] = (string) $xml->xpath('//ns:loine/ns:cm')[0];
            $payload['codigoMunicipioDgc'] = (string) $xml->xpath('//ns:cmc')[0];
            $payload['nombreProvincia'] = (string) $xml->xpath('//ns:np')[0];
            $payload['nombreMunicipio'] = (string) $xml->xpath('//ns:nm')[0];
            $payload['numInmuebles'] = (string) $xml->xpath('//ns:cudnp')[0];
            $payload['numUnidadesConstructivas'] = (string) $xml->xpath('//ns:cn')[0];
            $payload['numSubparcelas'] = (string) $xml->xpath('//ns:cucul')[0];
            $payload['tipoBienInmueble'] = (string) $xml->xpath('//ns:cucul')[0];
            
            $payload['localizacionUrbana']['codigoVia'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:cv')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['tipoVia'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:tv')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['nombreVia'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:nv')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['primerNumero'] = !empty($primerNumeroNodes) ? (string) $primerNumeroNodes[0] : null;
            $payload['localizacionUrbana']['primerLetra'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:plp')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['segundoNumero'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:snp')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['segundaLetra'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:slp')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['km'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dir/ns:km')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['bloque'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:loint/ns:bq')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['escalera'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:loint/ns:es')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['planta'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:loint/ns:pt')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['puerta'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:loint/ns:pu')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['codigoPostal'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dp')[0] ?? null) ?: null;
            $payload['localizacionUrbana']['distritoMunicipal'] = (string) ($xml->xpath('//ns:lous/ns:lourb/ns:dm')[0] ?? null) ?: null;

            $payload['localizacionRustico']['codigoMunicipio'] = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:cma')[0] ?? null) ?: null;
            $payload['localizacionRustico']['codigoZona'] = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:czc')[0] ?? null) ?: null;
            $payload['localizacionRustico']['codigoPoligono'] = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:cpp/ns:cpo')[0] ?? null) ?: null;
            $payload['localizacionRustico']['codigoParcela'] = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:cpp/ns:cpa')[0] ?? null) ?: null;
            $payload['localizacionRustico']['nombreParaje'] = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:npa')[0] ?? null) ?: null;
            $payload['localizacionRustico']['codigoParaje'] = (string) ($xml->xpath('//ns:lous/ns:lorus/ns:cpaj')[0] ?? null) ?: null;

            $payload['localizacionUrbanaAdicional']['codigoVia'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:cv')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['tipoVia'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:tv')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['nombreVia'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:nv')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['primerNumero'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:pnp')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['primerLetra'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:plp')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['segundoNumero'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:snp')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['segundaLetra'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:slp')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['km'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dir/ns:km')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['bloque'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:loint/ns:bq')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['escalera'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:loint/ns:es')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['planta'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:loint/ns:pt')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['puerta'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:loint/ns:pu')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['codigoPostal'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dp')[0] ?? null) ?: null;
            $payload['localizacionUrbanaAdicional']['distritoMunicipal'] = (string) ($xml->xpath('//ns:lors/ns:lourb/ns:dm')[0] ?? null) ?: null;

            $payload['localizacionRusticoAdicional']['codigoMunicipio'] = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:cma')[0] ?? null) ?: null;
            $payload['localizacionRusticoAdicional']['codigoZona'] = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:czc')[0] ?? null) ?: null;
            $payload['localizacionRusticoAdicional']['codigoPoligono'] = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:cpp/ns:cpo')[0] ?? null) ?: null;
            $payload['localizacionRusticoAdicional']['codigoParcela'] = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:cpp/ns:cpa')[0] ?? null) ?: null;
            $payload['localizacionRusticoAdicional']['nombreParaje'] = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:npa')[0] ?? null) ?: null;
            $payload['localizacionRusticoAdicional']['codigoParaje'] = (string) ($xml->xpath('//ns:lors/ns:lorus/ns:cpaj')[0] ?? null) ?: null;
            
            $payload['direccionCompleta'] = (string) ($xml->xpath('//ns:ldt')[0] ?? null) ?: null;
            $payload['usoPrincipal'] = (string) ($xml->xpath('//ns:debi/ns:luso')[0] ?? null) ?: null;
            $payload['superficieConstruida'] = (string) ($xml->xpath('//ns:debi/ns:sfc')[0] ?? null) ?: null;
            $payload['coeficienteParticipacion'] = (string) ($xml->xpath('//ns:debi/ns:cpt')[0] ?? null) ?: null;
            $payload['fechaConstruccion'] = (string) ($xml->xpath('//ns:debi/ns:ant')[0] ?? null) ?: null;
            
            $payload['listadoConstrucciones'] = $consData;

            $result[] = $payload;
            //-----------------------------------------------Fin de ejecucion de codigo para $refCat de 20 caracteres  -------------------------------------------------

            
            //-----------------------------------------------Inicio de ejecucion de codigo para $refCat de 14 caracteres  -------------------------------------------------
        } elseif (strlen($refCat) === 14) {
            $lrcdnp = $xml->xpath('//ns:lrcdnp/ns:rcdnp');

            foreach ($lrcdnp as $rcdnp) {
                $pc1 = isset($rcdnp->rc->pc1) ? (string) $rcdnp->rc->pc1 : '';
                $pc2 = isset($rcdnp->rc->pc2) ? (string) $rcdnp->rc->pc2 : '';
                $car = isset($rcdnp->rc->car) ? (string) $rcdnp->rc->car : '';
                $cc1 = isset($rcdnp->rc->cc1) ? (string) $rcdnp->rc->cc1 : '';
                $cc2 = isset($rcdnp->rc->cc2) ? (string) $rcdnp->rc->cc2 : '';
                $referenciaCatastral = $pc1 . $pc2 . $car . $cc1 . $cc2; // Construir la referencia catastral completa

                $codigoProvinciaIne = isset($rcdnp->dt->loine->cp) ? (string) $rcdnp->dt->loine->cp : '';
                $codigoMunicipioIne = isset($rcdnp->dt->loine->cm) ? (string) $rcdnp->dt->loine->cm : '';
                $codigoMunicipioDgc = isset($rcdnp->dt->cmc) ? (string) $rcdnp->dt->cmc : '';
                $nombreProvincia = isset($rcdnp->dt->np) ? (string) $rcdnp->dt->np : '';
                $nombreMunicipio = isset($rcdnp->dt->nm) ? (string) $rcdnp->dt->nm : '';
                $codigoVia = isset($rcdnp->dt->locs->lous->lourb->dir->cv) ? (string) $rcdnp->dt->locs->lous->lourb->dir->cv : '';
                $tipoVia = isset($rcdnp->dt->locs->lous->lourb->dir->tv) ? (string) $rcdnp->dt->locs->lous->lourb->dir->tv : '';
                $nombreVia = isset($rcdnp->dt->locs->lous->lourb->dir->nv) ? (string) $rcdnp->dt->locs->lous->lourb->dir->nv : '';
                $primerNumero = isset($rcdnp->dt->locs->lous->lourb->dir->pnp) ? (string) $rcdnp->dt->locs->lous->lourb->dir->pnp : '';
                $segundoNumero = isset($rcdnp->dt->locs->lous->lourb->dir->snp) ? (string) $rcdnp->dt->locs->lous->lourb->dir->snp : '';
                $planta = isset($rcdnp->dt->locs->lous->lourb->loint->pt) ? (string) $rcdnp->dt->locs->lous->lourb->loint->pt : '';
                $puerta = isset($rcdnp->dt->locs->lous->lourb->loint->pu) ? (string) $rcdnp->dt->locs->lous->lourb->loint->pu : '';
                $codigoPostal = isset($rcdnp->dt->locs->lous->lourb->dp) ? (string) $rcdnp->dt->locs->lous->lourb->dp : '';
                $distritoMunicipal = isset($rcdnp->dt->locs->lous->lourb->dm) ? (string) $rcdnp->dt->locs->lous->lourb->dm : '';

                
                $rcdpnData[] = [
                    'Referencia Catastral' => $referenciaCatastral,
                    'Codigo Provincia INE' => $codigoProvinciaIne,
                    'Codigo Municipio INE' => $codigoMunicipioIne,
                    'Codigo Municipio DGC' => $codigoMunicipioDgc,
                    'NombreProvincia' => $nombreProvincia,
                    'NombreMunicipio' => $nombreMunicipio,
                    'Codigo de Via' => $codigoVia,
                    'Tipo de Via'=> $tipoVia,
                    'Nombre de Via'=> $nombreVia,
                    'Primer Numero'=> $primerNumero,
                    'Segundo Numero'=> $segundoNumero,
                    'Planta'=>  $planta,
                    'Puerta'=>  $puerta,
                    'Codigo Postal'=> $codigoPostal,
                    'Distrito Municipal'=> $distritoMunicipal,
                    
                ]; 
            }
            $payload['listadoInmuebles'] = $rcdpnData;
            $result[] = $payload;
            
        
            //-----------------------------------------------FIN de ejecucion de codigo para $refCat de 14 caracteres  -------------------------------------------------------
        } else {
            $result = ['ERROR' => 'Longitud Invalida, por favor verifique'];
        }
        // -------------------------------------------------------------------------------------------------------------------------------------------------------------------

        // Devolver la respuesta en formato JSON
        return $this->json($result, 200, [], [
            'json_encode_options' => JSON_PRETTY_PRINT
        ]);
    }
}
