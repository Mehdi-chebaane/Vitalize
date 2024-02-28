<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request , \Swift_Mailer $mailer )
    {
        $form=$this->createForm(ContactType::class);
      
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();
            
            $message = (new \Swift_Message('Nouveau Contact '))
                ->setFrom($contact['email'])  // Corrected method is setFrom
                ->setTo('testp3253@gmail.com')
                ->setBody(
                    $this->renderView(
                        'emails/contact.html.twig',
                        compact('contact')
                    ),
                    'text/html'
                );
           $mailer->send($message);
           $this->addFlash('message','Le message a bien ete envoye');
           return $this ->redirectToRoute('app_contact');
           
        }
        return $this->render('contact/index.html.twig', [
            'contactform' => $form->createView()
        ]);
    }
}
