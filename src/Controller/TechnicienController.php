<?php

namespace App\Controller;
use App\Entity\Actif;
use App\Entity\Historique;
use App\Repository\ActifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/technicien/actif')]

final class TechnicienController extends AbstractController
{

    #[Route('', name: 'actif')]
    public function index(ActifRepository $actifRepository): Response
    {
        $actifs = $actifRepository->findActifsEnPanne();
        return $this->render('technicien/index.html.twig', [
            'actifs' => $actifs,
        ]);
    }  

    #[Route('/updatetat/{id}', name: 'update_etat', methods: ['POST'])]
    public function updateEtat(Request $request, Actif $actif, EntityManagerInterface $em): Response
    { 
        $etat = $request->request->get('etat');

        if ($etat==null) {
            $this->addFlash('error', "L'état ne peut pas être vide.");
            return $this->redirectToRoute('search_actif');
        }
            
            $actif->setEtat($etat);
            // $em->persist($actif);
             $em->flush();
            $historique = new Historique();
            $historique->setActif($actif);
            $historique->setAction('Modification');
            $historique->setDateAction(new \DateTimeImmutable());
            $historique->setActionneur($this->getUser());
            $historique->setDetails(['message' => "Actif modifié"]);
            $historique->setEtat($actif->getEtat());

            $em->persist($historique);
            $em->flush();

            $this->addFlash('success', "L'état de l'actif a été mis à jour avec succès.");
        

        return $this->redirectToRoute('actif');
    }


    #[Route('/search', name: 'search_actif', methods: ['GET'])]
    public function SearchActif(Request $request, ActifRepository $actifRepository): Response
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            // si le field input est vide
            $actifs = $actifRepository->findActifsEnPanne();
        } else {
            // si vous avez saisir le num de serie
            $actifs = $actifRepository->searchByNumSerie($query);
        }

        return $this->render('technicien/index.html.twig', [
            'actifs' => $actifs,
            'query' => $query,
        ]);
    }






    #[Route('/sort/{criteria}', name: 'actif_sort', methods: ['GET'])]
    public function sort(string $criteria, ActifRepository $actifRepository): Response
    {
        switch ($criteria) {
            case 'type':
                $actifs = $actifRepository->findActifsEnPanneSortedByType();
                break;
            case 'date':
                $actifs = $actifRepository->findActifsEnPanneSortedByDateAcquisation();
                break;
            default:
                $actifs = $actifRepository->findActifsEnPanne();
        }

        return $this->render('technicien/sort.html.twig', [
            'actifs' => $actifs,
        ]);
    }

}
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*#[Route('/pannes', name: 'actif_panne')]
    public function ActifPanne(ActifRepository $actif): Response
    {
        $actifs=$actif->findBy(['etat'=>'en panne']);
        return $this->render('technicien/ActifPanne.html.twig', [
            'actifs' => $actifs,
        ]);
    }

    #[Route('/updatetat/{id}',name:'update_name')]
    public function updateEtat(Request $request,Actif $actif,EntityManagerInterface $em,$id):Response{
        $etat=$request->request->get('etat');
        if($etat){
            $actif->setEtat($etat);
            $em->flush();
            $this->addFlash('sucess',"Etat de l'actif $id est mis à jour avec succés");
        }
        return $this->redirectToRoute('actif_panne');
    }

    #[Route('/search',name:'search_actif')]
    public function SearchActif(Request $request, ActifRepository $actifRepository): Response
    {
        $query=$request->query->get('q');
        $actifs=$actifRepository->searchByName($query);

        return $this->render('technicien/SearchActif.html.twig', [
            'actifs' => $actifs,
            'query' => $query,
        ]);
    }

/*
    // 📌 Trier les actifs
    #[Route('/sort/{criteria}', name: 'technicien_actif_sort', methods: ['GET'])]
    public function sort(string $criteria, ActifRepository $actifRepository): Response
    {
        $actifs = match ($criteria) {
            'type' => $actifRepository->findBy([], ['type' => 'ASC']),
            'date' => $actifRepository->findBy([], ['dateAcquisation' => 'DESC']),
            default => $actifRepository->findAll(),
        };

        return $this->render('technicien/sort.html.twig', [
            'actifs' => $actifs,
        ]);
    }

    // 📌 Filtrer les actifs par état
    #[Route('/filter/{etat}', name: 'technicien_actif_filter', methods: ['GET'])]
    public function filter(string $etat, ActifRepository $actifRepository): Response
    {
        $actifs = $actifRepository->findBy(['etat' => $etat]);

        return $this->render('technicien/filter.html.twig', [
            'actifs' => $actifs,
        ]);
}}*/


