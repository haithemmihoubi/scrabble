<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Joueur;
use App\Models\Message;

use App\Models\Partie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * retourner tous les message  triée par dateCreation
     */


    /**
     * @OA\Get(
     *      path="/v1/messages",
     *      operationId="index",
     *      tags={"message"},
     *      summary="la liste des messages",
     *      description="la liste des messages",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */

    public function index()
    {
        return Message::latest('dateCreation')->get();
    }

    /* =====================================================================================================================================
     =====================================================================================================================================
    */


    /**
     * retourner un  message a partir de son  Id
     */

    /**
     *
     * @OA\Get(
     *      path="/v1/message/{idMessage}",
     *      operationId="getMessageById",
     *      tags={"message"},
     *      summary="Trouver un Message a partir  de son id",
     *
     *  @OA\Parameter(
     *      name="idMessage",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      ),
     *   ),
     *    @OA\Response(
     *          response=200,
     *          description="Opération réussie",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="joueur inexistant"
     *   ),
     *  )
     */

    public function getMessageById($idMessage)
    {
        return Message::findOrFail($idMessage);
    }


    /*  =====================================================================================================================================
        =====================================================================================================================================
     */

    /**
     * retourner  Tous les messaege d'unn joueur a partir de son id
     */


    /**
     *
     * @OA\Get(
     *      path="/v1/messages/joueur/{idJoueur}",
     *      operationId="getMessageByPlayerId",
     *      tags={"message"},
     *      summary="Trouver un Message a partir  de l'ID d'un joueur",
     *
     *  @OA\Parameter(
     *      name="idJoueur",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      ),
     *   ),
     *    @OA\Response(
     *          response=200,
     *          description="Opération réussie",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="joueur inexistant"
     *   ),
     *  )
     */


    public function getMessageByPlayerId($idJoueur)
    {
        return Joueur::find($idJoueur)->messages()
            ->where('envoyeur', $idJoueur)
            ->latest('dateCreation')
            ->get();
    }
    /*  =====================================================================================================================================
          =====================================================================================================================================
       */


    /**
     * retourner  Tous les messages a partir d'un partie ID
     */

    /**
     *
     * @OA\Get(
     *      path="/v1/messages/partie/{partieId}",
     *      operationId="getMessageByPartieId",
     *      tags={"message"},
     *      summary="Trouver Tous les  Messages a partir  de l'ID d'une partie",
     *
     *  @OA\Parameter(
     *      name="partieId",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      ),
     *   ),
     *    @OA\Response(
     *          response=200,
     *          description="Opération réussie",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="joueur inexistant"
     *   ),
     *  )
     */


    public function getMessageByPartieId($partieId)
    {
        return Partie::find($partieId)->messages()
            ->where('partie', $partieId)
            ->latest('dateCreation')
            ->get();
    }
    /*  =====================================================================================================================================
          =====================================================================================================================================
       */
    /**
     * retourner  Tous les messages a partir d'un partie ID
     */


    /**
     *
     * @OA\Post(
     *   tags={"message"},
     *   path="/v1/message",
     *     summary="Ecrire un message",
     *   @OA\Response(
     *     response="200",
     *     description="Message envoyé avec succées",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/Message")
     *     )
     *   ),
     *     @OA\Response(
     *          response="422",
     *          description="L'un des champs est invalide",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *      ),
     *
     *
     *   @OA\RequestBody(
     *     description="Creer un Message avec son contenu,envoyeur,partie ",
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/Message")
     *     )
     *   )
     * )
     *
     */

    public function creerMessage(Request $request)
    {
        $ligneArray = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o"];
        $posArray = ["h", "v"];
        $joueur = Joueur::find($request->envoyeur);
        $partie = Partie::find($request->partie);

        $envoyeur = Joueur::find($request->envoyeur);
        //verifier si le joeuur est deja partant de la partie courante
        if ($envoyeur->partie === $request->partie) {
            // verifier le type de la commande
            $commande = trim($request->contenu);
            if (str_starts_with($commande, "!")) {
                //verifier si  c'est une commande placer EXEMPLE  !placer g15v bonjour
                $commandePlacer = substr($commande, 1, 6);
                $commandeChanger = substr($commande, 1, 7);
                $commandePasser = substr($commande, 1, 7);
                $commandeAider = substr($commande, 1, 5);

                if ($commandePlacer === "placer") {
                    // la taille de la commande <=12
                    if (strlen($commande) <= 11) {
                        return new JsonResponse([
                            "nom" => $joueur->nom,
                            "partie" => $partie->idPartie,
                            'message' => "$joueur->nom  vous devez entrer une une commande correcte ",
                        ], 404);
                    }
                    //nouvelle commande contiennent par  EXEMPLE g15v
                    $nouvelleCommande = substr($commande, 8, 4);
                    // verifier si longeur de la chaine est  égale a 3 exemple g5v
                    if ($nouvelleCommande[strlen($nouvelleCommande) - 1] === ' ') {

                        // verification de LIGNE  COLONNE POSITION
                        $lg = $nouvelleCommande[0];
                        /* 1 boolean */
                        $ligneCommande = in_array($lg, $ligneArray);
                        /* 2 boolean */
                        $colonneisNumber = is_numeric($nouvelleCommande[1]);
                        $colonneisNumberValid = (((int)$nouvelleCommande[1]) <= 9) && ((int)$nouvelleCommande[1] >= 1);
                        /* 3 boolean */
                        $pos = in_array($nouvelleCommande[2], $posArray);
                        /* 4 boolean */
                        // trouver le mot a remplacer
                        $motAplacer = substr($commande, 11, strlen($commande));
                        $chaine = is_string(substr($commande, 11, strlen($commande)));
                        // chaine est inexistante
                        if (empty($motAplacer) || substr_count($commande, ' ') !== 2) {
                            return new JsonResponse([
                                "nom" => $joueur->nom,
                                "partie" => $partie->idPartie,
                                "mot" => "Le mot a  placée est Inexistante"
                            ], 404);
                            //return new JsonResponse(['le mot a  placer est  inexistante' => "ok"], 404);
                        }
                        // TODO verfier si la chaine est correcte ou non
                        // TODO changer la valeur de statutMessage=false dans la base de donnes
                        if ($ligneCommande && $colonneisNumber && $colonneisNumberValid && $pos) {
                            return new JsonResponse([
                                "nom" => $joueur->nom,
                                "partie" => $partie->idPartie,
                                'message' => "$joueur->nom a Placée le mot  $motAplacer",
                                'mot' => "$motAplacer",
                            ],
                                200);
                        }
                        // message erreur de ligne

                        if (!$ligneCommande) {
                            return new JsonResponse([
                                "nom" => $joueur->nom,
                                "partie" => $partie->idPartie,
                                'message' => "$joueur->nom  vous devez entrer une ligne correcte ",
                                'mot' => "$motAplacer",
                            ], 404);
                        }

                        // message erreur de colonne
                        if (!$colonneisNumber) {
                            return new JsonResponse([
                                "nom" => $joueur->nom,
                                "partie" => $partie->idPartie,
                                'message' => "$joueur->nom vous devez entrer une colonne correcte ",
                                'mot' => "$motAplacer",
                            ], 404);
                        }
                        // message erreur de erreur position
                        if (!$pos) {
                            return new JsonResponse([
                                "nom" => $joueur->nom,
                                "partie" => $partie->idPartie,
                                'message' => "$joueur->nom  vous devez entrer une position correcte (h,v)",
                                'mot' => "$motAplacer",
                            ], 404);
                        }


                        return new JsonResponse([
                            "nom" => $joueur->nom,
                            "partie" => $partie->idPartie,
                            'message' => "$joueur->nom  probleme de ligne ou colonne ou ",
                            'mot' => "$motAplacer",
                        ], 404);

                    } else {
                        $ligneCorrecte = in_array($nouvelleCommande[0], $ligneArray, true);
                        $colIsNumber = substr($nouvelleCommande, 1, 2);
                        $coloneCorrecte = is_numeric($colIsNumber) && ((int)$colIsNumber <= 15);
                        $posCorrecte = in_array($nouvelleCommande[3], $posArray, true);
                        // chaine est inexistante
                        $mot = substr($commande, 12);
                        if (empty($mot)) {
                            //return new JsonResponse(['le mot  placer  inexistante' => "ok"], 404);

                            return new JsonResponse([
                                "nom" => $joueur->nom,
                                "partie" => $partie->idPartie,
                                'message' => "$joueur->nom vous devez taper le mot a placée",
                                'mot' => "$mot",
                            ], 404);
                        }


                        // TODO verfier si la chaine est correcte ou non
                        // TODO changer la valeur de statutMessage=false dans la base de donnes
                        if ($ligneCorrecte && $coloneCorrecte && $posCorrecte) {
                            //return new JsonResponse(['commande placer correcte' => "ok"], 200);
                            return new JsonResponse([
                                "nom" => $joueur->nom,
                                "partie" => $partie->idPartie,
                                'message' => "$joueur->nom  a Placée le mot $mot",
                                'mot' => "$mot",
                            ], 200);
                        }
                        //return new JsonResponse(['Erreur' => "commande placer erronée"], 200);
                        return new JsonResponse([
                            "nom" => $joueur->nom,
                            "partie" => $partie->idPartie,
                            'message' => "$joueur->nom a entrée une commande placer erronée",
                            'mot' => "$mot",
                        ], 404);
                    }

                    /* Changer des lettres  */
                } elseif ($commandeChanger === "changer") {
                    // changer des lettre exemple !changer mw*
                    $LettreChanger = substr($commande, 8, strlen($commande));
                    if (!empty($LettreChanger)) {
                        // TODO lettre alphabetiue et  le contiennet *
                        // TODO verifier si les lettres sont inclus dans le chavalet du joueur
                        // return new JsonResponse(['Les lettres a echanger' => $LettreChanger], 200);
                        return new JsonResponse([
                            "nom" => $joueur->nom,
                            "partie" => $partie->idPartie,
                            'message' => "$joueur->nom a changer les lettres $LettreChanger",
                            'mot' => "$LettreChanger",
                        ], 200);

                    }
                    //return new JsonResponse(['Aucune lettre a changer' => $LettreChanger], 404);
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "$joueur->nom vous devez taper des lettres a échangé ",
                        'mot' => "$LettreChanger",
                    ], 404);

                } elseif ($commandeAider === "aider") {
                    return new JsonResponse([
                        "nom" => $joueur->nom,
                        "partie" => $partie->idPartie,
                        'message' => "!placer g15v bonjour  ===> joue le mot bonjour à la verticale et le b est positionné en g15
                        changer un lettre avec  ===>  !changer mwb : remplace les lettres m, w et b.
                        !changer e*  =>  remplace une seule des lettres e et une lettre blanche
                        Passer son tour ===> !passer
                          Besoin d aide ===>  !aider "
                    ], 200);

                }

            } else {
                // ajouter le message ordinire
                $message = Message::create($request->all());
                return new JsonResponse($message);
            }

        } else {
            //  return new JsonResponse(['Erreur' => "Impossible d'envoyer un message dans cette partie"], 404);
            return new JsonResponse([
                "nom" => $joueur->nom,
                "partie" => $partie->idPartie,
                'message' => "$joueur->nom vous n'êtes pas autorisée a envoyer des message dans cette partie",
            ], 404);
        }

    }


    public function verifierPostionMotValable($ligne, $colonne, $pos, $mot)
    {
        //TODO verier si LE mot est existante dans le chevalet
        //! (ord('O')-ord(strtoupper($ligne)))*15+$colonne-1
        // g15v bonjour
        $longeurchaine = strlen($mot);
        if ($pos === 'v') {
            $limiteLigne = ord('P') - ord(strtoupper($ligne));
            return $limiteLigne >= $longeurchaine;
        }
        $limiteColonne = 16 - $colonne;
        return $limiteColonne >= $longeurchaine;

    }


// verifier si le mot est dans grille est-elle dans le chevalet
/*    public function placerMot($ligne, $colonne, $pos, $mot, $grille)
    {
        // retourner le position  du mot dans le tableau du  mot
        $posCoLigGrille = (ord('O') - ord(strtoupper($ligne))) * 15 + $colonne - 1;
     // ajouter +16 dans la boucle
        $motGrille = [];
        switch ($pos) {

            case 'v' :
                for ($i = $posCoLigGrille, $iMax = $posCoLigGrille + strlen($mot); $i <= $iMax; $i++) {
                    array_push($motGrille, $grille[$i]);
                }


        }

    }*/


    public function verifiermotvalide($mot)
    {


    }

    public function verifierlettre($lettre)
    {

    }


}
