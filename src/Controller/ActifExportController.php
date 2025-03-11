<?php

namespace App\Controller;

use App\Entity\Actif;
use App\Entity\Historique;
use App\Repository\ActifRepository;
use App\Repository\EmplacementRepository;
use App\Repository\EmployerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Form\ImportExcelType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ActifExportController extends AbstractController
{
    #[Route('/actif/export', name: 'actif_export')]
    public function export(ActifRepository $actif): Response
    {
        // Récupérer les actifs depuis la base
        $actifs = $actif->findBy(['DeletedAt' => null]);

        // Créer un nouveau fichier Excel
        $fexcel = new Spreadsheet();
        $pagexcel = $fexcel->getActiveSheet();

        // Définir le nom des colonnes
        $nomcolonne = ['ID', 'Nom', 'Type', 'Numéro de série', 'État', 'Localisation', 'Affecté à', 'Date d’acquisition'];
        $pagexcel->fromArray([$nomcolonne], null, 'A1');

        // Remplir le fichier avec les données des actifs
        $row = 2;
        foreach ($actifs as $act) {

            $userAssigned = $act->getUserAssigned();
            $userNames = $userAssigned->isEmpty() 
            ? 'Non affecté' 
            :  implode(', ', $userAssigned->map(function ($user) {
                return $user->getNom();
            })->toArray());//convertit la collection en chaine des noms separé par virgule
            
            $pagexcel->fromArray([
                $act->getId(),
                $act->getNom(),
                $act->getType(),
                $act->getNumSerie(),
                $act->getEtat(),
                $act->getLocation() ? $act->getLocation()->getBatiment() : 'Non spécifié',
                $userNames,
                $act->getDateAcquisation()?->format('Y-m-d'),
            ], null, "A$row");
            $row++;
        }

        // Créer une réponse pour télécharger le fichier
        $response = new StreamedResponse(function () use ($fexcel) {
            $writer = new Xlsx($fexcel);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="actifs.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    #[Route('/actif/import', name: 'actif_import')]
    public function import(
        Request $request,
        EntityManagerInterface $em,
        EmplacementRepository $emplacementRepo,
        EmployerRepository $employerRepo,
        UserRepository $userRepo
    ): Response {
        $form = $this->createForm(ImportExcelType::class);
        //gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                     
            //recupérer le fichier excel uploadé
            $file = $form->get('file')->getData();

            if ($file) {
                //charger le fichier excel en mémoire
                $spreadsheet = IOFactory::load($file->getPathname());
                //recupérer le feuille active
                $page  = $spreadsheet->getActiveSheet();

                //mettre les donneés dans un tableau 
                $rows = $page->toArray();

                //ignore la premiere ligne du fichier excel et boucle sur chaque ligne restant
                foreach (array_slice($rows, 1) as $index => $row) {
                    //verifier chaque ligne ontient tous les colonne  
                    if (!isset($row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7])) {
                        //si une colonne est obligatoire manquante on passe a la ligne suivante  
                        continue;
                    }

                    $actif = new Actif();
                    $actif->setNom($row[1]);
                    $actif->setType($row[2]);
                    $actif->setNumSerie($row[3]);
                    $actif->setEtat($row[4]);

                    $location = $emplacementRepo->findOneBy(['Batiment' => $row[5]]);
                    if ($location) {
                        $actif->setLocation($location);
                    } else {
                        $this->addFlash('warning', "Ligne $index : Emplacement non trouvé pour le bâtiment " . $row[5]);
                    }

                    $employer = $employerRepo->findOneBy(['nom' => $row[6]]);
                    if ($employer) {
                        $actif->addUserAssigned($employer);
                    } else {
                        $this->addFlash('warning', "Ligne $index : Employé non trouvé pour " . $row[6]);
                    }

                    if (!empty($row[7])) {
                        $date = \DateTime::createFromFormat('m/d/Y', $row[7]);
                        if ($date) {
                            $actif->setDateAcquisation($date);
                        } else {
                            $this->addFlash('warning', "Ligne $index : Format de date invalide " . $row[7]);
                        }
                    }

                    $admin = $userRepo->findOneBy(['email' => 'MayssaSboui@gmail.com']);
                    if ($admin) {
                        $actif->setCreatedBy($admin);
                    } else {
                        $this->addFlash('danger', "Admin non trouvé !");
                        return $this->redirectToRoute('actif_import');
                    }

                    $em->persist($actif);
                    
                    // Ajouter l'historique
                    $historique = new Historique();
                    $historique->setActif($actif);
                    $historique->setAction('Importation');
                    $historique->setDateAction(new \DateTimeImmutable());
                    $historique->setActionneur($admin);
                    $historique->setDetails(['message' => "Actif importé"]);
                    $historique->setEtat($actif->getEtat());
                    
                    $em->persist($historique);
                }

                $em->flush();
                $this->addFlash('success', 'Importation réussie avec enregistrement de l’historique !');
                return $this->redirectToRoute('all_actif');
            }
        }

        return $this->render('actif_export/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }




}