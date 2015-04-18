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
    var phase = 1;
    var uid;
    
    var LOGIN = 1;
    var SEARCH = 2;
    var PRODUCTS = 3;
    var PROFILE = 4;
    var OFFERS = 5;
    
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
    
    //state machine
    function changeState(state){
        if(state === LOGIN){
            if(offersVisible){
                $('div').remove('#offerfield');
                offersVisible = false;
                offerBack.hide();
            }
            phase = 1;
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
    }
    function homePreamble(data){
        if(phase === 2) return;//Multi-click protection
        mainContent.append(data);
        phase = 2;
        searchFrame = $('#searchfield');
        productFrame = $('#productfield');
        profileFrame = $('#profilefield');
        uid = document.getElementById('profilefield').dataset.uid;
        $('#chooser li a').click(siteRouter);  
        $('#queryFire').click(queryHandler);
        $('#queryCond li a').on('click', function(e){
            searchOptions.condition = $(this).text();
            return false;
        });
        $('#queryCategory li a').on('click', function(e){
            searchOptions.category = $(this).text();
            return false;
        });
        $('#fireLogout').on('click', logoutHandler);
        $('#offerBack').on('click', offersBackHandler);
        $('#updateFire').on('click', updateHandler);
        installProductHandlers();
        //record user product asin's
        fillAsins();
        //force bootstrap dropdowns to close on click
        $(".dropdown-menu a").click(function() {
            $(this).closest(".dropdown-menu").prev().dropdown("toggle");
        });
        changeState(SEARCH);
    }
    
    
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
        return false;
    };
    //Login Callbacks
    var loginSuccess = function (data) {
        homePreamble(data);
    };
    var loginFailure = function () {
        $('#errmsgLogin').text('  Invalid Credentials');
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
        return false;
    };
    /*
     * Handle successful registration.
     */
    var registerSuccess = function(data){
        homePreamble(data);
    };
    /*
     * Handle registration error.
     */
    var registerFailure = function(){
        $('#errmsgRegister').text('  Invalid Credentials');
    };
    /*
     * Updates user's profile.
     */
    var updateHandler = function(e){
        var info = {};
        info.uid = uid;
        info.action = 'update';
        var username = $('#profileUsername').val();
        var first = $('#profileFirst').val();
        var last = $('#profileLast').val();
        var phone = $('#profilePhone').val();
        var carrier = $('#profileCarrier').val();
        var email = $('#profileEmail').val();
        
        if(username) info['username'] = username;
        if(first) info['firstName'] = first;
        if(last) info['lastName'] = last;
        if(phone) info['phone'] = phone;
        if(carrier) info['carrier'] = carrier;
        if(email) info['email'] = email;
        
        $.ajax({
            type: "POST",
            url: 'services/controller.php',
            data: info
        });
        
        return false;
    };
    
    //Home Screen Navigation: does not affect Back button
    var siteRouter = function (e) {
        if (e.target) {
            var ident = e.target.id;
            if (ident == '1') {//search
                changeState(SEARCH);
            } else if (ident == 2) {//product
                changeState(PRODUCTS);
            } else if (ident == 3) {//profile
                changeState(PROFILE);
            }
        }
        return false;
    };
    
    function googleSuccess(html) {
        homePreamble(html);
    }
    function googleFailure(){
        //
    }
    /**
     * Destroys the user's data.
     * @returns {Boolean}
     */
    var logoutHandler = function(){
        //reloadLogin();
        //window.history.back();//history
        phase = 1; uid = -1;
        $('div').remove('#searchfield');
        $('div').remove('#productfield');
        $('div').remove('#profilefield');
        changeState(LOGIN);
        return false;
    };
    var searchSuccess = function(data){//each call obliterates, so no multi-click protection needed
        var holder = $('#searchload');
        holder.empty();
        holder.html(data);//html replaces existing content! empty() not needed
        var table = document.getElementById('searchTable');
        searchOptions.page = parseInt(table.dataset.currentpage, 10);
        searchOptions.totalPages = parseInt(table.dataset.totalpages, 10);
        //attach track button listeners.
        $('#searchTable button').on('click', addHandler);
        $('#searchPage li').on('click', paginationHandler);
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
        if($.inArray(asin, pkeys) !== -1){
            return;//dupe detected
        }
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
        //add handlers to new elements
        installProductHandlers();
        return false;
    };
    var installProductHandlers = function(){
        $('#productTable button').on('click', offerHandler);
        $('#productTable a').on('click', removeHandler);
        $('#productTable select').on('change', priorityHandler);
    };
    var addEntry = function(asin, title, maker, priority){
        var table = $('#productTable');
        //var count = $('#productTable tr').length - 1;//count jquery children
        
        entry.asin = asin;
        entry.title = title;
        entry.maker = maker;
        entry.priority = priority;
        //var ent = "<tr><td class='col-xs-1'><button class='btn btn-default' type='button'>offers</button></td>";
        //ent += "<td class='col-xs-4'>" + title + "</td>";
        //ent += "<td class='col-xs-4'>" + maker + "</td>";
        //ent += "<td class='col-xs-1'>" + asin + "</td>";
        //ent += "<td class='col-xs-1'>" + priority + "</td>";
        //ent += "<td class='col-xs-1'><a href='#'><span class='glyphicon glyphicon-trash'></span></a></td></tr>";
        table.append(entry.toString());
    };
    var pkeys;
    var fillAsins = function(){
        pkeys = [];
        $('#productTable tr').each(function(){
            var id = this.id;
            if(id && id.indexOf("row") > -1){
                pkeys.push(id.substring(3));
            }
        });
    };
    var searchOptions = {
        category: 'All',
        condition: 'All',
        minPrice: 'None',
        maxPrice: 'None',
        page: 1,
        keyword: '',
        totalPages: 1,
        action: 'query'
    };
    
    var entry = {
        asin: 0,
        title: '',
        maker: '',
        priority: 0,
        toString: function(){
            var out = "<tr id='row" + this.asin + "'>";
            out += "<td class='col-xs-1'><button id='offer" + this.asin + "' class='btn btn-default' type='button'>offers</button></td>";
            out += "<td class='col-xs-4'>" + this.title + "</td>";
            out += "<td class='col-xs-2'>" + this.maker + "</td>";
            out += "<td class='col-xs-1'>" + this.asin + "</td>";
            out += "<td class='col-xs-2'><div class='form-group'><select class='form-control' id='sticky" + this.asin + "'>";
            out += "<option value='normal'";
            if(this.priority === 0) out += " selected='selected'";
            out += ">Normal</option>";
            
            out += "<option value='email'";
            if(this.priority === 1) out += " selected='selected'";
            out += ">Email</option>";
            
            out += "<option value='text'";
            if(this.priority === 2) out += " selected='selected'";
            out += ">Text</option>";
            
            out += "</select></div></td>";
            out += "<td class='col-xs-1'><a id='remove" + this.asin + "' href='#'><span class='glyphicon glyphicon-trash'></span></a></td>";
            out += "</tr>";
            return out;
        }
    };
    //product remove
    var removeHandler = function(e){
        
        if(e.target){
            //parents drives up DOM to TR
            var snag = $(e.target).parents('tr');
            var row = snag.get(0);
            //extract asin for ajax
            var asin = row.id.substring(3);//from 3 to end
            //blow it away
            $('#' + row.id).remove();
            //notify base
            $.ajax({
                type: "POST",
                url: 'services/controller.php',
                data: {
                    action: 'remove',
                    uid: uid,
                    asin: asin
                }
            });//no response needed.
        }
        return false;
    };
    //see product's offers
    var offerHandler = function(e){
        
        var crew = $(this).parent().parent().find('p');
        var asin = $(crew.get(2)).text();
        //var title = $(crew.get(0)).text();
        //var maker = $(crew.get(1)).text();
        if(asin){
            $.ajax({
            type: "GET",
            url: 'services/controller.php',
            data: {
                action: 'offer',
                asin: asin
            },
            success: offerSuccess,
            error: offerFailure,
            dataType: 'html'
        });
        }
        return false;
    };
    var offersVisible = false;
    var offerSuccess = function(html){
        if(offersVisible) return;//Multi-click protection
        if(html){
            //var holder = $('#main');
            mainContent.append(html);
            changeState(OFFERS);
        }
    };
    var offerFailure = function(){
        
    };
    var offersBackHandler = function(e){
        changeState(PRODUCTS);
        offersVisible = false;
        return false;
    };
    //Search button
    var queryHandler = function(e){
        //gather query params
        searchOptions.keyword = $('#keywords').val();
        searchOptions.category = searchOptions.category || 'All';
        searchOptions.condition = searchOptions.condition || 'All';
        searchOptions.page = searchOptions.page || 1;
        if(!searchOptions.keyword) return;
        $.ajax({
            type: "GET",
            url: 'services/controller.php',
            data: searchOptions,
            success: searchSuccess,
            error: searchFailure,
            dataType: 'html'
        });
        return false;
    };
    var fireSearchQuery = function(){
        if(!searchOptions.keyword) return;
        $.ajax({
            type: "GET",
            url: 'services/controller.php',
            data: searchOptions,
            success: searchSuccess,
            error: searchFailure,
            dataType: 'html'
        });
    };
    var paginationHandler = function(e){
        var target = e.target;
        if(target.nodeName == 'SPAN'){
            target = $(target).parents('li').get(0);
        }
        if(target.id === 'searchPrev'){
            if(searchOptions.page > 1){
                searchOptions.page--;
                fireSearchQuery();
            }
        } else if(target.id === 'searchNext'){
            if(searchOptions.page < searchOptions.totalPages){
                searchOptions.page++;
                fireSearchQuery();
            }
        } else {
            var page = $(target).text();
            if(!$.isNumeric(page)) return;
            searchOptions.page = parseInt(page, 10);
            fireSearchQuery();
        }
        return false;
    };
    var priorityHandler = function(e){
        var select = e.target;
        var selid = select.id;
        var val = ($(select).children('option:selected')).val();
        var asin = selid.substring(6);
        //console.log(asin);
        return false;
    };

    $(function(){
        $('#fireLogin').click(loginHandler);
        $('#fireRegister').click(registerHandler);
        changeState(LOGIN);
    });

    //public interface for 3rd party vendors
    return {
        googleSuccess: googleSuccess,
        googleFailure: googleFailure
    };

})(window, jQuery);
