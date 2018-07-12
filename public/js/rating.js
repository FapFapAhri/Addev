(() => {

    let stars = document.querySelectorAll(".star") ;

    for( let i = 0 , size = stars.length ; i < size ; i++ ) {

        stars[i].addEventListener("mousemove" , function(e) {

            let x = e.pageX || e.clientX ;

            let = middle = ( this.offsetLeft + (this.offsetWidth / 2) ) ;  

            if( x >= middle ) 

                this.setAttribute("name" , "star-half") ;
            
            if(x > ( middle + 10 )  )

                this.setAttribute("name" , "star") ;


            if( x < middle )
                
                this.setAttribute("name" , "star-outline") ;    

            ajusteDefault() ;

        }) ;

        stars[i].addEventListener("click" , function(){

            let indexStar = this.getAttribute("data-index-star") ,
                statusStar = this.getAttribute("name") ;

            if( indexStar >= 1 && indexStar <= 5 ) {

                if( /.*star-half.*/.test( statusStar ) ) 

                    indexStar -= 1/2 ;
                
                else if( /.*star-outline.*/.test( statusStar ) )
            
                        indexStar -= 1 ;
                    
                $('input[type="hidden"].rate-input').val(indexStar) ;
            }

        } ) ;
    }

    let ajusteDefault = () => {

        let exit = false ;

        for( let i = (stars.length - 1 ) ;  i > 0 ; i-- ) {

            if( exit ) break ;

            if( !/.*star-outline.*/.test(stars[i].getAttribute("name") )  && (i) ) {// Si l'étoile n'est pas vide et que ce n'est pas la premiére étoile on remplies les étoiles précédentes ;
                
                for( let j=0 , size = i ; j < i ; j++)
                    
                    stars[j].setAttribute("name" , "star" ) ;

                exit = true ;
            } 
        }
    }

    document.querySelector(".contains-stars").addEventListener("mouseout", function(e){

            let coo = { x : e.pageX || e.clientX , y : e.pageY || e.clientY } ;

            if( coo.x > (this.offsetLeft + this.offsetWidth) || coo.y > ( this.offsetTop + this.offsetHeight ) || coo.y < this.offsetTop || coo.x < this.offsetLeft )
                
                for( let i = 0 , size = stars.length; i < size; i++ )

                    stars[i].setAttribute("name" , "star-outline")  ;
    });

})();