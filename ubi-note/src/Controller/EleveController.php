<?php

namespace App\Controller;

use App\Entity\Eleve;
use App\Repository\EleveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;

/**
 * Class EleveController
 * @package App\Controller
 */
class EleveController
    extends AbstractController
{
    
    /** @var EleveRepository */
    private $eleveRepositary;
    
    /** @var EntityManagerInterface */
    private $entityManager;
    
    /** @var ValidatorInterface */
    private $validator;
    
    /**
     * EleveController constructor.
     *
     * @param EleveRepository        $eleveRepositary
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface     $validator
     */
    public function __construct(EleveRepository $eleveRepositary, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->eleveRepositary = $eleveRepositary;
        $this->entityManager   = $entityManager;
        $this->validator       = $validator;
    }
    
    /**
     * @SWG\Tag(name="eleve")
     * @SWG\Response(
     *     response=400,
     *     description="Bad Parameter",
     *     @SWG\Schema(
     *          @SWG\Property(property="errors", type="array",
     *              @SWG\Items(
     *                  @SWG\Property(property="error_on", type="string"),
     *                  @SWG\Property(property="error_message", type="string"),
     *              ),
     *          ),
     *     )
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Created",
     *     @SWG\Header(type="string",header="Location",description="Lien vers la ressource créée")
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     type="string",
     *     @SWG\Schema(
     *          @SWG\Property(property="nom", type="string", description="Le nom de l'élève (1 à 100 caractères)",),
     *          @SWG\Property(property="prenom", type="string", description="Le prénom de l'élève (1 à 30 caractères)"),
     *          @SWG\Property(property="date_naissance", type="string", description="La date de naissance de l'élève (en texte, peut importe le format tant que c'est une date valide)"),
     *
     *     )
     * )
     * @SWG\Parameter(
     *     name="nom",
     *     in="query",
     *     type="string",
     *     description="Le nom de l'élève (1 à 100 caractères)",
     * )
     * @SWG\Parameter(
     *     name="prenom",
     *     in="query",
     *     type="string",
     *     description="Le prénom de l'élève (1 à 30 caractères)"
     * )
     * @SWG\Parameter(
     *     name="date_naissance",
     *     in="query",
     *     type="string",
     *     description="La date de naissance de l'élève (en texte, peut importe le format tant que c'est une date valide)"
     * )
     * @Route("/api/eleve", name="eleve-post", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function postEleveAction(Request $request)
    {
        //instancier et peuple l'eleve
        $eleve = new Eleve();
        $eleve->setNom($request->get('nom', null));
        $eleve->setPrenom($request->get('prenom', null));
        $eleve->setDateNaissance($request->get('date_naissance', null));
        
        dd($eleve);
        
        //valider et enregister si tout est ok
        $errors = $this->validator->validate($eleve);
        if (count($errors) === 0) {
            $this->entityManager->persist($eleve);
            $this->entityManager->flush();
            
            return new Response(null, 201, ["Location" => $this->generateUrl('eleve-get', ['id' => $eleve->getId()]),]);
        }
        
        //listes les erreurs et renvoyer une erreur 400
        $detailed_errors = [];
        foreach ($errors as $error) {
            array_push($detailed_errors, ['error_on' => $error->getPropertyPath(), 'error_message' => $error->getMessage()]);
        }
        
        return new Response(json_encode(["errors" => $detailed_errors]), 400, ['Content-Type' => 'application/json']);
    }
    
    /**
     * @SWG\Tag(name="eleve")
     * @SWG\Response(
     *     response=400,
     *     description="Bad Parameter",
     *     @SWG\Schema(
     *          @SWG\Property(property="errors", type="array",
     *              @SWG\Items(
     *                  @SWG\Property(property="error_on", type="string"),
     *                  @SWG\Property(property="error_message", type="string"),
     *              ),
     *          ),
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Response(
     *     response=204,
     *     description="No Content",
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     type="string",
     *     @SWG\Schema(
     *          @SWG\Property(property="nom", type="string", description="Le nom de l'élève (1 à 100 caractères)",),
     *          @SWG\Property(property="prenom", type="string", description="Le prénom de l'élève (1 à 30 caractères)"),
     *          @SWG\Property(property="date_naissance", type="string", description="La date de naissance de l'élève (en texte, peut importe le format tant que c'est une date valide)"),
     *
     *     )
     * )
     * @SWG\Parameter(
     *     name="nom",
     *     in="query",
     *     type="string",
     *     description="Le nom de l'élève (1 à 100 caractères)",
     *     @SWG\Schema(
     *          @SWG\Property(property="nom", type="string"),
     *     )
     * )
     * @SWG\Parameter(
     *     name="prenom",
     *     in="query",
     *     type="string",
     *     description="Le prénom de l'élève (1 à 30 caractères)"
     * )
     * @SWG\Parameter(
     *     name="date_naissance",
     *     in="query",
     *     type="string",
     *     description="La date de naissance de l'élève (en texte, peut importe le format tant que c'est une date valide)"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Id de l'élève",
     * )
     * @Route("/api/eleve/{id}", name="eleve-put", methods={"PUT"})
     * @param int     $id
     * @param Request $request
     *
     * @return Response
     */
    public function putEleveAction(int $id, Request $request): Response
    {
        //fetch l'eleve
        $eleve = $this->eleveRepositary->find($id);
        if ($eleve === null) {
            return new Response('', 404);
        }
        
        //repeupler l'eleve
        if ($request->query->has('nom')) {
            $eleve->setNom($request->get('nom', null));
        }
        if ($request->query->has('prenom')) {
            $eleve->setPrenom($request->get('prenom', null));
        }
        if ($request->query->has('date_naissance')) {
            $eleve->setDateNaissance($request->get('date_naissance', null));
        }
        
        //valider et enregister si tout est ok
        $errors = $this->validator->validate($eleve);
        if (count($errors) === 0) {
            $this->entityManager->persist($eleve);
            $this->entityManager->flush();
            
            return new Response(null, 204);
        }
        
        //listes les erreurs et renvoyer une erreur 400
        $detailed_errors = [];
        foreach ($errors as $error) {
            array_push($detailed_errors, ['error_on' => $error->getPropertyPath(), 'error_message' => $error->getMessage()]);
        }
        
        return new Response(json_encode(["errors" => $detailed_errors]), 400, ['Content-Type' => 'application/json']);
    }
    
    /**
     * @SWG\Tag(name="eleve")
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Response(
     *     response=204,
     *     description="No Content",
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Id de l'élève",
     * )
     * @Route("/api/eleve/{id}", name="eleve-delete", methods={"DELETE"})
     * @param int $id
     *
     * @return Response
     */
    public function deleteEleveAction(int $id): Response
    {
        //fetch l'eleve
        $eleve = $this->eleveRepositary->find($id);
        if ($eleve === null) {
            return new Response('', 404);
        }
        
        $this->entityManager->remove($eleve);
        $this->entityManager->flush();
        
        return new Response('', 204);
    }
    
    /**
     * @SWG\Tag(name="eleve")
     * @SWG\Response(
     *     response=400,
     *     description="Bad Parameter",
     *     @SWG\Schema(
     *          @SWG\Property(property="errors", type="array",
     *              @SWG\Items(
     *                  @SWG\Property(property="error_on", type="string"),
     *                  @SWG\Property(property="error_message", type="string"),
     *              ),
     *          ),
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *          @SWG\Property(property="id", type="integer"),
     *          @SWG\Property(property="nom", type="string"),
     *          @SWG\Property(property="prenom", type="string"),
     *          @SWG\Property(property="date_naissance", type="array",
     *              @SWG\Items(
     *                  @SWG\Property(property="date", type="string"),
     *                  @SWG\Property(property="timezone_type", type="integer"),
     *                  @SWG\Property(property="timezone", type="string"),
     *              ),
     *          ),
     *          @SWG\Property(property="links", type="array",
     *              @SWG\Items(
     *                  @SWG\Property(property="rel", type="string"),
     *                  @SWG\Property(property="href", type="string"),
     *              ),
     *          ),
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Id de l'élève",
     * )
     * @Route("/api/eleve/{id}", name="eleve-get", methods={"GET"})
     * @param int $id
     *
     * @return Response
     */
    public function getEleveAction(int $id)
    {
        //fetch l'eleve
        $eleve = $this->eleveRepositary->find($id);
        if ($eleve === null) {
            return new Response('', 404);
        }
        
        $data = [
            'id'             => $eleve->getId(),
            'nom'            => $eleve->getNom(),
            'prenom'         => $eleve->getPrenom(),
            'date_naissance' => $eleve->getDateNaissance(),
            'links'          => [
                [
                    'rel'  => 'self',
                    'href' => $this->generateUrl('eleve-get', ['id' => $eleve->getId()]),
                ],
                [
                    'rel'  => 'moyenne',
                    'href' => $this->generateUrl('moyenne-get-eleve', ['id' => $eleve->getId()]),
                ],
            ],
        ];
        
        return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
    }
    
}