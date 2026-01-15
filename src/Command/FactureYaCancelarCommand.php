<?php

namespace App\FactureYaTimbradoBundle\Command;

use App\FactureYaTimbradoBundle\Service\FactureYaTimbradoService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FactureYaCancelarCommand extends Command
{
    protected static $defaultName = 'factureya:cancelar';
    protected static $defaultDescription = 'Cancela una factura timbrada con FactureYa';

    private FactureYaTimbradoService $timbradoService;

    public function __construct(FactureYaTimbradoService $timbradoService)
    {
        parent::__construct();
        $this->timbradoService = $timbradoService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('uuid', InputArgument::REQUIRED, 'UUID de la factura a cancelar')
            ->addOption('motivo', 'm', InputOption::VALUE_OPTIONAL, 'Motivo de cancelación (01, 02, 03, 04)', '02')
            ->addOption('folio-sustituto', 'f', InputOption::VALUE_OPTIONAL, 'Folio sustituto (opcional)', '')
            ->addOption('rfc', null, InputOption::VALUE_OPTIONAL, 'RFC del emisor (si no está en configuración)', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $uuid = $input->getArgument('uuid');
        $motivo = $input->getOption('motivo');
        $folioSustituto = $input->getOption('folio-sustituto');
        $rfc = $input->getOption('rfc');

        $io->title('Cancelando factura con FactureYa');
        
        if ($this->timbradoService->isModoPruebas()) {
            $io->warning('MODO PRUEBAS ACTIVADO');
        }

        $io->section('Información de cancelación');
        $io->text([
            'UUID: ' . $uuid,
            'Motivo: ' . $this->getMotivoDescripcion($motivo),
            'Folio Sustituto: ' . ($folioSustituto ?: 'N/A'),
            'RFC Emisor: ' . ($rfc ?: 'De configuración'),
        ]);

        try {
            $io->section('Procesando cancelación...');
            
            $resultado = $this->timbradoService->cancelar($uuid, $motivo, $folioSustituto, $rfc);
            
            $io->success('Factura cancelada exitosamente!');
            
            $io->section('Resultado de cancelación');
            $io->table(
                ['Campo', 'Valor'],
                [
                    ['UUID', $resultado['uuid']],
                    ['Estatus', $resultado['estatus']],
                    ['Fecha Cancelación', $resultado['fechaCancelacion']],
                    ['Código Error', $resultado['codigoError']],
                    ['Mensaje', $resultado['mensaje']],
                ]
            );

            // Mostrar acuse si existe
            if (!empty($resultado['acuse'])) {
                $io->section('Acuse de cancelación');
                $io->text($resultado['acuse']);
                
                // Guardar acuse
                $nombreArchivoAcuse = sprintf('acuse_cancelacion_%s_%s.xml', 
                    $uuid, 
                    date('YmdHis')
                );
                file_put_contents($nombreArchivoAcuse, $resultado['acuse']);
                $io->note("Acuse guardado en: {$nombreArchivoAcuse}");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function getMotivoDescripcion(string $motivo): string
    {
        $motivos = [
            '01' => 'Comprobante emitido con errores con relación',
            '02' => 'Comprobante emitido con errores sin relación',
            '03' => 'No se llevó a cabo la operación',
            '04' => 'Operación nominativa relacionada en una factura global',
        ];

        return $motivos[$motivo] ?? 'Desconocido';
    }
}
