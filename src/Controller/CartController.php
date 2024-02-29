<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;

use App\Entity\Users;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Repository\MealRepository;
use App\Entity\Meal;
use App\Form\MealType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class CartController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, Security $security): Response
    {
        return $this->render('front/cart/index.html.twig', [
            
            'cart' => $session->get('panier')
        ]);
    }

    #[Route('/add-to-cart/{id}', name: 'add_to_cart', methods: ['GET'])]
    public function addToCart(int $id,Meal $meal, SessionInterface $session,EventDispatcherInterface $eventDispatcher,MealRepository $MealRepository,TranslatorInterface $translator): RedirectResponse
    {
       // Get the cart from the session or create an empty array if it doesn't exist
       $cart = $session->get('cart', []);

       // Check if the product is already in the cart
       $mealId = $meal->getId();
       if (isset($cart[$mealId])) {
           // If the product is already in the cart, increment the quantity
           $cart[$mealId]++;
       } else {
           // If the product is not in the cart, add it with quantity 1
           $cart[$mealId] = 1;
       }

       // Store the updated cart back into the session
       $session->set('cart', $cart);

       $meal = $MealRepository->find($id);
       $this->addFlash('success', sprintf('"%s" ajouté au panier.', $meal->getNomRepas()));
   

       // Redirect back to the product page or any other desired route
       return $this->redirectToRoute('show_cart', ['id' => $meal->getId()]);
    }
    #[Route('/show-cart', name: 'show_cart', methods: ['GET'])]
    public function showCart(SessionInterface $session,MealRepository $mealRepository )
    {
        // Get the cart from the session or create an empty array if it doesn't exist
        $cart = $session->get('cart', []);

        // Fetch the products (produits) from the database using the product IDs in the cart
        $mealIds = array_keys($cart);
        $meals = $mealRepository->findBy(['id' => $mealIds]);

        // Calculate the total for each product and the overall total
        $totalAmount = 0;
        foreach ($meals as $meal) {
            $quantity = $cart[$meal->getId()];
            $totalAmount += $meal->getPrix() * $quantity;
        }

        return $this->render('front/cart/show.html.twig', [
            'meals' => $meals,
            'cart' => $cart,
            'totalAmount' => $totalAmount,
        ]);
    }
    #[Route('/clear-cart', name: 'clear_cart', methods: ['POST'])]
    public function clearCart(SessionInterface $session): JsonResponse
    {
        // Clear the cart data from the session
        $session->remove('cart');

        return new JsonResponse(['success' => true]);
        return $this->redirectToRoute('show_cart');
    }
    
    #[Route('/update-cart/{id}', name: 'update_cart', methods: ['POST'])]
    public function updateCart(int $id, SessionInterface $session): RedirectResponse
    {
        $action = $_POST['action'] ?? null;

        if ($action === 'increase') {
            $this->changeQuantity($id, $session, 1);
        } elseif ($action === 'decrease') {
            $this->changeQuantity($id, $session, -1);
        }

        return $this->redirectToRoute('show_cart');
    }

    private function changeQuantity(int $id, SessionInterface $session, int $changeBy): void
    {
        // Get the cart from the session or create an empty array if it doesn't exist
        $cart = $session->get('cart', []);

        // Check if the product ID exists in the cart
        if (array_key_exists($id, $cart)) {
            // If the product is in the cart, update the quantity
            $cart[$id] += $changeBy;

            // Ensure the quantity is not negative
            $cart[$id] = max(0, $cart[$id]);
        }

        // Store the updated cart back into the session
        $session->set('cart', $cart);
    }

    // ...
}

