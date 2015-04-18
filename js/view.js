/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//dependencies: callbacks

App = (function(window, $, module){
    var app = module || {};
    
    var handler = app.handler = {};
    var core = app.core;
    
    var searchOptions = {
        category: 'All',
        condition: 'All',
        minPrice: 'None',
        maxPrice: 'None',
        page: 1,
        keyword: ''
    };
    
    handler.loginHandler = function (e) {
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
            success: app.cb.loginSuccess,
            error: app.cb.loginFailure,
            dataType: 'html'
        });
        return false;
    };
    
    handler.registerHandler = function (e) {
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
        core.registration();
        return false;
    };
    
    handler.navigation = function (e) {
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
    
    handler.queryHandler = function(e){
        //gather query params
        var keyword = $('#keywords').val();
        if(!keyword) return;
        $.ajax({
            type: "GET",
            url: 'services/controller.php',
            data: {
                action: 'query',
                keyword: keyword,
                category: searchOptions.category,
                page: searchOptions.page,
                condition: searchOptions.condition
            },
            success: searchSuccess,
            error: searchFailure,
            dataType: 'html'
        });
    };
    
    handler.categoryListener = function(e){
        searchOptions.category = $(this).text() || 'All';
        return false;
    };
    handler.conditionListener = function(e){
        searchOptions.condition = $(this).text() || 'All';
        return false;
    };
    handler.pageListener = function(e){
        searchOptions.page = $(this).text() || 1;
        return false;
    };
    
    handler.addHandler = function(e){
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
    
    handler.logoutHandler = function(){
        
    };
    
    return app;
    
})(window, jQuery, App);