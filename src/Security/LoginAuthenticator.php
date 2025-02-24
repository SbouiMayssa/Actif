<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
 
class LoginAuthenticator extends AbstractLoginFormAuthenticator

{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                //activer lle remeber me sans passer par l'utlisateur
                (new RememberMeBadge())->enable(),
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                //activer lle remeber me sans passer par l'utlisateur
                // new RememberMeBadge(),
            ]
        );
    }


   

    

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_admin'));
            //return new RedirectResponse($this->urlGenerator->generate('app_admin_dashboard'));
        }
    
        if (in_array('ROLE_TECH', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('actif'));
        }
    
        // For example:
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);


        //ajouter condition si il est admin rediriger vers sinon vers...
        
        //return new RedirectResponse($this->urlGenerator->generate('app_admin'));
    }
   

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
