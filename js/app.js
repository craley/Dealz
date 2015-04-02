/* 
 * Top navbar: topbar
 * Content: main
 * Bottom navbar: bottombar
 */

(function (window, $, undefined) {
    var status = 1;// 1: loginNode, 2: homeNode
    //
    //attach hash event listener
//    window.addEventListener('hashchange', function (e) {
//        if (window.location.hash == '#con1') {
//            $('#dump').text('Search');
//        } else if (window.location.hash == '#con2') {
//            $('#dump').text('Products');
//        } else if (window.location.hash == '#con3') {
//            $('#dump').text('Profile');
//        }
//    }, false);

//    $(document).ready(function () {
//        $('.carousel').each(function () {
//            $(this).carousel({
//                interval: false
//            });
//        });
//    });

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
            url: 'backend.php',
            data: {
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
        $('.carousel').each(function () {
            $(this).carousel({
                interval: false
            });
        });
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
    //setupLogin();
    setupHome();

})(window, jQuery);
