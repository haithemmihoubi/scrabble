<?php


namespace App\Http\Controllers;


use App\Models\Joueur;
use App\Models\Message;

use App\Models\Partie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class test extends Controller
{


    public function index()
    {
        return Message::latest('dateCreation')->get();
    }


    public function getMessageById($idMessage)
    {
        return Message::findOrFail($idMessage);
    }



    public function getMessageByPlayerId($idJoueur)
    {
        return Joueur::find($idJoueur)->messages()
            ->where('envoyeur', $idJoueur)
            ->latest('dateCreation')
            ->get();
    }


    public function getMessageByPartieId($partieId)
    {
        return Partie::find($partieId)->messages()
            ->where('partie', $partieId)
            ->latest('dateCreation')
            ->get();
    }



    public function creerMessage(Request $request)
    {
        $ligneArray = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o"];
        $posArray = ["h", "v"];
        $joueur = Joueur::find($request->envoyeur);
        $partie = Partie::find($request->partie);
        $envoyeur = Joueur::find($request->envoyeur);
        $ordre = $request->ordre;


        //? verifier si le joueur est deja partant de la partie courante
        if ($envoyeur->partie !== $request->partie) {
            return new JsonResponse([
                "nom" => $joueur->nom,
                "partie" => $partie->idPartie,
                'message' => "$joueur->nom vous n'êtes pas autorisée a envoyer des message dans cette partie",
            ], 404);
        }
        //? verifier les champs qui doivent etre obligatoire
        if (!$request->has("contenu") || !$request->has("envoyeur") || !$request->has("partie")) {
            return new JsonResponse(['message' => "Tout les champs sont obligatoires"], 404);
        }


        //? verifier le type de la commande
        $commande = trim($request->contenu);
        if (!str_starts_with($commande, "!")) {
            //? ajouter le message ordinaire
            $message = Message::create($request->all());
            return new JsonResponse($message);
        }
        //? verifier si c'est une commande placée EXEMPLE  !placer g15v bonjour
        $commandePlacer = substr($commande, 1, 6);
        $commandeChanger = substr($commande, 1, 7);
        $commandePasser = substr($commande, 1, 7);
        $commandeAider = substr($commande, 1, 5);
        if ($commandePlacer === "placer") {
            //? nouvelle commande contiennent par EXEMPLE g15v
            $nouvelleCommande = substr($commande, 8, 4);
            //? verifier si la longeur de la chaine est égale a 3 exemples g5v
            if ($nouvelleCommande[strlen($nouvelleCommande) - 1] === ' ') {
                //? recuperation de LIGNE  COLONNE POSITION
                $lg = $nouvelleCommande[0];
                $col = $nouvelleCommande[1];
                $posit = $nouvelleCommande[2];
                //? verifier si la ligne est correcte => retourne boolean
                $ligneCommande = in_array($lg, $ligneArray);
                //? verifier si la colonne est correcte => retourne boolean
                $colonneisNumber = is_numeric($nouvelleCommande[1]);
                //? verifier si la colonne est un numero valide
                $colonneisNumberValid = (((int)$nouvelleCommande[1]) <= 9) && ((int)$nouvelleCommande[1] >= 1);
                //? verifier si la position est correcte => retourne boolean
                $pos = in_array($nouvelleCommande[2], $posArray);
                //? recuperer le mot a remplacer
                $motAplacer = substr($commande, 11, strlen($commande));

                // ? verifier si les lettres sont inclus dans le chevalet du joueur
                //verfierMotDansChevalet($mot, $chevalet, $grille, $colonne, $ligne, $pos)
                if ($this->verfierMotDansChevalet(trim($motAplacer), trim($joueur->chevalet), $partie->grille, $col, $lg, $posit, $ordre) === false) {
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom  Commande impossible a realiser",
                        'mot' => $motAplacer,
                    ], 404);
                }

                //? verifier l'inexistance des espace entres les caracteres et la chaine doit contenir au moins deux caracteres
                // ? verifier que la longeur du mot <= chevalet
                // ? verifier que la chaine est alphabetique
                if (str_contains(trim($motAplacer), ' ') || (strlen(trim($motAplacer)) < 2) || !ctype_alpha(trim($motAplacer))
                    || strlen($motAplacer) > strlen($joueur->chevalet) + 1) {
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom  Commande impossible a realiser",
                        'mot' => $motAplacer,
                        "test" => strlen($motAplacer) > strlen($joueur->chevalet)
                    ], 404);
                }


                if ($this->verifierPostionMotValable($lg, $col, $posit, $motAplacer) === false) {
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom  Commande impossible a realiser",
                        'mot' => $motAplacer,
                        "test" => strlen($motAplacer) > strlen($joueur->chevalet)
                    ], 404);

                }

                //&& $this->verfierMotDansChevalet(trim($motAplacer), trim($joueur->chevalet), $partie->grille, $col, $lg, $posit, $ordre)
                //? verifier l'existance des condition
                if ($ligneCommande && $colonneisNumber && $colonneisNumberValid && $pos
                    && $this->verfierMotFranacaisValide(trim($motAplacer))
                ) {
                    //? creer le message dans la base de donnes


                           $resteMotGrille = '';
                           $isOrderOne = true;
                           // convertir la grille en d'une chaine vers un tableau
                           $grillTab = $this->StringToArray($partie->grille);
                           $nouvelGrilleChaine = $partie->grille;
                           // retourner la position du mot dans le tableau (grille sous forme d'un tableau)
                           $posMotTableau = (ord(strtoupper($lg)) - ord('A')) * 15 + ($col - 1);
                           // variable mot grille
                           $TabmotGrille = [];
                           $chaineGrille = '';
                           $motCopie = $motAplacer;
                           $reserve = $partie->reserve;

                           switch ($posit) {

                               case 'v' :
                                   for ($i = $posMotTableau, $iMax = ((ord(strtoupper($ligne)) - ord('A')) + strlen($mot) - 1) * 15 + ($colonne - 1); $i <= $iMax; $i += 15) {
                                       $chaineGrille .= $grillTab[$i];
                                       $nouvelGrilleChaine[$i] = $motAplacer[$j];
                                   }

                                   break;
                               case 'h' :
                                   for ($j = 0, $i = $posMotTableau, $iMax = $posMotTableau + strlen($motAplacer); $j < strlen($motAplacer) && $i <= $iMax; $j++, $i++) {
                                       $chaineGrille .= $grillTab[$i];
                                       $nouvelGrilleChaine[$i] = $motAplacer[$j];
                                   }
                                   break;
                           }

                           $x = 0;
                           while ($x < strlen($chaineGrille)) {
                               $char = $chaineGrille[$x];
                               if (str_contains($motCopie, $char)) {
                                   $posCharMot = strpos($motCopie, $char);
                                   $motCopie = substr($motCopie, 0, $posCharMot) . substr($motCopie, $posCharMot + 1);
                                   $x++;
                               }
                           }
                           $resteMotGrille = $motCopie;

                           $reserveMax = min(strlen($partie->reserve), strlen($resteMotGrille));

                           for ($j = 0, $jMax = $reserveMax; $j < $jMax; $j++) {
                               $resteMotGrille .= $reserve[random_int(0, strlen($reserve) - 1)];
                               $strpos = strpos($reserve, $resteMotGrille[$j]);
                               $reserve = substr($reserve, 0, $strpos) . substr($reserve, $strpos + 1);
                           }
                           // grillle === $nouvelGrilleChaine     chavlet=$resteMotGrille     resrve=$reserve*/
                    /*   return new JsonResponse([
                           "grille" => $nouvelGrilleChaine

                       ]);

                       DB::table('parties')->where("idPartie", $partie->idPartie)
                           ->update(["grille" => $nouvelGrilleChaine, "reserve" => $reserve]);
                       DB::table("joueurs")->where("idJoueur", $joueur->idJoueur)->update(["chevalet" => $resteMotGrille]);*/

                    /***********************************************/
                    $message = new Message;
                    $message->contenu = $request->contenu;
                    $message->envoyeur = $request->envoyeur;
                    $message->partie = $request->partie;
                    $message->statutMessage = 0;
                    $message->save();
                    // ? retourner les information de placement de lettres
                    // TODO verfier si la mot est valable dans  le chevalet placer le mot
                    // $this->verfierMotDansChevalet($motAplacer, $joueur->chevalet);


                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom a Placée le mot  $motAplacer",
                        'mot' => $motAplacer,
                        'statutMessage' => $message->statutMessage
                    ], 200);
                }
                return new JsonResponse([
                    "nom" => $joueur->nom,
                    "partie" => $partie->idPartie,
                    'message' => "$joueur->nom  Commande impossible a realiser",
                    'mot' => $motAplacer,
                ], 404);

            } else {
                //? verifier si la ligne est correcte => return boolean
                $ligneCorrecte = in_array($nouvelleCommande[0], $ligneArray, true);
                //? recuperer la colonne => return string
                $colIsNumber = substr($nouvelleCommande, 1, 2);
                //? verifier la colonne est correcte => return boolean
                $coloneCorrecte = is_numeric($colIsNumber) && ((int)$colIsNumber <= 15);
                //? verifier la position est correcte => return boolean
                $posCorrecte = in_array($nouvelleCommande[3], $posArray, true);
                //? verifier si la chaine est inexistante
                $mot = substr($commande, 12);

                // ? verifier si les lettres sont (inclus) dans le chevalet du joueur
                $ligne2 = $nouvelleCommande[0];
                $col2 = substr($nouvelleCommande, 1, 2);
                $pos2 = $nouvelleCommande[3];
//verfierMotDansChevalet($mot, $chevalet, $grille, $colonne, $ligne, $pos)

                if ($this->verfierMotDansChevalet(trim($mot), trim($joueur->chevalet), $partie->grille, $col2, $ligne2, $pos2, $ordre) === false) {
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom  Commande impossible a realiser",
                        'mot' => $mot,
                    ], 404);
                }

                //? verifier l'inexistance des espace entres les caracteres
                // ? la chaine doit etre alphabetique
                //? la longeur du mot doit etre <= longeur de chevalet
                if (str_contains(trim($mot), ' ') || strlen(trim($mot)) < 2 || !ctype_alpha(trim($mot))
                    || (strlen($mot) > strlen($joueur->chevalet) + 1)) {
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom  Commande impossible a realiser",
                        'mot' => $mot,
                        "test" => strlen($mot) > strlen($joueur->chevalet)
                    ], 404);
                }
                if (empty($mot)) {
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom Erreur de syntaxe",
                        'mot' => "$mot",
                    ], 404);
                }
                //? verifier si la mot est en position valable dans la grille
                $verifierMot = $this->verifierPostionMotValable($nouvelleCommande[0], (int)$colIsNumber, $nouvelleCommande[3], $mot);


                //$this->verfierMotDansChevalet(trim($mot), trim($joueur->chevalet), $partie->grille, $col2, $ligne2, $pos2, $ordre)
                // ? tester l'existance des conditions
                if ($ligneCorrecte && $coloneCorrecte && $posCorrecte && $verifierMot && $this->verfierMotFranacaisValide(trim($mot))
                ) {
                    // TODO verfier si la mot est valable dans  le chevalet placer le mot
                    // verfier MotDansChevalet($mot, $chevalet)
                    // ? creer un message dans la base de donnes
                    $message = new Message;
                    $message->contenu = $request->contenu;
                    $message->envoyeur = $request->envoyeur;
                    $message->partie = $request->partie;
                    $message->statutMessage = 0;
                    $message->save();
                    //? retourner le résultat
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom  a Placée le mot $mot",
                        'statutMessage' => $message->statutMessage,
                    ], 200);
                }
                //? retourner Une commande impossible à réaliser
                return new JsonResponse([
                    "nom" => $joueur->nom,
                    "partie" => $partie->idPartie,
                    'message' => "$joueur->nom Une commande impossible à réaliser",
                    'mot' => $mot,
                ], 404);
            }

            //  ============================================CHANGER LETTRE=======================================================================>
            /* Changer des lettres  */
        } elseif ($commandeChanger === "changer") {
            // changer des lettre exemple !changer mw*
            $LettreChanger = substr($commande, 8, strlen($commande));
            if ($this->changerlettres($LettreChanger, $joueur->chevalet, $partie->reserve, $joueur->idJoueur, $partie->idPartie)) {
                // TODO lettre alphabétique et  le contient *
                // TODO verifier si les lettres sont inclus dans le chevalet du joueur
                $message = new Message;
                $message->contenu = $request->contenu;
                $message->envoyeur = $request->envoyeur;
                $message->partie = $request->partie;
                $message->statutMessage = 0;
                $message->save();
                return new JsonResponse([
                    "nom" => $joueur->nom,
                    "partie" => $partie->idPartie,
                    'message' => "$joueur->nom a changer les lettres $LettreChanger",
                    'statutMessage' => "$message->statutMessage",
                    "chevalet" => $joueur->chevalet,
                    "reserve" => $partie->reserve,

                ], 200);

            }
            //return new JsonResponse(['Aucune lettre a changer' => $LettreChanger], 404);
            return new JsonResponse([
                "nom" => $joueur->nom,
                "partie" => $partie->idPartie,
                'message' => "$joueur->nom commande impossible à realiser",
                'mot' => "$LettreChanger",
            ], 404);
            //  ============================================AIDER=======================================================================>

        } elseif ($commandeAider === "aider") {
            $message = new Message;
            $message->contenu = $request->contenu;
            $message->envoyeur = $request->envoyeur;
            $message->partie = $request->partie;
            $message->statutMessage = 0;
            $message->save();
            return new JsonResponse([
                "nom" => $joueur->nom,
                "partie" => $partie->idPartie,
                'statutMessage' => "$message->statutMessage",
                'message' => "!placer g15v bonjour  ===> joue le mot bonjour à la verticale et le b est positionné en g15
                        changer un lettre avec  ===>  !changer mwb : remplace les lettres m, w et b.
                        !changer e*  =>  remplace une seule des lettres e et une lettre blanche
                        Passer son tour ===> !passer
                          Besoin d aide ===>  !aider ",

            ], 200);

        } elseif ($commandeAider === "passer") {
            return true;
        } else {
            return new JsonResponse([
                "nom" => $joueur->nom,
                "partie" => $partie->idPartie,
                'message' => "$joueur->nom fait une commande impossible à realiser",
            ], 404);
        }
    }


    //? changer lettres
    public function changerlettres($lettre, $chevalet, $reserve, $idjoueur, $idpartie)
    {
        $lettres = trim($lettre);

        // ?  La longeur de la chaine doit etre 1 ou 7 lettre au  maximum  et les lettres doivent etre minuscule
        if (ctype_upper($lettres) || $lettres === '' || strlen($lettres) > 7 || str_contains($lettres, ' ')) {
            return false;
        }
        //?  verifier si le chevalet contient des lettres  blanche *
        if (str_contains($lettres, "*") && !str_contains($chevalet, "*")) {
            return false;
        }

        // ? verifier l'existence des lettres dans le chevalet
        $valid = true;
        for ($i = 0, $iMax = strlen($lettres); $i < $iMax; $i++) {
            if (str_contains($chevalet, $lettres[$i]) === false) {
                $valid = false;
                return false;
            }
        }
        $reservefinal = '';
        $i = 0;
        while ($i < strlen($lettres)) {
            // generer des lettres aleatoire dans la reserve
            $posChar = random_int(0, strlen($reserve));
            // recuperer le charactere du  reserve
            $charReserve = $reserve[$posChar];
            // remplacer par un /
            $reserve[$posChar] = '/';
            //  remplacer dans le chevalet
            $posLettreChevalet = strpos($chevalet, $lettres[$i]);
            // replacer lettre dans le chevalet
            str_replace($chevalet[$posLettreChevalet], $charReserve, $chevalet);

            //  remplcaer le / dan sla reserve
            for ($j = 0, $jMax = strlen($reserve); $j < $jMax; $j++) {
                if ($reserve[$j] !== '/') {
                    $reservefinal .= $reserve[$j];
                }

            }
            $reserve = $reservefinal;
            $i++;

        }

// mise a jour dans la base de donnes
        DB::table('joueurs')
            ->where('idJoueur', $idjoueur)
            ->update(['chevalet' => $chevalet]);
        DB::table('parties')
            ->where('idPartie', $idpartie)
            ->update(['reserve' => $reservefinal]);
        return true;

    }



    /*
       // retourner le charactere random

            $char = $reserve[random_int(0, strlen($reserve) - 1)];

            // retourner la posistion  du character de random  de $reserve
            $charPos = strpos($reserve, $char);
            // position lettre dans le chevalet
            $lettrePositionchevalet = strpos($lettres[$i], $lettres);

            // remplacer dans le chavalet
            str_replace($lettres[$i], $char, $chevalet);
            // retirer de reserve
            str_replace($reserve[$charPos], '/', $reserve);
            // parcourir reserve
            $reserveCopie = '';
            for ($j = 0, $jMax = strlen($reserve); $j < $jMax; $j++) {
                if ($reserve[$j] !== '/') {
                    $reserveCopie .= $reserve[$j];
                }

            }
            // mise a jour dans la base de donnes
            DB::table('joueurs')
                ->where('idJoueur', $idjoueur)
                ->update(['chevalet' => $chevalet]);
            DB::table('parties')
                ->where('idPartie', $idpartie)
                ->update(['reserve' => $reserveCopie]);
       */


    //? fonction retirer lettre de chevalet apres un place avec toutes le verification necessaire du chevalet
    // ! les parametres ,$grille,$ligne,$colonne,$pos
    /*
       h6v bonbon
     */


    public function retournerMotGrille($mot, $grille, $colonne, $ligne, $pos)
    {
        $tabMot = str_split($mot);
        // convertir la grille en d'une chaine vers un tableau
        $grillTab = $this->StringToArray($grille);
        // retourner la position du mot dans le tableau (grille sous forme d'un tableau)
        $posMotTableau = (ord(strtoupper($ligne)) - ord('A')) * 15 + ($colonne - 1);
        // variable mot grille
        $TabmotGrille = [];
        $chaineGrille = '';
        switch ($pos) {
            case 'v' :
                for ($i = $posMotTableau, $iMax = strlen($mot); $i <= $iMax; $i += 16) {
                    $chaineGrille .= $grillTab[$i];
                }
                // ? verifier que la longeur mot < longeur chevalet
                $motCopie = $mot;
                $x = 0;
                while ($x < strlen($chaineGrille)) {
                    $char = $chaineGrille[$x];
                    if (str_contains($motCopie, $char)) {
                        $posCharMot = strpos($motCopie, $char);
                        $motCopie = substr($motCopie, 0, $posCharMot) . substr($motCopie, $posCharMot + 1);
                        $x++;
                    }
                }
                return $motCopie;
            case 'h' :
                for ($i = $posMotTableau, $iMax = $posMotTableau + strlen($mot); $i <= $iMax; $i++) {
                    $chaineGrille .= $grillTab[$i];
                }
                // ? verifier que la longeur mot < longeur chevalet
                $motCopie = $mot;
                $x = 0;
                while ($x < strlen($chaineGrille)) {
                    $char = $chaineGrille[$x];
                    if (str_contains($motCopie, $char)) {
                        $posCharMot = strpos($motCopie, $char);
                        $motCopie = substr($motCopie, 0, $posCharMot) . substr($motCopie, $posCharMot + 1);
                        $x++;
                    }
                }
                return $motCopie;


        }
    }


    public function verfierMotDansChevalet($mot, $chevalet, $grille, $colonne, $ligne, $pos, $order): bool
    {
        $resteMotGrille = '';
        $isOrderOne = true;
        // convertir la grille en d'une chaine vers un tableau
        $grillTab = $this->StringToArray($grille);
        // retourner la position du mot dans le tableau (grille sous forme d'un tableau)
        $posMotTableau = (ord(strtoupper($ligne)) - ord('A')) * 15 + ($colonne - 1);
        // variable mot grille
        $TabmotGrille = [];
        $chaineGrille = '';
        $motCopie = $mot;
        switch ($pos) {
            case 'v' :
                for ($i = $posMotTableau, $iMax = ((ord(strtoupper($ligne)) - ord('A')) + strlen($mot) - 1) * 15 + ($colonne - 1); $i <= $iMax; $i += 15) {
                    $chaineGrille .= $grillTab[$i];

                    if ($i === 112) {
                        $isOrderOne = false;
                    }
                }
                // ? verifier que la longeur mot < longeur chevalet

                $x = 0;
                while ($x < strlen($chaineGrille)) {
                    $char = $chaineGrille[$x];
                    if (str_contains($motCopie, $char)) {
                        $posCharMot = strpos($motCopie, $char);
                        $motCopie = substr($motCopie, 0, $posCharMot) . substr($motCopie, $posCharMot + 1);
                        $x++;
                    }
                }
                $resteMotGrille = $motCopie;
                break;
            case 'h' :
                for ($i = $posMotTableau, $iMax = $posMotTableau + strlen($mot); $i <= $iMax; $i++) {
                    $chaineGrille .= $grillTab[$i];
                    if ($i === 112) {
                        $isOrderOne = false;
                    }
                }
                // ? verifier que la longeur mot < longeur chevalet

                $x = 0;
                while ($x < strlen($chaineGrille)) {
                    $char = $chaineGrille[$x];
                    if (str_contains($motCopie, $char)) {
                        $posCharMot = strpos($motCopie, $char);
                        $motCopie = substr($motCopie, 0, $posCharMot) . substr($motCopie, $posCharMot + 1);
                        $x++;
                    }
                }
                $resteMotGrille = $motCopie;
                break;

        }


        // ***************************************************************************************
        //   $resteMotGrille = $this->retournerMotGrille($mot, $grille, $colonne, $ligne, $pos);

        if ($isOrderOne && $order === 1) {
            return false;
        }

        if ($resteMotGrille === '') {
            return false;
        }
        // ? verifier que la longeur mot < longeur chevalet
        $chevaletCopie = $chevalet;
        $x = 0;
        while ($x < strlen($resteMotGrille)) {
            $char = $resteMotGrille[$x];
            if (ctype_upper($char)) {
                $char = '*';
            }
            if (str_contains($chevaletCopie, $char)) {
                $posChar = strpos($chevaletCopie, $char);
                $chevaletCopie = substr($chevaletCopie, 0, $posChar) . substr($chevaletCopie, $posChar + 1);
                $x++;
            } else {
                return false;
            }
        }
        return true;
    }


