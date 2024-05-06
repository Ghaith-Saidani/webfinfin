<?php

namespace App\Controller;

use App\Form\FormulaireType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Blog;
use App\Form\RegisterType;
use App\Form\SigninType;
use App\Form\SendEmailType;
use App\Form\CodeType;
use App\Form\UpdateType;
use App\Form\AddType;
use App\Form\ImageType;
use App\Form\ChangePasswordFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\UserRepository;
use App\Repository\BlogRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Mime\Email;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserController extends AbstractController
{
    private $security;
    private $session;
   
 //   private $propertyMappingFactory;
    
    public function __construct(Security $security, SessionInterface $session)
    {
         $this->security = $security;
         $this->session = $session;
        
        // $this->uploadHandler = $uploadHandler;
         
    }
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('welcome');
        }
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
   /* */
    #[Route('/login', name: 'login')]
    public function goToSignin(): Response
    {
        return $this->render('security/login.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
   
    #[Route('/resetCode', name: 'resetCode')]
    public function goToResetCode(): Response
    {
        return $this->render('user/resetCode.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
   
    
    
    #[Route('/user/formulaire', name: 'formulaire')]
    public function fillForm( Request $request, UserRepository  $userRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $idUser = $this->session->get('idUser');;
        $user = $entityManager->getRepository(User::class)->find($idUser);
        $form = $this->createForm(FormulaireType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newValues = [
                'incometype' => $request->request->get('incometype'),
                'budgettype' => $request->request->get('budgettype'),
                'transport' => $request->request->get('transport'),
                'rent' => $request->request->get('rent'),
                'debt' => $request->request->get('debt'),
                // Add more properties as needed
            ];
            $userRepository->updateUserValues($idUser, $newValues);
            return $this->redirectToRoute('app_login');
        }
        // Render the formulaire form
        return $this->render('user/formulaire.html.twig', [
            'idUser' => $idUser,
            'form'=> $form->createView(),
        ]);
    }
    




#[Route('/signup', name: 'signup')]
public function register(ManagerRegistry $doctrine, Request $request, UserPasswordEncoderInterface $encoder): Response
{
    $user = new User();
    $form = $this->createForm(RegisterType::class, $user);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $hash=$encoder->encodePassword($user,$user->getPassword());
        $user->setPassword($hash);

        
        $em= $doctrine->getManager();
        $user = $form->getData();
        $defaultImageUrl = 'user-avatar.png';
        $user->setUrlimage($defaultImageUrl);
        $em->persist($user);            
        $em->flush();
        $this->session->set('idUser', $user->getIdUser());
        return $this->redirectToRoute('formulaire');
    }
   
    return $this->render('user/signup.html.twig', [
        'form' => $form->createView(),
    ]);
}


//send email containing code to reset password

#[Route('/sendemail', name: 'sendemail')]
    public function sendemail(ManagerRegistry $doctrine, Request $request, UserRepository  $userRepository,   MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(SendEmailType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() ) {
            $em= $doctrine->getManager();
            $email = $form['email']->getData();
            $user = $user = $userRepository->findOneByEmail($email);
            if($user){
               $this->session->set('reset_user_email', $user->getEmail());
               $code= $user->generateResetCode();
               $user->setResetCode($code);
               $em->persist($user);            
               $em->flush();
               $emailtosend = (new Email())
                ->from('eyaboukh@gmail.com')
                ->to($user->getEmail())
                ->subject('Reset Password Code')
                ->html($twig->render('email/resetcodesent.html.twig', [
                    'code' => $user->getResetCode(),
                ]));
            $mailer->send($emailtosend);
            
            return $this->redirectToRoute('resetCode');
            }
            else
            {
                return $this->redirectToRoute('sendemail');
            }   
        }
        return $this->render('user/sendEmail.html.twig', [
            'form' => $form->createView(),
        ]);
    }
//enter rest password code and verify
    #[Route('/resetCode', name: 'resetCode')]
    public function sendcode(ManagerRegistry $doctrine, Request $request, UserRepository  $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(CodeType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $code = $form['resetCode']->getData();
            $email = $this->session->get('reset_user_email');
            $user=$userRepository->findOneByEmail( $email);
            
            if($user || $code==$user->getResetCode()){
                return $this->redirectToRoute('newPass'); 
            }
            else {
                $this->addFlash('error', 'Invalid or expired reset code.');
            }
        
        }
        return $this->render('user/resetCode.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    //set new password 
    #[Route('/newPass', name: 'newPass')]
    public function goToNewPass(ManagerRegistry $doctrine, Request $request, UserRepository  $userRepository, UserPasswordEncoderInterface $encoder): Response
    {
        
        $user = new User();
        $form = $this->createForm(ChangePasswordFormType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $email = $this->session->get('reset_user_email');
            $user=$userRepository->findOneByEmail( $email);
            
            if($user){
                
                 $password=$form['password']->getData();
                $hash=$encoder->encodePassword($user,$password);
                $user->setPassword($hash);
                $em= $doctrine->getManager();
                $em->persist($user);            
               $em->flush();
                return $this->redirectToRoute('app_login');
               
            }
            
        }
        return $this->render('user/newPass.html.twig', [
            'controller_name' => 'UserController',
            'form' => $form->createView(),
        ]);
    }
  
    #[Route('/settings-profile', name: 'settings-profile')]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();
        
        $form = $this->createForm(UpdateType::class, $user);
        $imageForm = $this->createForm(ImageType::class, $user);
        if ($user!==null){

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $imageForm->handleRequest($request);

        if ($form->isSubmitted() ) {
            $this->editUser($form->getData());
            // Rediriger ou afficher un message de succès
        }

        if ($imageForm->isSubmitted() ) {
             /** @var UploadedFile $uploadedFile */
            $uploadedFile = $imageForm['imageFile']->getData();
            if ($uploadedFile){

            $destination = $this->getParameter('kernel.project_dir').'/public/images/users';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $user->setUrlimage($newFilename);
           }


            // Rediriger ou afficher un message de succès
        }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }

    }
        return $this->render('user/settings-profile.html.twig', [
            'form' => $form->createView(),
            'imageForm' => $imageForm->createView(),
            'user' => $user,
        ]);
    }
    private function editUser(User $user): void
    {
        // Save the updated user entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
    }
    
    
   
    #[Route('/adduser', name: 'adduser')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        
        $user = new User();
        $form = $this->createForm(AddType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            $em= $doctrine->getManager();
            $user = $form->getData();
           
            $em->persist($user);            
            $em->flush();
            return $this->redirectToRoute('welcome');
        }
        
        return $this->render('user/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/delete', name: 'deleteuser')]
    public function deleteUser(
        UserRepository $rep,
        ManagerRegistry $doctrine,Request $request): Response
    {
        $user = $this->getUser();
        if($user){
            $em= $doctrine->getManager();
        
            $em->remove($user);
             $em->flush();
            $request->getSession()->invalidate();
            $this->container->get('security.token_storage')->setToken(null);
            return $this-> redirectToRoute('app_front_office');
        }
  
       
    }
    #[Route('/blogslist', name: 'app_blogslist', methods: ['GET'])]
    public function blogslist(BlogRepository $blogRepository): Response
{
    
    return $this->render('blog/blogs.html.twig', [
        'blogs' => $blogRepository->findAll(),
        'likedblogs' => $blogRepository->findMostLikedPosts(4),
    ]);
}


    #[Route('/app_blog_show/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Blog $blog): Response
    {
        return $this->render('blog/blog-details.html.twig', [
            'blog' => $blog,
        ]);
    }

    #[Route('/incrementLikes/{id}', name: 'increment_likes', methods: ['POST'])]
    public function incrementLikes(Request $request, int $id)
{
    // Fetch the blog post from the database
    $entityManager = $this->getDoctrine()->getManager();
    $blog = $entityManager->getRepository(Blog::class)->find($id);

    // If the blog post does not exist, return an error response
    if (!$blog) {
        return new JsonResponse(['error' => 'Blog post not found'], 404);
    }

    // Increment the number of likes
    $blog->setNbrOfLikes($blog->getNbrOfLikes() + 1);

    // Save the changes to the database
    $entityManager->flush();

    // Return a JSON response with the updated number of likes
    return new JsonResponse(['idBlog' => $blog->getId(), 'nbrOfLikes' => $blog->getNbrOfLikes()]);
}

   
}

    



