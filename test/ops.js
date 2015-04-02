/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


(function(window, $, undefined){
    
    var onSuccess = function(data){
        $('#results').text(data);
    };
    
    $('#loginFire').click(function(e){
        //first, get user name
        var name = $('#loginUser').val();
        //verify it exists
        if(!name){
            alert("We need a fucking name!");
            return;
        }
        $.ajax({
            type: "POST",
            url: 'backend.php',
            data: {
                userLogin: name
            },
            success: onSuccess,
            dataType: 'text'
        });
    });
    function showAjax(){
        //expected return type: dataType: xml,json,script,text,html
    }
    
})(window, jQuery);