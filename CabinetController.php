<?php


namespace frontend\controllers;


use common\models\Brands;
use common\models\Category;
use common\models\Colors;
use common\models\FavoritesGoods;
use common\models\GoodColorSizes;
use common\models\Goods;
use common\models\GoodsHashtags;
use common\models\GoodsPhotos;
use common\models\Notification;
use common\models\Orders;
use common\models\Requests;
use common\models\Sizes;
use common\models\UserMessageNotifications;
use common\models\UserPreferenceNotifications;
use common\models\UserPreferences;
use frontend\models\AdditionalInformation;
use frontend\models\BaseInfoForm;
use frontend\models\ContactInfoForm;
use frontend\models\GoodForm;
use frontend\models\PasswordForm;
use frontend\models\RequestForm;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\validators\Validator;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

class CabinetController extends BaseController
{

    const PURCHASE_ACTIVE = 'purchase';
    const PURCHASE_HISTORY = 'purchase-history';

    const SALES_ACTIVE = 'sales';
    const SALES_DRAFTS = 'sales-drafts';
    const SALES_HISTORY = 'sales-history';

    const TYPE_REQUEST = 'request';
    const TYPE_GOOD = 'good';
    const TYPE_DRAFT = 'draft';

    public $defaultAction = 'profile';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
//                'only' => ['create-good', 'login'],
                'rules' => [
//                    [
//                        'actions' => ['create-good', 'create-request'],
//                        'allow' => true,
//                        'roles' => ['?'],
//                    ],
                    [
//                        'actions' => ['create-good', 'personal-data', 'profile', 'get-subcat', 'test', 'settings', 'you-goods', 'get-sizes', 'preferences', 'personal-area', 'purchase-sale', 'messages', 'notifications', 'success', 'change-photo', 'create-request'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }


    /**
     * Экшн профиля
     * @return string
     * @throws HttpException
     */
    public function actionProfile()
    {
//        \Yii::warning(print_r(Colors::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC])->union(Brands::find()->where(['is_active' => 1]))->all(), true));

        if (!$userPref = UserPreferences::findOne(['user_id' => \Yii::$app->user->id])) {
            $userPref = new UserPreferences(['user_id' => \Yii::$app->user->id]);
        } else {
            $userPref->cat_ids = $userPref->cat;
        }
        $transaction = \Yii::$app->db->beginTransaction();

        $catObj = Category::find()->where(['is_active' => 1, 'parent_id' => Category::DEFAULT_PARENT])->with('children')->all();
        $preferences = [];

        if ($userPref->load($this->request->post())) {
//            $cat_ids_str = implode(',', $preferencesForm->category);
//            if($userPref->category_ids !== $cat_ids_str) {
//                $userPref->category_ids = $cat_ids_str;
//            }
            $cat_ids_str = implode(',', $userPref->cat_ids);
            if ($userPref->getOldAttribute('category_ids') !== $cat_ids_str) {
                $userPref->category_ids = $cat_ids_str;
            }

            if (!$userPref->save()) {
                \Yii::error(print_r($userPref->getErrors(), true));
                $transaction->rollBack();
                throw new HttpException('500', 'Ошибка сохранения');
            }
            \Yii::$app->session->setFlash('profile', 'done');
        }

        $subcat_fields = [];
        if ($userPref->cat_ids) {
            $category = ArrayHelper::index($catObj, 'id');

            if ($userPref->subcategory_ids) $subcat = $userPref->subcat;
            else $subcat = null;
            foreach ($userPref->cat_ids as $key => $category_id) {
                $field_name = 'subcat_' . $key;
                $subcat_fields[] = $field_name;
                $preferences[$key] = [
                    'label' => $category[$category_id]->name,
                    'list' => ArrayHelper::map($category[$category_id]->children, 'id', 'name'),
                    'field' => $field_name,
                ];
                if ($subcat) {
                    $preferences[$key]['value'] = $subcat;
                }
            }
        }

        $preferencesForm = (new DynamicModel($subcat_fields))->addRule($subcat_fields, 'safe');
        if ($preferencesForm->load($this->request->post())) {
            $subcat_ids = '';
            foreach ($subcat_fields as $key => $attribute) {
                if (!$preferencesForm->$attribute) continue;
                if ($subcat_ids !== '' && $key != 0) $subcat_ids .= ',';
                $subcat_ids .= implode(',', $preferencesForm->$attribute);
            }
//            \Yii::warning(print_r($subcat_ids, true));
            $userPref->subcategory_ids = $subcat_ids;
            if (!$userPref->save()) {
                \Yii::error(print_r($userPref->getErrors(), true));
                $transaction->rollBack();
                throw new HttpException('500', 'Ошибка сохранения');
            }
        }

        if ($subcat_fields) {
            foreach ($subcat_fields as $field) {
                $preferencesForm->$field = $userPref->subcat;
            }
        }

        $userAdditionalInformation = AdditionalInformation::findOne(['user_id' => \Yii::$app->user->id]);
        $userIdentity = \Yii::$app->user->identity;
        $baseInfoForm = new BaseInfoForm([
            'sex' => $userAdditionalInformation->sex,
            'currency' => $userAdditionalInformation->currency,
            'full_name' => $userAdditionalInformation->full_name,
            'date_of_birth' => $userAdditionalInformation->date_of_birth,
            'language' => $userAdditionalInformation->language,
            'biography' => $userAdditionalInformation->biography,
            'username' => $userIdentity->username,
        ]);
        if ($baseInfoForm->load($this->request->post())) {
            if ($baseInfoForm->username != $userIdentity->username) $baseInfoForm->uniqueUsername();
            if ($baseInfoForm->validate()) {
                $userIdentity->username = $baseInfoForm->username;
                $userAdditionalInformation->setAttributes($baseInfoForm->getAttributes());
//                \Yii::warning(print_r($userAdditionalInformation->getAttributes(), true));
//                \Yii::warning(print_r($baseInfoForm->getAttributes(), true));
                if (!$userIdentity->save() || !$userAdditionalInformation->save()) {
                    \Yii::error(print_r($userIdentity->getErrors(), true));
                    \Yii::error(print_r($userAdditionalInformation->getErrors(), true));
                    $transaction->rollBack();
                    throw new HttpException('500', 'Ошибка сохранения данных');
                }
                $transaction->commit();
                \Yii::$app->session->setFlash('profile', 'done');
                return $this->redirect(['profile']);
            }
        }

        $contactInfoForm = new ContactInfoForm([
            'country' => $userAdditionalInformation->country,
            'city' => $userAdditionalInformation->city,
            'email' => $userIdentity->email,
            'telephone' => $userAdditionalInformation->telephone,
            'delivery_address' => $userAdditionalInformation->delivery_address,
        ]);
        if ($contactInfoForm->load($this->request->post())) {
            if ($contactInfoForm->email != $userIdentity->email) $contactInfoForm->uniqueEmail();
            if ($contactInfoForm->validate()) {
                $userIdentity->email = $contactInfoForm->email;
                $userAdditionalInformation->setAttributes($contactInfoForm->getAttributes());
                if (!$userIdentity->save() || !$userAdditionalInformation->save()) {
                    \Yii::error(print_r($userIdentity->getErrors(), true));
                    \Yii::error(print_r($userAdditionalInformation->getErrors(), true));
                    $transaction->rollBack();
                    throw new HttpException('500', 'Ошибка сохранения данных');
                }
                $transaction->commit();
                \Yii::$app->session->setFlash('profile', 'done');
                return $this->redirect(['profile']);
            }
        }

        $transaction->commit();

        /*if($this->request->isAjax) {
            $this->getView()->registerJs("$(\"#base-informations\").niceSelect(\"update\");");
            return $this->renderAjax('profile', [
                'preferencesForm' => $preferencesForm,
                'category' => ArrayHelper::map($catObj, 'id', 'name'),
                'preferences' => $preferences,
                'userPref' => $userPref,
                'baseInfoForm' => $baseInfoForm,
            ]);
        }*/

        return $this->render('profile', [
            'preferencesForm' => $preferencesForm,
            'category' => ArrayHelper::map($catObj, 'id', 'name'),
            'preferences' => $preferences,
            'userPref' => $userPref,
            'baseInfoForm' => $baseInfoForm,
            'contactInfoForm' => $contactInfoForm,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws HttpException
     * @throws \yii\base\Exception
     */
    public function actionChangePhoto()
    {
        $user = \Yii::$app->user->identity;
        $user->validators[] = Validator::createValidator('required', $user, 'photo');
        if ($user->load($this->request->post())) {
            if ($file = UploadedFile::getInstance($user, 'photo')) {
                $file_name = \Yii::$app->security->generateRandomString(20);
                $path = 'images/users/photo';
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                if ($file->saveAs("$path/$file_name.{$file->extension}")) {
                    if (($old_photo = $user->getOldAttribute('photo')) && file_exists("$path/$old_photo")) {
                        unlink("$path/$old_photo");
                    }
                    $user->photo = "$file_name.$file->extension";
                    $user->save();
                    \Yii::$app->session->setFlash('profile', 'done');
                } else {
                    \Yii::error(print_r($user->getErrors(), true));
                    throw new HttpException(500, 'Ошибка сохранения фото');
                }
            }

            return $this->redirect(['profile']);
        }
        return $this->render('change-photo', [
            'user' => $user,
        ]);
    }

    /**
     * Экшн настроек профиля
     * @return string
     */
    public function actionSettings()
    {
        $user = \Yii::$app->user->identity;
        $pwd = new PasswordForm();
        if ($pwd->load($this->request->post()) && $pwd->validate()) {
            $user->setPassword($pwd->new_pass);
            $user->save();
            \Yii::$app->session->setFlash('settings', 'done');
            return $this->redirect(['settings']);
        }

        if (!$msgNotification = UserMessageNotifications::findOne(['user_id' => $user->id])) {
            $msgNotification = new UserMessageNotifications(['user_id' => $user->id]);
        }
        if ($msgNotification->load($this->request->post())) {
            if (!$msgNotification->save()) {
                \Yii::error(print_r($msgNotification->getErrors(), true));
                throw new HttpException(500, 'Ошибка сохранения уведомлений');
            }
            \Yii::$app->session->setFlash('settings', 'done');
            return $this->redirect(['settings']);
        }

        if (!$prefNotification = UserPreferenceNotifications::findOne(['user_id' => $user->id])) {
            $prefNotification = new UserPreferenceNotifications(['user_id' => $user->id]);
        }
        if ($prefNotification->load($this->request->post())) {
            if (!$prefNotification->save()) {
                \Yii::error(print_r($prefNotification->getErrors(), true));
                throw new HttpException(500, 'Ошибка сохранения уведомлений');
            }
            \Yii::$app->session->setFlash('settings', 'done');
            return $this->redirect(['settings']);
        }

        return $this->render('settings', compact('pwd', 'msgNotification', 'prefNotification'));
    }

    public function actionPersonalArea($type = 'wish_list')
    {
        if ($favorites = FavoritesGoods::findAll(['user_id' => \Yii::$app->user->id])) {
            $good_ids = ArrayHelper::getColumn($favorites, 'good_id');
        } else $good_ids = [];

        $dataProvider = new ActiveDataProvider([
            'query' => Goods::find()->where(['id' => $good_ids])->with(['favorites', 'photos', 'brand'])
        ]);

        return $this->render('personal-area', [
            'type' => $type,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionPurchase($type = self::PURCHASE_ACTIVE)
    {
        if ($type !== self::PURCHASE_ACTIVE) {
            $status_list = [
                Orders::STATUS_REFUNDED,
                Orders::STATUS_DECLINED,
                Orders::STATUS_DONE,
            ];
        } else {
            $status_list = [
                Orders::STATUS_PAYMENT_AWAITED,
                Orders::STATUS_PAYMENT_RECEIVED,
                Orders::STATUS_BOUGHT,
                Orders::STATUS_SHIPPING,
            ];
        }
        $purchase = new ActiveDataProvider([
            'query' => Orders::find()
                ->where([
                    'user_id' => \Yii::$app->user->id,
                    'status' => $status_list,
                ])
                ->with(['good']),
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]]
        ]);

        return $this->render('purchase', [
            'type' => $type,
            'dataProvider' => $purchase,
        ]);
    }

    public function actionChangeStatus($id, $new_status)
    {
        if (!$order = Orders::findOne(['id' => $id])) {
            \Yii::error('Такого заказа нету');
            throw new HttpException('404', 'Что-то пошло не так');
        }
        $next_status_list = Orders::getSequenceStatuses($order->status);
//        \Yii::warning(print_r($next_status_list, true));
        if (!in_array(intval($new_status), $next_status_list)) {
            \Yii::error('Недопустимый статус!');
            throw new HttpException('404', 'Что-то пошло не так');
        }
        $order->status = $new_status;
        $order->save();
        \Yii::$app->session->setFlash('done', 'Статус успешно изменён');

        return $this->redirect(\Yii::$app->request->referrer ?: (\Yii::$app->controller->action->id == 'sales-history' ? ['sales-history'] : ['purchase']));
    }

    public function actionSales($type = self::SALES_ACTIVE)
    {
        if ($type == self::SALES_HISTORY) {
            $goods_ids = Goods::find()->select('id')->where(['user_id' => \Yii::$app->user->id])->asArray()->column();
            $orders = new ActiveDataProvider([
                'query' => Orders::find()
                    ->where([
//                        'user_id' => \Yii::$app->user->id, /** TODO: можент нужно добавить в заказ не только ид покупателя, но и ид продавца???? */
//                        'status' => $status_list,
                        'good_id' => $goods_ids
                    ])
                    ->with(['good']),
                'sort' => ['defaultOrder' => ['updated_at' => SORT_DESC]]
            ]);

            return $this->render('sales', [
                'type' => $type,
                'dataProvider' => $orders,
            ]);
        } else {
            if ($type == self::SALES_ACTIVE) {
                $status_list = [
                    Goods::STATUS_PUBLISHED,
                ];
            } elseif ($type == self::SALES_DRAFTS) {
                $status_list = [
                    Goods::STATUS_DRAFT,
                ];
            } else throw new HttpException('404', 'Что-то пошло не так');

            $goods = new ActiveDataProvider([
                'query' => Goods::find()
                    ->where([
                        'user_id' => \Yii::$app->user->id,
                        'status' => $status_list,
                    ])
                    ->with(['photos', 'brand'])
                ,
                'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
                //            'pagination' => ['pageSize' => 1]
            ]);

            return $this->render('sales', [
                'type' => $type,
                'dataProvider' => $goods,
            ]);
        }
    }

    public function actionUpdateGood($id)
    {
        if (!$good = Goods::findOne(['id' => $id, 'user_id' => \Yii::$app->user->id])) throw new \Exception('Такого товара не существует!', '404');
        $goodPhotos = GoodsPhotos::findAll(['good_id' => $good->id]);
        $goodTags = GoodsHashtags::findAll(['good_id' => $good->id]);
        $goodColorSizes = GoodColorSizes::findAll(['good_id' => $good->id]);

        $model = new GoodForm(['good_id' => $good->id, 'scenario' => GoodForm::SCENARIO_PUBLISH]);

        if ($this->request->isAjax && $model->load($this->request->post())) {
//            $model->validate_before_publication();

            $model->validate_before_publication();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($this->request->post())) {
//            if(\Yii::$app->user->isGuest) return $this->redirect(['/site/registration']);
//            if($this->request->post('draft')){
//                return $this->redirect(['success']);
//            }

//            if(!$this->request->post('draft')/* && $this->request->post('draft') == 1*/){
            $model->validate_before_publication();
////                ActiveForm::validate($model);
            if (!$model->validate()) {
                $model->setValues($good, $goodPhotos, $goodTags, $goodColorSizes);
                \Yii::error(print_r($model->getErrors(), true));
                return $this->render('update-good', compact('model'));
            }
//            }
            if (!$id = $model->saveDatas(Goods::STATUS_PUBLISHED, $good, $goodPhotos, $goodTags, $goodColorSizes)) throw new \Exception('Ошибка при сохранении товара!', '500');
//
            return $this->redirect(['/offers/success', 'type' => self::TYPE_GOOD, 'id' => $id]);
        }
        $model->setValues($good, $goodPhotos, $goodTags, $goodColorSizes);
        return $this->render('update-good', compact('model', 'goodColorSizes'));
    }

    public function actionCreateDraft()
    {
        $model = new GoodForm();
        if ($this->request->isAjax && $model->load($this->request->post())) {
            $model->validate_draft();
            if (!$model->validate()) {
                \Yii::error(print_r($model->getErrors(), true));

                \Yii::$app->session->setFlash('validate-good', 'errors');
//                return $this->render('create-good', compact('model'));
//                throw new \Exception('Ошибка при сохранении товара!', '500');

                return Json::encode(['isDone' => 0, 'errors' => $model->getErrorsMessages()]);
            }
            \Yii::warning(print_r($model, true));
            if ($model->good_id) {
                \Yii::warning('good_id');
                if (!$good = Goods::findOne(['id' => $model->good_id, 'user_id' => \Yii::$app->user->id])) return Json::encode(['isDone' => 0, 'errors' => 'Ошибка сохранения черновика!']);
                $goodPhotos = GoodsPhotos::findAll(['good_id' => $good->id]);
                $goodTags = GoodsHashtags::findAll(['good_id' => $good->id]);
                $goodColorSizes = GoodColorSizes::findAll(['good_id' => $good->id]);
                $isSave = $model->saveDatas(Goods::STATUS_DRAFT, $good, $goodPhotos, $goodTags, $goodColorSizes);
            } else $isSave = $model->saveDatas(Goods::STATUS_DRAFT);

            if (!$isSave) return Json::encode(['isDone' => 0, 'errors' => 'Ошибка при сохранении черновика!']);

            return Json::encode(['isDone' => 1]);
        }
//        return $this->redirect(['/site/index']);
        throw new \Exception('Ошибка при сохранении товара!', '500');
    }

    public function actionMessages()
    {
        return $this->render('direct');
    }

    public function actionNotifications()
    {
        if ($notifications = Notification::find()->where(['user_id' => \Yii::$app->user->id])->with('order')->orderBy(['created_at' => SORT_DESC])->limit(10)->all()) {
            foreach ($notifications as $notification) {
                if ($notification->is_viewed) continue;
                $notification->is_viewed = 1;
                $notification->save();
            }
        }
        return $this->render('notification', compact('notifications'));
    }

}
