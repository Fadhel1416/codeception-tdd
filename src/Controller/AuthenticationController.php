<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\AuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\ParameterNotFoundException;
use App\Repository\UserRepository;

class AuthenticationController extends AbstractController 
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request,EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse 
    {
//         $firstName = $request->get('firstName');
//         $lastName = $request->get('lastName');
//         $emailAddress = $request->get('emailAddress');
//         $password = $request->get('password');



//         if (is_null($firstName)) {
//             return $this->json(
//                 [
//                     'error' => 'First name is required'
//                 ],
//                 Response::HTTP_BAD_REQUEST
//             );
//         }
//         if (is_null($lastName)) {
//             return $this->json(
//                 [
//                     'error' => 'Last name is required'
//                 ],
//                 Response::HTTP_BAD_REQUEST
//             );
//         }
//         if (is_null($emailAddress)) {
//             return $this->json(
//                 [
//                     'error' => 'Email address is required'
//                 ],
//                 Response::HTTP_BAD_REQUEST
//             );
//         }
//         if (is_null($password)) {
//             return $this->json(
//                 [
//                     'error' => 'Password is required'
//                 ],
//                 Response::HTTP_BAD_REQUEST
//             );
//         }
    
//         return $this->json(
//             ['message' => 'Account created successfully'],
//             Response::HTTP_CREATED
//         );
//     }
// }
 
/* refactoring of the code ........................*/
//     $requestBody = $request->request->all();

//     $requiredParameters = [
//         'firstName'    => 'First name is required',
//         'lastName'     => 'Last name is required',
//         'emailAddress' => 'Email address is required',
//         'password'     => 'Password is required'
//     ];
//     foreach ($requiredParameters as $parameter => $errorMessage) {
//         if (!isset($requestBody[$parameter])) {
//             return $this->errorResponse($errorMessage);
//         }
//     }
//     $user=new User($requestBody['firstName'],
//                    $requestBody['lastName'],
//                    $requestBody['emailAddress']    
//    );

//    $hashedPassword = $passwordHasher->hashPassword(
//     $user, 
//     $requestBody['password']
// );
//     $user->setPassword($hashedPassword);

//     $em->persist($user);
//     $em->flush();

//     return $this->json(
//         [
//             'message' => 'Account created successfully',
//         ],
//         Response::HTTP_CREATED
//     );

$requestBody = $request->request->all();

$firstName = $this->getRequiredParameter('firstName', $requestBody, 'First name is required');
$lastName = $this->getRequiredParameter('lastName', $requestBody, 'Last name is required');
$emailAddress = $this->getRequiredParameter('emailAddress', $requestBody, 'Email address is required');
$password = $this->getRequiredParameter('password', $requestBody, 'Password is required');

$user = new User($firstName, $lastName, $emailAddress);

$hashedPassword = $passwordHasher->hashPassword($user, $password);
$user->setPassword($hashedPassword);

$em->persist($user);
$em->flush();

return $this->json(
    [
        'message' => 'Account created successfully'
    ],
    Response::HTTP_CREATED
);

        }


    public function errorResponse(string $errorMessage):JsonResponse
    {
        return $this->json(
                [
                    'error' => $errorMessage
                ],
                Response::HTTP_BAD_REQUEST
            );

    }
    private function getRequiredParameter(
        string $parameterName,
        array $requestBody,
        string $errorMessage
    ) {
        if (!isset($requestBody[$parameterName])) {
            throw new ParameterNotFoundException($errorMessage);
        }
        return $requestBody[$parameterName];
    }

    /** 
     * @Route("/login", name="login")
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse 
    
      {
        $requestBody = $request->request->all();

        $emailAddress = $requestBody['emailAddress'];
        $password = $requestBody['password'];
        $user = $userRepository->findOneBy(['email' => $emailAddress]);
        if (is_null($user) || !$passwordHasher->isPasswordValid($user, $password)) {

            throw new AuthenticationException();
            
        }

        // if (is_null($user)) {
        //     return $this->json(
        //         [
        //             'error' => 'Invalid login credentials provided'
        //         ],
        //         Response::HTTP_UNAUTHORIZED
        //     );
        // }

        // if (!$passwordHasher->isPasswordValid($user, $password)) {
        //     return $this->json(
        //         [
        //             'error' => 'Invalid login credentials provided'
        //         ],
        //         Response::HTTP_UNAUTHORIZED
        //     );
        // }

        $apiToken = bin2hex(random_bytes(32));
        $user->setApiToken($apiToken);

        $em->persist($user);
        $em->flush();

        return $this->json(
            [
                'token' => $apiToken
            ]
        );
    }


    
}