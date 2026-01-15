<?php

namespace App\FactureYaTimbradoBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class FactureYaTimbradoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        
        $loader->load('services.yaml');

        // Determinar endpoint basado en modo pruebas
        $endpoint = $config['modo_pruebas'] 
            ? $config['endpoint_pruebas'] 
            : $config['endpoint'];

        // Preparar configuración para el servicio principal
        $serviceConfig = [
            'usuario' => $config['usuario'],
            'password' => $config['password'],
            'endpoint' => $endpoint,
            'modo_pruebas' => $config['modo_pruebas'],
            'rfc_emisor' => $config['rfc_emisor'] ?? null,
            'timeout' => $config['timeout'],
            'debug' => $config['debug'],
            'notificaciones' => $config['notificaciones'],
            'almacenamiento' => $config['almacenamiento'],
        ];

        // Configurar el servicio principal
        $timbradoService = $container->getDefinition('factureya.timbrado_service');
        $timbradoService->setArgument('$configuracion', $serviceConfig);

        // Configurar opciones SOAP
        $soapOptions = $config['soap_options'];
        
        // Configurar SSL basado en modo pruebas
        if ($config['modo_pruebas']) {
            $soapOptions['stream_context'] = stream_context_create([
                'ssl' => [
                    'verify_peer' => $soapOptions['ssl_verify_peer'],
                    'verify_peer_name' => $soapOptions['ssl_verify_peer_name'],
                    'allow_self_signed' => true
                ]
            ]);
        }

        // Configurar cliente SOAP
        $soapClient = $container->getDefinition('factureya.soap_client');
        $soapClient->setArgument('$wsdl', $endpoint . '?wsdl');
        $soapClient->setArgument('$options', $soapOptions);

        // Definir parámetros
        $container->setParameter('factureya.config', $serviceConfig);
        $container->setParameter('factureya.endpoint', $endpoint);
        $container->setParameter('factureya.modo_pruebas', $config['modo_pruebas']);
        $container->setParameter('factureya.debug', $config['debug']);
    }

    public function getAlias(): string
    {
        return 'factureya_timbrado';
    }
}
