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

#[AsCommand(
    name: 'app:actualisation',
    description: '',
    hidden: false,
)]

class ActualisationCommand extends Command
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

        $url = "https://prim.iledefrance-mobilites.fr/marketplace/estimated-timetable";
        $options = [
            'http' => [
                'header' => "apikey: oVP6EMlvlVVABpXPwyglS8RepvnLVvkD\r\n" .
                    "Accept: application/json"
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        $lignes = $this->ligneRepository->findBy(["initialisee" => true]);

        $estimatedVehicleJourneyList = $data['Siri']['ServiceDelivery']['EstimatedTimetableDelivery'][0]['EstimatedJourneyVersionFrame'][0]['EstimatedVehicleJourney'];

        $ligneIds = [];

        $stopNextPassage = [];

        foreach ($lignes as $ligne) {
            $ligneIds[] = "STIF:Line::" . explode(':',  $ligne->getNomId())[1] . ":";
        }

        $now = new \DateTime();
        foreach ($estimatedVehicleJourneyList as $estimatedVehicleJourney) {
            if(in_array($estimatedVehicleJourney['LineRef']['value'], $ligneIds)) {
                $estimatedCallList = $estimatedVehicleJourney['EstimatedCalls']['EstimatedCall'];
                foreach ($estimatedCallList as $estimatedCall) {
                    $idArret = "IDFM:" . explode(':',  $estimatedCall['StopPointRef']['value'])[3];
                    $arret = $this->entityManager->getRepository(Arret::class)->findOneBy(['nomId' => $idArret]);

                    $prochainPassageDateTime = new \DateTime($estimatedCall['ExpectedDepartureTime']);
                    $interval = $now->diff($prochainPassageDateTime);

                    try {
                        // $io->writeln($arret->getNom() . " (" . $estimatedCall['DestinationDisplay'][0]['value']  . ') : ' . $interval->format('%i minutes %s secondes'));
                    } catch (\Exception $e) {
                        // dump($estimatedCall);
                    }

                    $direction1 = match ($estimatedCall['DestinationDisplay'][0]['value']) {
                        // 113
                        "Mairie de Chelles" => true,
                        "Pointe de Gournay" => true,
                        "Nogent-Sur-Marne <RER>" => false,
                        // 220
                        "Bry-Sur-Marne <RER>" => false,
                        "Noisiel <RER>" => true,
                        "Torcy <RER>" => true,
                        // 320
                        "Gare de Chelles <RER>" => false,
                        "Noisy-Champs <RER>" => true,
                        default => null,
                    };

                    if($direction1 === null) {
                        continue;
                    }

                    if (!isset($stopNextPassage[$estimatedVehicleJourney['LineRef']['value']][$direction1][$idArret]) || $prochainPassageDateTime->getTimestamp() < $stopNextPassage[$estimatedVehicleJourney['LineRef']['value']][$direction1][$idArret]) {
                        $stopNextPassage[$estimatedVehicleJourney['LineRef']['value']][$direction1][$idArret] = $prochainPassageDateTime->getTimestamp();
                    }
                
                    // $io->writeln($arret->getNom() . " : ". $interval->format('%i minutes %s secondes'));
                }
                // $io->writeln("--------------------");
            }
        }

        // dd($stopNextPassage);

        foreach ($stopNextPassage as $ligneTab => $value) {
            $ligneId = explode(':', $ligneTab)[3];
            $ligne = $this->entityManager->getRepository(Ligne::class)->findOneBy(['nomId' => "IDFM:" . $ligneId]);
            if($ligne === null) {
                dd($ligneId);
            }
            foreach ($value as $direction1 => $idArrets) {
                foreach ($idArrets as $idArret => $timestamp) {
                    $arret = $this->entityManager->getRepository(Arret::class)->findOneBy(['nomId' => $idArret]);
                    $ligneArret = $this->entityManager->getRepository(LigneArret::class)->findOneBy(['ligne' => $ligne, 'arret' => $arret]);

                    if($ligneArret === null) {
                        dd($ligne, $arret);
                    }
                    
                    if($direction1 === 1) {
                        $enregistrement = new Enregistrement();

                        $enregistrement
                            ->setDateTime(new \DateTime())
                            ->setProchainPassage(new \DateTime('@' . $timestamp));
                        $this->entityManager->persist($enregistrement);
                        
                        $ligneArret->addEnregistrementsDirection1($enregistrement);
                        $this->entityManager->persist($ligneArret);
                    } else if($direction1 === 0) {
                        $enregistrement = new Enregistrement();

                        $enregistrement
                            ->setDateTime(new \DateTime())
                            ->setProchainPassage(new \DateTime('@' . $timestamp));
                        $this->entityManager->persist($enregistrement);
                        
                        $ligneArret->addEnregistrementsDirection2($enregistrement);
                        $this->entityManager->persist($ligneArret);
                    }
                    $this->entityManager->flush();
                }
            }
        }

        return Command::SUCCESS;
    }
}