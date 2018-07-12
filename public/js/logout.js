document.addEventListener('DOMContentLoaded', function(){
 

    $('#logout').click(function(){
     
       
 
        $.ajax({
            url : $(this).data('link'),
            type : 'POST',
            data : {
                action : 'logout'
            },
            timeout :  4000,
            dataType : 'json',
            success: function(data){
            
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