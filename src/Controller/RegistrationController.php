<?php

namespace App\Controller;
use App\Entity\Users;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RegistrationFormType;
use App\Security\UsersAuthentificationAuthenticator;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use App\Service\SendMailService;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\UsersRepository;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        UsersAuthentificationAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                    
                )
            );

            $imageFile = $form->get('verif')->getData();
            if ($imageFile) {
                $imageName = md5(uniqid()) . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                    $imageName
                );
                $user->setVerif('/uploads/images/' . $imageName);
             
            }
            

            $entityManager->persist($user);
            $entityManager->flush();

           
            $this->sendWelcomeEmail($user, $mailer);
            $this->notifyAdmins($user, $mailer);

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function sendWelcomeEmail(Users $user, MailerInterface $mailer): void
    {
        $welcomeEmail = (new Email())
            ->from('testp3253@gmail.com') 
            ->to($user->getEmail()) 
            ->subject('Welcome to the site') 
            ->html($this->renderView('emails/user_welcome.html.twig', ['user' => $user]));

        $mailer->send($welcomeEmail);
    }
    private function notifyAdmins(Users $user, MailerInterface $mailer): void
{
    
    $admins = $this->getDoctrine()->getRepository(Users::class)->findAll();

    
    $admins = array_filter($admins, function ($admin) {
        return in_array('ROLE_ADMIN', $admin->getRoles(), true);
    });

    foreach ($admins as $admin) {
        $adminEmail = $admin->getEmail();

        
        dump($admin->getRoles());

        $adminNotificationEmail = (new Email())
            ->from('testp3253@gmail.com') 
            ->to($adminEmail) 
            ->subject('New User Registration')
            ->html($this->renderView('emails/admin_notification.html.twig', ['user' => $user]));

        $mailer->send($adminNotificationEmail);
    }
}
   
}
