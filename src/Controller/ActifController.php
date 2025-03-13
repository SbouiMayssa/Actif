<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Entity\Actif;
use App\Entity\Historique;
use App\Entity\Employer; // Added for employee assignment
use App\Form\ActifType;
use App\Repository\ActifRepository;
use App\Repository\EmployerRepository; // Added for fetching employees
use App\Repository\HistoriqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin'), IsGranted('ROLE_ADMIN')]
final class ActifController extends AbstractController
{
    private $actifRepository;

    public function __construct(ActifRepository $actifRepository)
    {
        $this->actifRepository = $actifRepository;
    }

    #[Route('', name: 'all_actif')]
    public function index(ActifRepository $actifRepository, EmployerRepository $employerRepository): Response
    {
        return $this->render('actif/AllActif.html.twig', [
            'actifs' => $actifRepository->findBy(['DeletedAt' => null]),
            'employees' => $employerRepository->findAll(), // Pass all employers to the template
        ]);
    }

    #[Route('/actif/add', name: 'add_actif')]
    public function AddAsset(EntityManagerInterface $manager, Request $request): Response
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
                $historique->setEtat($actif->getEtat());

                $manager->persist($historique);
                $manager->flush();

                $this->addFlash('success', "L'actif est ajouté avec succès");
                return $this->redirectToRoute('all_actif');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', "Erreur : Un actif avec ce numéro de série existe déjà !");
                return $this->redirectToRoute('all_actif');
            } catch (\Exception $e) {
                $this->addFlash('error', "Une erreur est survenue lors de l'ajout de l'actif.");
            }
        }

        return $this->render('actif/add.html.twig', [
            'form' => $form->createView(),
            'action' => 'Ajouter',
        ]);
    }

    #[Route('/actif/edit/{id<\d+>}', name: 'actif_edit')]
    public function editActif(
        EntityManagerInterface $manager,
        Request $request,
        Actif $actif,
        $id,
        Security $security
    ): Response {
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
    public function delete(Actif $actif, ActifRepository $actifRepository, $id, EntityManagerInterface $manager): Response
    {
        $actif = $actifRepository->find($id);

        if (!$actif) {
            $this->addFlash('error', "Actif n'existe pas ");
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
        $actifs = [];

        if (!empty($query)) {
            $actifs = $actifRepository->searchByNumSerieActif($query);
        }

        return $this->render('actif/searchActif.html.twig', [
            'actifs' => $actifs,
            'query' => $query,
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

    #[Route('/filter/{etat}', name: 'actif_filter', methods: ['GET'])]
    public function filter(string $etat, ActifRepository $actifRepository): Response
    {
        $actifs = $actifRepository->findByEtat($etat);

        return $this->render('actif/filter.html.twig', [
            'actifs' => $actifs,
        ]);
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(Request $request, EmployerRepository $employerRepository): Response
    {
        $activeAssets = $this->actifRepository->countActiveAssets();
        $faultyAssets = $this->actifRepository->countFaultyAssets();
        $replacedAssets = $this->actifRepository->countReplacedAssets();

        // Get search query
        $searchQuery = $request->query->get('employee_search', '');
        $employee = null;

        if (!empty($searchQuery)) {
            $employee = $employerRepository->createQueryBuilder('e')
                ->where('LOWER(e.nom) LIKE LOWER(:query) or LOWER(e.prenom) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $searchQuery . '%')
                ->setMaxResults(1) 
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $this->render('actif/index.html.twig', [
            'active_assets' => $activeAssets,
            'faulty_assets' => $faultyAssets,
            'replaced_assets' => $replacedAssets,
            'search_query' => $searchQuery,
            'employee' => $employee,
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

    #[Route('/actif/archive', name: 'actif_archives', methods: ['GET'])]
    public function showArchivedActifs(ActifRepository $actifRepository): Response
    {
        $actifs = $actifRepository->findAllArchived();

        return $this->render('actif/archive.html.twig', [
            'actifs' => $actifs,
        ]);
    }

    #[Route('/actif/assign/{id}', name: 'actif_assign_employee', methods: ['POST'])]
    public function assignEmployee(
        Actif $actif,
        Request $request,
        EntityManagerInterface $manager,
        EmployerRepository $employerRepository
    ): Response {
        if ($actif->getDeletedAt() !== null) {
            $this->addFlash('error', "L'actif {$actif->getId()} est archivé et ne peut pas être modifié.");
            return $this->redirectToRoute('all_actif');
        }

        $employeeId = $request->request->get('employee');
        $employee = $employerRepository->find($employeeId);

        if (!$employee) {
            $this->addFlash('error', "Employé non trouvé.");
            return $this->redirectToRoute('all_actif');
        }

        // Assign the employee to the actif
        $actif->addUserAssigned($employee);
        $manager->persist($actif);
        $manager->flush();

        // Record the assignment in historique
        $historique = new Historique();
        $historique->setActif($actif);
        $historique->setAction('Assignation');
        $historique->setDateAction(new \DateTimeImmutable());
        $historique->setActionneur($this->getUser());
        $historique->setDetails(['message' => "Actif assigné à {$employee->getNom()} {$employee->getPrenom()}"]);
        $historique->setEtat($actif->getEtat());
        $manager->persist($historique);
        $manager->flush();

        $this->addFlash('success', "L'actif a été assigné à {$employee->getNom()} {$employee->getPrenom()} avec succès.");
        return $this->redirectToRoute('all_actif');
    }
}