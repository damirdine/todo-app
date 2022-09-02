<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class GoogleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/login/google',name:"connect_google_start")]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        // on Symfony 3.3 or lower, $clientRegistry = $this->get('knpu.oauth2.registry');

        // will redirect to google!
        return $clientRegistry
            ->getClient('google_main') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect();
    }

    #[Route('/login/google/check',name:"connect_google_check")]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry,AuthenticationUtils $authenticationUtils)
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient $client */
        $client = $clientRegistry->getClient('google_main');

        try {
            
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\GoogleUser $googleUser */
            $googleUser = $client->fetchUser();
            $email = $googleUser->getEmail();

            $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);
            if(!$existingUser){
                $user = new User();
                $user->setEmail($googleUser->getEmail());
                $user->setAvatar($googleUser->getAvatar());
                $user->setFirstName($googleUser->getFirstName());
                $user->setLastName($googleUser->getLastName());
                $user->setRoles(['ROLE_USER']);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
            
            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            //var_dump($user,$existingUser); die;
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            var_dump($e->getMessage()); die;
        }

        return $this->redirectToRoute('app_task_index');
    }
}