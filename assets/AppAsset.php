<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       // 'css/site.css',
	//   'https://fonts.googleapis.com/css?family=Lato&display=swap',
     	  'vendor/bootstrap/css/bootstrap.min.css',
	  'vendor/font-awesome/css/font-awesome.min.css',
	    //'css/css/fontastic.css',
        'css/css/style10.css',
	    'css/css/style_common.css',	   
		  'css/css/style.default.css',
          'css/css/dataTables.bootstrap4.min.css',
		  		  'css/css/gijgo.min.css',
		  'css/css/select2.css',
	  'css/css/custom.css',
	  'css/css/table.css',
	  'css/css/dropify.min.css',	
	  'css/css/sweetalert2.min.css',
	  'css/css/toastr.min.css',
	  'img/favicon.ico',

    ];
    public $js = [
   // 'css/vendor/jquery/jquery.min.js',
    'vendor/popper.js/umd/popper.min.js',
    'vendor/bootstrap/js/bootstrap.min.js',
    'vendor/jquery.cookie/jquery.cookie.js',
    //'vendor/chart.js/Chart.min.js',
    'vendor/jquery-validation/jquery.validate.min.js',
    'js/dataTables.min.js',
	'js/jquery.dataTables.min.js',
	'js/dataTables.bootstrap4.min.js', 
	'js/bootstrap-multiselect.min.js',
	'js/gijgo.min.js',
    
    //'js/charts-home.js',
	'js/select2.min.js',
    'js/front.js',
    'js/toastr.min.js',
    'js/custom.js',
	'js/sweetalert2.all.min.js',
	    'js/scroll.js',

    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',

    ];
}
