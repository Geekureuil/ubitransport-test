<?php
/**
 * Created by PhpStorm.
 * User: Xavier
 * Date: 30/03/2020
 * Time: 22:37
 */

namespace App\Controller;

use App\Entity\Note;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EleveRepository;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;

/**
 * Class NoteController
 * @package App\Controller
 */
class NoteController
    extends AbstractController
{
    
    /** @var EleveRepository */
    private $eleveRepositary;
    
    /** @var NoteRepository */
    private $noteRepositary;
    
    /** @var EntityManagerInterface */
    private $entityManager;
    
    /** @var ValidatorInterface */
    private $validator;
    
    /**
     * NoteController constructor.
     *
     * @param EleveRepository        $eleveRepositary
     * @param NoteRepository         $noteRepositary
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface     $validator
     */
    public function __construct(EleveRepository $eleveRepositary, NoteRepository $noteRepositary, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->eleveRepositary = $eleveRepositary;
        $this->noteRepositary  = $noteRepositary;
        $this->entityManager   = $entityManager;
        $this->validator       = $validator;
    }
    
    /**
     * @SWG\Tag(name="note")
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
     *     description="Not found",
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Created",
     *     @SWG\Header(type="string",header="Location",description="Lien vers la ressource créée"),
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     type="string",
     *     @SWG\Schema(
     *          @SWG\Property(property="matiere", type="string", description="Le nom de la matière (1 à 100 caractères)"),
     *          @SWG\Property(property="note", type="number", description="La note obtenue (de 0 à 20)"),
     *     )
     * )
     * @SWG\Parameter(
     *     name="matiere",
     *     in="query",
     *     type="string",
     *     description="Le nom de la matière (1 à 100 caractères)",
     * )
     * @SWG\Parameter(
     *     name="note",
     *     in="query",
     *     type="number",
     *     description="La note obtenue (de 0 à 20)",
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Id de l'élève",
     * )
     * @Route("/api/eleve/{id}/note", name="note-post", methods={"POST"})
     * @param         $id
     * @param Request $request
     *
     * @return Response
     */
    public function postNote($id, Request $request): Response
    {
        //fetch l'eleve
        $eleve = $this->eleveRepositary->find($id);
        if ($eleve === null) {
            return new Response('', 404);
        }
        
        //instancier et peuple la note
        $note = new Note();
        $note->setEleve($eleve);
        $note->setMatiere($request->get('matiere', null));
        $note->setNote($request->get('note', null));
        
        //valider et enregister si tout est ok
        $errors = $this->validator->validate($note);
        if (count($errors) === 0) {
            $this->entityManager->persist($note);
            $this->entityManager->flush();
            
            return new Response(null, 201, ["Location" => $this->generateUrl('note-get', ['id_eleve' => $eleve->getId(), 'id_note' => $note->getId()]),]);
        }
        
        //listes les erreurs et renvoyer une erreur 400
        $detailed_errors = [];
        foreach ($errors as $error) {
            array_push($detailed_errors, ['error_on' => $error->getPropertyPath(), 'error_message' => $error->getMessage()]);
        }
        
        return new Response(json_encode(["errors" => $detailed_errors]), 400, ['Content-Type' => 'application/json']);
    }
    
    /**
     * @SWG\Tag(name="note")
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *          @SWG\Property(property="id", type="integer"),
     *          @SWG\Property(property="matière", type="string"),
     *          @SWG\Property(property="note", type="number"),
     *          @SWG\Property(property="links", type="array",
     *              @SWG\Items(
     *                  @SWG\Property(property="rel", type="string"),
     *                  @SWG\Property(property="href", type="string"),
     *              ),
     *          ),
     *     )
     * )
     * @SWG\Parameter(
     *     name="id_eleve",
     *     in="path",
     *     type="integer",
     *     description="Id de l'élève",
     * )
     * @SWG\Parameter(
     *     name="id_note",
     *     in="path",
     *     type="integer",
     *     description="Id de la note",
     * )
     * @Route("/api/eleve/{id_eleve}/note/{id_note}", name="note-get", methods={"GET"})
     * @param     $id_eleve
     * @param     $id_note
     *
     * @return Response
     */
    public function getNote($id_eleve, $id_note)
    {
        //fetch la note
        $note = $this->noteRepositary->find($id_note);
        if ($note === null) {
            return new Response('', 404);
        }
        
        //si la note n'appartient pas à l'élève, l'url est fausse on renvoie une 404
        if ($note->getEleve()
                 ->getId() != $id_eleve) {
            return new Response('', 404);
        }
        
        $data = [
            'id'      => $note->getId(),
            'matiere' => $note->getMatiere(),
            'note'    => $note->getNote(),
            'links'   => [
                [
                    'rel'  => 'self',
                    'href' => $this->generateUrl('note-get', ['id_eleve' => $id_eleve, 'id_note' => $id_note]),
                ],
                [
                    'rel'  => 'eleve',
                    'href' => $this->generateUrl('eleve-get', ['id' => $id_eleve]),
                ],
            ],
        ];
        
        return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
    }
    
    /**
     * @SWG\Tag(name="note")
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *          @SWG\Property(property="moyenne", type="number"),
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
     * @Route("/api/eleve/{id}/moyenne", name="moyenne-get-eleve", methods={"GET"})
     * @param                 $id
     *
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMoyenneEleve($id): Response
    {
        //check si eleve existe
        if ($this->eleveRepositary->count(['id' => $id]) === 0) {
            return new Response('', 404);
        }
        
        $data = [
            'moyenne' => $this->noteRepositary->getMoyenneEleve($id),
            'links'   => [
                [
                    'rel'  => 'self',
                    'href' => $this->generateUrl('moyenne-get-eleve', ['id' => $id]),
                ],
                [
                    'rel'  => 'eleve',
                    'href' => $this->generateUrl('eleve-get', ['id' => $id]),
                ],
            ],
        ];
        
        return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
    }
    
    /**
     * @SWG\Tag(name="note")
     * @SWG\Response(
     *     response=404,
     *     description="Not Found",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *          @SWG\Property(property="moyenne", type="number"),
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
     * @Route("/api/moyenne-generale", name="moyenne-get-generale", methods={"GET"})
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMoyenneGenerale(): Response
    {
        $data = [
            'moyenne' => $this->noteRepositary->getMoyenneGenerale(),
            'links'   => [
                [
                    'rel'  => 'self',
                    'href' => $this->generateUrl('moyenne-get-generale'),
                ],
            ],
        ];
        
        return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
    }
    
}