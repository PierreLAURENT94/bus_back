<?php

// namespace App\Command;

// use App\Entity\Arret;
// use App\Entity\Ligne;
// use App\Repository\LigneRepository;
// use Symfony\Component\Console\Attribute\AsCommand;
// use Symfony\Component\Console\Command\Command;
// use Symfony\Component\Console\Input\InputInterface;
// use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Style\SymfonyStyle;
// use Doctrine\ORM\EntityManagerInterface;

// #[AsCommand(
//     name: 'app:actualisation',
//     description: '',
//     hidden: false,
// )]

// class ActualisationCommand extends Command
// {
//     public function __construct(
//         private readonly EntityManagerInterface $entityManager,
//         private readonly LigneRepository $ligneRepository
//         )
//     {
//         parent::__construct();
//     }

//     protected function configure()
//     {
//         $this
//             ->setDescription('')
//         ;
//     }

//     protected function execute(InputInterface $input, OutputInterface $output): int
//     {
//         $io = new SymfonyStyle($input, $output);
//         $lignes = $this->ligneRepository->findBy([], ['nom' => 'ASC']);
//         foreach ($lignes as $ligne) {
//             $ligneArrets = $ligne->getLigneArrets();
//             foreach ($ligneArrets as $ligneArret) {
//                 if($ligneArret->getIndexDirection1() !== null) {
//                     $arretId = explode(':',  $ligneArret->getArret()->getNomId())[1];
//                     $monitoringRef = "STIF:StopPoint:Q:" . $arretId . ":";
//                     $lineRef = "STIF:Line:" . $ligne->getNomId() . ":";
//                     // $url = "https://prim.iledefrance-mobilites.fr/marketplace/stop-monitoring?MonitoringRef={$monitoringRef}&LineRef={$lineRef}";
//                     $url = "https://prim.iledefrance-mobilites.fr/marketplace/estimated-timetable";
//                     $options = [
//                         'http' => [
//                             'header' => "apikey: oVP6EMlvlVVABpXPwyglS8RepvnLVvkD\r\n" .
//                                 "Accept: application/json"
//                         ]
//                     ];
//                     $context = stream_context_create($options);
//                     $response = file_get_contents($url, false, $context);
//                     $data = json_decode($response, true);
//                     $testt = $data['Siri']['ServiceDelivery']['EstimatedTimetableDelivery'][0]['EstimatedJourneyVersionFrame'][0]['EstimatedVehicleJourney'];
//                     $ligneId = explode(':',  $ligne->getNomId())[1];
//                     $lineRef2 = "STIF:Line::" . $ligneId . ":";
//                     $now = new \DateTime();
//                     foreach ($testt as $testtu) {
//                         if($testtu['LineRef']['value'] === $lineRef2) {
//                             $arretsEst = $testtu['EstimatedCalls']['EstimatedCall'];
//                             foreach ($arretsEst as $arretEst) {
//                                 $idArret = "IDFM:" . explode(':',  $arretEst['StopPointRef']['value'])[3];
//                                 $arret = $this->entityManager->getRepository(Arret::class)->findOneBy(['nomId' => $idArret]);

//                                 $prochainPassageDateTime = new \DateTime($arretEst['ExpectedDepartureTime']);
//                                 $interval = $now->diff($prochainPassageDateTime);

//                                 $io->writeln($arret->getNom() . " (" . $testtu['DirectionName'][0]['value']  . ') : ' . $interval->format('%i minutes %s secondes'));
//                             }
//                             $io->writeln("--------------------");
//                             // if(count($testtu['EstimatedCalls']['EstimatedCall']) > 10){
//                             //     dd($testtu);
//                             // }
//                         }
//                     }



//                     file_put_contents("data/test.json", $response);
//                     die();
//                     // $data = json_decode($response, true);
//                     // dd(count($data['Siri']['ServiceDelivery']));
//                     // die();
//                     try {
//                         $prochainPassage = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][0]['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime'];
//                     } catch (\Throwable $th) {
//                         $prochainPassage = null;
//                     }
//                     try {
//                         $prochainPassage2 = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][1]['MonitoredVehicleJourney']['MonitoredCall']['ExpectedDepartureTime'];
//                     } catch (\Throwable $th) {
//                         $prochainPassage2 = null;
//                     }
//                     // $direction = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][0]['MonitoredVehicleJourney']['DestinationName'][0]['value'];
//                     // $direction2 = $data['Siri']['ServiceDelivery']['StopMonitoringDelivery'][0]['MonitoredStopVisit'][1]['MonitoredVehicleJourney']['DestinationName'][0]['value'];
                    
//                     $now = new \DateTime();

//                     if ($prochainPassage === null) {
//                         $interval = $now->diff($now);
//                     }
//                     else {
//                         $prochainPassageDateTime = new \DateTime($prochainPassage);
//                         $interval = $now->diff($prochainPassageDateTime);
//                     }

//                     if ($prochainPassage2 === null) {
//                         $interval = $now->diff($now);
//                     }
//                     else {
//                         $prochainPassageDateTime2 = new \DateTime($prochainPassage2);
//                         $interval2 = $now->diff($prochainPassageDateTime2);
//                     }

//                     $io->writeln("");
//                     $io->writeln($ligneArret->getArret()->getNom());
//                     $io->writeln($ligne->getNom() . ' direction ' /*. $direction*/ . ' : Prochain passage dans: ' . $interval->format('%i minutes %s secondes'));
//                     $io->writeln($ligne->getNom() . ' direction ' /*. $direction2*/ . ' : Prochain passage dans: ' . $interval2->format('%i minutes %s secondes'));
//                     $io->writeln("");
//                 }
//             }
//         }

//         return Command::SUCCESS;
//     }
// }