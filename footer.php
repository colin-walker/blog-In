    <div id="siteID" class="siteID">
        <span class="nameSpan">
            <a href="/about/"><?php echo constant("NAME"); ?></a>
        </span>
        <br/>
        <span class="licSpan">
            <?php if(getOption('Use_Now') == 'yes') { ?><a href="/now/">NOW</a> | <?php } ?><a href="/colophon/">Colophon</a> | Content: <a href="https://creativecommons.org/licenses/by-nc/2.0/uk/">CC BY-NC 2.0 UK</a>
        </span>
    </div>

    <div class="h-card p-author vcard author">
        <img class="u-photo" src="<?php echo constant('AVATAR'); ?>" alt="<?php echo constant("NAME"); ?>"/>
        <a class="u-url" rel="me" href="<?php echo constant('BASE_URL'); ?>"><?php echo constant("NAME"); ?></a>
        <a rel="me" class="u-email" href="mailto:<?php echo constant("MAILTO"); ?>"><?php echo constant("MAILTO"); ?></a>
        <p class="p-note"></p>
    </div>
    
<?php
if ($_SESSION['auth'] == $dbauth) {
	if ($date == INSTALL_DATE || $noposts == 'true') { ?>
    	<style>
        	@media screen and (min-width: 768px) {
            	#page {
                	min-height: calc(100vh - 157px) !important;
            	}
        	}
    	</style>		
	<?php } else { ?>
    	<style>
        	@media screen and (min-width: 768px) {
            	#page {
                	min-height: calc(100vh - 207px) !important;
            	}
        	}
    	</style>
	<?php
	}
} 
?>

</body>
</html>