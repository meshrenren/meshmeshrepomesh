<?php
/* @var $this yii\web\View */
$this->title = 'Beginning of Day';

?>
<beginning-of-day
	:base-url='<?= json_encode(Yii::$app->request->baseUrl) ?>'
	:current-date='<?= json_encode($currentDate) ?>'>
</beginning-of-day>
