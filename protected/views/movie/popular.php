<?php
/* @var $this SiteController */

$this->pageTitle = 'Most popular movies (API: movie/popular)';
?>

<h1><?=CHtml::encode($this->pageTitle)?></h1>

<div class="form">
    <div class="errorSummary" style="<?php if(!Yii::app()->user->hasFlash('error')):?>display:none<?php endif;?>">
        <p><?=Yii::app()->user->getFlash('error')?></p>
    </div>
</div>    

<?php 
$columns = [
    array(
        'name'=>'id',
    ),
    array(
        'name'=>'title',
    ),
    array(
        'name'=>'release_date',
    ),
    array(
        'class'=>'CButtonColumn',
        'template'=>'{view}',
        'viewButtonUrl'=>'CHtml::normalizeUrl(array("view","id"=>$data["id"]))', 
    )
];
$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$dataProvider,
    'template'=>'{items}',
    'columns'=>$columns
));
?>

<?php $this->widget('CLinkPager', array(
    'pages' => $pagination,
)) ?>