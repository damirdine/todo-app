<?php

namespace App\Security;

use App\Entity\User; // your user entity
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\EventListener\UserProviderListener;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    private $clientRegistry;
    private $entityManager;
    private $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';    
    }
    // public function getCredentials(Request $request)
    // {
    //     // this method is only called if supports() returns true

    //     // For Symfony lower than 3.4 the supports method need to be called manually here:
    //     // if (!$this->supports($request)) {
    //     //     return null;
    //     // }

    //     return $this->fetchAccessToken($this->getGoogleClient());
    // }

    public function getUser($credentials, UserProviderListener $userProvider)
    {
        /** @var GoogleUser $GoogleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();

        // 1) have they logged in with google before? Easy!
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);
        if (!$existingUser) {
            $user = new User();
            $user->setEmail($googleUser->getEmail());
            $user->setAvatar($googleUser->getAvatar());
            $user->setFirstName($googleUser->getFirstName());
            $user->setLastName($googleUser->getLastName());
            $user->setRoles(['ROLE_USER']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        // 2) do we have a matching user by email
        return $user;
    }
    /**
     * @return GoogleClient
     */
    private function getGoogleClient()
    {
        return $this->clientRegistry
            // "google_main" is the key used in config/packages/knpu_oauth2_client.yaml
            ->getClient('google_main');
    }
    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google_main');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();

                // 1) have they logged in with google before? Easy!
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $googleUser->getEmail()]);

                if (!$existingUser) {
                    $user = new User();
                    $user->setEmail($googleUser->getEmail());
                    $user->setAvatar($googleUser->geAvatar());
                    $user->setFirstName($googleUser->getGivenName());
                    $user->setLastName($googleUser->getFamilyName());
                    $user->setRoles(['ROLE_USER']);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    return $user;
                }
            
                return $existingUser;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('app_task_index');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/login/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
