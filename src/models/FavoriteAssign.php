<?php
namespace kilyakus\package\favorite\models;

use Yii;
use yii\data\ActiveDataProvider;

class FavoriteAssign extends \kilyakus\modules\components\ActiveRecord
{
    public static function tableName()
    {
        return 'favorites_assign';
    }

    public function rules()
    {
        return [
            [['class', 'item_id', 'owner_class', 'owner_id'], 'required'],
            [['class', 'owner_class'], 'string', 'max' => 255],
            [['item_id', 'owner_id', 'favorite_id'], 'integer'],
            ['favorite', 'safe'],
        ];
    }

    public function getName()
    {
        $favorite = Favorite::findOne([
            'and', 
            ['class' => $this->class], 
            ['favorite_id' => $this->favorite_id]
        ]);

        return $favorite->name;
    }

    public function getTitle()
    {
        $favorite = Favorite::findOne([
        	'and', 
        	['class' => $this->class], 
        	['favorite_id' => $this->favorite_id]
        ]);

        if($this->hasOne(FavoriteAssign::className(), [
        	'and', 
        	['class' => $this->class], 
        	['item_id' => $this->item_id], 
        	['owner_class' => $this->owner_class], 
        	['owner_id' => $this->owner_id],
        	['favorite_id' => $this->favorite_id]
        ])){
            return $favorite->title_untrack;
        }else{
            return $favorite->title_track;
        }
    }

    public function getOwner()
    {
        $ownerClass = $this->owner_class;

        return $this->hasOne($ownerClass::className(), ['id' => 'owner_id']);
    }

    public function search($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query
            ->andFilterWhere(['class' => $this->class])
            ->andFilterWhere(['item_id' => $this->item_id])
            ->andFilterWhere(['owner_class' => $this->class])
            ->andFilterWhere(['owner_id' => $this->item_id]);

        return $dataProvider;
    }
}