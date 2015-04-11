/* 
 * Top navbar: topbar
 * Content: main
 * Bottom navbar: bottombar
 * 
 * Navigation:
 *  Login -> Home
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
            error: App.googleFailure,
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
    
    //Login Handler
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
    //Login Callbacks
    var loginSuccess = function (data) {
        loadApp(data);
    };
    var loginFailure = function () {
        $('#errmsgLogin').text('Invalid Credentials');
    };
    
    //Register Handler
    var registerHandler = function (e) {
        var username = $('#userRegister').val();
        var pswd = $('#pswdRegister').val();
        var email = $('#emailRegister').val();
        if(!username){
            alert("Must provide a username");
            $('#userRegister').focus();
            return;
        } else if(!pswd){
            alert("Must provide a password");
            $('#pswdRegister').focus();
            return;
        } else if(!email){
            alert("Must provide an email");
            $('#emailRegister').focus();
            return;
        }
        $.ajax({
            type: "POST",
            url: 'services/controller.php',
            data: {
                action: 'register',
                userLogin: username,
                pswd: pswd,
                email: email
            },
            success: registerSuccess,
            error: registerFailure,
            dataType: 'html'
        });
    };
    //Register Callbacks
    var registerSuccess = function(data){
        loadApp(data);
    };
    var registerFailure = function(){
        $('#errmsgRegister').text('Invalid Credentials');
    };
    
    
    //Home Screen Navigation: does not affect Back button
    var siteRouter = function (e) {
        if (e.target) {
            var ident = e.target.id;
            if(offersVisible){
                //blow it away
            }
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
    
    function googleSuccess(html) {
        loadApp(html);
    }
    function googleFailure(){
        alert("Failed");
    }
    var uid;
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
        var bottomList = $('#bottomload');
        $('#signinButton').attr('style', 'display: none');
        bottomList.html('<li><a href id="fireLogout">Logout</a></li>');
        $('#fireLogout').on('click', logoutHandler);
        setupHome();
        
        //var path = 'dealz';
        //window.history.pushState({page: path}, 'title', path);//comment
        
        uid = document.getElementById('profilefield').dataset.uid;
    };
    //fired when user presses 'Back'
    var stateChange = function(e){
        if(!e.state) return;
        var loc = document.location;
        var state = e.state;
        //console.log('Loc: ' + loc + ', state: ' + state.page);
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
        //deactivate logout button
        $('#fireLogout').attr('style', 'display: none');
        $('#signinButton').attr('style', 'display: block');
        $('#fireLogin').click(loginHandler);
        $('#fireRegister').click(registerHandler);
    };
    
    var logoutHandler = function(){
        reloadLogin();
        //window.history.back();//history
    };
    var searchSuccess = function(data){
        var holder = $('#searchload');
        holder.empty();
        holder.html(data);
        //attach track button listeners.
        $('#searchTable button').on('click', addHandler);
    };
    var searchFailure = function(){
        var holder = $('#searchload');
        holder.empty();
        holder.html('No Results Found');
    };
    //product add
    var addHandler = function(e){
        var button = $(this);
        var cell = button.parent();
        var asin = cell.get(0).dataset.asin;
        var attribs = [];
        cell.children('p').each(function(){
            attribs.push($(this).text());
        });
        //0: title 1: maker
        addEntry(asin, attribs[0], attribs[1], 0);
        //notify server
        $.ajax({
            type: "POST",
            url: 'services/controller.php',
            data: {
                action: 'add',
                uid: uid,
                asin: asin,
                title: attribs[0],
                maker: attribs[1]
            }
        });
        installProductHandlers();
    };
    var installProductHandlers = function(){
        $('#productTable button').on('click', offerHandler);
        $('#productTable a').on('click', removeHandler);
        
    };
    var addEntry = function(asin, title, maker, priority){
        var table = $('#productTable');
        var ent = "<tr><td class='col-xs-1'><button class='btn btn-default' type='button'>offers</button></td>";
        ent += "<td class='col-xs-4'>" + title + "</td>";
        ent += "<td class='col-xs-4'>" + maker + "</td>";
        ent += "<td class='col-xs-1'>" + asin + "</td>";
        ent += "<td class='col-xs-1'>" + priority + "</td>";
        ent += "<td class='col-xs-1'><a href='#'><span class='glyphicon glyphicon-trash'></span></a></td></tr>";
        table.append(ent);
    };
    //product remove
    var removeHandler = function(e){
        
    };
    //see product's offers
    var offerHandler = function(e){
        var crew = $(this).parent().parent().find('p');
        var asin = $(crew.get(2)).text();
        //var title = $(crew.get(0)).text();
        //var maker = $(crew.get(1)).text();
        
    };
    var offersVisible = false;
    var offerSuccess = function(html){
        //make product screen invisible
        
    };
    var offerFailure = function(){
        
    };
    var offersBackHandler = function(e){
        
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
    
    /*
     * Page Creation
     */
    
    //Setup Login Screen
    var setupLogin = function () {
        $('#fireLogin').click(loginHandler);
        $('#fireRegister').click(registerHandler);
        //window.history.replaceState({page:'login'}, 'title', 'login');//history
        //window.addEventListener('popstate', stateChange, false);
    };
    
    //Setup Home Screen
    var setupHome = function () {
        //set site nav
        $('#chooser li a').click(siteRouter);

        //Primary routing via hashchange: back button works.
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
        });
        $('#queryCategory li a').on('click', function(e){
            category = $(this).text();
        });
        installProductHandlers();
    };

    //testing only
    setupLogin();

    //public interface for 3rd party vendors
    return {
        setupLogin: setupLogin,
        setupHome: setupHome,
        googleSuccess: googleSuccess,
        googleFailure: googleFailure
    };

})(window, jQuery);
