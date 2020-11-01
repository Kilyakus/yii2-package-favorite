<?php
namespace kilyakus\package\favorite\widgets;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;

use kilyakus\web\widgets as Widget;

use kilyakus\package\favorite\models\Favorite as FavoriteModel;
use kilyakus\package\favorite\models\FavoriteAssign;

class Favorite extends \yii\base\Widget
{
	public $trackButton = [];

	public $untrackButton = [];

	public $scroller = [];

	public $pjax = true;

	public $postUrl;

	public $model;

	public $modelClass;

	public $owner;

	public $ownerClass;

	public $options = [];

	protected $hasOne = false;

	public function init()
	{
		if( empty($this->postUrl)) {

			if( !empty(Yii::$app->getModule('favorites')) ){

				$this->postUrl[] = Url::toRoute(['/favorites']);

			}elseif( 

				file_exists(Yii::getAlias('@vendor') . '/kilyakus/yii2-module-base/src/AdminModule.php') && 

				(
					Yii::$app->getModule('system') || 
					Yii::$app->getModule('admin')
				) &&

				(
					isset(Yii::$app->getModule('system')->activeModules['favorites']) || 
					isset(Yii::$app->getModule('admin')->activeModules['favorites'])
				)

			){

				$this->postUrl[] = Url::toRoute(['/admin/favorites']);

			}

		}

		$this->modelClass = get_class($this->model);

		$this->ownerClass = get_class($this->owner);

		parent::init();
	}

	public function run()
	{
		$searchModel  = \Yii::createObject(FavoriteModel::className());
		$dataProvider = $searchModel->search(\Yii::$app->request->get());
		$dataProvider->query->andFilterWhere(['class' => $this->modelClass]);

		$dataProvider->pagination = false;

		$favorites = $dataProvider->getModels();

		$items = [];

		if($this->pjax){
			Pjax::begin(['timeout' => 15000, 'enablePushState' => false]);
		}

		if($dataProvider->query->count()){

			foreach ($favorites as $key => $favorite) {

				$exists = $favorite->relative($this->model, $this->owner, $favorite->primaryKey)->exists();

				if($exists){
					$this->hasOne = true;
				}

				$html = Html::beginForm($this->postUrl . ($exists ? '/untrack' : '/track'), 'post', ['data-pjax' => 'true']);
				$html .= Html::hiddenInput('FavoriteAssign[class]', $this->modelClass);
				$html .= Html::hiddenInput('FavoriteAssign[item_id]', $this->model->primaryKey);
				$html .= Html::hiddenInput('FavoriteAssign[owner_class]', $this->ownerClass);
				$html .= Html::hiddenInput('FavoriteAssign[owner_id]', $this->owner->primaryKey);
				$html .= Html::hiddenInput('FavoriteAssign[favorite_id]', $favorite->primaryKey);
				$html .= Html::hiddenInput('trackButton', Json::encode($this->trackButton));
				$html .= Html::hiddenInput('untrackButton', Json::encode($this->untrackButton));
				$html .= Html::submitButton($favorite->getTitle($exists),['class' => 'btn btn-block']);
				$html .= Html::endForm();

				$items[] = ['label' => $html, 'url' => null, 'linkOptions' => ['class' => 'p-0']];
				$html = null;

			}

		}

		echo Widget\DropDown::widget([
			'button' => $this->hasOne ? $this->untrackButton : $this->trackButton,
			'items' => $items,
		]);

		if($this->pjax){
			Pjax::end();
		}
	}
}