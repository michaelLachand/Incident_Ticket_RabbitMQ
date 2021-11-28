<?php

namespace App\Controller;

use App\Entity\Incident;
use App\Form\IncidentType;
use App\Message\MailNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $incident = new Incident();
        $incident->setUser($this->getUser())
            ->setCreatedAt(new \DateTimeImmutable('now'));

        $form = $this->createForm(IncidentType::class, $incident);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $incident = $form->getData();

            $em->persist($incident);
            $em->flush();

            $this->dispatchMessage(new MailNotification($incident->getDescription(),$incident->getId(),$incident->getUser()->getEmail()));

            return $this->redirectToRoute('home');
        }
        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
