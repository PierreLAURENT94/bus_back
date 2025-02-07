<?php

namespace App\Command;

use App\Entity\Arret;
use App\Entity\Ligne;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:test2',
    description: '',
    hidden: false,
)]

class Test2Command extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $ligne = $this->entityManager->getRepository(Ligne::class)->findOneBy(['nom' => "113"]);
        $io->success('Ligne trouvée: ' . $ligne->getNomId());
        $arrets = $ligne->getArrets();
        $urls = [];
        $noms = [];
        foreach ($arrets as $arret) {
            $io->text($arret->getNomId());
            $arretId = explode(':',  $arret->getNomId())[1];
            $monitoringRef = rawurlencode("STIF:StopPoint:Q:" . $arretId . ":");
            $lineRef = rawurlencode("STIF:Line::" . $ligne->getNomId() . ":");
            $url = "https://prim.iledefrance-mobilites.fr/marketplace/stop-monitoring?MonitoringRef={$monitoringRef}&LineRef={$lineRef}";
            $urls[] = $url;
            $noms[] = $arret->getNom();
        }

        while (true) {
            foreach ($urls as $index => $url) {
                $options = [
                    'http' => [
                        'header' => "apikey: oVP6EMlvlVVABpXPwyglS8RepvnLVvkD\r\n" .
                            "Accept: application/json"
                    ]
                ];
                $context = stream_context_create($options);
                $response = file_get_contents($url, false, $context);
                $data = json_decode($response, true);
                // $io->success('Données récupérées depuis l\'API:');
                // $io->text(print_r($data, true));
    
                $prochainPassage = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][0]['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime'];
                $prochainPassage2 = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][1]['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime'];
                $direction = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][0]['MonitoredVehicleJourney']['DestinationName'][0]['value'];
                $direction2 = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][1]['MonitoredVehicleJourney']['DestinationName'][0]['value'];
                $now = new \DateTime();
                $prochainPassageDateTime = new \DateTime($prochainPassage);
                $interval = $now->diff($prochainPassageDateTime);
                $prochainPassageDateTime2 = new \DateTime($prochainPassage2);
                $interval2 = $now->diff($prochainPassageDateTime2);
    
                $io->writeln("");
                $io->writeln($noms[$index]);
                $io->writeln($ligne->getNom() . ' direction ' . $direction . ' : Prochain passage dans: ' . $interval->format('%i minutes %s secondes'));
                $io->writeln($ligne->getNom() . ' direction ' . $direction2 . ' : Prochain passage dans: ' . $interval2->format('%i minutes %s secondes'));
                $io->writeln("");
            }
            $io->writeln('-----------------------------------');
            sleep(seconds: 20);
        }
        

        // $url = 'https://prim.iledefrance-mobilites.fr/marketplace/ilico/getData?method=getlc&id=C01142&format=json';
        // $options = [
        //     'http' => [
        //         'header' => "apikey: oVP6EMlvlVVABpXPwyglS8RepvnLVvkD\r\n" .
        //             "Accept: application/json"
        //     ]
        // ];
        // $context = stream_context_create($options);
        // $io->text(print_r($options['http']['header'], true));
        // $response = file_get_contents($url, false, $context);

        // if ($response === FALSE) {
        //     $io->error('Erreur lors de la requête HTTP.');
        //     return Command::FAILURE;
        // }

        // $data = json_decode($response, true);

        // if (json_last_error() !== JSON_ERROR_NONE) {
        //     $io->error('Erreur lors du décodage du JSON de la requête HTTP: ' . json_last_error_msg());
        //     return Command::FAILURE;
        // }

        // $io->success('Données récupérées depuis l\'API:');
        // $io->text(print_r($data, true));

        return Command::SUCCESS;
    }
}