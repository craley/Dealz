/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


App = (function(window, $, module){
    var app = module || {};
    var vendor = app.vendor = {};
    
    vendor.googleSuccess = function(data){
        app.core.loadUser(data);
    };
    vendor.googleFailure = function(){
        
    };
    
    return app;
    
})(window, jQuery, App);