<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('/inscription', name: 'app_register')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $encoder): Response
    {
        $user = new User();
        
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            
            // Encodage du mot de passe avec la méthode hashPassword
            $hashedPassword = $encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
    
            // Persistance des données
            $entityManager->persist($user);
            $entityManager->flush();
    
            // Message flash et redirection
            $this->addFlash('success', 'Inscription réussie !');
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
