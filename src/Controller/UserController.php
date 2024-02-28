<?php

namespace App\Controller;
use App\Entity\Users; 
use App\Form\EditProfileType; 

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig');
    }

    #[Route('/ModifUser', name: 'ModifUser', methods: ['GET', 'POST'])]
    public function editprofil(Request $request)
    {
        $user = $this->getUser();
       
        
        $form = $this->createForm(EditProfileType::class, $user);
    
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
    
            
          
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('message', 'Profil mis à jour');
    
            return $this->redirectToRoute('app_user');
        }
    
        return $this->render('user/EditProfile.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/ModifPassUser', name: 'ModifPassUser')]
public function editPass(Request $request, UserPasswordEncoderInterface $passwordEncoder)
{
    if ($request->isMethod('POST')) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        // On vérifie si les 2 mots de passe sont identiques
        $newPassword = $request->request->get('pass');
        $confirmPassword = $request->request->get('pass2');

        if (!empty($newPassword) && $newPassword == $confirmPassword) {
            $user->setPassword($passwordEncoder->encodePassword($user, $newPassword));
            $em->flush();
            $this->addFlash('message', 'Mot de passe mis à jour avec succès');
            return $this->redirectToRoute('app_user');
        } else {
            if (empty($newPassword)) {
                $this->addFlash('error', 'Le mot de passe ne peut pas être vide');
            } else {
                $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
            }
        }
    }

    return $this->render('user/editpass.html.twig');
}

}
