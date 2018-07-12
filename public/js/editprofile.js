document.addEventListener('DOMContentLoaded', function(){
    
    var remove = function(){
        $('body').find('#exampleModal').remove();
    }
        $('#delete-advert').click(function(e){
          var linkAd = $(this).data('retour');
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
    
                     
                      $('body').find('.msg').html(`<div class="alert alert-success" role="alert">
                        L'annonces a bien été supprimé
                        `);
                        
                    // $('.row').find('.msg').append();
                    setTimeout(4000,remove);
                    // $('body').find('#exampleModal').remove();
    
    
                    }
                   
     
                },
                error: function(xhr){
    
                    alert( xhr.status ) ;
                },
                beforeSend : function(){
                    setOverlay();
                },
                complete : function(){
                    removeOverlay();
                }
    
            });
    
        });
    
    });