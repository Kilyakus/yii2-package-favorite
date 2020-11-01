<?php
namespace kilyakus\package\favorite\behaviors;

use Yii;
use yii\db\ActiveRecord;
use kilyakus\package\favorite\models\Favorite;
use kilyakus\package\favorite\models\FavoriteAssign;

class FavoriteBehavior extends \yii\base\Behavior
{
    private $_favorites;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    public function getFavoritesAssigns()
    {
        return $this->owner->hasMany(FavoriteAssign::className(), ['item_id' => $this->owner->primaryKey()[0]])->where(['class' => get_class($this->owner)]);
    }

    public function getFavorites()
    {
        return $this->owner->hasMany(Favorite::className(), ['favorite_id' => 'favorite_id'])->via('favoriteAssigns');
    }

    public function getFavoriteNames()
    {
        return implode(', ', $this->getFavoritesArray());
    }

    public function setFavoriteNames($values)
    {
        $this->_favorites = $this->filterFavoriteValues($values);
    }

    public function getFavoritesArray()
    {
        if($this->_favorites === null){
            $this->_favorites = [];
            foreach($this->owner->favorites as $favorite) {
                $this->_favorites[] = $favorite->name;
            }
        }
        return $this->_favorites;
    }

    public function afterSave()
    {
        if(!$this->owner->isNewRecord && $this->owner->favoriteNames || !$this->owner->favoriteNames) {
            $this->beforeDelete();
        }

        if(count($this->_favorites)) {
            $favoriteAssigns = [];
            $modelClass = get_class($this->owner);

            foreach ($this->_favorites as $name) {
                if (!($favorite = Favorite::findOne(['name' => $name]))) {
                    $favorite = new Favorite(['name' => $name]);
                }
                $favorite->frequency++;

                if ($favorite->save()) {
                    $updatedFavorites[] = $favorite;
                    $favoriteAssigns[] = [$modelClass, $this->owner->primaryKey, $favorite->favorite_id];
                }
            }

            if(count($favoriteAssigns)) {
                Yii::$app->db->createCommand()->batchInsert(FavoriteAssign::tableName(), ['class', 'item_id', 'favorite_id'], $favoriteAssigns)->execute();
                $this->owner->populateRelation('favorites', $updatedFavorites);
            }
        }
    }

    public function beforeDelete()
    {
        $pks = [];

        foreach($this->owner->favorites as $favorite){
            $pks[] = $favorite->primaryKey;
        }

        if (count($pks)) {
            Favorite::updateAllCounters(['frequency' => -1], ['in', 'favorite_id', $pks]);
        }
        Favorite::deleteAll(['frequency' => 0]);
        FavoriteAssign::deleteAll(['class' => get_class($this->owner), 'item_id' => $this->owner->primaryKey]);
    }

    public function filterFavoriteValues($values)
    {
        return array_unique(preg_split(
            '/\s*,\s*/u',
            preg_replace('/\s+/u', ' ', is_array($values) ? implode(',', $values) : $values),
            -1,
            PREG_SPLIT_NO_EMPTY
        ));
    }
}