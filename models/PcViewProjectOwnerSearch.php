<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PcViewProjectOwner;

/**
 * PcViewProjectOwnerSearch represents the model behind the search form of `app\models\PcViewProjectOwner`.
 */
class PcViewProjectOwnerSearch extends PcViewProjectOwner
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'project_id'], 'integer'],
            [['name', 'lastname', 'office', 'post', 'tel'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PcViewProjectOwner::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
        ]);

        $query->andFilterWhere(['ilike', 'name', $this->name])
            ->andFilterWhere(['ilike', 'lastname', $this->lastname])
            ->andFilterWhere(['ilike', 'office', $this->office])
            ->andFilterWhere(['ilike', 'post', $this->post])
            ->andFilterWhere(['ilike', 'tel', $this->tel]);

        return $dataProvider;
    }
}
