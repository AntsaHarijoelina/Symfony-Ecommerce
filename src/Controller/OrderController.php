<?php

namespace App\Controller;

use App\Class\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
    }

    #[Route('/commande', name: 'app_order')]
   
    public function index(Cart $cart, Request $request): Response
    {
        if(!$this->getUser()->getAddresses()->getValues()){
            return $this->redirectToRoute('app_account_address_add');
        };

        $form = $this->createForm(OrderType::class, null, [
            'user' =>$this->getUser()
        ]);

        return $this->render('order/index.html.twig',[
            'form'=>$form->createView(),
            'cart'=>$cart->getfull()

        ]);
    }

    #[Route('/commande/recapitulatif', name: 'app_order_recap', methods: ['POST'])]
    
    public function add(Cart $cart, Request $request): Response
    {

        $form = $this->createForm(OrderType::class, null, [
            'user' =>$this->getUser()
        ]);
        $form->handleRequest($request);

        
        
        if ($form->isSubmitted() && $form->isValid()) { 

            $date = new DateTime();
            $carriers = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();
            $delivery_content = $delivery->getFirstname(). ' '. $delivery->getLastname();
            $delivery_content .= '</br>'.$delivery->getCountry();
            if ($delivery->getCompany()){
                $delivery_content = '</br>'.$delivery->getCompany();
            }
            $delivery_content .= '</br>'.$delivery->getAddress();
            $delivery_content .= '</br>'.$delivery->getPostal().' '. $delivery->getCity() ;
            $delivery_content .= '</br>'.$delivery->getCountry();
            

            //enregistrer ma commande order()
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice());
            $order->setDelivery($delivery_content);
            $order->setIsPaid(0);

            $this->entityManager->persist($order);

           
            //enregistrer mes produits orderDetails()
           
            foreach ($cart->getFull() as $product){
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                
                $this->entityManager->persist($orderDetails);
            }

             $this->entityManager->flush();
            return $this->render('order/add.html.twig',[
                'cart'=>$cart->getfull(),
                'carrier'=> $carriers,
                'delivery' => $delivery_content 
    
            ]);
        }

        return $this->redirectToRoute('app_cart'); 
    }
}
