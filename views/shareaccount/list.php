<?php
/* @var $this yii\web\View */
$this->title = 'Share Accounts List';
?>
<share-list
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'>
</share-list>
