/* 
 * Top navbar: topbar
 * Content: main
 * Bottom navbar: bottombar
 * 
 * Navigation:
 *  Login -> Home
 *  ** Back button should leave?
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
//        $.ajax({
//            type: 'POST',
//            url: 'services/controller.php',
//            success: App.googleSuccess,
//            data: {
//                action: 'google',
//                code: authResult['code']
//            },
//            dataType: 'html'
//
//        });
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

//create global footprint for external callbacks.
var App = (function (window, $, undefined) {
    var status = 1;// 1: loginNode, 2: homeNode


    //Callbacks
    var loginSuccess = function (data) {
        loadApp(data);
    };
    var loginFailure = function () {
        $('#errmsg').text('Invalid Credentials');
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
        return false;
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
        window.history.replaceState({page:'login'}, 'title', 'login');
        window.addEventListener('popstate', stateChange, false);
    };
    function googleSuccess(html) {
        loadApp(html);
        //$('#signinButton').attr('style', 'display: none');
    }
    var loginComponent;
    var loadApp = function(content){
        var holder = $('#main');
        //holder.empty();//blow login away
        loginComponent = holder.children().detach();//save children
        
        holder.html(content);
        //load bars
        var topList = $('#chooser');
        topList.empty();
        //topList.html("<li><a href='#search'>Search</a></li><li><a href='#products'>Products</a></li><li><a href='#profile'>Profile</a></li>");
        topList.html("<li><a id='1' href>Search</a></li><li><a id='2' href>Products</a></li><li><a id='3' href>Profile</a></li>");
        //topList.html("<li><button type='button'>Search</button></li><li><button>Products</button></li><li><button>Profile</button></li>");
        var bottomList = $('#bottomload');
        bottomList.empty();
        bottomList.html('<li><a href id="fireLogout">Logout</a></li>');
        $('#fireLogout').on('click', logoutHandler);
        setupHome();
        //display a different url
        var path = 'dealz';
        window.history.pushState({page: path}, 'title', path);
        //$(window).on('popstate', stateChange);
        
    };
    //fired when user presses 'Back'
    var stateChange = function(e){
        if(!e.state) return;
        var loc = document.location;
        var state = e.state;
        console.log('Loc: ' + loc + ', state: ' + state.page);
        if(state.page == 'login'){
            reloadLogin();
        }
    };
    var reloadLogin = function(){
        var topList = $('#chooser');
        topList.empty();
        topList.html('<li><a href="#">Home</a></li>');
        var holder = $('#main');
        //blow it away
        holder.empty();
        //push login
        loginComponent.appendTo(holder);
        var bottomList = $('#bottomload');
        bottomList.empty();
        bottomList.html(googleButton);
    };
    var googleButton = "<li><div id='signinButton'><span class='g-signin' data-scope='https://www.googleapis.com/auth/plus.login' " + 
            "data-clientid='961741834099-nv3c7j13nm3fmis23sm1g8g83ctr995l.apps.googleusercontent.com' " + 
            "data-redirecturi='postmessage' data-accesstype='offline' data-cookiepolicy='single_host_origin' " +
            "data-callback='signInCallback'></span></div></li>";
    var logoutHandler = function(){
        reloadLogin();
    };
    var searchSuccess = function(data){
        var holder = $('#searchload');
        holder.empty();
        holder.html(data);
        //attach track button listeners.
        
    };
    var searchFailure = function(){
        var holder = $('#searchload');
        holder.empty();
        holder.html('No Results Found');
    };
    //Search button
    var category;
    var condition;
    var page;
    var queryHandler = function(e){
        //gather query params
        var keyword = $('#keywords').val();
        var cat = category || 'All';
        var cond = condition || 'All';
        var pg = page || 1;
        if(!keyword) return;
        $.ajax({
            type: "GET",
            url: 'services/controller.php',
            data: {
                action: 'query',
                keyword: keyword,
                category: cat,
                page: pg,
                condition: cond
            },
            success: searchSuccess,
            error: searchFailure,
            dataType: 'html'
        });
    };

    var setupHome = function () {
        //set site nav
        $('#chooser li a').click(siteRouter);

        //Primary routing: back button works.
//        window.addEventListener('hashchange', function (e) {
//            if (window.location.hash == '#search') {
//                $('#productfield').addClass('hide');
//                $('#profilefield').addClass('hide');
//                $('#searchfield').removeClass('hide');
//            } else if (window.location.hash == '#products') {
//                $('#searchfield').addClass('hide');
//                $('#profilefield').addClass('hide');
//                $('#productfield').removeClass('hide');
//            } else if (window.location.hash == '#profile') {
//                $('#productfield').addClass('hide');
//                $('#searchfield').addClass('hide');
//                $('#profilefield').removeClass('hide');
//            }
//        }, false);
        $('#queryFire').click(queryHandler);
        $('#queryCond li a').on('click', function(e){
            condition = $(this).text();
            console.log(condition);
        });
        $('#queryCategory li a').on('click', function(e){
            category = $(this).text();
            console.log(category);
        });
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
