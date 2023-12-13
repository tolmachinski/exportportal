<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-1-12-0/jquery-1.12.0.min.js');?>"></script>
<?php widgetMetaHeader($meta_params ?? [], $meta_data ?? [], 'new/');?>
<style>
    body{
        width:100%;
        height:100%;
        margin: 0;
        padding:0;
    }

    #map-google-b{
        width: 100%;
        height: 100%;
    }
</style>
<?php tmvc::instance()->controller->view->display('new/google_maps/map_view');?>
