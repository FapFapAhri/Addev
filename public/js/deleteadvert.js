document.addEventListener('DOMContentLoaded', function(){
    

    $('#delete-advert').click(function(e){
      
        e.preventDefault();

        $.ajax({
            url : $(this).data('link'),
            type : 'POST',
            data : {
                action : 'delete'
            },
            timeout :  4000,
            dataType : 'json',

            success: function(data){
               
                if(data.delete){
                    
                    

                  $('#exampleModal').modal('toggle');
                  $('body').html(`<div class="alert alert-success" role="alert">
                  L'annonces a bien été supprimé
                </div>`);

                $('body').append(`<div><a class="btn btn-primary"  href="http://127.0.0.1:8000/adverts/" >Retour</a></div>`);

                }
               
 
            },
            error: function(xhr){

                alert( xhr.status ) ;
            },
            beforeSend : function(){
               
            },
            complete : function(){
               
            }

        });

    });

});