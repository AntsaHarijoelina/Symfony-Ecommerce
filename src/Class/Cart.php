<?php

namespace App\Class;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart 
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        // On utilise RequestStack pour récupérer la session courante
        $this->session = $requestStack->getSession();
    }

    public function add($id)
    {
        $cart= $this->session->get('cart', []);

        if(!empty($cart[$id])){
            $cart[$id]++;
        }else{
            $cart[$id]=1;
        }
        
        
        $this->session->set('cart', $cart);

    }

    public function get()
    {
        return $this->session->get('cart');

    }
    
    public function remove()
    {
        return $this->session->remove('cart');

    }
}