<?php
/* @var $this SiteController */

$this->pageTitle = $model->title;
?>

<h1><?=CHtml::encode($this->pageTitle)?></h1>

<p>
    <?=CHtml::link('Edit', array('update','id'=>$model->id))?> 
    <?=CHtml::link('Delete', array('delete','id'=>$model->id),array(
        'submit'=>CHtml::normalizeUrl(array('delete','id'=>$model->id)),
        'csrf'=>true,
        'confirm'=>'Do you really want to delete?',
        'style'=>'color: red'
    ))?>
</p>

<div class="form">
    <div class="errorSummary" style="<?php if(!Yii::app()->user->hasFlash('error')):?>display:none<?php endif;?>">
        <p><?=Yii::app()->user->getFlash('error')?></p>
    </div>
</div> 
<?php 
$this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>array(
        'title',
        'original_title',
        'release_date:date',
        'runtime',
        'overview',
        'genres',
        array(               
            'name'=>'poster_path',
            'type'=>'raw',
            'value'=>$model->poster_path ? CHtml::image($model->getPosterUrl()) : ''
        )
    )
));
?>

<div style="margin-top: 15px;">
<?php echo CHtml::activeLabel($model,'rating',array(
    'style'=>'display: block;'
)); ?>
<?php
$csrf = '';
$request = Yii::app()->request;
if($request->enableCsrfValidation) {
    $csrf = '&'.$request->csrfTokenName.'='.$request->csrfToken;
}
$this->widget('CStarRating',array(
    'model'=>$model,
    'attribute'=>'rating',
    'resetValue'=>0,
    'callback'=>'function(){
        $.ajax({
            type: "POST",
            dataType: "json", 
            url: "'.CHtml::normalizeUrl(array('rate','id'=>$model->id)).'",
            data: "'.CHtml::activeName($model, 'rating').'=" + $(this).val() + "'.$csrf.'",
            success: function(data){
                alert(data.msg);
            },
            error: function(jqXHR, textStatus, errorThrown){
                var msg = textStatus+": "+jqXHR.statusText+" "+(jqXHR.responseText ? "("+jqXHR.responseText+")" : "");
                alert(msg);
            }
        }
    )}'
));
?>
</div>

