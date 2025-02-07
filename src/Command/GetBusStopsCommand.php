<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:GetBusStops',
    description: 'Add a short description for your command',
)]
class GetBusStopsCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Chemin vers les fichiers GTFS
        $gtfsPath = 'var/gtfs/';
        $routesFile = $gtfsPath . 'routes.txt';
        $tripsFile = $gtfsPath . 'trips.txt';
        $stopTimesFile = $gtfsPath . 'stop_times.txt';
        $stopsFile = $gtfsPath . 'stops.txt';

        // Vérifier que les fichiers existent
        if (!file_exists($routesFile) || !file_exists($tripsFile) || !file_exists($stopTimesFile) || !file_exists($stopsFile)) {
            $io->error('Les fichiers GTFS sont manquants. Veuillez les télécharger https://www.data.gouv.fr/fr/datasets/horaires-prevus-sur-les-lignes-de-transport-en-commun-dile-de-france-gtfs-datahub/#/resources et les placer dans var/gtfs/.');
            return Command::FAILURE;
        }

        // Lire le fichier routes.txt pour trouver l'ID de la ligne 113
        $routeId = null;
        if (($handle = fopen($routesFile, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($data[2] === '113') { // Colonne route_short_name
                    $routeId = $data[0]; // Colonne route_id
                    // $io->info("Ligne 113 : " . $routeId);
                    break;
                }
            }
            fclose($handle);
        }

        if (!$routeId) {
            $io->error('La ligne 113 n\'a pas été trouvée dans routes.txt.');
            return Command::FAILURE;
        }

        // Lire le fichier trips.txt pour trouver les trip_id de la ligne 113
        $tripIds = [];
        if (($handle = fopen($tripsFile, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ','); // Lire l'en-tête
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($data[0] === $routeId) { // Colonne route_id
                    $tripIds[] = $data[2]; // Colonne trip_id
                }
            }
            fclose($handle);
        }

        if (empty($tripIds)) {
            $io->error('Aucun trajet trouvé pour la ligne 113 dans trips.txt.');
            return Command::FAILURE;
        }

        // Lire le fichier stop_times.txt pour trouver les arrêts dans l'ordre
        $stopSequence = [];
        if (($handle = fopen($stopTimesFile, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ','); // Lire l'en-tête
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (in_array($data[0], $tripIds)) { // Colonne trip_id
                    $stopSequence[$data[6]] = $data[5]; // stop_sequence => stop_id
                }
            }
            fclose($handle);
        }

        if (empty($stopSequence)) {
            $io->error('Aucun arrêt trouvé pour les trajets de la ligne 113 dans stop_times.txt.');
            return Command::FAILURE;
        }

        // Trier les arrêts par stop_sequence
        ksort($stopSequence);

        // Lire le fichier stops.txt pour obtenir les noms des arrêts
        $stops = [];
        if (($handle = fopen($stopsFile, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ','); // Lire l'en-tête
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $stops[$data[0]] = $data[2]; // stop_id => stop_name
            }
            fclose($handle);
        }

        // Afficher les arrêts dans l'ordre
        $io->title('Liste des arrêts de la ligne 113 :');
        foreach ($stopSequence as $stopId) {
            if (isset($stops[$stopId])) {
                $io->writeln($stops[$stopId]);
            } else {
                $io->warning("Arrêt avec l'ID $stopId introuvé dans stops.txt.");
            }
        }

        return Command::SUCCESS;

    }
}
