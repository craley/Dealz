/* 
 * Dependencies:
 * Callbacks registered first.
 * Then events
 * Lastly, core who registers them.
 */


App = (function(window, $, module){
    var app = module || {};
    
    app.uid; app.phase;
    //establish alias
    var core = app.core || {};
    //shortcuts
    var view = app.view || {};
    var sv = app.sv || {};
    
    core.LOGIN = 1;
    core.SEARCH = 2;
    core.PRODUCTS = 3;
    core.PROFILE = 4;
    core.OFFERS = 5;
    
    var offersVisible;
    
    //I. App Functionality
    
    core.registration = function(username, pswd, email){
        sv.serverRegister(username, pswd, email);
    };
    core.loginAttempt = function(username, pswd){
        sv.attemptLogin(username, pswd);
    };
    
    core.loadLogin = function(){
        app.phase = 1;
        view.attachLoginListeners();
        changeState(core.LOGIN);
    };
    
    core.loadUser = function(html_data){
        app.phase = 2;
        //load gui
        view.initAppGui(html_data);
        
        view.attachHomeListeners();
        installProductHandlers();
        changeState(SEARCH);
    };
    
    core.unloadUser = function(){
        app.phase = 1; app.uid = -1;
        view.removeAppGui();
        changeState(LOGIN);
    };
    //Search
    core.queryProducts = function(params){
        
    };
    
    core.addProduct = function(asin, title, maker){
        
        
    };
    core.deleteProduct = function(){
        
    };
    core.productOffers = function(){
        
    };
    core.updateProfile = function(){
        
    };
    
    //II. Navigation
    //state machine
    core.nav = function changeState(state){
        if(state === core.LOGIN){
            
            if(offersVisible){
                view.toggleOffersTab(false);
                offersVisible = false;
            }
            view.showLoginTab();
            
        } else if(state === core.SEARCH){
            if(offersVisible){
                view.toggleOffersTab(false);
                offersVisible = false;
            }
            view.showSearchTab();
            
        } else if(state === core.PRODUCTS){
            if(offersVisible){
                view.toggleOffersTab(false);
                offersVisible = false;
            }
            view.showProductsTab();
        } else if(state === core.PROFILE){
            if(offersVisible){
                view.toggleOffersTab(false);
                offersVisible = false;
            }
            
        } else if(state === core.OFFERS){
            offersVisible = true;
            
        }
    };
    
    //Ignitor
    $(function main(){
        core.loadLogin();
    });
    
    return app;
    
})(window, jQuery, App);