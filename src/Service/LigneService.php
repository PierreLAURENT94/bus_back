<?php

namespace App\Service;

use App\Repository\ArretRepository;

class LigneService
{
    public function __construct(private readonly ArretRepository $arretRepository)
    {
    }
    public function mergePaths($paths, $start, $end) {
        $graph = [];
        
        // Construire le graphe
        foreach ($paths as $path) {
            $path = array_unique($path);
            for ($i = 0; $i < count($path) - 1; $i++) {
            $from = $path[$i];
            $to = $path[$i + 1];
            $graph[$from][$to] = 1;
            }
        }

        // // Générer le fichier .dot pour visualiser le graphe https://dreampuf.github.io/GraphvizOnline/?engine=dot
        // $dotFileContent = "digraph G {\n";
        // foreach ($graph as $from => $neighbors) {
        //     foreach ($neighbors as $to => $cost) {
        //     $fromName = $this->arretRepository->findOneBy(["nomId" => $from])->getNom();
        //     $toName = $this->arretRepository->findOneBy(["nomId" => $to])->getNom();
        //     $dotFileContent .= "    \"$fromName - $from\" -> \"$toName - $to\" [label=\"$cost\"];\n";
        //     }
        // }
        // $dotFileContent .= "}\n";
        
        // file_put_contents('graph.dot', $dotFileContent);
        
        return $this->dijkstra($graph, $start, $end);
    }

    // Algorithme de Dijkstra pour trouver le chemin le plus court
    private function dijkstra($graph, $start, $end) {
        $distances = [];
        $previous = [];
        $queue = new \SplPriorityQueue();
        
        foreach ($graph as $node => $neighbors) {
            $distances[$node] = INF;
            $previous[$node] = null;

            // Ajouter aussi les voisins dans les distances pour éviter des clés manquantes
            foreach ($neighbors as $neighbor => $cost) {
                if (!isset($distances[$neighbor])) {
                    $distances[$neighbor] = INF;
                    $previous[$neighbor] = null;
                }
            }
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