//? verifier si un mot contient un caractere Majuscule
    public function verifierMotContientLettreMajuscule($mot): bool
    {
        //  verifier si toute la chaine est en Minuscule
        $mot = trim($mot);
        $chaineMinuscule = ctype_lower($mot);
        if ($chaineMinuscule) {
            return true;
        }
        return false;
    }

    public function verifierPostionMotValable($ligne, $colonne, $pos, $mot)
    {
        // g15v bonjour
        $longeurchaine = strlen($mot);
        if ($pos === 'v') {
            $limiteLigne = ord('P') - ord(strtoupper(trim($ligne)));
            return ($limiteLigne >= $longeurchaine);
        }
        $limiteColonne = 16 - $colonne;
        return ($limiteColonne >= $longeurchaine);
    }


    // verifier si le mot est dans grille est-elle dans le chevalet et placer un mot dans la grille
    /* public function placerMot($ligne, $colonne, $pos, $mot, $grille)
     {
         $tabMot = str_split($mot);
         //? convertir la grille en d'une chaine vers un tableau
         $grillTab = $this->StringToArray($grille);
         //? retourner la position du mot dans le tableau (grille sous forme d'un tableau)
         $posMotTableau = (ord(strtoupper($ligne)) - ord('A')) * 15 + ($colonne - 1);
         //? tableau de lettres dans la position de la grille
         $motGrille = [];
         switch ($pos) {
             case 'v' :
                 for ($i = $posMotTableau, $iMax = strlen($mot); $i <= $iMax; $i += 16) {
                     $motGrille[$i] = $grillTab[$i];
                 }
                 //? verifier si le mot dans la grille est disponible dans le mot actuel
                 // ? convertir la chaine de grille
                 // ? verifier si la chaine a placer contient les lettres de la chaine de grille (cas a completer)
                 // ? placer le mot
                 $counter = 0;
                 for ($i = $posMotTableau, $iMax = strlen($mot); $i <= $iMax; $i += 16) {
                     $grillTab[$i] = $motGrille[$counter];
                     $counter++;
                 }
                 return true;
                 break;
             case 'h' :
                 for ($i = $posMotTableau, $iMax = $posMotTableau + strlen($mot); $i <= $iMax; $i++) {
                     $motGrille[$i] = $grillTab[$i];
                 }
                 if (emptyArray(implode($motGrille))) {
                     // si la position de mot dans la grille est vide on la place
                     $counter = 0;
                     for ($i = $posMotTableau, $iMax = $posMotTableau + strlen($mot); $i <= $iMax; $i++) {
                         $grillTab[$i] = $motGrille[$counter];
                         $counter++;
                     }
                     return true;
                 }
                 $counter = 0;
                 for ($i = $posMotTableau, $iMax = $posMotTableau + strlen($mot); $i <= $iMax; $i++) {
                     $grillTab[$i] = $motGrille[$counter];
                     $counter++;
                 }
                 return true;
                 break;


         }
     }*/


    public function verfierMotFranacaisValide($mot)
    {
        return true;
    }

    /*


    StringToArray(grille : any){
      let Arraygrille = grille.split('');
      for (let i=0;i<Arraygrille.length;i++){
        if(Arraygrille[i]=='-'){
          Arraygrille[i]="";
        }
      }
      return Arraygrille;
    }
     */

    public function StringToArray($string)
    {
        $array = str_split($string);
        for ($i = 0, $iMax = strlen($string); $i < $iMax; $i++) {
            if ($array[$i] === '-') {
                $array[$i] = '';
            }
        }
        return $array;
    }

    //? la chaine contient des -
    public function ArrayToString($array)
    {
        $chaine = "";
        for ($i = 0, $iMax = count($array); $i <= $iMax; $i++) {
            if ($array[$i] === "") {
                $chaine .= "-";
            } else {
                $chaine += $chaine[$i];
            }

        }
        return $chaine;

    }


}
