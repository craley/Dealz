/* 
 * Loaded at top for carousel so document.ready() needed.
 */

(function (window, $, undefined) {

    //attach hash event listener
    window.addEventListener('hashchange', function (e) {
        if (window.location.hash == '#con1') {
            $('#dump').text('Search');
        } else if (window.location.hash == '#con2') {
            $('#dump').text('Products');
        } else if (window.location.hash == '#con3') {
            $('#dump').text('Profile');
        }
    }, false);

    $(document).ready(function () {
        $('.carousel').each(function () {
            $(this).carousel({
                interval: false
            });
        });
    });

})(window, jQuery);
