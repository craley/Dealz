/* 
 * The view subsystem encapsulates the DOM. Event handlers
 * update the gui component's models for use in other parts
 * of the system.
 * 
 */

App = (function (window, $, module) {
    var app = module || {};

    var view = app.view || {};
    var core = app.core || {};

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
    //profile
    

    //Gui Models

    var searchOptions = view.searchOptions = {
        category: 'All',
        condition: 'All',
        minPrice: 'None',
        maxPrice: 'None',
        page: 1,
        keyword: ''
    };

//    var product = {//just showing what product looks like.
//        asin: '',
//        title: '',
//        maker: '',
//        category: null,
//        priority: 0
//    };
    var products = [];

    var profile = {
        username: '',
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        carrier: 0
    };
    var phoneReg = /\(?\d{3}\)?-?\d{3}-?\d{4}/;
    var testPhone = function(attempt){
        var match;
        if(match = phoneReg.exec(attempt)){
            //[0]: contains entire string.
        }
    };
    var regArray = function(regex){
        return function(attempt){
            return regex.exec(attempt);
        };
    };
    var isNumeric = function(n){
        return !isNaN(parseFloat(n)) && isFinite(n);
    };
    /*
     * Populate local data models.
     */
    var loadLocalModel = function(){//tested
        profile.username  = $('#profileUsername').val() || null;
        profile.firstName = $('#profileFirst').val()    || null;
        profile.lastName  = $('#profileLast').val()     || null;
        profile.email     = $('#profileEmail').val()    || null;
        profile.phone     = $('#profilePhone').val()    || null;
        profile.carrier   = $('#profileCarrier').val()  || 0;
        
        products = $('#productTable tr').map(function(){
            var row = $(this);
            //skip table header row
            if(!this.id || this.id.indexOf("row") === -1) return null;
            return {//nth starts at 1
                title:    row.find(':nth-child(2) p').text(),
                maker:    row.find(':nth-child(3) p').text(),
                asin:     row.find(':nth-child(4) p').text(),
                priority: row.find(':nth-child(5) option:selected').val()
            };
        }).get();//map builds array of results.
    };
    /*
     * Snag HTML5 data attributes:
     *  1. Vanilla js: document.getElement('').dataset.###
     *  2. jQuery: $('').data('###')
     */
    view.initAppGui = function(html_data){
        //load in html
        mainContent.append(html_data);
        //secure references
        searchFrame = $('#searchfield');
        productFrame = $('#productfield');
        profileFrame = $('#profilefield');
        //extract uid from HTML5 data attribute.
        app.uid = profileFrame.data('uid');//pulls
        loadLocalModel();
    };
    
    view.removeAppGui = function(){
        $('div').remove('#searchfield');
        $('div').remove('#productfield');
        $('div').remove('#profilefield');
    };

    view.loginHandler = function (e) {
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
        core.loginAttempt(username, pswd);
        return false;
    };

    view.registerHandler = function (e) {
        var username = $('#userRegister').val();
        var pswd = $('#pswdRegister').val();
        var email = $('#emailRegister').val();
        if (!username) {
            alert("Must provide a username");
            $('#userRegister').focus();
            return;
        } else if (!pswd) {
            alert("Must provide a password");
            $('#pswdRegister').focus();
            return;
        } else if (!email) {
            alert("Must provide an email");
            $('#emailRegister').focus();
            return;
        }
        core.registration(username, pswd, email);
        return false;
    };

    view.navigation = function (e) {
        if (e.target) {
            var ident = e.target.id;
            if (ident == '1') {//search
                changeState(app.core.SEARCH);
            } else if (ident == 2) {//product
                changeState(app.core.PRODUCTS);
            } else if (ident == 3) {//profile
                changeState(app.core.PROFILE);
            }
        }
        return false;
    };

    view.queryHandler = function (e) {
        //gather query params
        var keyword = $('#keywords').val();
        if (!keyword)
            return;

    };

    view.categoryListener = function (e) {
        searchOptions.category = $(this).text() || 'All';
        return false;
    };
    view.conditionListener = function (e) {
        searchOptions.condition = $(this).text() || 'All';
        return false;
    };
    view.pageListener = function (e) {
        searchOptions.page = $(this).text() || 1;
        return false;
    };
    //Add Product button
    view.addHandler = function (e) {
        var button = $(this);
        var cell = button.parent();
        var asin = cell.get(0).dataset.asin;
        var attribs = [];
        cell.children('p').each(function () {
            attribs.push($(this).text());
        });
        core.addProduct(asin, attribs[0], attribs[1]);
        //0: title 1: maker
        addEntry(asin, attribs[0], attribs[1], 0);
        //notify server
        $.ajax({
            type: "POST",
            url: 'services/controller.php',
            data: {
                action: 'add',
                uid: app.uid,
                asin: asin,
                title: attribs[0],
                maker: attribs[1]
            }
        });
        //add handlers to new elements
        installProductHandlers();
        return false;
    };

    view.logoutHandler = function () {
        
    };

    //Gui Update
    view.showLoginTab = function () {
        searchButton.hide();
        productButton.hide();
        profileButton.hide();
        logoutButton.hide();
        offerBack.hide();
        googleButton.show();
        twitterButton.show();
        facebookButton.show();
        loginFrame.show();
    };
    view.showSearchTab = function () {
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
    };
    view.showProductsTab = function () {
        loginFrame.hide();
        searchFrame.addClass('hide');
        productFrame.removeClass('hide');
        profileFrame.addClass('hide');
    };
    view.showProfileTab = function () {
        loginFrame.hide();
        searchFrame.addClass('hide');
        productFrame.addClass('hide');
        profileFrame.removeClass('hide');
    };
    view.toggleOffersTab = function (show) {
        if (show) {
            loginFrame.hide();
            searchFrame.addClass('hide');
            productFrame.addClass('hide');
            profileFrame.addClass('hide');
            offerBack.show();
        } else {
            $('div').remove('#offerfield');
            offerBack.hide();
        }
    };

    view.loadSearchResults = function () {

    };
    
    view.productTableAdd = function(asin){
        
    };
    view.productTableRemove = function(asin){
        
    };

    view.closeOfferWindow = function () {//phase out
        $('div').remove('#offerfield');
        offerBack.hide();
    };

    view.attachLoginListeners = function () {
        $('#fireLogin').click(view.loginHandler);
        $('#fireRegister').click(view.registerHandler);
    };
    view.attchHomeListeners = function () {
        //Tabs
        $('#chooser li a').click(view.navigation);
        //Search button
        $('#queryFire').click(view.queryHandler);
        //Query: Condition
        $('#queryCond li a').on('click', function (e) {
            searchOptions.condition = $(this).text();
            return false;
        });
        //Query: Category
        $('#queryCategory li a').on('click', function (e) {
            searchOptions.category = $(this).text();
            return false;
        });
        //Logout button
        $('#fireLogout').on('click', logoutHandler);
        //Offers button
        $('#offerBack').on('click', offersBackHandler);
    };
    view.attachProductListeners = function(){
        $('#productTable button').on('click', offerHandler);
        $('#productTable a').on('click', removeHandler);
    };

    return app;

})(window, jQuery, App);