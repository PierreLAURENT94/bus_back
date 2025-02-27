<?php

namespace App\Command;

use App\Entity\Arret;
use App\Entity\Enregistrement;
use App\Entity\Ligne;
use App\Entity\LigneArret;
use App\Repository\LigneRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\TypeInfo\Type\NullableType;

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
        )
    {
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
        $ligne = $this->entityManager->getRepository(Ligne::class)->findOneBy(['nom' => "220"]);
        $io->success('Ligne trouvée: ' . $ligne->getNomId());
        $ligneArrets = $ligne->getLigneArrets();
        $urls = [];
        $noms = [];
        foreach ($ligneArrets as $ligneArret) {
            $io->text($ligneArret->getArret()->getNomId());
            $arretId = explode(':',  $ligneArret->getArret()->getNomId())[1];
            $monitoringRef = rawurlencode("STIF:StopPoint:Q:" . $arretId . ":");      
            $lineRef = rawurlencode("STIF:Line::" . $ligne->getNomId() . ":");
            $lineRef = "STIF:Line::" . explode(':',  $ligne->getNomId())[1] . ":";
            $url = "https://prim.iledefrance-mobilites.fr/marketplace/stop-monitoring?MonitoringRef={$monitoringRef}&LineRef={$lineRef}";
            $urls[] = $url;
            $noms[] = $ligneArret->getArret()->getNom();
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

                $StopMonitoringDelivery = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'];
                try {
                    $prochainPassage = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][0]['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime'];
                    $direction = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][0]['MonitoredVehicleJourney']['DestinationName'][0]['value'];
                } catch (\Throwable $th) {
                    $prochainPassage = null;
                    $direction = "N/A";
                }
                
                $strInterval = "Pas de passage";
                if($prochainPassage !== null){
                    $now = new \DateTime();
                    $prochainPassageDateTime = new \DateTime($prochainPassage);
                    $interval = $now->diff($prochainPassageDateTime);
                    $strInterval = $interval->format('%i minutes %s secondes');
                }
    
                $io->writeln("");
                $io->writeln($noms[$index]);
                $io->writeln($ligne->getNom() . ' direction ' . $direction . ' : Prochain passage dans: ' . $strInterval);
                $io->writeln("");
            }
            $io->writeln('-----------------------------------');
            sleep(seconds: 20);
        }

        return Command::SUCCESS;
    }
}