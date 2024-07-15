<?php

namespace App\Controller;

use App\Entity\PersonalInfo;
use App\Entity\TextEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController
{
    #[Route('/api/person', methods: ['POST', 'HEAD'])]
    public function createPerson(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $person = new PersonalInfo();
        $person->setFirstName($data['firstName']);
        $person->setLastName($data['lastName']);
        $person->setAdress($data['address']);
        $person->setPhoneNumber($data['phoneNumber']);
        
        $em->persist($person);
        $em->flush();
        
        return new Response('Person created', 201);
    }

    #[Route('/api/text-entry', methods: ['POST', 'HEAD'])]
    public function createTextEntry(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $textEntry = new TextEntry();
        $textEntry->setContent($data['content']);
        
        $em->persist($textEntry);
        $em->flush();
        
        return new Response('Text entry created', 201);
    }
}
