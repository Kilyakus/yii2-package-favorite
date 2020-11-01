<?php
namespace kilyakus\package\favorite\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\Url;

use kilyakus\package\favorite\actions\TrackAction;
use kilyakus\package\favorite\actions\UntrackAction;
use kilyakus\package\favorite\models\FavoriteAssign;

class FavoritesController extends \kilyakus\controller\BaseController
{
    public function actions()
    {
        return [
            'track' => [
                'class' => TrackAction::className(),
                'model' => FavoriteAssign::className(),
            ],
            'untrack' => [
                'class' => UntrackAction::className(),
                'model' => FavoriteAssign::className(),
            ],
        ];
    }
}