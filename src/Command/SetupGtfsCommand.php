<?php

namespace App\Command;

use App\Entity\Arret;
use App\Entity\LigneArret;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ligne;
use App\Repository\LigneRepository;
use App\Repository\ArretRepository;
use App\Repository\LigneArretRepository;
use App\Service\LigneService;

#[AsCommand(
    name: "app:setup-gtfs",
    description: "Importe les données GTFS dans la base de données",
)]
class SetupGtfsCommand extends Command
{
    private $gtfsChemin = [
        "routes" => "var/gtfs/routes.txt",
        "trips" => "var/gtfs/trips.txt",
        "stop_times" => "var/gtfs/stop_times.txt", // LOAD DATA LOCAL INFILE "stop_times.txt" INTO TABLE stop_time FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 LINES (trip_id, @dummy1, @dummy2, @dummy3, @dummy4, stop_id, stop_sequence, @dummy5, @dummy6, @dummy7, @dummy8, @dummy9, @dummy10, @dummy11);
        "stops" => "var/gtfs/stops.txt",
        "arrets-lignes" => "var/gtfs/arrets-lignes.json" // https://data.iledefrance-mobilites.fr/explore/dataset/arrets-lignes/
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LigneRepository $ligneRepository,
        private readonly ArretRepository $arretRepository,
        private readonly LigneService $ligneService,
        private readonly LigneArretRepository $ligneArretRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Importation des données GTFS");

        if (
            !file_exists($this->gtfsChemin["routes"])
            ||
            !file_exists($this->gtfsChemin["trips"])
            ||
            !file_exists($this->gtfsChemin["stop_times"])
            ||
            !file_exists($this->gtfsChemin["stops"])
        ) {
            $io->error("Les fichiers GTFS sont manquants. Veuillez les télécharger https://www.data.gouv.fr/fr/datasets/horaires-prevus-sur-les-lignes-de-transport-en-commun-dile-de-france-gtfs-datahub/#/resources et les placer dans var/gtfs/.");
            return Command::FAILURE;
        }

        ini_set('memory_limit', '-1');

        // $this->ajoutDesLignesDeBus($io);

        // $this->ajoutDesArrets($io);

        // $this->LinkDesLignesEtDesArrets2($io);

        // $this->CleanEntityInutile($io);

        $lignesAInitialiser = [
            [
                "nom" => "113",
                "depart" => "Nogent-sur-Marne",
                "arrivee" => "Terre Ciel"
            ],
            // [
            //     "nom" => "220",
            //     "depart" => "Bry-sur-Marne",
            //     "arrivee" => "Torcy"
            // ],
            // [
            //     "nom" => "213",
            //     "depart1" => "IDFM:424467", // Chelles - Gournay RER
            //     "arrivee1" => "IDFM:463385", // Le Village #1
            //     "depart2" => "IDFM:463386", // Le Village #2
            //     "arrivee2" => "IDFM:424467" // Chelles - Gournay RER
            // ],
            // [
            //     "nom" => "N34",
            //     "depart1" => "IDFM:22801", // Gare de Lyon - Diderot
            //     "arrivee1" => "IDFM:41446", // Gare de Torcy
            //     "depart2" => "IDFM:41446", // Gare de Torcy
            //     "arrivee2" => "IDFM:421409" // Gare de Lyon - Maison de la RATP
            // ]
        ];

        $this->test($io, $lignesAInitialiser);

        return Command::SUCCESS;
    }

    private function ajoutDesLignesDeBus(SymfonyStyle $io): void
    {
        $lignes = [];

        $routesStream = fopen($this->gtfsChemin["routes"], "r");

        fgetcsv($routesStream, 500, ","); // Lire l'en-tête

        while ($data = fgetcsv($routesStream, 500, ",")) {
            // route_type
            // $data[5] === "3" -> BUS
            if ($data[5] === "3") {
                $lignes[] = [
                    "route_id" => $data[0],
                    "route_short_name" => $data[2],
                    "route_color" => $data[7],
                    "route_text_color" => $data[8]
                ];
            }
        }
        fclose($routesStream);

        $io->section("Ajout des lignes de bus");

        $io->progressStart(count($lignes));

        foreach ($lignes as $ligne) {
            $ligneEntity = new Ligne();

            $ligneEntity
                ->setNomId($ligne["route_id"])
                ->setNom($ligne["route_short_name"])
                ->setCouleurHexa($ligne["route_color"])
                ->setTexteCouleurHexa($ligne["route_text_color"])
                ->setInitialisee(false);

            $this->entityManager->persist($ligneEntity);

            $io->progressAdvance();
        }

        $this->entityManager->flush();

        $io->progressFinish();

        $io->info("Importation des lignes de bus terminée");
    }

