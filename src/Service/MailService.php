<?php 
namespace App\Service;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class MailService
{
    private $mailer;
    private $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendNotificationEmail(string $to, string $subject, string $template, array $context): void
    {
       try{
        $htmlContent = $this->twig->render($template, $context);

        $email = (new Email())
            ->from('tonemail@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
        dump('🚀 Tentative d’envoi d’email via l’interface web avec sucec');

       }catch (TransportExceptionInterface $e) {
        dump('🚀 ❌ Erreur lors de l’envoi'. $e->getMessage());

    }
    }


    
}
