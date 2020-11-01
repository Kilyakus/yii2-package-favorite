<?php
namespace kilyakus\package\favorite\actions;

use Yii;
use yii\helpers\Url;
use yii\helpers\Json;
use kilyakus\action\BaseAction as Action;

use kilyakus\package\favorite\models\Favorite as FavoriteModel;
use kilyakus\package\favorite\widgets\Favorite;

class TrackAction extends Action
{
    public $model;

    public function run()
    {
        $success = null;

        if(!($modelClass = $this->model)){

            $this->error = Yii::t('easyii', 'Action property `model` is empty');

        }else{

            $model = new $modelClass;

            $modelName = (new \ReflectionClass($model))->getShortName();

            if($post = Yii::$app->request->post($modelName)){

                $class = $post['class'];
                $item_id = $post['item_id'];

                $owner = $post['owner_class'];
                $owner_id = $post['owner_id'];

                $id = $post['favorite_id'];

                $relative = FavoriteModel::relative($class::findOne($item_id), $owner::findOne($owner_id), $id);

                if($relative && !$relative->exists()){

                    if(($model->class = $class) && $class::findOne($item_id)){
                        $model->item_id = $item_id;
                    }else{
                        $this->error = Yii::t('easyii', 'Create error. {0}', $model->formatErrors());
                    }

                    if(($model->owner_class = $owner) && $owner::findOne($owner_id)){
                        $model->owner_id = $owner_id;
                    }else{
                        $this->error = Yii::t('easyii', 'Create error. {0}', $model->formatErrors());
                    }

                    $model->favorite_id = $id;

                    $model->save();

                }
                
                return Favorite::widget([
                    'pjax' => false,
                    'postUrl' => Url::toRoute(['/admin/favorites']),
                    'model' => $class::findOne($item_id),
                    'owner' => $owner::findOne($owner_id),
                    'trackButton' => Json::decode(Yii::$app->request->post('trackButton')),
                    'untrackButton' => Json::decode(Yii::$app->request->post('untrackButton')),
                ]);

            }else{

                return $this->back();

            }
        }
    }
}