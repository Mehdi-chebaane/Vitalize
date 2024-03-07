<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Meal;
use App\Entity\Commande;
use App\Entity\Users;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MealRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mime\Address;

class PaymentController extends AbstractController
{
    private $stripesk;

    public function __construct(string $stripesk)
    {
        $this->stripesk = $stripesk;
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }

    #[Route('/checkout', name: 'checkout')]
    public function checkout(SessionInterface $session, Security $security, MealRepository $mealRepository): Response
    {
        $user = $security->getUser();

        if (!$user) {
            // Redirigez l'utilisateur vers la page de connexion
            return new RedirectResponse($this->generateUrl('app_login'));
        }

        // Configurez Stripe avec votre clé secrète
        Stripe::setApiKey($this->stripesk);

        // Initialiser $lineItems en tant qu'array vide
        $lineItems = [];

        $cart = $session->get('cart', []);
        foreach ($cart as $productId => $quantity) {
            $product = $mealRepository->find($productId);
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getNomRepas(),
                    ],
                    'unit_amount' => $product->getPrix() * 100, // en cents
                ],
                'quantity' => $quantity,
            ];
        }

        // Vérifiez si le panier est vide
        if (empty($lineItems)) {
            // Redirigez l'utilisateur vers une page indiquant que le panier est vide
            return $this->redirectToRoute('home');
        }

        // Créez une session de paiement Stripe
        $session = Session::create([
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('success-url', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('cancel-url', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // Redirigez l'utilisateur vers l'URL de paiement Stripe
        return $this->redirect($session->url, 303);
    }

    #[Route('/success-url', name: 'success-url')]
    public function successUrl(SessionInterface $session, EntityManagerInterface $entityManager, Security $security, MealRepository $mealRepository,MailerInterface $mailer): Response
    {
        // Retrieve the order details from the session
        $orderDetails = $session->get('order_details', []);
        $user = $security->getUser();

        // Get the current user
        $cart = $session->get('cart', []);
        $mealIds = array_keys($cart);
        $meals = $mealRepository->findBy(['id' => $mealIds]);

        $totalAmount = 0;
        foreach ($meals as $meal) {
            $quantity = $cart[$meal->getId()]; // Get the quantity from the cart
            $totalAmount += $meal->getPrix() * $quantity;
        }

        // Create a new Commande entity and populate it with the order details
        $commande = new Commande();
        // Populate $commande with order details from $orderDetails
        $commande->setUser($user); 
        $commande->setClientName($user->getNom());
        $commande->setClientFamilyName($user->getPrenom());
        $commande->setClientAdresse($user->getAdresse());
        $commande->setClientPhone($user->getTel());
        $commande->setEtatCommande("en attente");
        $commande->setDate(new \DateTime());
        $commande->setPrixtotal($totalAmount);
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
        // Set clientName to user's name

        // Persist the Commande entity
        $entityManager->persist($commande);
        $entityManager->flush();

        // Clear the session
        $session->remove('order_details');
    
        $this->sendCommandeNotificationEmail($mailer, $user, $commande);

        return $this->render('payment/success.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
    private function sendCommandeNotificationEmail(MailerInterface $mailer, UserInterface $currentUser, Commande $commande): void
    {
        $email = (new TemplatedEmail())
            ->from("testp3253@gmail.com")
            ->to($currentUser->getEmail())
            ->subject('New commande Created')
            ->htmlTemplate('front/emails/factureL.html.twig')
            ->context([
                'currentUser' => $currentUser,
                'commande' => $commande,
            ]);
    
        $mailer->send($email);
    }

    #[Route('/cancel-url', name: 'cancel-url')]
    public function cancelUrl(): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
}
