<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

    if (class_exists('backend\assets\AppAsset')) 
    {
        backend\assets\AppAsset::register($this);
    }
    else 
    {
        app\assets\AppAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>

<?php $this->beginPage() ?>

    <!DOCTYPE html>

    <html lang="<?= Yii::$app->language ?>">

        <head>
            <meta charset="<?= Yii::$app->charset ?>"/>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <?= Html::csrfMetaTags() ?>
            <title><?= Html::encode($this->title) ?></title>
            <link rel="icon" type="image/png" sizes="96x96" href="/faviconcoop.png">
            <?php $this->head() ?>
        </head>

        <body class="hold-transition skin-blue fixed sidebar-mini sidebar-collapse">

            <?php $this->beginBody() ?>

            <div class="wrapper"  id = 'vue-app'>
                <div id="main_preloader" class="main-preloader"></div>

                <?= $this->render(
                    'header.php',
                    [
                        'directoryAsset'    => $directoryAsset
                    ]
                ) ?>

                <?php /*$this->render(
                    'left.php',
                    [
                        'directoryAsset'    => $directoryAsset
                    ]
                )*/
                ?>

                <?= $this->render(
                    'content.php',
                    [
                        'content'           => $content, 
                        'directoryAsset'    => $directoryAsset
                    ]
                ) ?>

            </div>

            <?php $this->endBody() ?>
            <?php 
                $currentDate = app\helpers\particulars\ParticularHelper::getCurrentDay();
                $systemDate = date("Y-m-d", strtotime($currentDate));
                $cutOff = Yii::$app->view->getCutOff();
            ?>
            <script> 
            window.Yii = {
            'csrfToken': "<?php echo Yii::$app->request->csrfToken; ?>",
            'baseUrl': "<?php echo Yii::$app->request->baseUrl; ?>"
            }; 
            window.coopData = {
                'systemDate': "<?php echo $systemDate ?>",
                'cutOffDate': "<?php echo $cutOff ?>"
            }
            </script>
            <script src = '<?php echo Yii::$app->request->baseUrl; ?>/js/main.js'> </script>


        </body>

    </html>

<?php $this->endPage() ?>
