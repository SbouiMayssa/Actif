<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use App\Entity\Actif;
use App\Entity\Historique;
use App\Form\ActifType;
use App\Repository\ActifRepository;
use App\Repository\HistoriqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use \DateTime;
use \DateTimeInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[
    Route('/admin'),
    IsGranted('ROLE_ADMIN')
    ]
final class ActifController extends AbstractController
{
    private $actifRepository;

    // Injection du repository dans le contrôleur via le constructeur
    public function __construct(ActifRepository $actifRepository)
    {
        $this->actifRepository = $actifRepository;
    }

    #[Route('', name: 'all_actif')]
    public function index(ActifRepository $Actif): Response
    {
        return $this->render('actif/AllActif.html.twig', [
            'actifs' => $Actif->findBy(['DeletedAt' => null]),
        ]);
    }



   

    #[Route('/actif/add', name: 'add_actif')]
    public function AddAsset(EntityManagerInterface $manager, Request $request)
    {
        $actif = new Actif();
        $form = $this->createForm(ActifType::class, $actif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $actif->setCreatedBy($this->getUser());
                $manager->persist($actif);
                $manager->flush();

                // Enregistrer l'historique
                $historique = new Historique();
                $historique->setActif($actif);
                $historique->setAction('Création');
                $historique->setDateAction(new \DateTimeImmutable());
                $historique->setActionneur($this->getUser());
                $historique->setDetails(['message' => "Actif créé"]);

                $manager->persist($historique);
                $manager->flush();

                $this->addFlash('success', "L'actif est ajouté avec succès");
                return $this->redirectToRoute('all_actif');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', "Erreur : Un actif avec ce numéro de série existe déjà !");
            } catch (\Exception $e) {
                $this->addFlash('error', "Une erreur est survenue lors de l'ajout de l'actif.");
            }
        }

        return $this->render('actif/add.html.twig', [
            'form' => $form->createView(),
            'action' => 'Ajouter',
        ]);
    }



    #[Route('/actif/sort/{criteria}', name: 'sort_actif', methods: ['GET'])]
    public function sort(string $criteria, ActifRepository $actifRepository): Response
    {
         switch ($criteria) {
                case 'type':
                    $actifs = $actifRepository->findAllSortedByType();
                    break;
                case 'date':
                    $actifs = $actifRepository->findAllSortedByDateAcquisation();
                    break;
                default:
                    $actifs = $actifRepository->findActifsEnPanne();
            }
    
            return $this->render('actif/sort.html.twig', [
                'actifs' => $actifs,
            ]);
        }



    #[Route('/actif/edit/{id<\d+>}', name:'actif_edit')]
    public function editActif(EntityManagerInterface $manager, Request $request, Actif $actif, $id, Security $security){
        if ($actif->getDeletedAt() !== null) {
            $this->addFlash('error', "L'actif $id est archivé et ne peut pas être modifié.");
            return $this->redirectToRoute('all_actif');
        }

        $form = $this->createForm(ActifType::class, $actif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($actif);
            $manager->flush();

            $historique = new Historique();
            $historique->setActif($actif);
            $historique->setAction('Modification');
            $historique->setDateAction(new \DateTimeImmutable());
            $historique->setActionneur($this->getUser());
            $historique->setDetails(['message' => "Actif modifié"]);
            $historique->setEtat($actif->getEtat());

            $manager->persist($historique);
            $manager->flush();

            $this->addFlash('success', "L'actif de $id est modifié avec succès");
            return $this->redirectToRoute('all_actif');
        }

        return $this->render('actif/edit.html.twig', [
            'form' => $form->createView(),
            'actif' => $actif,
            'action' => 'Modifier',
        ]);
    }


    #[Route('/actif/delete/{id}', name: 'actif_delete')]
public function delete(Actif $actif, ActifRepository $actifRepository,$id,EntityManagerInterface $manager): Response
{

   $actif=$actifRepository->find($id);

    if(!$actif){
        $this->addFlash('error',"Actif n' existe pas ");
        return $this->redirectToRoute('all_actif');
    }
    $actif->setDeletedAt(new \DateTimeImmutable()); 
    $manager->flush();

    $historique = new Historique();
    $historique->setActif($actif);
    $historique->setAction('Suppression');
    $historique->setDateAction(new \DateTimeImmutable());
    $historique->setActionneur($this->getUser());
    $historique->setDetails(['message' => "Actif archivé"]);
    $historique->setEtat($actif->getEtat());
    
    $manager->persist($historique);
    $manager->flush();
    

    $this->addFlash('success', "L'actif a été archivé avec succès.");
    return $this->redirectToRoute('all_actif');
}

#[Route('/actif/search', name: 'actif_s', methods: ['GET'])]
    public function SearchActif(Request $request, ActifRepository $actifRepository): Response
    {
        $query = $request->query->get('q', '');
        $actifs = []; // Ajoutez cela pour initialiser la variable

        if (!empty($query)) {
            $actifs = $actifRepository->searchByNumSerieActif($query);
        }

        return $this->render('actif/searchActif.html.twig', [
            'actifs' => $actifs,
            'query' => $query, // Assurez-vous de transmettre la valeur de la recherche
        ]);
    }
    


    #[Route('/filter/{etat}', name: 'actif_filter', methods: ['GET'])]
    public function filter(string $etat, ActifRepository $actifRepository): Response
    {
        $actifs = $actifRepository->findByEtat($etat);

        return $this->render('actif/filter.html.twig', [
            'actifs' => $actifs,
        ]);
    }

    #[Route('/actif/etat/{id<\d+>}', name: 'actif_etat')]
public function etatActif(Actif $actif): Response
{
    return $this->render('actif/etat.html.twig', [
        'actif' => $actif,
    ]);
}

#[Route('/actif/archive', name: 'actif_archives', methods: ['GET'])]
public function showArchivedActifs(ActifRepository $actifRepository): Response
{
    $actifs = $actifRepository->findAllArchived(); // Récupérer les actifs archivés

    return $this->render('actif/archive.html.twig', [
        'actifs' => $actifs,
    ]);
}

#[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        // Récupérer les statistiques des actifs
        $activeAssets = $this->actifRepository->countActiveAssets();  // Nombre d'actifs fonctionnels
        $faultyAssets = $this->actifRepository->countFaultyAssets();  // Nombre d'actifs en panne
        $replacedAssets = $this->actifRepository->countReplacedAssets(); // Nombre d'actifs remplacés

        // Retourner les données à la vue
        return $this->render('actif/index.html.twig', [
            'active_assets' => $activeAssets,
            'faulty_assets' => $faultyAssets,
            'replaced_assets' => $replacedAssets,
        ]);
    }

    #[Route('/actif/{id}/historique', name: 'actif_historique')]
public function historique(Actif $actif, HistoriqueRepository $historiqueRepository): Response
{
    $historiques = $historiqueRepository->findBy(['actif' => $actif], ['dateAction' => 'DESC']);


    return $this->render('actif/historique.html.twig', [
        'actif' => $actif,
        'historiques' => $historiques,
    ]);
}


}
