<?php

namespace istvan0304\imagemanager\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CkImageSearch represents the model behind the search form about `istvan0304\ck-image-manager\models\CkImage`.
 */
class CkImageSearch extends CkImage
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['orig_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param $params
     * @return ActiveDataProvider
     * @throws \Throwable
     */
    public function search($params)
    {
        $query = CkImage::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'orig_name', $this->orig_name]);

        return $dataProvider;
    }
}