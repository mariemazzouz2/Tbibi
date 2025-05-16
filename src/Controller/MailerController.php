<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerController extends AbstractController
{
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('mahdimasmoudi300@gmail.com')
            ->to('Masmoudi.Mahdi@esprit.tn')
            ->subject('Test Symfony Mailer')
            ->text('Ceci est un test !')
            ->html('<p>Ceci est un <strong>test</strong> !</p>');

        $mailer->send($email);

        return new Response('Email envoyé avec succès !');
    }
}