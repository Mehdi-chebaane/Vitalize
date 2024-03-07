<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Meal;
use App\Entity\Users;
use App\Form\CommandeType;
use App\Form\CommandeType1;
use App\Form\CommandeType2;
use App\Repository\CommandeRepository;
use App\Repository\MealRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mime\Address;
use Knp\Component\Pager\PaginatorInterface;


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
    #[Route('/admin', name: 'app_commande_indexA', methods: ['GET'])]
public function indexA(CommandeRepository $commandeRepository, PaginatorInterface $paginator, Request $request): Response
{ 
    // Retrieve the query to fetch commandes
    $query = $commandeRepository->createQueryBuilder('c')
        ->getQuery();

    // Paginate the query
    $commandes = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1), // Current page number, default to 1
        3// Number of items per page
    );

    // Render the template with paginated commandes
    return $this->render('admin/commande/indexA.html.twig', [
        'commandes' => $commandes,
    ]);
}


    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MealRepository $mealRepository, SessionInterface $session, Security $security,MailerInterface $mailer): Response
    {   
        // Get the current user
        $user = $security->getUser();
    
        if (!$user) {
            // Redirigez l'utilisateur vers la page de connexion
            return new RedirectResponse($this->generateUrl('app_login'));
        }
    
        // Get the cart from the session using the session property
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            // Retourner une redirection vers la page précédente avec un message d'erreur
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_commande_index');
        }
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
        $commande->setEtatCommande("En Attente");
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
        $commande->setMethodePaiement( $commande->getMethodePaiement());
    
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
           
            // Persist the Commande entity
            $entityManager->persist($commande);
            $entityManager->flush();
            $session->set('cart', []);
    
           
           $this->sendCommandeNotificationEmail($mailer, $user, $commande);
    
            // Redirect the user to the command index page
            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }
       
    
        // Render the form
        return $this->render('front/commande/new.html.twig', [
            'form' => $form->createView(),
            'commandes' => $commande,
        ]);}
    
    private function sendCommandeNotificationEmail(MailerInterface $mailer, UserInterface $currentUser, Commande $commande): void
    {
        $email = (new TemplatedEmail())
            ->from("testp3253@gmail.com")
            ->to($currentUser->getEmail())
            ->subject('New commande Created')
            ->htmlTemplate('emails/factureL.html.twig')
            ->context([
                'currentUser' => $currentUser,
                'commande' => $commande,
            ]);
    
        $mailer->send($email);
    }



    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('front/commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }
    #[Route('admin/{id}', name: 'app_commande_showA', methods: ['GET'])]
    public function showA(Commande $commande): Response
    {
        return $this->render('admin/commande/showA.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType2::class, $commande);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $formData = $form->getData();
            $newTotalAmount = 0;
            foreach ($formData->getMealQuantities() as $mealId => $quantity) {
                $meal = $this->getDoctrine()->getRepository(Meal::class)->find($mealId);
                if ($meal) {
                    $newTotalAmount += $meal->getPrix() * $quantity;
                }
            }
            
            // Mettre à jour le prix total de la commande dans la base de données
            $commande->setPrixtotal($newTotalAmount);
            
            // Flush pour enregistrer les modifications
            $entityManager->flush();
    
            // Rediriger l'utilisateur vers une page appropriée
            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('front/commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }
    

#[Route('/admin/{id}/edit', name: 'admin_commande_edit', methods: ['GET', 'POST'])]
public function editA(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(CommandeType1::class, $commande); // Utilisez CommandeType2 ici
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('admin/commande/editA.html.twig', [
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

    #[Route('/save', name: 'save-order', methods: ['POST'])]
public function saveOrder(Request $request, SessionInterface $session, Security $security): Response
{
    // Récupérer l'utilisateur actuel
    $user = $security->getUser();
    
    if (!$user) {
        // Rediriger l'utilisateur vers la page de connexion
        return $this->redirectToRoute('app_login');
    }

    // Extraire les détails de la commande de la requête
    $orderDetails = $request->request->all();

    // Ajouter la clé 'client_name' aux détails de la commande
    $orderDetails['client_name'] = $user->getNom(); 
    $orderDetails['client_family_name'] = $user->getPrenom();// Assumant que 'Nom' est le champ contenant le nom du client
    $orderDetails['user_id'] = $user->getId();
    // Stocker les détails de la commande dans la session
    $session->set('order_details', $orderDetails);

    // Rediriger vers la page de paiement
    return $this->redirectToRoute('checkout');
}
}
