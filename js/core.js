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
    var core = app.core = {};
    //shortcuts
    var handler = app.handler;
    var sv = app.sv;
    
    core.LOGIN = 1;
    core.SEARCH = 2;
    core.PRODUCTS = 3;
    core.PROFILE = 4;
    core.OFFERS = 5;
    
    var topbar = $('#chooser');
    var bottombar = $('#bottomload');
    //lower buttons(containers, not actual button)
    var googleButton = $('#signinButton');
    var logoutButton = $('#bottomLogout');
    var offerBack = $('#bottomBack');
    var twitterButton = $('#bottomTwitter');
    var facebookButton = $('#bottomFacebook');
    //top buttons
    var searchButton = $('#topSearch');
    var productButton = $('#topProducts');
    var profileButton = $('#topProfile');
    
    var mainContent = $('#main');
    var loginFrame = $('#myCarousel');
    var searchFrame;// = $('#searchfield')
    var productFrame;// = $('#productfield');
    var profileFrame;// = $('#profilefield');
    
    var offersVisible;
    
    //I. App Functionality
    
    core.registration = function(){
        sv.serverRegister();
    };
    
    core.loadLogin = function(){
        app.phase = 1;
        $('#fireLogin').click(handler.loginHandler);
        $('#fireRegister').click(handler.registerHandler);
        changeState(core.LOGIN);
    };
    
    core.loadUser = function(data){
        mainContent.append(data);
        app.phase = 2;
        searchFrame = $('#searchfield');
        productFrame = $('#productfield');
        profileFrame = $('#profilefield');
        app.uid = document.getElementById('profilefield').dataset.uid;
        $('#chooser li a').click(handler.navigation);  
        $('#queryFire').click(handler.queryHandler);
        $('#queryCond li a').on('click', function(e){
            condition = $(this).text();
            return false;
        });
        $('#queryCategory li a').on('click', function(e){
            category = $(this).text();
            return false;
        });
        $('#fireLogout').on('click', logoutHandler);
        $('#offerBack').on('click', offersBackHandler);
        installProductHandlers();
        changeState(SEARCH);
    };
    
    core.unloadUser = function(){
        phase = 1; uid = -1;
        $('div').remove('#searchfield');
        $('div').remove('#productfield');
        $('div').remove('#profilefield');
        changeState(LOGIN);
    };
    //Search
    core.queryProducts = function(params){
        
    };
    
    core.addProduct = function(){
        $('#productTable button').on('click', offerHandler);
        $('#productTable a').on('click', removeHandler);
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
                $('div').remove('#offerfield');
                offersVisible = false;
                offerBack.hide();
            }
            searchButton.hide();
            productButton.hide();
            profileButton.hide();
            logoutButton.hide();
            offerBack.hide();
            googleButton.show();
            twitterButton.show();
            facebookButton.show();
            loginFrame.show();
            
        } else if(state === SEARCH){
            if(offersVisible){
                $('div').remove('#offerfield');
                offersVisible = false;
                offerBack.hide();
            }
            loginFrame.hide();
            searchFrame.removeClass('hide');
            productFrame.addClass('hide');
            profileFrame.addClass('hide');
            searchButton.show();
            productButton.show();
            profileButton.show();
            logoutButton.show();
            offerBack.hide();
            googleButton.hide();
            twitterButton.hide();
            facebookButton.hide();
            
        } else if(state === PRODUCTS){
            if(offersVisible){
                $('div').remove('#offerfield');
                offersVisible = false;
                offerBack.hide();
            }
            loginFrame.hide();
            searchFrame.addClass('hide');
            productFrame.removeClass('hide');
            profileFrame.addClass('hide');
        } else if(state === PROFILE){
            if(offersVisible){
                $('div').remove('#offerfield');
                offersVisible = false;
                offerBack.hide();
            }
            loginFrame.hide();
            searchFrame.addClass('hide');
            productFrame.addClass('hide');
            profileFrame.removeClass('hide');
        } else if(state === OFFERS){
            offersVisible = true;
            loginFrame.hide();
            searchFrame.addClass('hide');
            productFrame.addClass('hide');
            profileFrame.addClass('hide');
            offerBack.show();
        }
    };
    
    return app;
    
})(window, jQuery, App);