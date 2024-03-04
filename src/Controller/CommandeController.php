<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Users;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\MealRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('front/commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, MealRepository $mealRepository, SessionInterface $session, Security $security): Response
{   
    // Get the current user
    $user = $security->getUser();

    // Get the cart from the session using the session property
    $cart = $session->get('cart', []);
    $mealIds = array_keys($cart);
    $meals = $mealRepository->findBy(['id' => $mealIds]);

    // Calculate the total amount
    $totalAmount = 0;
    foreach ($meals as $meal) {
        $quantity = $cart[$meal->getId()]; // Get the quantity from the cart
        $totalAmount += $meal->getPrix() * $quantity;
    }

    // Initialize the product quantities array
    $productQuantities = [];

    $commande = new Commande();
    // Assigner l'utilisateur à la commande
    $commande->setUser($user);
    $commande->setClientName($user->getNom());
    $commande->setClientFamilyName($user->getPrenom());
    $commande->setClientAdresse($user->getAdresse());
    $commande->setClientPhone($user->getTel());
    $commande->setEtatCommande("en attente");
    $commande->setDate(new \DateTime());
    $commande->setPrixtotal($totalAmount); // Set the total amount

    foreach ($meals as $meal) {
        $quantity = $cart[$meal->getId()]; // Get the quantity from the cart
        $commande->addMeal($meal); // Add the product to the order
        $meal->addCommande($commande); // Add the order to the product (bi-directional relationship)
        // Store the quantity for the product using the product ID as the key
        $productQuantities[$meal->getId()] = $quantity;
        $meal->setQuantity($meal->getQuantity() - $productQuantities[$meal->getId()]);
        if ($meal->getQuantity() < 0) {
            $meal->setQuantity(0);
        }
    }

    $commande->setMealQuantities($productQuantities);
    $commande->setMethodePaiement('à la livraison');

    $form = $this->createForm(CommandeType::class, $commande);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Persist the Commande entity
        $entityManager->persist($commande);
        $entityManager->flush();
        $session->set('cart', []);
        // Redirect the user to the command index page
        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    // Render the form
    return $this->render('front/commande/new.html.twig', [
        'form' => $form->createView(),
    ]);}



    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('front/commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
