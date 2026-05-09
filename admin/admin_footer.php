<?php $B = BASE_URL; ?>
</div><!-- /admin-main -->
</div><!-- /admin-content -->
</div><!-- /admin-wrap -->
<script>window.BASE_URL = '<?php echo addslashes($B); ?>';</script>
<script src="<?php echo $B; ?>/js/main.js"></script>
<style>
@media(max-width:992px){
  .admin-sidebar{transform:translateX(-100%);transition:transform .3s;position:fixed;z-index:200;}
  .admin-sidebar.open{transform:translateX(0);}
  .admin-content{margin-left:0!important;}
}
</style>
</body>
</html>
