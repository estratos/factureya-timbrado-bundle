<?php

namespace App\FactureYaTimbradoBundle\Command;

use App\FactureYaTimbradoBundle\Service\FactureYaTimbradoService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FactureYaConsultarCommand extends Command
{
    protected static $defaultName = 'factureya:consultar';
    protected static $defaultDescription = 'Consulta el estado de una factura en FactureYa';

    private FactureYaTimbradoService $timbradoService;

    public function __construct(FactureYaTimbradoService $timbradoService)
    {
        parent::__construct();
        $this->timbradoService = $timbradoService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('uuid', InputArgument::REQUIRED, 'UUID de la factura a consultar')
            ->addOption('rfc', null, InputOption::VALUE_OPTIONAL, 'RFC del emisor (si no está en configuración)', '')
            ->addOption('saldo', 's', InputOption::VALUE_NONE, 'Consultar también el saldo disponible');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $uuid = $input->getArgument('uuid');
        $rfc = $input->getOption('rfc');
        $consultarSaldo = $input->getOption('saldo');

        $io->title('Consultando factura en FactureYa');
        
        if ($this->timbradoService->isModoPruebas()) {
            $io->warning('MODO PRUEBAS ACTIVADO');
        }

        try {
            $io->section('Consultando estado...');
            
            $resultado = $this->timbradoService->consultar($uuid, $rfc);
            
            if (!$resultado['success']) {
                $io->error('Error en consulta: ' . ($resultado['mensajeError'] ?? 'Desconocido'));
                return Command::FAILURE;
            }

            $io->success('Consulta exitosa!');
            
            $io->section('Estado de la factura');
            $io->table(
                ['Campo', 'Valor'],
                [
                    ['UUID', $resultado['uuid']],
                    ['Estatus', $resultado['estatus']],
                    ['Es Cancelable', $resultado['esCancelable'] ? 'Sí' : 'No'],
                    ['Estatus Cancelación', $resultado['estatusCancelacion'] ?? 'N/A'],
                    ['Fecha Timbrado', $resultado['fechaTimbrado']],
                    ['Fecha Cancelación', $resultado['fechaCancelacion'] ?? 'N/A'],
                    ['RFC Emisor', $resultado['rfcEmisor']],
                    ['RFC Receptor', $resultado['rfcReceptor']],
                    ['Total', '$' . number_format($resultado['total'], 2)],
                    ['Código Error', $resultado['codigoError']],
                ]
            );

            // Consultar saldo si se solicitó
            if ($consultarSaldo) {
                $io->section('Consultando saldo...');
                
                $saldoResultado = $this->timbradoService->consultarSaldo();
                
                if ($saldoResultado['success']) {
                    $io->table(
                        ['Concepto', 'Valor'],
                        [
                            ['Saldo', '$' . number_format($saldoResultado['saldo'], 2)],
                            ['Límite', '$' . number_format($saldoResultado['limite'], 2)],
                            ['Créditos Usados', $saldoResultado['creditosUsados']],
                            ['Créditos Disponibles', $saldoResultado['creditosDisponibles']],
                            ['Fecha Consulta', $saldoResultado['fechaConsulta']],
                        ]
                    );
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
