<?php

/**
 * This is the model class for table "{{movie}}".
 *
 * The followings are the available columns in table '{{movie}}':
 * @property integer $id
 * @property string $title
 * @property string $original_title
 * @property string $release_date
 * @property integer $runtime
 * @property string $overview
 * @property string $genres
 * @property string $poster_path
 * @property int $rating
 */
class Movie extends CActiveRecord
{
    /**
     * @var string directory name for storing movie posters
     */
    public static $posterDir = 'posters';
    
    public $file;


    /**
    * @return string the associated database table name
    */
    public function tableName()
    {
            return '{{movie}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('title, original_title, release_date, runtime, overview, genres', 'required'),
            array('runtime, rating', 'numerical', 'integerOnly'=>true),
            array('title, original_title, genres, poster_path', 'length', 'max'=>255),
            array('file', 'file', 'types'=>'jpg,gif,png', 'maxSize'=>2000000, 'allowEmpty'=>true), 
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'original_title' => 'Original Title',
            'release_date' => 'Release Date',
            'runtime' => 'Runtime',
            'overview' => 'Overview',
            'genres' => 'Genres',
            'poster_path' => 'Poster',
            'file' => 'Poster',
            'rating' => 'Your rating',
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Movie the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function getPosterUrl()
    {
        return $this->poster_path ? Yii::app()->baseUrl.'/'.Movie::$posterDir.'/'.$this->poster_path : '';
    }

    protected function beforeDelete() {
        if ($this->poster_path) {
            $posterFile = FileHelper::normalizePath(Yii::getPathOfAlias('webroot.'.Movie::$posterDir).$this->poster_path);
            if (!@unlink($posterFile)) {
                Yii::app()->user->setFlash('error','Can\'t remove the movie poster. Please, notify the website administrator.');
                Yii::log(sprintf('Can\'t remove a poster: %s for a movie with ID: %s',$posterFile,$model->id),CLogger::LEVEL_ERROR);
            }
        }
        return parent::beforeDelete();
    }
                
}
