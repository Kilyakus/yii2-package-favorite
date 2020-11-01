<?php
namespace kilyakus\package\favorite\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use kilyakus\modules\components\ActiveRecord;
use kilyakus\modules\behaviors\SortableModel;

class Favorite extends ActiveRecord
{
    const STATUS_OFF = 0;
    const STATUS_ON = 1;

    public static function tableName()
    {
        return 'favorites';
    }

    public function rules()
    {
        return [
            [['class', 'title_track', 'title_untrack'], 'required'],
            [['item_id', 'status'], 'integer'],
            [['class', 'name'], 'string', 'max' => 255],
            [['title_track','title_untrack'], 'trim'],
        ];
    }

    public function behaviors()
    {
        return [
            SortableModel::className()
        ];
    }

    public function getTitle($status)
    {
        if($status){
            return Yii::t('easyii', $this->title_untrack);
        }else{
            return Yii::t('easyii', $this->title_track);
        }
    }

    public function relative($model, $owner, $id = null)
    {
        $query = [
            'and',
            ['class' => get_class($model)],
            ['item_id' => $model->primaryKey],
            ['owner_class' => get_class($owner)],
            ['owner_id' => $owner->primaryKey],
            ['favorite_id' => $id],
        ];

        $searchModel  = \Yii::createObject(FavoriteAssign::className());
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $dataProvider->query->andFilterWhere($query);

        return $dataProvider->query;
    }

    public function list($model, $owner, $id = null)
    {
        $query = [
            'and',
            ['class' => get_class($model)],
            ['item_id' => $id],
            ['owner_class' => get_class($owner)],
            ['owner_id' => $owner->primaryKey],
        ];

        $searchModel  = \Yii::createObject(FavoriteAssign::className());
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $dataProvider->query->andFilterWhere($query);

        return $dataProvider;
    }

    public function items($model, $owner, $options = [])
    {
        $className = get_class($model);

        $list = self::list($model, $owner);

        if(!empty($options['where'])){
            $list->query->andFilterWhere($options['where']);
        }
        if(!empty($options['orderBy'])){
            $list->query->orderBy($options['orderBy']);
        }

        $links = ArrayHelper::getColumn($list->query->all(), 'item_id');

        $searchModel  = \Yii::createObject($className);
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $dataProvider->query->where([$className::primaryKey()[0] => $links]);

        return $dataProvider;
    }

    public function search($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['status' => SORT_ASC, 'order_num' => SORT_ASC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query
            ->andFilterWhere(['class' => $this->class])
            ->andFilterWhere(['item_id' => $this->item_id])
            ->andFilterWhere(['like', 'title', $this->title_track])
            ->andFilterWhere(['like', 'title', $this->title_untrack])
            ->andFilterWhere(['status' => $this->status]);

        return $dataProvider;
    }
}