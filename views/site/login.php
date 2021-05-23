<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;
use yii\bootstrap\ActiveForm;
AppAsset::register($this);
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
	<title>FoodQ Merchant Login</title>
	<link rel="shortcut icon" href="<?php echo Url::base(); ?>/favicon.png" type="image/x-icon" />
	<!-- Meta tag Keywords -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" type="text/css" href="<?= Yii::$app->request->baseUrl.'/css/css/login.css';?>">
</head>

<body>
	<section class="w3l-forms-23">
		<div class="forms23-block-hny">
			<div class="wrapper">
		
				<div class="d-grid forms23-grids">
					<div class="form23">
						<div class="main-bg">
							<h6 class="sec-one">FoodQ Merchant Login</h6>
							<div class="speci-login first-look">
								<img src="<?= Yii::$app->request->baseUrl.'/img/food-q.png';?>" width="100%" alt="" class="img-responsive">
							</div>
						</div>
						<div class="bottom-content">
	<?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'layout' => 'horizontal',
	'options' => [
                'class' => 'form-validate',
         ],
	'fieldConfig' => [
		'template' => "{label}\n{input}\n{hint}\n{error}"
    	],

    ]); ?>
                    <div class="form-group">

				      <?= $form->field($model, 'username')->textInput(['class' => 'input-material','placeholder'=>'Username'])->label(false); ?>

                    </div>
                    <div class="form-group">
				      <?= $form->field($model, 'password')->passwordInput(['class' => 'input-material','placeholder'=>'Password'])->label(false); ?>

                    </div><?= Html::submitButton('Login', ['class' => 'loginhny-btn btn', 'name' => 'login-button']) ?>
                    <!-- This should be submit button but I replaced it with <a> for demo purposes-->
<!--                  </form>-->
				    <?php ActiveForm::end(); ?>
					<p>Forgot Password? <a href="<?= Url::to(['site/forgetpassword']); ?>">Click Here</a></p>
						</div>
					</div>
				</div>
				<div class="w3l-copy-right text-center">
					<p>© 2020 FoodQ Technologies</p>
				</div>
			</div>
		</div>
	</section>
	<!-- //login-section -->
</body>

</html>