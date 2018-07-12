<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\Recaptcha;
use App\Entity\User;
use App\Entity\Advert;
use App\Entity\JobRequest;
use App\Entity\StatusJob;
use \DateTime;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; 



class FrontalController extends Controller{

/**
*@Route("/", name="home")
*/
public function index(){
    $dateNow = new DateTime();
    $advertRepository = $this->getDoctrine()->getRepository(Advert::class);
     $lastAd = $advertRepository -> lastThreeAdverts();
     $statusjobRepository = $this->getDoctrine()->getRepository(StatusJob::class);
    $status=  $statusjobRepository -> findAll();
    if (!$status){
        // on fixe le nombre de status (dans StatusJob)
        $nbstatus=5;
        for($i=1; $i<=$nbstatus; $i++){
        $statusjob[$i-1] = new Statusjob();// on créer le nombre de nouveau objet qu'on s'est fixé avec $nbstatus.
        }

        $statusjob[0]->setLabel('En Attente');
        $statusjob[1]->setLabel('Refusé');
        $statusjob[2]->setLabel('Accepté');
        $statusjob[3]->setLabel('Projet Clos');
        $statusjob[4]->setLabel('A été Noté');

        $entityManager = $this->getdoctrine()->getManager();

        for($i=1; $i<=$nbstatus; $i++){
        $entityManager->persist($statusjob[$i-1]);
        }
        $entityManager->flush();

    }
    if ($lastAd){ 
        foreach ($lastAd as $lastDate) {
             $dateToCompare = $lastDate->getPostDate();
              $lastDiffDate[] = date_diff($dateToCompare,$dateNow);
             }
     }else{
          return $this->render('index.html.twig');
    } 
    
    return $this->render('index.html.twig',array('lastThreeAd'=> $lastAd,'lastDiffDate'=> $lastDiffDate));
}

   /**
    *@Route("/register", name="register")
    */
    public function register(Request $request, Recaptcha $recaptchaService ){ 
        
        if($this->get('session')->has('account')){
            throw new AccessDeniedHttpException();
           
         }
        //variable des request
        $email = $request->request->get('email') ?? null;
        $name = $request->request->get('name') ?? null;
        $firstname = $request->request->get('firstname') ?? null;
        $password = $request->request->get('password') ?? null;
        $copassword = $request->request->get('confirm_password') ?? null;
        $recaptcha = $request->request->get('g-recaptcha-response') ?? null;
       
        //On vérifie que les champs ne soient pas vide.
        if(
            isset($email)
            && isset($name)
            && isset($firstname)
            && isset($password)
            && isset($copassword)
            && isset($recaptcha)        
        ){
            //verification de champs
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors[] = 'Email incorrect';
            }

            if(!preg_match('#^[a-z]{2,60}$#i', $name)){
                $errors[] = 'Nom incorrect (lettres autorisés seulement)';
            }

            if(!preg_match('#^[a-z]{2,60}$#i', $firstname)){
                $errors[] = 'Prenom incorrect (lettres autorisés seulement)';
            }

            if(!preg_match('#^.{2,300}$#', $password)){
                $errors[] = 'Mot de passe incorrect';
            }
            if(!preg_match('#^.{2,300}$#', $password)){
                $errors[] = 'format confirmation invalide';
            }

            if($password != $copassword){
                $errors[] = 'Confirmation invalide';
            }

            if(!$recaptchaService->isValid($recaptcha, $request->server->get('REMOTE_ADDR'))){
                $errors[] = 'Recaptcha invalide';
            }

            //si il n'y a pas d'erreurs on crée l'utilisateur.

            if(!isset($errors)){
               
                 $userRepository = $this->getDoctrine()->getRepository(User::class);
            
                 $verifUser = $userRepository->findOneByEmail($email);

                if($verifUser == false){

                    // On créer un nouvel objet
                    $user = new User();

                    $adminLevel = 0;
                    $token = md5(rand().time().uniqid());
                    $remoteAdress = $request->server->get('REMOTE_ADDR');
                    $active = 0;
                    

                    // l'objet user est aussitôt "hydraté"
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setFirstname($firstname);
                    $user->setAdminLevel($adminLevel);
                    $user->setActivationToken($token);
                    $user->setActive($active);
                    $user->setPassword(password_hash($password, PASSWORD_BCRYPT)); 
                    $user->setRegisterDate(new DateTime()); 
                    $user->setRegisterIp($remoteAdress); 

                    // on récupère le manager
                    $entityManager = $this->getdoctrine()->getManager();

                    // on l'enregistre auprès de doctrine
                    $entityManager->persist($user);

                   // Puis on "execute" pour que tout soit enregistré dans la base de donnée.
                    $entityManager->flush();

                    // // ceci c'est un dump pour la simulation activation compte
                    // dump($this->generateUrl('activation', array(
                    //     'id' => $user->getId(),
                    //     'token' => $user->getActivationToken()
                    // )));



                    //message de success
                    $success = 'Compte créé !';
                   
                } else {
                     //message d'erreur
                    $errors[] = 'Email déjà utilisé';
                }
                
            }
        }
             
        
        if(isset($errors)){
            // return $this->json( array('success' => false,'errors' => $errors ) ) ;
            return $this->render('register.html.twig', array("errors" => $errors));
        }
        
