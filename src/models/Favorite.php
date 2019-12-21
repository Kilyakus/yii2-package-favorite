<?php
namespace kilyakus\package\favorite\models;

class Favorite extends \kilyakus\modules\components\ActiveRecord
{
    public static function tableName()
    {
        return 'favorites';
    }

    public function rules()
    {
        return [
            [['class', 'name'], 'required'],
            ['class', 'string', 'max' => 255],
            ['name', 'string', 'max' => 64],
        ];
    }
}