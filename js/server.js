/* 
 * The server subsystem encapsulates all the
 * details of interaction with the server.
 * 
 */

App = (function(window, $, module){
    var app = module || {};
    
    var sv = app.sv || {};
    var core = app.core || {};
    
    
    //User Login
    sv.attemptLogin = function(username, pswd){
        $.ajax({
            type: "POST",
            url: 'services/controller.php',
            data: {
                action: 'login',
                userLogin: username,
                pswd: pswd
            },
            success: sv.loginSuccess,
            error: sv.loginFailure,
            dataType: 'html'
        });
    };
    
    sv.loginSuccess = function(data){//could get rid of this
        core.loadUser(data);
    };
    sv.loginFailure = function(){
        
    };
    
    //User Registration
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
    
    sv.searchQuery = function(params){
        $.ajax({
            type: "GET",
            url: 'services/controller.php',
            data: params,
            success: searchSuccess,
            error: searchFailure,
            dataType: 'html'
        });
    };
    
    return app;
    
})(window, jQuery, App);
