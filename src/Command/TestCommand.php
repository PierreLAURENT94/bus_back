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
    name: 'app:create-user',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['app:add-user']
)]

class TestCommand extends Command
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
            ->setDescription('Lit le fichier data/referentiel-des-lignes.json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '1G'); // Augmente la limite de mémoire à 1 Go
        $io = new SymfonyStyle($input, $output);
        $currentDirectory = getcwd();
        $filePath = $currentDirectory . '/data/referentiel-des-lignes.json';

        $io->text('Current directory: ' . $currentDirectory);

        if (!file_exists($filePath)) {
            $io->error('Le fichier data/referentiel-des-lignes.json n\'existe pas.');
            return Command::FAILURE;
        }

        $fileContent = file_get_contents($filePath);
        $io->success('Contenu du fichier:');
        $lines = json_decode($fileContent, true);

        foreach ($lines as $line) {
            $ligne = new Ligne();
            $ligne->setNomId($line['id_line']);
            $ligne->setNom($line['name_line']);
            $ligne->setType($line['transportmode']);
            if (isset($line['picto'])) {
                $ligne->setLogo($line['picto']['url']);
            }
            $this->entityManager->persist($ligne);
            
        }
        $this->entityManager->flush();

        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error('Erreur lors du décodage du fichier JSON: ' . json_last_error_msg());
            return Command::FAILURE;
        }


        $filePath2 = $currentDirectory . '/data/arrets-lignes.json';
        $fileContent2 = file_get_contents($filePath2);
        $io->success('Contenu du fichier:');
        $arrets = json_decode($fileContent2, true);
        $i = 0;
        foreach ($arrets as $arret) {
            // if ($arret['stop_name'] == 'Pointe de Gournay'){
                $arretEntity = new Arret();
                $arretEntity->setNomId($arret['stop_id']);
                $arretEntity->setNom($arret['stop_name']);
                // $ligneId = explode(':', $arret['id'])[1];
                // $ligne = $this->entityManager->getRepository(Ligne::class)->findOneBy(['nomId' => $ligneId]);
                // $arretEntity->addLigne($ligne);

                $this->entityManager->persist($arretEntity);
                $i++;
                if ($i > 100) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    $i = 0;
                }
            // }
        }
        $this->entityManager->flush();

        // $io->success('Contenu du fichier JSON:');
        // $io->text(print_r($arrets, true));

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