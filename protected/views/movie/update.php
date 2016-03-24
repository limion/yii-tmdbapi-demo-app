<?php
/* @var $this MovieController */
/* @var $model Movie */
/* @var $form CActiveForm */

$this->pageTitle = 'Edit: '.$model->title;
?>

<h1><?=CHtml::encode($this->pageTitle)?></h1>

<p>
    <?=CHtml::link('View', array('view','id'=>$model->id))?> 
</p>

<?php $this->renderPartial('_form', array(
    'model'=>$model
))?>