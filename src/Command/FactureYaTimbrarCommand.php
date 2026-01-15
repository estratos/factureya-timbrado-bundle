<?php

namespace App\FactureYaTimbradoBundle\Command;

use App\FactureYaTimbradoBundle\Service\FactureYaTimbradoService;
use App\FactureYaTimbradoBundle\Service\Exception\FactureYaTimbradoException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FactureYaTimbrarCommand extends Command
{
    protected static $defaultName = 'factureya:timbrar';
    protected static $defaultDescription = 'Timbra una factura usando FactureYa desde un archivo XML';

    private FactureYaTimbradoService $timbradoService;

    public function __construct(FactureYaTimbradoService $timbradoService)
    {
        parent::__construct();
        $this->timbradoService = $timbradoService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('archivo', InputArgument::REQUIRED, 'Ruta al archivo XML')
            ->addOption('referencia', 'r', InputOption::VALUE_OPTIONAL, 'Referencia de la factura', '')
            ->addOption('email', 'e', InputOption::VALUE_OPTIONAL, 'Email para enviar factura', '')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Directorio de salida', './')
            ->addOption('guardar-pdf', null, InputOption::VALUE_NONE, 'Guardar PDF de la factura');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $archivo = $input->getArgument('archivo');
        $referencia = $input->getOption('referencia');
        $email = $input->getOption('email');
        $outputDir = $input->getOption('output');
        $guardarPdf = $input->getOption('guardar-pdf');

        if (!file_exists($archivo)) {
            $io->error("El archivo {$archivo} no existe");
            return Command::FAILURE;
        }

        $xml = file_get_contents($archivo);

        try {
            $io->title('Timbrando factura con FactureYa');
            
            if ($this->timbradoService->isModoPruebas()) {
                $io->warning('MODO PRUEBAS ACTIVADO - Usando endpoints de demostración');
            }

            // Opciones
            $opciones = [];
            if ($email) {
                $opciones['email'] = $email;
                $opciones['enviar_email'] = true;
            }

            $io->section('Información de timbrado');
            $io->text([
                'Archivo: ' . $archivo,
                'Referencia: ' . ($referencia ?: 'Generada automáticamente'),
                'Email: ' . ($email ?: 'No enviar'),
                'Modo: ' . ($this->timbradoService->isModoPruebas() ? 'Pruebas' : 'Producción'),
            ]);

            $io->section('Procesando...');
            
            $resultado = $this->timbradoService->timbrar($xml, $referencia, $opciones);
            
            $io->success('Factura timbrada exitosamente!');
            
            $io->section('Resultado del timbrado');
            $io->table(
                ['Campo', 'Valor'],
                [
                    ['UUID', $resultado['uuid']],
                    ['Referencia', $resultado['referencia']],
                    ['Fecha Timbrado', $resultado['fechaTimbrado']],
                    ['Serie/Folio', ($resultado['serie'] ?? '') . '/' . ($resultado['folio'] ?? '')],
                    ['Código Error', $resultado['codigoError']],
                ]
            );

            // Guardar XML timbrado
            $nombreArchivoXml = sprintf('factura_%s_%s.xml', 
                $resultado['referencia'], 
                date('YmdHis')
            );
            $rutaXml = $outputDir . '/' . $nombreArchivoXml;
            file_put_contents($rutaXml, $resultado['xmlTimbrado']);
            $io->note("XML timbrado guardado en: {$rutaXml}");

            // Guardar PDF si se solicitó
            if ($guardarPdf && $resultado['uuid']) {
                $io->section('Obteniendo PDF...');
                $pdfResultado = $this->timbradoService->obtenerPdf($resultado['uuid']);
                
                if ($pdfResultado['success'] && $pdfResultado['pdfBase64']) {
                    $nombreArchivoPdf = sprintf('factura_%s_%s.pdf', 
                        $resultado['referencia'], 
                        date('YmdHis')
                    );
                    $rutaPdf = $outputDir . '/' . $nombreArchivoPdf;
                    file_put_contents($rutaPdf, base64_decode($pdfResultado['pdfBase64']));
                    $io->success("PDF guardado en: {$rutaPdf}");
                }
            }

            // Mostrar QR Code si existe
            if (!empty($resultado['qrCode'])) {
                $io->section('Código QR');
                $io->text($resultado['qrCode']);
                
                // Guardar QR como PNG si es base64
                if (str_starts_with($resultado['qrCode'], 'data:image/png;base64,')) {
                    $qrData = base64_decode(substr($resultado['qrCode'], 22));
                    $nombreArchivoQr = sprintf('qr_%s_%s.png', 
                        $resultado['referencia'], 
                        date('YmdHis')
                    );
                    $rutaQr = $outputDir . '/' . $nombreArchivoQr;
                    file_put_contents($rutaQr, $qrData);
                    $io->note("QR Code guardado en: {$rutaQr}");
                }
            }

            return Command::SUCCESS;

        } catch (FactureYaTimbradoException $e) {
            $io->error('Error FactureYa: ' . $e->getMessage());
            
            if ($e->getCodigoError()) {
                $io->error('Código Error: ' . $e->getCodigoError());
            }
            
            if ($e->getErrorDetails()) {
                $io->error('Detalles: ' . print_r($e->getErrorDetails(), true));
            }
            
            return Command::FAILURE;
            
        } catch (\Exception $e) {
            $io->error('Error inesperado: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