    private function ajoutDesArrets(SymfonyStyle $io): void
    {
        $arrets = [];

        $stopsStream = fopen($this->gtfsChemin["stops"], "r");

        fgetcsv($stopsStream, 500, ","); // Lire l'en-tête

        while ($data = fgetcsv($stopsStream, 500, ",")) {
            $arrets[] = [
                "stop_id" => $data[0],
                "stop_name" => $data[2]
            ];
        }
        fclose($stopsStream);

        $io->section("Ajout des arrets");

        $io->progressStart(count($arrets));

        $i = 0;

        foreach ($arrets as $arret) {
            $arretEntity = new Arret();

            $arretEntity
                ->setNomId($arret["stop_id"])
                ->setNom($arret["stop_name"]);

            $this->entityManager->persist($arretEntity);

            $i++;
            if ($i > 1000) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $i = 0;
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();

        $io->progressFinish();

        $io->info("Importation des arrets terminée");
    }

    private function LinkDesLignesEtDesArrets2(SymfonyStyle $io): void
    {
        $jsonData = file_get_contents($this->gtfsChemin["arrets-lignes"]);
        $arretsLignes = json_decode($jsonData, true);

        $io->section("Associations des lignes et des arrets depuis le fichier JSON");

        $io->progressStart(count($arretsLignes));

        $i = 0;
        foreach ($arretsLignes as $arretLigne) {
            $ligne = $this->ligneRepository->findOneBy(["nomId" => $arretLigne["id"]]);
            $arret = $this->arretRepository->findOneBy(["nomId" => $arretLigne["stop_id"]]);

            if ($ligne && $arret) {
                $ligneArret = new LigneArret();
                $ligneArret->setArret($arret);
                $ligneArret->setLigne($ligne);
                $ordre = count($ligne->getLigneArrets());
                $ligneArret->setOrdre($ordre);
                $this->entityManager->persist($ligneArret);
                if ($arret->getVille() === null) {
                    $arret->setVille($arretLigne["nom_commune"]);
                    $this->entityManager->persist($arret);
                }
            }

            if ($i++ > 1000) {
                $this->entityManager->flush();
                $i = 0;
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();

        $io->progressFinish();

        $io->info("Associations des lignes et des arrets depuis le fichier JSON terminée");
    }

    // Provisoire pour les lignes car objetif 100% lignes couvertes
    private function CleanEntityInutile(SymfonyStyle $io): void
    {
        ini_set('memory_limit', '-1');

        $io->section("Nettoyage des entités inutiles");

        $lignes = $this->ligneRepository->findAll();

        $io->progressStart(count($lignes));

        $i = 0;
        foreach ($lignes as $ligne) {
            try {
                if ($ligne->getLigneArrets()->isEmpty()) {
                    $this->entityManager->remove($ligne);
                }
            } catch (\Doctrine\ORM\ORMInvalidArgumentException $e) {
                $io->warning("Ligne détachée trouvée et ignorée: " . $ligne->getNom());
                continue;
            }
            $io->progressAdvance();
            $i++;
            if ($i > 1000) {
                $this->entityManager->flush();
                $i = 0;
            }
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->info("Suppression des lignes sans arrêts terminée (Provisoire)");

        $arrets = $this->arretRepository->findAll();
        
        $io->progressStart(count($arrets));

        $i = 0;
        foreach ($arrets as $arret) {
            if ($arret->getLigneArrets()->isEmpty()) {
                $this->entityManager->remove($arret);
            }
            $io->progressAdvance();
            $i++;
            if ($i > 1000) {
                $this->entityManager->flush();
                $i = 0;
            }
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->info("Suppression des arrêts sans lignes terminée");
    }

    private function test($io, $lignesAInitialiser): void
    {
        $routeIds = [];

        foreach ($lignesAInitialiser as $ligneAInitialiser) {
            $ligneNomId = $this->ligneRepository->findOneBy(["nom" => $ligneAInitialiser["nom"]])->getNomId();
            $routeIds[] = $ligneNomId;
        }

        $tripIds = [];

        $tripsStream = fopen($this->gtfsChemin["trips"], "r");

        fgetcsv($tripsStream, 500, ","); // Lire l'en-tête

        $routeIdsLink = [];

        while ($data = fgetcsv($tripsStream, 500, ",")) {
            if (in_array($data[0], $routeIds)) { // Colonne route_id
                $tripIds[] = $data[2]; // Colonne trip_id
                $routeIdsLink[$data[2]] = $data[0]; // trip_id => route_id
            }
        }
        fclose($tripsStream);

        $stopSequence = [];

        $stopTimesStream = fopen($this->gtfsChemin["stop_times"], "r");

        fgetcsv($stopTimesStream, 500, ","); // Lire l'en-tête

        while ($data = fgetcsv($stopTimesStream, 500, ",")) {
            if (in_array($data[0], $tripIds)) { // Colonne trip_id
                $ligneId = $routeIdsLink[$data[0]];
                $stopSequence[$ligneId][$data[0]][$data[6]] = $data[5]; // trip_id => [stop_sequence => stop_id]
            }
        }
        fclose($stopTimesStream);

        foreach ($lignesAInitialiser as $ligneAInitialiser) {
            $ligne = $this->ligneRepository->findOneBy(["nom" => $ligneAInitialiser["nom"]]);
            $ligneNomId = $ligne->getNomId();

            // Grouper les trips par ordre d’arrêts
            $groupedTrips = [];
            foreach ($stopSequence[$ligneNomId] as $one) {
                $key = implode('-', $one); // Création d'une clé unique pour comparer les séquences
                $groupedTrips[$key] = $one;
            }

            $paths = [];
            foreach ($groupedTrips as $groupedTrip) {
                $paths[] = array_values($groupedTrip);
            }

            if (isset($ligneAInitialiser["depart"]) && isset($ligneAInitialiser["arrivee"])) {
                $ligneArretStart = array_values(array_filter($ligne->getLigneArrets()->toArray(), function($ligneArret) use ($ligneAInitialiser) {
                    return stripos($ligneArret->getArret()->getNom(), $ligneAInitialiser["depart"]) !== false;
                }));

                $ligneArretStop = array_values(array_filter($ligne->getLigneArrets()->toArray(), function($ligneArret) use ($ligneAInitialiser) {
                    return stripos($ligneArret->getArret()->getNom(), $ligneAInitialiser["arrivee"]) !== false;
                }));

                if (empty($ligneArretStart) || empty($ligneArretStop)) {
                    $io->error("Les arrêts de départ ou d'arrivée n'ont pas été trouvés.");
                    foreach ($ligne->getLigneArrets() as $ligneArret) {
                        $io->writeln($ligneArret->getArret()->getNom());
                    }
                    return;
                }
            }

            if(isset($ligneAInitialiser["arrivee1"]) && isset($ligneAInitialiser["arrivee2"]) && isset($ligneAInitialiser["depart1"]) && isset($ligneAInitialiser["depart2"])){
                // Exécution de l'algorithme
                $globalPath = $this->ligneService->mergePaths($paths, $ligneAInitialiser["depart1"], $ligneAInitialiser["arrivee1"]);

                // Exécution de l'algorithme sens inverse
                $globalPathInverse = $this->ligneService->mergePaths($paths, $ligneAInitialiser["depart2"], $ligneAInitialiser["arrivee2"]);
            } else {
                // Exécution de l'algorithme
                $globalPath = $this->ligneService->mergePaths($paths, $ligneArretStart[0]->getArret()->getNomId(), $ligneArretStop[0]->getArret()->getNomId());

                // Exécution de l'algorithme sens inverse
                $globalPathInverse = $this->ligneService->mergePaths($paths, $ligneArretStop[0]->getArret()->getNomId(), $ligneArretStart[0]->getArret()->getNomId());
            }

            // Affichage du résultat

            $io->info($ligne->getNom() . " - Direction 1 :\n");
            $i = 0;
            foreach ($globalPath as $stopId) {
                $arret = $this->arretRepository->findOneBy(["nomId" => $stopId]);
                $ligneArret = $this->ligneArretRepository->findOneBy(["arret" => $arret, "ligne" => $ligne]);
                $ligneArret->setIndexDirection1($i++);
                $this->entityManager->persist($ligneArret);
                $io->write($arret->getNom() . " -> " );
            }
            $this->entityManager->flush();

            $io->info($ligne->getNom() . " - Direction 2 :\n");
            $i = 0;
            foreach ($globalPathInverse as $stopId) {
                $arret = $this->arretRepository->findOneBy(["nomId" => $stopId]);
                $ligneArret = $this->ligneArretRepository->findOneBy(["arret" => $arret, "ligne" => $ligne]);
                $ligneArret->setIndexDirection2($i++);
                $this->entityManager->persist($ligneArret);
                $io->write($arret->getNom() . " -> " );
            }
            $this->entityManager->flush();

            $ligne->setInitialisee(true);
            $this->entityManager->persist($ligne);
            $this->entityManager->flush();
        }
    }
}
