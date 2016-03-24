<?php

use GuzzleHttp\Client;

class MovieController extends Controller
{
    public $defaultAction = 'popular';
    public $layout = '//layouts/column2';
    
    public function init() {
        parent::init();
        $this->menu = array(
            array('label'=>'Popular movies','url'=>array('popular')),
            array('label'=>'Top rated movies','url'=>array('toprated')),
            array('label'=>'Movies released 2 month ago or later','url'=>array('now'))
        );
    }
    
    public function filters() {
	return array(
            'accessControl'
	);
    }
    
    public function accessRules() {
	return array(
            array('allow',
                'users'=>array('@'),
            ),
            array('deny')
        );
    }
    
    public function actionPopular($page = null)
    {
        $apiParams = [];
        if ($page) {
            $apiParams['page'] = $page;
        }
        
        list($movies,$moviesTotal,$perPage) = $this->moviesProvider('getMoviePopular',$apiParams);
        
        $pagination = new CPagination($moviesTotal);
        $pagination->pageSize = $perPage;
        $dataProvider = new CArrayDataProvider($movies,array(
            'keyField'=>'id',
            'pagination'=>false
        ));

        $this->render('popular',array(
            'dataProvider'=>$dataProvider,
            'pagination'=>$pagination
        ));
            
    }
    
    public function actionToprated($page = null)
    {
        $apiParams = [];
        if ($page) {
            $apiParams['page'] = $page;
        }
        
        list($movies,$moviesTotal,$perPage) = $this->moviesProvider('getMovieTopRated',$apiParams);
        
        $pagination = new CPagination($moviesTotal);
        $pagination->pageSize = $perPage;
        $dataProvider = new CArrayDataProvider($movies,array(
            'keyField'=>'id',
            'pagination'=>false
        ));

        $this->render('toprated',array(
            'dataProvider'=>$dataProvider,
            'pagination'=>$pagination
        ));
            
    }
    
    public function actionNow($page = null)
    {
        $date = new DateTime;
        $date->sub(new DateInterval('P2M'));
        $apiParams = array(
            'primary_release_date.gte'=>$date->format('Y-m-d'),
            'primary_release_date.lte'=>date('Y-m-d')    
        );
        if ($page) {
            $apiParams['page'] = $page;
        }
        
        list($movies,$moviesTotal,$perPage) = $this->moviesProvider('getDiscoverMovie',$apiParams);
        
        $pagination = new CPagination($moviesTotal);
        $pagination->pageSize = $perPage;
        $dataProvider = new CArrayDataProvider($movies,array(
            'keyField'=>'id',
            'pagination'=>false
        ));

        $this->render('now',array(
            'dataProvider'=>$dataProvider,
            'pagination'=>$pagination
        ));
            
    }
    
    public function actionView($id) {
        $model = Movie::model()->findByPk($id);
        if (null === $model) {
            $model = new Movie;
            // get from TMDb and save to the database
            $result = array();
            try {
                $result = Yii::app()->tmdb->setApiKey(Yii::app()->user->id)->getMovie($id);
            } catch (TmdbDemo\ApiException $e) {
                if (34 == $e->getCode()) {
                    throw new CHttpException(404,sprintf('The requested movie (ID:%s) is not found',$id));
                }
                Yii::app()->user->setFlash('error','Something went wrong. Can\'t get the movie. Please, notify the website administrator.');
                Yii::log(LogHelper::buildApiMessage($e),CLogger::LEVEL_ERROR);
            }
            if ($result) {
                $attributes = array_intersect_key($result, $model->attributes);
                $attributes['genres'] = implode(', ',array_values(CHtml::listData($attributes['genres'], 'id', 'name')));
                $model->attributes = $attributes;
                $model->id = $result['id'];
                
                // save the poster
                $posterDir = Yii::getPathOfAlias('webroot.'.Movie::$posterDir);
                if (!is_dir($posterDir)) {
                    if(!CFileHelper::createDirectory($posterDir)) {
                        Yii::app()->user->setFlash('error','Can\'t create a directory for storing posters. Please, notify the website administrator.');
                        Yii::log(sprintf('Can\'t create a directory for storing posters: %s',$posterDir),CLogger::LEVEL_ERROR);
                    }
                }
                if (is_dir($posterDir)) {
                    try {
                        $tmdbConfig = Yii::app()->user->getState('tmdbConfig');
                        $posterUrl = $tmdbConfig['images']['base_url'].$tmdbConfig['images']['poster_sizes'][0].$attributes['poster_path'];
                        $posterFile = FileHelper::normalizePath($posterDir.$attributes['poster_path']);
                        $client = new Client();
                        $client->request('GET', $posterUrl, ['sink' => $posterFile]);
                    } catch (RequestException $e) {
                        Yii::app()->user->setFlash('error','Can\'t save the movie poster. Please, notify the website administrator.');
                        Yii::log(sprintf('Can\'t save a poster %s for a movie with ID: %s. %s',$posterUrl,$model->id,$e->getMessage()),CLogger::LEVEL_ERROR);
                    }
                    if($model->save(false)) {
                        $this->refresh();
                    }
                }
                Yii::app()->user->setFlash('error','Something went wrong. Can\'t save the movie. Please, notify the website administrator.');
            }
        }
        
        $this->render('view',array(
            'model'=>$model
        ));
    }
    
