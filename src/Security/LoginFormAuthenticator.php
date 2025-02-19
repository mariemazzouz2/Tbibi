<?php

namespace App\Security;

use App\Repository\UtilisateurRepository; // Ajoute l'import
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UtilisateurRepository $userRepository; // Déclare la propriété

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        UtilisateurRepository $userRepository // Injecte le repository
    ) {
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Vérification du statut de l'utilisateur
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Utilisateur non trouvé.');
        }

        if ($user->getStatus() === 0) {
            throw new CustomUserMessageAuthenticationException('Vous n\'êtes pas encore accepté par l\'administrateur.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
    
        // Récupérer l'utilisateur authentifié
        $user = $token->getUser();
    
        // Vérifier le rôle et rediriger en conséquence
        if (in_array('ROLE_MEDECIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->urlGenerator->generate('app_medecin'));
        } elseif (in_array('ROLE_PATIENT', $user->getRoles(), true)) {
            return new RedirectResponse($this->urlGenerator->generate('app_utilisateur'));
        }
        elseif (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->urlGenerator->generate('back'));
        }
    
        // Redirection par défaut si aucun rôle spécifique n'est trouvé
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
