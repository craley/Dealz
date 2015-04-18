/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App = (function(window, $, module){
    var app = module || {};
    
    var sv = app.sv = {};
    
    sv.loginSuccess = function(data){
        app.core.loadUser(data);
    };
    sv.loginFailure = function(){
        
    };
    
    
    sv.serverRegister = function(username, pswd, email){
        $.ajax({
            type: "POST",
            url: 'services/controller.php',
            data: {
                action: 'register',
                userLogin: username,
                pswd: pswd,
                email: email
            },
            success: sv.registerSuccess,
            error: sv.registerFailure,
            dataType: 'html'
        });
    };
    sv.registerSuccess = function(data){
        
    };
    sv.registerFailure = function(data){
        
    };
    
    
    return app;
    
})(window, jQuery, App);
