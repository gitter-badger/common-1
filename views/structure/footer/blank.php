        <script type="text/javascript">
            var ENVIRONMENT         = '<?=strtoupper(ENVIRONMENT)?>';
            window.SITE_URL         = '<?=site_url('', isPageSecure())?>';
            window.NAILS            = {};
            window.NAILS.URL        = '<?=NAILS_ASSETS_URL?>';
            window.NAILS.LANG       = {};
            window.NAILS.USER       = {};
            window.NAILS.USER.ID    = <?=active_user('id') ? active_user('id') : 'null'?>;
            window.NAILS.USER.FNAME = '<?=active_user('first_name')?>';
            window.NAILS.USER.LNAME = '<?=active_user('last_name')?>';
            window.NAILS.USER.EMAIL = '<?=active_user('email')?>';
        </script>
        <?php

            //  Load JS
            $this->asset->output('JS');
            $this->asset->output('JS-INLINE');

            //  Analytics
            if (app_setting('google_analytics_account')) {

                ?>
                <script type="text/javascript">
                <!--//

                    var _gaq = _gaq || [];
                    _gaq.push(['_setAccount', '<?=app_setting('google_analytics_account')?>]);
                    _gaq.push(['_trackPageview']);

                    (function() {
                        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                    })();

                //-->
                </script>
                <?php
            }
        ?>
    </body>
</html>