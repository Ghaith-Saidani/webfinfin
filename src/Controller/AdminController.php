<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UpdateUserType;
use Symfony\Component\Form\AbstractType;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/admin', name: 'app_admin')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    /*#[Route('/userslist', name: 'userslist')]
    public function usersList(UserRepository $rep): Response
    {
       $list=$rep->findAll();
        return $this->render('admin/UsersList.html.twig', [
            'list' => $list
        ]);
    }*/
    #[Route('/userslist', name: 'userslist')]
    public function usersListPaginate(Request $request,UserRepository $rep, PaginatorInterface $paginator): Response
    {
       $list=$rep->findAll();
       $list = $paginator->paginate(
        $list, /* query NOT result */
        $request->query->getInt('page', 1),
        4
    );
        return $this->render('admin/UsersList.html.twig', [
            'list' => $list
        ]);
    }
    #[Route('/delete_user/{id}', name: 'delete_user')]
    public function deleteUser($id,
        UserRepository $rep,
        ManagerRegistry $doctrine): Response
    {
        $em= $doctrine->getManager();
        $user= $rep->find($id);
       $em->remove($user);
       $em->flush();
       return $this-> redirectToRoute('app_adminuserslist');
    }
  
#[Route('/update/{id}', name: 'updateuser')]
public function updateuser($id, Request $req, UserRepository  $userRepository,ManagerRegistry $doctrine): Response
{
    $user=$userRepository->find($id);
    $form=$this->createForm(UpdateUserType::class,$user);
    $form->handleRequest($req);
    if($form->isSubmitted()){
        $em= $doctrine->getManager();
        $em->persist($user);
        $em->flush();
        return $this-> redirectToRoute('app_adminuserslist');
    }
    return $this->render('admin/update.html.twig',[
        'form'=>$form->createView(),
        'user'=>$user
    ]);

}



}
