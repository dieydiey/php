<?php 
// Fonctions d'accès aux données
function selectClients(): array {
    return [
        [
            "nom" => "Wane",
            "prenom" => "Baila",
            "telephone" => "777661010",
            "adresse" => "FO"
        ],
        [
            "nom" => "Wane1",
            "prenom" => "Baila1",
            "telephone" => "777661011",
            "adresse" => "FO1"
        ]
    ];
}

function selectClientByTel(array $clients, string $tel): array|null {
    foreach ($clients as $client) {
        if ($client["telephone"] == $tel) {
            return $client;
        }
    }
    return null;
}

function insertClient(array &$tabClients, $client): void {
    $tabClients[] = $client;
}

function selectDettesByClient(): array {
    return [
        [
            "reference" => "DET-64FE3B2B9A4C3",
            "montant" => 500,
            "date" => "2024-12-04",
            "montant_verse" => 200
        ],
        [
            "reference" => "DET-64FE3B2B9A4C3",
            "montant" => 500,
            "date" => "2024-12-04",
            "montant_verse" => 200
        ]
    ];
}

function insertDette(array &$tabDettes, array $dette): void {
    $tabDettes[] = $dette;
}

// Fonctions Services ou Use Case ou Métier
function enregistrerClient(array &$tabClients, array $client): bool {
    $result = selectClientByTel($tabClients, $client["telephone"]);
    if ($result == null) {
        insertClient($tabClients, $client);
        return true;
    }
    return false;
}

function listerClient(): array {
    return selectClients();
}

function estVide(string $value): bool {
    return empty($value);
}

function ctrl($sms): int {
    do {
        $montant = (int) readline($sms);
    } while ($montant <= 0);
    return $montant;
}

function ctrlversement($montant): int {
    do {
        $montantVerse = (int) readline("entrer le montant du versement");
    } while ($montantVerse <= 0 || $montantVerse > $montant);
    return $montantVerse;
}

function infdetteClient(): array {
    $sms = "Entrer le montant de la dette client : ";
    $montant = ctrl($sms);
    return [
        "reference" => uniqid("DET-"),
        "montant" => $montant,
        "date" => date("Y-m-d"),
        "montant_verse" => ctrlversement($montant),
    ];
}

function enregistrerDettes(array &$dettes, array $dette, array $clients, string $telephone): bool {
    $client = selectClientByTel($clients, $telephone);
    if ($client == null) {
        return false;
    }
    insertDette($dettes, $dette);
    return true;
}

function payerDette(array &$dettes, string $reference, int $montant): bool {
    foreach ($dettes as &$dette) {
        if ($dette["reference"] == $reference) {
            $reste = $dette["montant"] - $dette["montant_verse"];
            if ($montant <= $reste) {
                $dette["montant_verse"] += $montant;
                return true;
            }
            break;
        }
    }
    return false;
}

// Fonctions Présentation
function saisieChampObligatoire(string $sms): string {
    do {
        $value = readline($sms);
    } while (estVide($value));
    return $value;
}

function telephoneIsUnique(array $clients, string $sms): string {
    do {
        $value = readline($sms);
    } while (estVide($value) || selectClientByTel($clients, $value) != null);
    return $value;
}

function afficheClient(array $clients): void {
    if (count($clients) == 0) {
        echo "Pas de client à afficher\n";
    } else {
        foreach ($clients as $client) {
            echo "\n-----------------------------------------\n";
            echo "Telephone : " . $client["telephone"] . "\t";
            echo "Nom : " . $client["nom"] . "\t";
            echo "Prenom : " . $client["prenom"] . "\t";
            echo "Adresse : " . $client["adresse"] . "\t";
        }
    }
}

function saisieClient(array $clients): array {
    return [
        "telephone" => telephoneIsUnique($clients, "Entrer le Telephone: "),
        "nom" => saisieChampObligatoire(" Entrer le Nom: "),
        "prenom" => saisieChampObligatoire(" Entrer le Prenom: "),
        "adresse" => saisieChampObligatoire(" Entrer l'Adresse: "),
    ];
}

function afficheDettes(array $dettes): void {
    if (count($dettes) === 0) {
        echo "Aucune dette à afficher.\n";
    } else {
        foreach ($dettes as $dette) {
            echo "\n-----------------------------------------\n";
            echo "Référence : " . $dette["reference"] . "\n";
            echo "Montant : " . $dette["montant"] . "\n";
            echo "Montant versé : " . $dette["montant_verse"] . "\n";
            echo "Date : " . $dette["date"] . "\n";
        }
    }
}

function menu(): int {
    echo "
     1.Ajouter client \n
     2.Lister les clients\n 
     3.Rechercher client par telephone\n
     4.Enregistrer une dette\n
     5.Lister les dettes\n
     6.Enregistrer un paiement\n
     7.Quitter\n";
    return (int) readline(" Faites votre choix: ");
}

function principal() {
    $clients = selectClients();
    $dettes = [];
    do {
        $choix = menu();
        switch ($choix) {
            case 1:
                $client = saisieClient($clients);
                if (enregistrerClient($clients, $client)) {
                    echo "Client enregistré avec succès \n";
                } else {
                    echo "Le numéro de téléphone existe déjà \n";
                }
                break;
            case 2:
                afficheClient($clients);
                break;
            case 3:
                $tel = saisieChampObligatoire("Entrer le Telephone pour la recherche: ");
                $client = selectClientByTel($clients, $tel);
                if ($client === null) {
                    echo "Client non trouvé \n";
                } else {
                    echo "Le client existe \n";
                }
                break;
            case 4:
                $dette = infdetteClient();
                $telephone = saisieChampObligatoire("Entrer le Telephone du client : ");
                if (enregistrerDettes($dettes, $dette, $clients, $telephone)) {
                    echo "Dette enregistrée avec succès \n";
                } else {
                    echo "Le numéro de téléphone n'existe pas \n";
                }
                break;
            case 5:
                afficheDettes($dettes);
                break;
            case 6:
                $reference = readline("Entrer la référence de la dette : ");
                $montant = (int) readline("Entrer le montant du paiement : ");
                payerDette($dettes,  $reference, $montant);
                break;
            case 7:
                echo "BYEEE !\n";
                break;
            default:
                echo "Veuillez faire un bon choix: \n";
                break;
        }
    } while ($choix != 7);
}

principal();
?>

