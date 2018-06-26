<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\Recaptcha;
use App\Entity\User;
use App\Entity\Advert;
use \DateTime;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; 


/**
 * @Route("/api")
 */
class ApiController extends Controller{


    /**
     * @Route("/deleteadvert/{id}", name="api_deleteadvert", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function deleteAdvert(Request $request, $id){
        
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
        }

        // on récupère le repository de Advert (une annonce)
        $ar = $this->getDoctrine()->getRepository(Advert::class);
        $advert = $ar->findOneById($id);

        if(!$advert){
            throw new NotFoundHttpException('pas trouvé');
        }

        if($this->get('session')->get('account')->getId() != $advert->getAuthor()->getId()){
            throw new AccessDeniedHttpException('pas auteur');
        }

        //on récupère l'entity manager
        $entityManager = $this->getdoctrine()->getManager();

        
        // $entityManager->remove($advert);
        // $entityManager->flush();
       


        return $this->json(array("delete" => true));
        
    }

    /**
     * @Route("/admineditor/{id}", name="api_admineditor", requirements={"id"="\d+"}, methods={"POST"})
     */
     public function adminEditor(Request $request, $id){
       
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
        }

        if($this->get('session')->get('account')->getAdminLevel() != 1 ){
            throw new AccessDeniedHttpException('Vous n\êtes pas admin');
        }

        $ur = $this->getDoctrine()->getRepository(User::class);
        $user = $ur->findOneById($id);

        if(!$user){
            throw new NotFoundHttpException('pas trouvé');
        }

        $newStatus = $request->request->get('status') ?? null;
        $newLevel = $request->request->get('level') ?? null;

        if(isset($newStatus) && isset($newLevel)){

            if(!preg_match('#^0|1$#', $newStatus)){
                $errors['status'] = true;
            }

            if(!preg_match('#^0|1$#', $newLevel)){
                $errors['level'] = true;
            }

            if(!isset($errors)){
                $ur = $this->getDoctrine()->getRepository(User::class);
                $user = $ur->findOneById($id);


                if(!$user){
                    $errors['userNotFound'] = true;
                } else {
                    $user->setActive($newStatus);
                    $user->setAdminLevel($newLevel);
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();


                    return $this->json(array(
                        'success' => true,
                    ));

                    
                }
            }

            if(isset($errors)){
                return $this->json(array(
                    'success' => false,
                    'errors' => $errors
                ));
            }

        }

        throw new AccessDeniedHttpException();
    }
    
    


/**
*@Route("/login", name="api_login")
*/
    public function login(Request $request){
        //403
        if($this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Deja connecté');
            
        }
        
        
        $email = $request->request->get('email') ?? null;
        $password = $request->request->get('password') ?? null;
        
        // Vérification des champs du formulaire.
        if(
            isset($email)
            && isset($password)
            
        ){

            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors[] = 'Email incorrect';
            }


            if(!preg_match('#^.{2,300}$#', $password)){
                $errors[] = 'Password incorrect';
            }
            
            if(!isset($errors)){
        
                
                $session = $this->get('session');
                $userRepository = $this->getDoctrine()->getRepository(User::class);
                $user = $userRepository->findOneByEmail($email);
                

                if($user){
                    if(password_verify($password, $user->getPassword())){

                        if($user->getActive() == 0){
                            
                            $errors[] = "compte non activé";

                        }else{

                            $session->set('account', $user);
                            $success = 'Authentification réussi'; 

                        }

                    } else {
                        $errors[] = 'Mot de passe invalide';
                    }

                } else {
                    $errors[] = 'Compte inexistant';
                }
            }
                  
            if(isset($errors)){
                return $this->render('login.html.twig', array("errors" => $errors));
            }

            if(isset($success)){

                $entityManager = $this->getDoctrine()->getManager(); 
                $user->setLastConnection(new DateTime);
                $entityManager->flush();


                return $this->render('login.html.twig', array("success" => $success));
            }


        }
        return $this->render('login.html.twig');
    }


    /**
    *@Route("/logout", name="api_logout")
    */
    public function logout(){
            
        if(!$this->get('session')->has('account')){
           throw new AccessDeniedHttpException('Deja deconnecté');
           
       }
       
       if($this->get('session')->has('account')){
        
            //On stocke le message de success dans une variable.
            $success = 'Vous etes déconnecté';
            // on déconnecte en supprimant la variable de session.
            $this->get('session')->remove('account');
       }
       
       if(isset($success)){
           return $this->render('logout.html.twig', array("success" => $success));
           }

       return $this->render('logout.html.twig');
       
   }  
}