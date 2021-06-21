</div>
<!-- gf main content container -->
<footer id="colophon" class="nssFooter" role="contentinfo">
    <?php gfGetTemplate('footer'); ?>
</footer>
<!-- #colophon -->
</div>
<!-- #page -->
<?php wp_footer(); ?>

<!--facebook share-->
<div id="fb-root"></div>
<script>(function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.1';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

<!--Pinterest share-->
<script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>

<!--Google+ share-->
<script src="https://apis.google.com/js/platform.js" async defer></script>

<!--Twitter share-->
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
<script>
    const cartForm = document.getElementById('wcCartForm');
    const mutObserver = new MutationObserver(function callback(mutationsList, mutObserver) {
        mutationsList.forEach(mutation => {
            if (mutation.attributeName === 'class') {
                ajaxRefreshCartCount();
            }
        });
    });
    if (cartForm !== null) {
        mutObserver.observe(
            cartForm,
            {attributes: true}
        );
    }
</script>
</body>
</html>



