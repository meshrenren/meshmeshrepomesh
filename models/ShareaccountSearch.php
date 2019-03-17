<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Shareaccount;

/**
 * ShareaccountSearch represents the model behind the search form about `app\models\Shareaccount`.
 */
class ShareaccountSearch extends Shareaccount
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountnumber', 'fk_memid', 'date_created', 'is_active', 'no_of_shares', 'totalSubscription', 'balance', 'status', 'fk_share_product'], 'required'],
            [['fk_memid', 'is_active', 'no_of_shares', 'fk_share_product'], 'integer'],
            [['date_created'], 'safe'],
            [['totalSubscription', 'balance'], 'number'],
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
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Shareaccount::find();

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
            'fk_memid' => $this->fk_memid,
            'no_of_shares' => $this->no_of_shares,
            'totalSubscription' => $this->totalSubscription,
            'balance' => $this->balance,
            'dateCreated' => $this->dateCreated,
        ]);

        $query->andFilterWhere(['like', 'accountnumber', $this->accountnumber])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
