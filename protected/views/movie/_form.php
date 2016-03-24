<?php
/* @var $this MovieController */
/* @var $model Movie */
/* @var $form CActiveForm */

$css = <<< CSS
.form .row input[type=text], .form .row textarea {
    width: 100%;    
}
CSS;
Yii::app()->clientScript->registerCss('update-movie',$css);

?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'movie-update-form',
        'htmlOptions'=>array(
            'enctype'=>'multipart/form-data'
        )
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textField($model,'title'); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'original_title'); ?>
		<?php echo $form->textField($model,'original_title'); ?>
		<?php echo $form->error($model,'original_title'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'release_date'); ?>
                <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                    'name'=>CHtml::activeName($model,'release_date'),
                    'value'=>Yii::app()->dateFormatter->format('dd.MM.yyyy',$model->release_date),
                    // additional javascript options for the date picker plugin
                    'options'=>array(
                        'showAnim'=>'fold',
                        'dateFormat'=>'dd.mm.yy'
                    ),
                    'htmlOptions'=>array(
                        'style'=>'height:20px; width: 100px;'
                    ),
                ));?>
                <?php echo $form->error($model,'release_date'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'runtime'); ?>
		<?php echo $form->textField($model,'runtime'); ?>
		<?php echo $form->error($model,'runtime'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'overview'); ?>
                <?php echo $form->textArea($model,'overview',array(
                    'rows'=>10
                )); ?>
		<?php echo $form->error($model,'overview'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'genres'); ?>
		<?php echo $form->textField($model,'genres'); ?>
		<?php echo $form->error($model,'genres'); ?>
	</div>

	<div class="row">
                <?php if($model->poster_path):?><?=CHtml::image($model->getPosterUrl())?><?php endif;?>
		<?php echo $form->labelEx($model,'file'); ?>
		<?php echo $form->fileField($model,'file'); ?>
		<?php echo $form->error($model,'file'); ?>
	</div>

	<div class="row buttons" style="margin-top:15px">
		<?php echo CHtml::submitButton('Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->