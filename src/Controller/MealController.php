<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Entity\Commande;
use App\Form\MealType;
use App\Form\CommandeType;
use App\Repository\MealRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;



#[Route('/meal')]

class MealController extends AbstractController

{
    #[Route('/', name: 'app_meal')]
public function list(MealRepository $repo): Response
{ 
    // Récupérer tous les types de repas uniques
    $typesRepas = $repo->findUniqueMealTypes(); // Vous devez implémenter cette méthode dans votre repository

    // Passer les types de repas et tous les repas au modèle Twig
    return $this->render('front/meal/list.html.twig', [
        'typesRepas' => $typesRepas,
        'meals' => $repo->findAll(), // Récupérer tous les repas
    ]);
}
#[Route('/meal_details/{id}', name: 'meal_details')]
public function mealDetails(Meal $meal, Request $request, EntityManagerInterface $entityManager): Response
{
    $commande = new Commande();
    $form = $this->createForm(CommandeType::class, $commande);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Associer le repas à la commande
        $commande->addMeal($meal);

        // Enregistrer la commande
        $entityManager->persist($commande);
        $entityManager->flush();

        // Redirection après la création de la commande
        return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
    }

    return $this->render('front/meal/mealDetails.html.twig', [
        'meals' => $meal,
        'form' => $form->createView(),
    ]);
}


    #[Route('/list', name: 'app_meal_index', methods: ['GET'])]
    public function index(MealRepository $mealRepository, PaginatorInterface $paginator, Request $request): Response
    {    $query = $mealRepository->createQueryBuilder('c')
        ->getQuery();

    // Paginate the query
    $meals = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1), // Current page number, default to 1
        4// Number of items per page
    );
        return $this->render('admin/meal/index.html.twig', [
            'meals' => $meals,
            
        ]);
    }

    #[Route('/new', name: 'app_meal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $meal = new Meal();
        $form = $this->createForm(MealType::class, $meal);
        $form->handleRequest($request);
        $meal-> setQuantity(0);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($meal);
            $entityManager->flush();

            return $this->redirectToRoute('app_meal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/meal/new.html.twig', [
            'meal' => $meal,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_meal_show', methods: ['GET'])]
    public function show(Meal $meal): Response
    {
        return $this->render('admin/meal/show.html.twig', [
            'meal' => $meal,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meal $meal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MealType::class, $meal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            
            $entityManager->flush();

            return $this->redirectToRoute('app_meal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/meal/edit.html.twig', [
            'meal' => $meal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meal_delete', methods: ['POST'])]
    public function delete(Request $request, Meal $meal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$meal->getId(), $request->request->get('_token'))) {
            $entityManager->remove($meal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_meal_index', [], Response::HTTP_SEE_OTHER);
    }
    

    
 
}