    public function actionUpdate($id)
    {
        if (null === $model = Movie::model()->findByPk($id)) {
            throw new CHttpException(404,sprintf('The requested movie (ID:%s) is not found',$id));
        }
        if (isset($_POST['Movie'])) {
            $oldPoster = $model->poster_path;
            $model->attributes = $_POST['Movie'];
            $model->release_date = Yii::app()->dateFormatter->format('yyyy-MM-dd',CDateTimeParser::parse($model->release_date,'dd.MM.yyyy'));
            $posterDir = Yii::getPathOfAlias('webroot.'.Movie::$posterDir);
            $model->file = CUploadedFile::getInstance($model,'file');
            if ($model->validate()) {
                if ($model->file) {
                    $model->poster_path = $model->file->getName();
                    $posterFile = FileHelper::normalizePath($posterDir.'/'.$model->file->getName());
                    if(!$model->file->saveAs($posterFile)) {
                        Yii::app()->user->setFlash('error','Can\'t save movie poster. Please, notify the website administrator.');
                        Yii::log(sprintf('Can\'t save a poster: %s for a movie with ID: %s',$posterFile,$model->id),CLogger::LEVEL_ERROR);
                    }
                    if ($oldPoster) {
                        $posterFile = FileHelper::normalizePath($posterDir.'/'.$oldPoster);
                        if(!@unlink($posterFile)) {
                            Yii::log(sprintf('Can\'t remove an old poster: %s for a movie with ID: %s',$posterFile,$model->id),CLogger::LEVEL_ERROR);
                        }    
                    }
                }
                $model->save(false);
                $this->redirect(array('view','id'=>$id));
            }
            $model->poster_path = $oldPoster;
        }
        
        $this->render('update',array(
            'model'=>$model
        ));
    }
    
    public function actionDelete($id)
    {
        if (empty(Yii::app()->request->isPostRequest)) {
            throw new CHttpException('400','Unsupported request');
        }
        if (null === $model = Movie::model()->findByPk($id)) {
            throw new CHttpException(404,sprintf('The requested movie (ID:%s) is not found',$id));
        }
        $model->delete();
        $this->redirect(array('popular'));
    }
    
    public function actionRate($id)
    {
        $response = array(
            'success'=>true,
            'msg'=>'Thank you for your vote!'
        );
        if (empty(Yii::app()->request->isPostRequest)) {
            $response = array(
                'success'=>false,
                'msg'=>'Unsupported request'
            );
        }
        elseif (null === $model = Movie::model()->findByPk($id)) {
            $response = array(
                'success'=>false,
                'msg'=>sprintf('The requested movie (ID:%s) is not found',$id)
            );
        }
        elseif (isset($_POST['Movie'])) {
            $model->attributes = $_POST['Movie'];
            try {
                $parameters = array(
                    'guest_session_id'=>Yii::app()->user->getState('guest_session_id')
                );
                if ($model->rating) {
                    $result = Yii::app()->tmdb->setApiKey(Yii::app()->user->id)->setMovieRating($id,$parameters,$model->rating);
                }
                else {
                    $result = Yii::app()->tmdb->setApiKey(Yii::app()->user->id)->unsetMovieRating($id,$parameters);
                }
            } catch (TmdbDemo\ApiException $e) {
                $response = array(
                    'success'=>false,
                    'msg'=>'Something went wrong. Can\'t rate the movie. Please, notify the website administrator.'
                );
                Yii::log(LogHelper::buildApiMessage($e),CLogger::LEVEL_ERROR);
                echo json_encode($response);
                Yii::app()->end();
            }
            // save model
            $model->save(false);
        }
        echo json_encode($response);
    }
    
    protected function moviesProvider($method,$apiParams = [])
    {
        $movies = array();
        $moviesTotal = 0;
        $perPage = 0;
        try {
            $result = Yii::app()->tmdb->setApiKey(Yii::app()->user->id)->$method($apiParams);
            $movies = $result['results'];
            $moviesTotal = $result['total_results'];
            $perPage = count($movies);
        } catch (TmdbDemo\ApiException $e) {
            Yii::app()->user->setFlash('error','Something went wrong. Can\'t get movie list. Please, notify the website administrator.');
            Yii::log(LogHelper::buildApiMessage($e),CLogger::LEVEL_ERROR);
        }  
        return array($movies,$moviesTotal,$perPage);
    }

}