        if(isset($success)){
            // return $this->json( array('success' => true ) ) ;
            return $this->render('register.html.twig', array("success" => $success));
        }
        // return $this->json( array('success' => true ) ) ;
        return $this->render('register.html.twig');
    }
    
    
    /**
    *@Route("/profile", name="profile")
    */
    public function profile(){
        
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
            
        }
       
        
        return $this->render('profile.html.twig');
         
    }

   /**  
    * @Route("/activation/{id}/{token}/", name="activation", requirements={"id"="\d+", "token"="[a-fA-F0-9]{32}"}) 
    */ 
   public function activation($id, $token){ 

        // on vérifie si la personne est connecté sinon on envoie une erreur.
        if($this->get('session')->get('account')){ 
           throw new AccessDeniedHttpException('Déjà connecté');
        } 

         // Récupération du repository.
         $userRepository = $this->getDoctrine()->getRepository(User::class); 
         $user = $userRepository->findOneById($id);

        if($user && $user->getActivationToken() == $token && $user->getActive() == false){

            $user -> setActive(1); // On modifie le champ concernant l'activation 0 désactivé et 1 activé
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush(); 
            
            return $this->render('activation.html.twig'); 
            
        } else { 

            throw new NotFoundHttpException(); // 403 
        }
    }

    /**
     * @Route("/addadvert",  name ="addadvert")
     */
    public function addAdvert(Request $request){
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
            
        }

        $title = $request->request->get('title') ?? null;
        $content = $request->request->get('content') ?? null;
        $contract = $request->request->get('contractType') ?? null;
    
        if(isset($title) && isset($content)){
           
            if(!preg_match("#^.{2,150}$#", $title)){
                $errors[] = "Titre invalide ( 2 à 150 caractères)";
            }

            if(!preg_match("#^.{2,30000}$#", $content)){
                $errors[] = "Contenu ( 2 à 30 000 caractères)";
            }

            if(!isset($errors)){
                
                $id=$this->get('session')->get('account')->getId();
                $entityManager = $this->getDoctrine()->getManager(); 
                
                $advertRepository = $this->getDoctrine()->getRepository(Advert::class);
                $advert = new Advert();

                $userRepository= $this->getDoctrine()
                ->getRepository(User::class);
                $author= $userRepository->findOneById($id);

                //hydratation de l'objet Advert.
                $advert->setTitle($title);
                $advert->setContent($content);
                $advert->setContractType($contract);
                $advert->setPostDate(new DateTime);
                $advert->setAuthor($author); // author est un objet qui désigne en réalité un User

                $entityManager->persist($advert);

                $entityManager->flush();
                

            }

            if(isset($errors)){
                return $this->render('addadvert.html.twig', array("errors" => $errors));
            }else{
                return $this->render('addadvert.html.twig', array("success" => "L'annonce a bien été posté"));
            }
        }

        
        return $this->render('addadvert.html.twig');
    }

    /**
    * @Route("/advert/{id}/", name="advert", requirements={"id"="[0-9]{1,5}"})
    */
    public function displayAdvert(Request $request, $id){

        $session = $this->get('session');
        $usersession = $session->get('account');


        $advertRepo= $this->getDoctrine()->getRepository(Advert::class);
        $advert = $advertRepo->findOneById((int)$id);
        if($this->get('session')->has('account')){
            
        $userRepo= $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findOneByEmail($usersession->getEmail());
      

        $jrRepo= $this->getDoctrine()->getRepository(JobRequest::class);
        $jobs = $jrRepo->findAll();
        
        foreach ($jobs as $job){


            if($job->getAuthor()->getId()==$usersession->getId() && $job->getAdvert()->getId()== (int)$id){
                $jobRequestSelected[] = $job;
            }
        }
       
        if (isset($jobRequestSelected)){

            return $this->render('advert.html.twig', array("advert" => $advert,"jobs"=> $jobRequestSelected));
            
        }
           
        }

        return $this->render('advert.html.twig', array("advert" => $advert));
    }

 
    /**
     * @Route("/adverts/", name="adverts")
     */
    public function adverts(){

        $advertRepo= $this->getDoctrine()->getRepository(Advert::class);
        // $adverts = $advertRepo->findAll();
         $adverts = $advertRepo->descAdverts();

        if($this->get('session')->has('account')){

            $session = $this->get('session');
            $usersession = $session->get('account');
            
            $jrRepo= $this->getDoctrine()->getRepository(JobRequest::class);
            $jobs = $jrRepo->findAll();

           
            if (!empty($jobs)){
                foreach($jobs as $job){
                    if ($job->getStatus()->getId()==5 || $job->getStatus()->getId() == 4 ){
                        $jobClosed[]=$job;
                    }
                }

                dump($session->get('account'));
            }

           
           
           if (!isset($jobs)){
                return $this->render('adverts.html.twig', array("adverts" => $adverts));
           }else{

            if (isset($jobClosed)){
                return $this->render('adverts.html.twig', array("adverts" => $adverts, "jobs"=> $jobs,"jobClosed"=>$jobClosed));
            }
           

           }

           
               
            

            
        }

        return $this->render('adverts.html.twig', array("adverts" => $adverts));

    }
    /**
     * @Route("/changemail/", name="changemail")
     */
    public function changemail(){

        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
        }

        if (!$this->get('session')->has('changemail')){
            // si la personne a déjà demandé un changement de mail par son adresse mail, on n'exécute

                $session = $this->get('session');
                $usersession = $session->get('account');
                $mailsession = $usersession->getEmail();


                $ury = $this->getDoctrine()->getRepository(User::class);
                $user1 = $ury->findByEmail($mailsession);
            
                
              
          
               $entityManager = $this->getDoctrine()->getManager();
                
                $this->get('session')->set('changemail',true);
                $token = md5(rand().time().uniqid());
                // $user1->setToken($token);
               

              
                $entityManager->persist($user1);
                $entityManager->flush();
                $success[]="Un mail de confirmation a été envoyé à votre boîte mail ";
                $success[]="[/!\simulation - en réalité, l'url n'apparait pas / sécurité // ]";
                $success[]=$this->generateUrl('api_editmail', array(
                    'id' => $usersession->getId(),
                    'token' => $token
                ));
  
                
                return $this->render('changemail.html.twig', array('success' => $success));
        }


        return $this->render('changemail.html.twig');

    }

    
    /**
     * @Route("/editadvert/{id}", name="editadvert", requirements={"id"="[0-9]{1,5}"})
     */
    public function editadvert(Request $request, $id){
        
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
            
        }

        $ar = $this->getDoctrine()->getRepository(Advert::class);

        $advert = $ar->findOneById($id);

        if(!$advert){
            throw new AccessDeniedHttpException('pas trouvé');
        }

        if($this->get('session')->get('account')->getId() != $advert->getAuthor()->getId()){
            throw new AccessDeniedHttpException('pas auteur');
        }

        $title = $request->request->get('title') ?? null;
        $content = $request->request->get('content') ?? null;
        $contract = $request->request->get('contractType') ?? null;
    
        if(isset($title) && isset($content)){
           
            if(!preg_match("#^.{2,150}$#", $title)){
                $errors[] = "Titre invalide ( 2 à 150 caractères)";
            }

            if(!preg_match("#^.{2,30000}$#", $content)){
                $errors[] = "Contenu invalide ( 2 à 30 000 caractères)";
            }

            if(!isset($errors)){
                
                $entityManager = $this->getDoctrine()->getManager(); 
                $userRepository= $this->getDoctrine()->getRepository(User::class);

                $advert->setTitle($title);
                $advert->setContent($content);
                $advert->setContractType($contract);
                $advert->setLastModified(new DateTime);
                $entityManager->flush();

                return $this->render('editadvert.html.twig', array("success" => "L'annonce a bien été modifié"));

            }

            if(isset($errors)){
                return $this->render('editadvert.html.twig', array("errors" => $errors, 'advert' => $advert));
            }
        }

        return $this->render('editadvert.html.twig', array('advert' => $advert));

        
    }
    
    /**
     * @Route("/admin/", name="admin")
     */
    public function admin(){

        // vérification 
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
        }
       
        if($this->get('session')->get('account')->getAdminLevel() < 1){
            throw new AccessDeniedHttpException('Vous n\'êtes pas modérateur ou administrateur');
        }

        $mr = $this->getDoctrine()->getRepository(User::class);
        $members = $mr->findAll();

        if(!$members){
            throw new AccessDeniedHttpException('Aucun membre trouvé');
        }

        
        // Si un au moins un utilisateur est trouvé, toutes les informations est envoyé sur la page admin
        return $this->render('admin.html.twig',array('members'=>$members));

    }

     /**
     *@Route("/faq/", name="faq")
     */
    public function faq(){

        return $this->render('faq.html.twig');
    }

      /**
     *@Route("/info/", name="info")
     */
    public function info(){
        
        return $this->render('info.html.twig');
    }

    /**
     * @Route("/project-manager/", name="project")
     */
    public function gotoProjectManager(){

        // vérification 
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
        }
       
        $customer = $this->get('session')->get('account');
        // if($this->get('session')->get('account')->getAdminLevel() < 1){
        //     throw new AccessDeniedHttpException('Vous n\'êtes pas modérateur ou administrateur');
        // }

        $ar = $this->getDoctrine()->getRepository(Advert::class);
        $authoradvert = $ar->findByAuthor($customer);
       
        
        if(!$authoradvert){
            $errors[] = "Vous n'avez publié aucune annonce";
         }

         if(!isset($errors)){
            $jobr = $this->getDoctrine()->getRepository(JobRequest::class);
            $jobrequest = $jobr->findBy(
                ['advert' => $authoradvert]
            );
            
            return $this->render('projectmanager.html.twig',array('jobs'=>$jobrequest));
         }
         dump($this->get('session')->all());
        // $jobr = $this->getDoctrine()->getRepository(JobRequest::class);

        // $jobrequest = $jobr->findBy(
        //     ['advert_id' => $]
        // );
        // $jobrequest = $jobr->findByAdvertId();



        
        // Si un au moins un utilisateur est trouvé par rapport à une annonce, toutes les informations est envoyé sur la page projet
        return $this->render('projectmanager.html.twig');

    }
}

