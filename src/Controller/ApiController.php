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
use App\Entity\Comment;
use \DateTime;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; 
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;


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

        
        $entityManager->remove($advert);
        $entityManager->flush();
       


        return $this->json(array('delete' => true));
        
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
        
                //on va voir si le compte existe.
                $session = $this->get('session');
                $userRepository = $this->getDoctrine()->getRepository(User::class);
                $user = $userRepository->findOneByEmail($email);
                
                // si le compte existe, on vérifie le mot de passe.
                if($user){

                        if(password_verify($password, $user->getPassword())){

                                if($user->getActive() == 0){
                                    $errors[] = "compte non activé";
                                }else{
                                    // la variable de session de l'utilisateur est crée avec l'objet user
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

            $customer = $this->get('session')->get('account');

            $ar = $this->getDoctrine()->getRepository(Advert::class);
            $authoradvert = $ar->findByAuthor($customer);
       
            
            if(!$authoradvert){
                $errors[] = "Vous n'avez publié aucune annonce";
            }

            if(!isset($errors)){
                $jobr = $this->getDoctrine()->getRepository(JobRequest::class);
                $jobRequest = $jobr->findBy(
                    ['advert' => $authoradvert]
            );
                }    // si tout est bon, on enregistre une date pour garder une trace de la connexion.
                if(!empty($jobRequest)){
                    
                    $session = $this->get('session');
                    foreach ($jobRequest as $job){
    
                     if($job->getStatus()->getId() == 1){
                            $jobRequestSelected[] = $job;
                        }
                    }
                   
                    if (!empty($jobRequestSelected)){
                        $nbJobRequest = count($jobRequestSelected);
                        $session->set('job', $nbJobRequest);
                       
                    }

                }


                $entityManager = $this->getDoctrine()->getManager(); 
                $user->setLastConnection(new DateTime());
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
            $success = true;

            // détruit toute les variables de sessions
            $this->get('session')->clear();
            // on déconnecte en supprimant la variable de session.
            // $this->get('session')->remove('account');
       }
       
       if(isset($success)){
        // return $this->json(array('success' => true ));
           return $this->render('logout.html.twig', array("success" => "Vous avez bien été déconnecté"));
        }
        //    return $this->json(array('success' => false));
       return $this->render('logout.html.twig');
       
   } 
   
    /**
     * @Route("/upload/", name="api_upload", methods={"POST"})
     */
    public function upload(Request $request){

        if(!$this->get('session')->get('account')){
            throw new AccessDeniedHttpException('Vous n\'êtes pas connecté');
        }
        
        $file = $request->files->get('picture_updload') ?? false;

        // dump($file);

        if( $file ){

            if( $file->getError() != 0 ){

                if( $file->getError() == 1 || $file->getError() == 2 || $file->getSize() > $this->getParameters('uploaded_file_max_size') ) // Size file ;

                $errors[] = 'Taille trop grande du fichier .';

                elseif( $file->getError() == 3 )  // Erreur serveur ;

                $errors[] = 'Upload (téléversement) impossible .';

                elseif( $file->getError() == 6 ) // Le répertoire Temporaire n'existe pas ;

                    $errors[] = 'Le dossier temporaire du serveur n\'existe pas .';

                elseif( $file->getError() == 7 )  // CHMOD Access Denied ;

                    $errors[] = 'Le serveur n\'a pas l\'autorisation d\écriture du fichier .';

                else // File Extends Error ;

                    $errors[] = 'L\'extension n\'a pas été reconnue par le serveur .';
            }
            else { // Uploads Ok ;

                //finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getPathname());
                $typeMime = $file->guessExtension() ;

                if(!in_array($typeMime, $this->getParameter('authorized_images_types') ) ) {

                    $errors[] = 'Ce type de fichier n\'est pas autorisé .';

                } else {

                    $fileName = md5( uniqid().rand().time() ) . '.'.$typeMime ;

                    $file->move(
                        $this->getParameter('directory_upd') ,
                        $fileName
                    ) ; // Img save ;

                        $session = $this->get('session');
                        $email = $session->get('account')->getEmail();
                        $userRepository = $this->getDoctrine()->getRepository(User::class);
                        $user = $session->get('account');

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->merge($user);
                        $user->setImg($fileName);
                        $entityManager->flush();
                        // $image = new Image;

                        // $image->setName( $fileName )->setUser( $this->get('session')->get('account') ) ;

                        // $imageManager = $this->getDoctrine()->getManager() ;

                        // $imageManager->merge($image);
                        // $imageManager->flush();

                        // return $this->json( array('success' => true ) ) ;
                        $success[]="L'image a bien été chargée";
                        return $this->render('profile.html.twig', array("success" => $success));
                    }
                }
            } else  // Uploads vide ;
            
            $errors[] = 'Vous n\'avez rien envoyé .';

        if( isset( $errors ) )
        return $this->render('profile.html.twig', array("errors" => $errors) );
            // return json_encode( array("errors" => $errors) ) ;

    }

    /**
     * @Route("/edit-profile/", name="api_edit_profile", methods={"POST"})
     */

    public function editProfile(Request $request ){ 
        
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException();
           
         }
        //variable des request
        

        $name = $request->request->get('name') ?? null;
        $firstname = $request->request->get('firstname') ?? null;
        $phone = $request->request->get('phone') ?? null;
        
      
        //On vérifie que les champs ne soient pas vide.
        if(

            isset($name)
            && isset($firstname)
      
        ){
            //verification de champs

            if(!preg_match('#^[a-z]{2,60}$#i', $name)){
                $errors[] = 'Nom incorrect (lettres autorisés seulement)';
            }

            if(!preg_match('#^[a-z]{2,60}$#i', $firstname)){
                $errors[] = 'Prenom incorrect (lettres autorisés seulement)';
            }
            if($phone){
                if(!preg_match('#^0[1-9]([-. ]?[0-9]{2}){4}$#', $phone)){
                    $errors[] = 'numéro de téléphone invalide';
                }
            }

         
            //si il n'y a pas d'erreurs on crée l'utilisateur.

            if(!isset($errors)){

              


                    // l'objet user est aussitôt "hydraté"




                    $session = $this->get('session');
                    $email = $session->get('account')->getEmail();
                    if ($session->get('account')->getName()==$name && $session->get('account')->getFirstname()==$firstname && $session->get('account')->getPhone()==$phone){
                        $errors[] = "Aucun champs modifié ";

                    }
                    // on vérifie les champs pour rien si les deux champs ont bien été modifié.
                    if(!isset($errors)){

                    if($session->get('account')->getName()!=$name && $session->get('account')->getFirstname()!=$firstname && $session->get('account')->getPhone() != $phone){

                        $success[]= "Le nom , prénom  et numéro de téléphone ont bien été modifié.";

                    }else{

                                                // si un des trois champs à été modifié, il faut savoir lequel.
                                                if ($session->get('account')->getName() != $name){
                                                    $success[]= "Le nom ";
                                                }
                                                if ($session->get('account')->getFirstname() != $firstname){
                                                    $success[] = "Le prénom ";
                                                }
                                                if ($session->get('account')->getPhone() != $phone){
                                                    $success[]= "Le numéro de téléphone ";
                                                }
                        
                                                if ($success){
                                                    $countsuccess = count($success);
                                                    if($countsuccess == 1){
                                                        $success[] = "a bien été modifié.";
                                                    }else{
                                                        $success[] = "ont bien été modifié.";
                                                    }
                                                }

                    }
                      

                       
                       

                        // le nom où/et prénom sont modifié selon les cas de figures
                        $userRepository = $this->getDoctrine()->getRepository(User::class);
                        $user1 = $userRepository->findOneByEmail($email);
                        $user = $session->get('account');
    
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->merge($user);
                        if (!$phone){
                            $user1->setPhone(NULL);
                            $user->setPhone(NULL);
                        }else{
                            $user1->setPhone($phone);
                            $user->setPhone($phone);
                        }
                        
                        $user1->setName($name);
                        $user1->setFirstname($firstname);
                        $user->setName($name);
                        $user->setFirstname($firstname);
                        $entityManager->flush();

                    
                    }



 
              
                   
           
                
            }
        }
             
        
        if(isset($errors)){
            // return $this->json( array('success' => false,'errors' => $errors ) ) ;
            return $this->render('profile.html.twig', array("errors" => $errors));
        }
        
        if(isset($success)){
            // return $this->json( array('success' => true ) ) ;
            return $this->render('profile.html.twig', array("success" => $success));
        }
        // return $this->json( array('success' => true ) ) ;
        return $this->render('profile.html.twig');
    }

   /**  
    * @Route("/editmail/{id}/{token}/", name="api_editmail", requirements={"id"="\d+", "token"="[a-fA-F0-9]{32}"}) 
    */ 
    public function editMail(Request $request,$id, $token){ 

        // on vérifie si la personne est connecté sinon on envoie une erreur.
        if(!$this->get('session')->has('account')){ 
           throw new AccessDeniedHttpException('Non connecté');
        } 

         // Récupération du repository.
         $userRepository = $this->getDoctrine()->getRepository(User::class); 
         $user1 = $userRepository->findOneById($id);

        if($user1 && $user1->getActivationToken() == $token){
            $email = $request->request->get('email') ?? null;
            $coemail = $request->request->get('confirmemail') ?? null;
            
            // Vérification des champs du formulaire.
            if(
                isset($email)
                && isset($coemail)
            ){
    
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $errors[] = 'Email incorrect';
                }
                if(!filter_var($coemail, FILTER_VALIDATE_EMAIL)){
                    $errors[] = 'Format confirmation incorrect';
                }
    
                if($email != $coemail){
                    $errors[] = 'La confirmation est incorrect';
                }
                
                if(!isset($errors)){

                    $mailuser=$user1->getEmail();
                    
                    // Si le l'adresse mail n'existe pas et que les champs sont bon, on confirme l'enregistrement.
                    if($mailuser==$email){
                        $errors[] = 'L\'adresse e-mail est déjà prise !';
                    }else{
                        $success="Votre adresse e-mail à bien été modifié en " . $email;
                    }
                        
                    
                }
                      
                if(isset($errors)){
                    return $this->render('changemail.html.twig', array("errors" => $errors));
                }
    
                if(isset($success)){
                    $session = $this->get('session');
                    $usersession = $session->get('account');


                   
                    $entityManager = $this->getDoctrine()->getManager();

                    
                    $user1->setEmail($email);
                   
                    $entityManager->flush();


                    // on détruit la variable de session changemail (évite de spam la génération de token)
                    $this->get('session')->remove('changemail');
                    return $this->render('changemail.html.twig', array("success" => $success));
                }
    
    
            }else{

                $errors[]="Remplissez les champs !";
                return $this->render('changemail.html.twig', array("errors" => $errors ));
            }
            return $this->render('changemail.html.twig');
            
        } else { 

            throw new NotFoundHttpException(); // 403 
        }
    }
    
    /**
     * @Route("/apply/{idAdvert}/{idAdvertAuthor}/{status}", name="api_apply", requirements={"idAdvert"="\d+","idAdvertAuthor"="\d+","status"="\d+"})
     */
     public function applyJob(Request $request,$idAdvert, $idAdvertAuthor,$status){
        
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
        }


        $devSession = $this->get('session')->get('account');


        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $advertAuthor= $userRepo->findOneById((int)$idAdvertAuthor);
        $authorJobRequest = $userRepo->findOneById($devSession->getId());

       


        $advertRepository = $this->getDoctrine()->getRepository(Advert::class);
        $allAdvert =$advertRepository->findAll();
       
       
  
        
        if(!$allAdvert){
            throw new NotFoundHttpException('pas trouvé');
        }

        $advertSelected = $advertRepository->findOneById($idAdvert);
        
        
        $jr = $this->getDoctrine()->getRepository(JobRequest::class);
        $jobRequest = $jr->findOneByAdvert($authorJobRequest->getEmail());

       
    
    
        $sjr = $this->getDoctrine()->getRepository(StatusJob::class);
        $statusjob = $sjr->findOneById(1);
      
        if (empty($jobRequest)){
            
       

                      // l'objet user est aussitôt "hydraté"

                      $statusjob = $sjr->findOneById(1);
                  
                      $job = new JobRequest();
                    
                      $job->setAuthor($authorJobRequest);
                      $job->setAdvert($advertSelected);
                      $job->setDateApply(new DateTime());
          
                      $job->setStatus($statusjob);
                      
            
                      
                      // on récupère le manager
                      $entityManager = $this->getdoctrine()->getManager();
          
                      // on fusionne deux objet author qui sont en réalité la même personne
                      $entityManager->merge($job);
          
                  // Puis on "execute" pour que tout soit enregistré dans la base de donnée.
                      $entityManager->flush();
                      $success="La candidature a bien été envoyé";
                    if(!isset($errors)){
                        if ($status==1){
                            $job = true;
                        }
                        return $this->render('advert.html.twig',array('advert'=>$advertSelected,'success'=>$success));
                    }
                     
            return $this->render('advert.html.twig',array('advert'=>$advertSelected,'errors'=>$errors));

        }else{


               

                if($jobRequest->getAuthor()->getId() == $authorJobRequest->getId() && $jobRequest->getAdvert()->getId() == $advertSelected->getId()){
                    $errors="Vous avez déjà postulé pour l'annonce";
                   
                }else{

                    $job = new JobRequest();
        
                    $job->setAuthor($authorJobRequest);
                    $job->setAdvert($advertSelected);
                    $job->setDateApply(new DateTime());
                
                    $job->setStatus($statusjob);
                    
          
                    
                    // on récupère le manager
                    $entityManager = $this->getdoctrine()->getManager();
                   
                    $entityManager->persist($job);
        
                // Puis on "execute" pour que tout soit enregistré dans la base de donnée.
                    $entityManager->flush();
            
                    $success="La candidature a bien été envoyé";

                    return $this->render('advert.html.twig',array('advert'=>$advertSelected,'success'=>$success));




                }

                if(isset($errors)){

                    return $this->render('advert.html.twig',array('advert'=>$advertSelected,'errors'=>$errors));
                }
      

                

                  
        }
        
      
        
      


        
        return $this->render('advert.html.twig',array('advert'=>$advert));
        
    }
    
    /**
     * @Route("/changestate/{idTargetedAdvert}/{idDevelopper}/{idStatus}/{action}", name="api_changestate", requirements={"idTargetedAdvert"="\d+", "idDevelopper"="\d+", "idStatus"="\d+", "action"="\d+"})
     */
     public function changeState(Request $request,$idTargetedAdvert, $idDevelopper,$idStatus,$action){
        
        if(!$this->get('session')->has('account')){
            throw new AccessDeniedHttpException('Vous n\'est pas connecté');
        }
       
        $customer = $this->get('session')->get('account');
        $ar = $this->getDoctrine()->getRepository(Advert::class);
        $advertAuthor = $ar->findByAuthor($customer);
        $selectedAdvert = $ar->findOneById($idTargetedAdvert);
        
        if(!$advertAuthor){
            $errors[] = "Vous n'avez publié aucune annonce";
         }

         if(!isset($errors)){

                $userRepo = $this->getDoctrine()->getRepository(User::class);
                $user=$userRepo->findOneByEmail($customer->getEmail());
                $developper = $userRepo->findOneById($idDevelopper);
      


            $jobr = $this->getDoctrine()->getRepository(JobRequest::class);
            $jobRequest = $jobr->findBy(
                ['advert' => $advertAuthor]
            );
            if (!$jobRequest){
                $session->set('job', 0);
                return $this->render('projectmanager.html.twig');
            }

     
           
           foreach($jobRequest as $job){
               if ($job->getAdvert()->getId() == (int)$idTargetedAdvert && $job->getAdvert()->getAuthor()->getId() == $customer->getId() && $job->getAuthor()->getId() == (int)$idDevelopper ){
                $oneJob= $job;
               }
           }
          
            $oneJobRequest=$jobr->findOneByAdvert($oneJob);
            
           
            $sjr = $this->getDoctrine()->getRepository(StatusJob::class);

            $statusjob = $sjr->findOneById((int)$idStatus);
            $state=$statusjob->getId();

                if($action == 1){
                    

                   
                        if($state == 2 || $state== 3 ){

                            $oneJob->setStatus($statusjob);
                            $entityManager = $this->getdoctrine()->getManager();
               
                            $entityManager->merge($oneJob);
    
                            $entityManager->flush();
                            // message personnalisé
                            $string = $oneJob->getAuthor()->getName(). " " .  $oneJob->getAuthor()->getFirstName() ." a été ". $oneJob->getStatus()->getLabel();
                            $success= $string ." sur le projet ( Annonce N°".$oneJob->getAdvert()->getId().")";

                            if($this->get('session')->has('job')){

                                foreach($jobRequest as $request){
                
                                    if($request->getStatus()->getId() == 1 && $request->getAdvert()->getAuthor()->getId()== $customer->getId()){
                                        $allPendingRequest[] = $request;
                                    }
                                }
                                if(isset($allPendingRequest)){
                                    $nbJobRequest = count($allPendingRequest);
                                    $session = $this->get('session');
                                    $session->set('job', $nbJobRequest);
                                
                                }
                
                            }


                        }
                        
                        if($state == 4){

                            $allJobRequest = $jobr->findByAdvert($advertAuthor);
    
                            foreach ($allJobRequest as $jobRequest){
                                if($jobRequest->getAdvert()->getId() == (int)$idTargetedAdvert && $jobRequest->getAdvert()->getAuthor()->getId() == $customer->getId() ){
                                    $stateJob = $jobRequest;
                                    $stateJob->setStatus($statusjob);
                                    $tabstate[]=$stateJob;
                                    
                                }
                            }
                            
                            $entityManager = $this->getdoctrine()->getManager();
                            
                            $entityManager->flush();

                            
                            $success="Le projet ( Annonce N°".$oneJob->getAdvert()->getId().") est désormais clos !";
                            return $this->render('projectmanager.html.twig',array('jobs'=>$allJobRequest,'success'=>$success));
                        }
                } 



                if($action == 2){
                    if($state == 4){


                        
           
                            $newRate = $request->request->get('rate') ?? null;
                            $newComment = $request->request->get('comment') ?? null;
                            $lengthRate = mb_strlen($newRate) ;

                            if (!empty($newRate) && !empty($newComment)){

                                // if( $lengthRate >= 1 ) {

                                //     if(!preg_match("#^.{2,30000}$#", $newRate)&& ($newRate[0] == "5" && $lengthRate == 1 ) ){
                                //         $errors[] = "Note invalide";
                                //     }
                                // }
                                // else {
                                    
                                //     $errors[] = "Veuillez noter le développeur, s'il vous plaît";
                                // }
                            
                                if(!preg_match("#^.{2,30000}$#", $newComment)){
                                    $errors[] = "Commentaire invalide( 2 à 30 000 caractères)";
                                }
                    
                                if(isset($errors)){
                                    return $this->render('projectmanager.html.twig',array('jobs'=>$jobRequest,'errors'=>$errors));
                                }
                                $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
                                $allComment = $commentRepository->findByAdvert($advertAuthor);
                                
                                if (!empty($allComment)){
                                   
                                    foreach($allComment as $oneComment){

                                        if ($oneComment->getAuthor()->getId() == $customer->getId()
                                            && $oneComment->getAdvert()->getId() == (int)$idTargetedAdvert 
                                            && $oneComment->getTargetingUser()->getId() == (int)$idDevelopper
                                        ){
                                                 $oneCommentExist[]=$oneComment;
                                        } 
                                        
                                    }
                                
                                }
                                $oneCommentAuthor = $commentRepository->findOneByAdvert($selectedAdvert);
                               if(isset($oneCommentExist)){
                                $errors[]="Le développeur ".$oneJob->getAuthor()->getName()." ".$oneJob->getAuthor()->getFirstname() ."a déjà été noté pour l'annonces N°".$oneJob->getAdvert()->getId();
                               }

            
                                
                                if(!isset($errors)){
                                    // remplace les , par . pour respecter le format et que le typecasting (transtypage puisse marcher)
                                    $newRate =  str_replace(',' , '.' , $newRate ) ; 
                                   
                                    $entityManager = $this->getdoctrine()->getManager();
                                    $statusRated = $sjr->findOneById(5);
                                 
                                    $oneJob->setStatus($statusRated);
                               
                                    // if($oneCommentAuthor->getAdvert() != $selectedAdvert && $idDevelopper != $oneCommentAuthor->getTargetingUser()->getId() && $oneCommentAuthor->getAuthor()->getEmail() != $customer->getEmail() ){
                                        $comment = new Comment();
                                        $comment -> setAuthor($user);
                                        $comment -> setTargetingUser($developper);
                                        $comment -> setComment($newComment);
                                        $comment -> setMark((double)$newRate);
                                        $comment -> setAdvert($selectedAdvert);
                                    
                                        
                                      
                                        $entityManager->persist($comment);
        
                                        $entityManager->flush();
                                        
                                        $success="Merci d'avoir pris le temps de noter ".$developper->getName()." ".$developper->getFirstname(). "pour le projet (Annonces N°".$selectedAdvert->getId()." :-)
                                         ) !";
        
                                   
                                            return $this->render('projectmanager.html.twig',array('jobs'=>$jobRequest,'success'=>$success));
                                        
                                 }else{

                                    
                                    return $this->render('projectmanager.html.twig',array('jobs'=>$jobRequest,'errors'=>$errors));

                                 }
                                                
                                if (isset($success)){
                                    return $this->render('projectmanager.html.twig',array('jobs'=>$jobRequest,'success'=>$success));
                                }
                                    
                            }else{
                                $errors[]="Veuillez remplir le champ et noter";
                            }
                            
                                if(isset($errors)){
                                    return $this->render('projectmanager.html.twig',array('jobs'=>$jobRequest,'errors'=>$errors));
                                }

                        
                    }

                }
                


            if (isset($success)){


                return $this->render('projectmanager.html.twig',array('jobs'=>$jobRequest,'success'=>$success));
            }




            return $this->render('projectmanager.html.twig',array('jobs'=>$jobRequest));
         }

       
            

              
        
       
            return $this->render('projectmanager.html.twig');
        
    }


}