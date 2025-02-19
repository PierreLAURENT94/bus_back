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

#[AsCommand(
    name: "app:setup-gtfs",
    description: "Importe les données GTFS dans la base de données",
)]
class SetupGtfsCommand extends Command
{
    private $gtfsChemin = [
        "routes" => "var/gtfs/routes.txt",
        "trips" => "var/gtfs/trips.txt",
        "stop_times" => "var/gtfs/stop_times.txt",
        "stops" => "var/gtfs/stops.txt",
        "arrets-lignes" => "var/gtfs/arrets-lignes.json" // https://data.iledefrance-mobilites.fr/explore/dataset/arrets-lignes/
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LigneRepository $ligneRepository,
        private readonly ArretRepository $arretRepository
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

        $this->test($io);

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

    private function LinkDesLignesEtDesArrets(SymfonyStyle $io): void
    {
        // Provisoirement ont prends les lignes 113 et 220 mais plus tard on prendra toutes les lignes
        // $lignes = $this->ligneRepository->findAll();
        $lignes = [];
        $lignes[] = $this->ligneRepository->findOneBy(["nom" => "113"]);
        $lignes[] = $this->ligneRepository->findOneBy(["nom" => "220"]);

        $io->section("Associations des lignes et des arrets");

        $io->warning("Provisoirement ont prends les lignes 113 et 220 mais plus tard on prendra toutes les lignes");

        $io->progressStart(count($lignes));

        foreach ($lignes as $ligne) {
            $routeId = $ligne->getNomId();

            // Lit le fichier trips.txt pour trouver les trip_id de la ligne

            $tripIds = [];

            $tripsStream = fopen($this->gtfsChemin["trips"], "r");

            fgetcsv($tripsStream, 500, ","); // Lire l'en-tête

            while ($data = fgetcsv($tripsStream, 500, ",")) {
                if ($data[0] === $routeId) { // Colonne route_id
                    $tripIds[] = $data[2]; // Colonne trip_id
                }
            }
            fclose($tripsStream);

            if (empty($tripIds)) {
                // Aucun trajet trouvé pour la ligne
                $io->progressAdvance();
                continue;
            }

            $io->warning(count($tripIds) . " trajets trouvés pour la ligne " . $ligne->getNom());
            $i = 0;
            foreach ($tripIds as $tripId) {
                if($i++ > 10) {
                    break;
                }
                $io->warning($tripId);
            }
            

            // Lit le fichier stop_times.txt pour trouver les arrêts dans l'ordre

            $stopSequence = [];

            $stopTimesStream = fopen($this->gtfsChemin["stop_times"], "r");

            fgetcsv($stopTimesStream, 500, ","); // Lire l'en-tête

            while ($data = fgetcsv($stopTimesStream, 500, ",")) {
                if (in_array($data[0], $tripIds)) { // Colonne trip_id
                    $stopSequence[$data[6]] = $data[5]; // stop_sequence => stop_id
                }
            }
            fclose($stopTimesStream);

            if (empty($stopSequence)) {
                // Aucun arrêt trouvé pour les trajets de la ligne
                $io->progressAdvance();
                continue;
            }

            // Trier les arrêts par stop_sequence
            ksort($stopSequence);

            $i = 0;
            foreach ($stopSequence as $stopId) {
                $arret = $this->arretRepository->findOneBy(["nomId" => $stopId]);
                $ligneArret = new LigneArret();
                $ligneArret->setArret($arret);
                $ligneArret->setLigne($ligne);
                $ligneArret->setOrdre($i++);
                $this->entityManager->persist($ligneArret);
            }

            $this->entityManager->flush();
            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->info("Associations des lignes et des arrets terminée");
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

    private function test($io): void
    {
        $ligne = $this->ligneRepository->findOneBy(["nom" => "220"]); // 113

        $routeId = $ligne->getNomId();

        $tripIds = [];

        $tripsStream = fopen($this->gtfsChemin["trips"], "r");

        fgetcsv($tripsStream, 500, ","); // Lire l'en-tête

        // $i = 0;
        while ($data = fgetcsv($tripsStream, 500, ",")) {
            if ($data[0] === $routeId) { // Colonne route_id
                $tripIds[] = $data[2]; // Colonne trip_id
                // if($i++ > 5) {
                //     break;
                // }
            }
        }
        fclose($tripsStream);

        $io->warning(count($tripIds) . " trajets trouvés pour la ligne " . $ligne->getNom());
        // foreach ($tripIds as $tripId) {
        //     $io->warning($tripId);
        // }


        $stopSequence = [];

        $stopTimesStream = fopen($this->gtfsChemin["stop_times"], "r");

        fgetcsv($stopTimesStream, 500, ","); // Lire l'en-tête

        while ($data = fgetcsv($stopTimesStream, 500, ",")) {
            if (in_array($data[0], $tripIds)) { // Colonne trip_id
                $stopSequence[$data[0]][$data[6]] = $data[5]; // trip_id => [stop_sequence => stop_id]
            }
        }
        fclose($stopTimesStream);

        // Trier les arrêts par stop_sequence
        // ksort($stopSequence);

        // Grouper les trips par ordre d’arrêts
        $groupedTrips = [];
        foreach ($stopSequence as $one) {
            $key = implode('-', $one); // Création d'une clé unique pour comparer les séquences
            $groupedTrips[$key] = $one;
        }

        // dump($stopSequence);
        // dump($groupedTrips);

        $paths = [];
        foreach ($groupedTrips as $groupedTrip) {
            $paths[] = array_values($groupedTrip);
        }

        $ligneArretStart = array_values(array_filter($ligne->getLigneArrets()->toArray(), function($ligneArret) {
            return stripos($ligneArret->getArret()->getNom(), 'Bry-sur-Marne') !== false; // Nogent-sur-Marne RER
        }));

        $ligneArretStop = array_values(array_filter($ligne->getLigneArrets()->toArray(), function($ligneArret) {
            return stripos($ligneArret->getArret()->getNom(), 'Torcy') !== false; // Terre Ciel
        }));

        if (empty($ligneArretStart) || empty($ligneArretStop)) {
            $io->error("Les arrêts de départ ou d'arrivée n'ont pas été trouvés.");
            return;
        }

        // Exécution de l'algorithme
        $globalPath = $this->mergePaths($paths, $ligneArretStart[0]->getArret()->getNomId(), $ligneArretStop[0]->getArret()->getNomId());

        // Exécution de l'algorithme sens inverse
        $globalPathInverse = $this->mergePaths($paths, $ligneArretStop[0]->getArret()->getNomId(), $ligneArretStart[0]->getArret()->getNomId());

        // Affichage du résultat
        $io->info("Chemin sens 1 :\n");
        // dump(implode(" -> ", $globalPath));

        foreach ($globalPath as $stopId) {
            $arret = $this->arretRepository->findOneBy(["nomId" => $stopId]);
            $io->write($arret->getNom() . " -> " );
        }

        $io->info("Chemin sens 2 :\n");
        // dump(implode(" -> ", $globalPath));

        foreach ($globalPathInverse as $stopId) {
            $arret = $this->arretRepository->findOneBy(["nomId" => $stopId]);
            $io->write($arret->getNom() . " -> " );
        }
    }

    function mergePaths($paths, $start, $end) {
        $graph = [];
        
        // Construire le graphe
        foreach ($paths as $path) {
            $path = array_unique($path); // Supprimer les doublons dans un même segment
            for ($i = 0; $i < count($path) - 1; $i++) {
            $from = $path[$i];
            $to = $path[$i + 1];
            $graph[$from][$to] = 1;
            }
        }

        $nodesWithSingleNeighbor = [];

        foreach ($graph as $node => $neighbors) {
            if (count($neighbors) === 1) {
            $nodesWithSingleNeighbor[] = $node;
            }
        }

        $io->info("Nodes that have only one neighbor:");
        foreach ($nodesWithSingleNeighbor as $node) {
            echo $node . "\n";
        }
        
        return $this->dijkstra($graph, $start, $end);
    }

    // Algorithme de Dijkstra pour trouver le chemin le plus court
    function dijkstra($graph, $start, $end) {
        $distances = [];
        $previous = [];
        $queue = new \SplPriorityQueue();
        
        foreach ($graph as $node => $neighbors) {
            $distances[$node] = INF;
            $previous[$node] = null;
        }
        $distances[$start] = 0;
        $queue->insert($start, 0);
        
        while (!$queue->isEmpty()) {
            $current = $queue->extract();
            if ($current === $end) break;
            
            foreach ($graph[$current] as $neighbor => $cost) {
                $alt = $distances[$current] + $cost;
                if ($alt < $distances[$neighbor]) {
                    $distances[$neighbor] = $alt;
                    $previous[$neighbor] = $current;
                    $queue->insert($neighbor, -$alt);
                }
            }
        }
    
        $path = [];
        for ($node = $end; $node !== null; $node = $previous[$node]) {
            array_unshift($path, $node);
        }
        
        return ($path[0] === $start) ? $path : [];
    }
}
