/* 
 * Top navbar: topbar
 * Content: main
 * Bottom navbar: bottombar
 */
//Globals

//Google
/**
 * Google Sigin Handler
 * if user not signed in, called when button pressed
 * if user already signed, this is immediately fired.
 * dont put:  contentType: 'application/octet-stream; charset=utf-8'  in ajax.
 */
function signInCallback(authResult) {
    if (authResult['status']['signed_in'] && authResult['status']['method'] == 'PROMPT') {
        //user clicked the button
        $.ajax({
            type: 'POST',
            url: 'services/controller.php',
            success: App.googleSuccess,
            data: {
                action: 'google',
                code: authResult['code']
            },
            dataType: 'html'

        });
    } else if (authResult['status']['signed_in'] && authResult['status']['method'] == 'AUTO') {
        //auto login attempt: change to userSnoop
        $.ajax({
            type: 'POST',
            url: 'services/controller.php',
            success: App.googleSuccess,
            data: {
                action: 'google',
                code: authResult['code']
            },
            dataType: 'html'

        });
    } else if (authResult['error']) {
        //error
    }
    //process the one-time code
//    if (authResult['code']) {
//        
//        //alert('Good:  ' + authResult['code']);
//    } else if (authResult['error']) {
//        //possible errs:
//        //  access_denied: user denied access
//        //  immediate_failed: could not auto log in user
//        //alert('Bad:  ' + authResult['error']);
//    }
}


var App = (function (window, $, undefined) {
    var status = 1;// 1: loginNode, 2: homeNode


    //Intermediates
    var loginSuccess = function (data) {

    };
    var loginFailure = function () {

    };

    //Handlers
    var loginHandler = function (e) {
        var username = $('#userLogin').val();
        var pswd = $('#pswdLogin').val();
        if (!username) {
            alert("Must provide username");
            $('#userLogin').focus();
            return;
        } else if (!pswd) {
            alert("Must provide password");
            $('#pswdLogin').focus();
            return;
        }
        $.ajax({
            type: "POST",
            url: 'services/controller.php',
            data: {
                action: 'login',
                userLogin: username,
                pswd: pswd
            },
            success: loginSuccess,
            error: loginFailure,
            dataType: 'html'
        });
    };
    var registerHandler = function (e) {

    };
    //Backup client routing: Back button does not work here
    var siteRouter = function (e) {
        if (e.target) {
            var ident = e.target.id;
            if (ident == '1') {//search
                $('#productfield').addClass('hide');
                $('#profilefield').addClass('hide');
                $('#searchfield').removeClass('hide');
            } else if (ident == 2) {//product
                $('#searchfield').addClass('hide');
                $('#profilefield').addClass('hide');
                $('#productfield').removeClass('hide');
            } else if (ident == 3) {//profile
                $('#productfield').addClass('hide');
                $('#searchfield').addClass('hide');
                $('#profilefield').removeClass('hide');
            }
        }
    };
    //Creation
    var setupLogin = function () {
        $('#fireLogin').click(loginHandler);
        $('#fireRegister').click(registerHandler);
//        $('.carousel').each(function () {
//            $(this).carousel({
//                interval: false
//            });
//        });
    };
    function googleSuccess(html) {
        loadApp(html);
        //$('#signinButton').attr('style', 'display: none');
    }
    var loadApp = function(content){
        var holder = $('#main');
        holder.empty();
        holder.html(content);
        //load bars
        var topList = $('#chooser');
        topList.empty();
        topList.html("<li><a href='#search'>Search</a></li><li><a href='#products'>Products</a></li><li><a href='#profile'>Profile</a></li>");
        var bottomList = $('#bottomload');
        bottomList.empty();
        bottomList.html('<li><a href="#logout">Logout</a></li>');
        setupHome();
    };

    var setupHome = function () {
        //set site nav
        //$('#chooser > li').click(siteRouter);

        //Primary routing: back button works.
        window.addEventListener('hashchange', function (e) {
            if (window.location.hash == '#search') {
                $('#productfield').addClass('hide');
                $('#profilefield').addClass('hide');
                $('#searchfield').removeClass('hide');
            } else if (window.location.hash == '#products') {
                $('#searchfield').addClass('hide');
                $('#profilefield').addClass('hide');
                $('#productfield').removeClass('hide');
            } else if (window.location.hash == '#profile') {
                $('#productfield').addClass('hide');
                $('#searchfield').addClass('hide');
                $('#profilefield').removeClass('hide');
            }
        }, false);
    };

    //testing only
    setupLogin();
    //setupHome();

    //public interface
    return {
        setupLogin: setupLogin,
        setupHome: setupHome,
        googleSuccess: googleSuccess
    };

})(window, jQuery);
