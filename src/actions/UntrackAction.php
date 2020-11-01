<?php
namespace kilyakus\package\favorite\actions;

use Yii;
use yii\helpers\Url;
use yii\helpers\Json;

use kilyakus\action\BaseAction as Action;
use kilyakus\package\favorite\models\Favorite as FavoriteModel;
use kilyakus\package\favorite\widgets\Favorite;

class UntrackAction extends Action
{
    public $model;

    public function run()
    {
        if(!($modelClass = $this->model)){
            $this->error = Yii::t('easyii', 'Not found');
        }else{

            $modelName = (new \ReflectionClass($modelClass))->getShortName();
            
            if($post = Yii::$app->request->post($modelName)){

                $class = $post['class'];
                $item_id = $post['item_id'];

                $owner = $post['owner_class'];
                $owner_id = $post['owner_id'];

                $id = $post['favorite_id'];

                $relative = FavoriteModel::relative($class::findOne($item_id), $owner::findOne($owner_id), $id);

                if($relative && $relative->exists()){

                    $relative->one()->delete();

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