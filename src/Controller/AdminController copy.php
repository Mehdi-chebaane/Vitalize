<?php

namespace App\Controller;
use App\Entity\Users; 
use App\Form\UsersType; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface; 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; 
use App\Form\EditProfilAdminType; 
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;



#[Route('/admin', name: 'admin_')]

class AdminController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }



    #[Route('/dashbord', name: 'dashbord')]
    public function admin(): Response
    {
        return $this->render('admin/dashbord.html.twig');
    }


    
    #[Route('/liste', name: 'liste')]
    public function admin2(): Response
    {
        $users = $this->entityManager->getRepository(Users::class)->findAll();

        return $this->render('admin/liste.html.twig', ['users' => $users]);
    }

    #[Route('/user/{id}', name:'show')]
    public function show($id)
    {
        $user = $this->entityManager->getRepository(Users::class)->find($id);
    
        return $this->render('admin/consulte.html.twig', ['user' => $user]);
    }
    
    #[Route('/user/delete/{id}', name:'delete', methods: ['GET'])]
    public function delete(Request $request, $id)
    {
        $user = $this->entityManager->getRepository(Users::class)->find($id);
       
        if (!$user) {
            throw $this->createNotFoundException('Users not found');
        }
      

        $this->entityManager->remove($user);
        $this->entityManager->flush();
        
        return $this->redirectToRoute('admin_liste');
    }





    #[Route('/ajout1', name: 'ajout1', methods: ['GET', 'POST'])]
public function new(Request $request,UserPasswordHasherInterface $userPasswordHasher)
{
    $user = new Users();

    
    $form = $this->createForm(UsersType::class, $user);


    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $user = $form->getData();

        
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            )
        );
       // $user->setRoles(['ROLE_PATIENT']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_liste');
    }

    return $this->render('admin/ajout.html.twig', ['form' => $form->createView()]);
}



    #[Route('/user/edit/{id}', name: 'modiff', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id) {
        $user = new Users();
        $user = $this->entityManager->getRepository(Users::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
       
  
        $form1 = $this->createFormBuilder($user)
        ->add('nom', TextType::class)
        ->add('email', EmailType::class) 
        
       
        ->add('roles', ChoiceType::class, [
            'choices' => [
                'Admin' => 'ROLE_ADMIN',
                'Patient' => 'ROLE_PATIENT',
                'Medecin' => 'ROLE_MEDECIN',
            ],
            'multiple' => true,
            'expanded' => true,
            'label' => 'Roles',
            'required' => true,
          
        ])
    
        ->add('tel', TextType::class)
        ->add('prenom', TextType::class)
          ->add('save', SubmitType::class, array(
            'label' => 'Modifier'         
          ))->getForm();
  
        $form1->handleRequest($request);
        if($form1->isSubmitted() && $form1->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
          return $this->redirectToRoute('admin_liste');
        }
  
        return $this->render('admin/modif.html.twig', ['form1' => $form1->createView(),
    'id' => $id]);
      }



      #[Route('/adminprofile', name: 'adminprofile')]
      public function index(): Response
      {
          return $this->render('admin/profil.html.twig');
      }
  
      #[Route('/ModifAdmin', name: 'ModifAdmin', methods: ['GET', 'POST'])]
      public function editprofil(Request $request)
      {
          $user = $this->getUser();
         
          
          $form = $this->createForm(EditProfilAdminType::class, $user);
      
      
          $form->handleRequest($request);
          if ($form->isSubmitted() && $form->isValid()) {
              $user = $form->getData();
      
              
            
              $this->entityManager->persist($user);
              $this->entityManager->flush();
  
              $this->addFlash('message', 'Profil mis à jour');
      
              return $this->redirectToRoute('admin_adminprofile');
          }
      
          return $this->render('admin/EditProfil.html.twig', [
              'form1' => $form->createView(),
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
              return $this->redirectToRoute('admin_adminprofile');
          } else {
              if (empty($newPassword)) {
                  $this->addFlash('error', 'Le mot de passe ne peut pas être vide');
              } else {
                  $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
              }
          }
      }
  
      return $this->render('admin/editpass.html.twig');
  }
  



    #[Route('/ajout', name: 'ajout')]
    public function admin3(): Response
    {
        return $this->render('admin/ajout.html.twig');
    }

    #[Route('/consulte', name: 'consulte')]
    public function admin4(): Response
    {
        return $this->render('admin/consulte.html.twig');
    }

    #[Route('/modif', name: 'modif')]
   public function admin5(): Response
   {
    return $this->render('admin/modif.html.twig');
    }

    #[Route('/profil', name: 'profil')]
    public function admin6(): Response
    {
        return $this->render('admin/profil.html.twig');
    }

    #[Route('/login', name: 'login')]
    public function admin7(): Response
    {
        return $this->render('admin/login.html.twig');
    }

    
    #[Route('/register', name: 'register')]
    public function admin8(): Response
    {
        return $this->render('admin/register.html.twig');
    }

    #[Route('/fpassword', name: 'fpassword')]
    public function admin9(): Response
    {
        return $this->render('admin/fpassword.html.twig');
    }
    #[Route('/404', name: '404')]
    public function erreur(): Response
    {
        return $this->render('admin/404.html.twig');
    }

}
