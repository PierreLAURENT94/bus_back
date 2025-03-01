<?php

namespace App\Command;

use App\Entity\Enregistrement;
use App\Repository\LigneRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:actualisation2',
    description: '',
    hidden: false,
)]

class Actualisation2Command extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LigneRepository $ligneRepository
    ) {
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
        $lignes = $this->ligneRepository->findBy(["initialisee" => true]);

        $requetesIDFM = [];
        $multiHandle = curl_multi_init();

        foreach ($lignes as $ligne) {
            $ligneArrets = $ligne->getLigneArrets();
            foreach ($ligneArrets as $ligneArret) {
                $arretId = explode(':', $ligneArret->getArret()->getNomId())[1];
                $monitoringRef = rawurlencode("STIF:StopPoint:Q:" . $arretId . ":");

                $lineRef = "STIF:Line::" . explode(':', $ligne->getNomId())[1] . ":";

                $sessionCurl = curl_init();
                curl_setopt_array($sessionCurl, [
                    CURLOPT_URL => "https://prim.iledefrance-mobilites.fr/marketplace/stop-monitoring?MonitoringRef={$monitoringRef}&LineRef={$lineRef}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                            "apikey: oVP6EMlvlVVABpXPwyglS8RepvnLVvkD",
                            "Accept: application/json"
                        ],
                ]);

                curl_multi_add_handle($multiHandle, $sessionCurl);

                $requetesIDFM[] = [
                    "ligneArret" => $ligneArret,
                    "sessionCurl" => $sessionCurl
                ];

            }
        }

        $dateActuelle = new \DateTime();

        // Exécution des requêtes en parallèle
        do {
            $status = curl_multi_exec($multiHandle, $active);
            curl_multi_select($multiHandle);
        } while ($active && $status == CURLM_OK);

        // Récupération des réponses
        foreach ($requetesIDFM as $requeteIDFM) {
            $response = curl_multi_getcontent($requeteIDFM["sessionCurl"]);
            $responseDecode = json_decode($response, true);
            curl_multi_remove_handle($multiHandle, $requeteIDFM["sessionCurl"]);
            curl_close($requeteIDFM["sessionCurl"]);

            // if($requeteIDFM["ligneArret"]->getArret()->getNom() === "Les Prévoyants") {
            //     $responseTimestamp = $responseDecode['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['ResponseTimestamp'];
            //     $expectedDepartureTime =$responseDecode['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][0]['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime'];
            //     $heureIDFM = new \DateTime($responseTimestamp);
            //     $heureExpectedDeparture = new \DateTime($expectedDepartureTime);
            //     $interval = $heureIDFM->diff($heureExpectedDeparture);
            //     dump($heureIDFM);
            //     dump($heureExpectedDeparture);
            //     dump($interval->format('%i minutes %s secondes'));
            //     dd($responseDecode);
            // }

            try {
                $monitoredStopVisit = $responseDecode['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'];
                // foreach ($monitoredStopVisit as $monitoredStopVisit) {
                //     $prochainPassage = $monitoredStopVisit['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime'];
                //     $direction = $monitoredStopVisit['MonitoredVehicleJourney']['DirectionName'][0]['value'];
                // }
                // $prochainPassage = new \DateTime($monitoredStopVisit[0]['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime']);

                $responseTimestamp = $responseDecode['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['ResponseTimestamp'];
                $expectedDepartureTime =$responseDecode['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][0]['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime'];
                $heureIDFM = new \DateTime($responseTimestamp);
                $heureExpectedDeparture = new \DateTime($expectedDepartureTime);
                $tempsVersprochainPassage = $heureIDFM->diff($heureExpectedDeparture);

                $direction = $monitoredStopVisit[0]['MonitoredVehicleJourney']['DirectionName'][0]['value'];
            } catch (\Throwable $th) {
                continue; // pas de prochain passage
            }

            $enregistrement = new Enregistrement();
            $enregistrement
                ->setDateTime($dateActuelle)
                ->setTempsVersProchainPassage($tempsVersprochainPassage);
            $this->entityManager->persist($enregistrement);

            switch ($direction) {
                case 'Gare de Torcy':
                    $requeteIDFM["ligneArret"]->addEnregistrementsDirection1($enregistrement);
                    $this->entityManager->persist($requeteIDFM["ligneArret"]);
                    break;
                case 'Bry-sur-Marne RER':
                    $requeteIDFM["ligneArret"]->addEnregistrementsDirection2($enregistrement);
                    $this->entityManager->persist($requeteIDFM["ligneArret"]);
                    break;
            }
        }

        $this->entityManager->flush();
        curl_multi_close($multiHandle);

        return Command::SUCCESS;
    }
}